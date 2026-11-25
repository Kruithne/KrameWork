<?php
	namespace KrameWork\Reporting;

	require_once(__DIR__.'/ReportResults.php');
	require_once(__DIR__.'/ReportColumn.php');
	require_once(__DIR__.'/../Data/CurrencyValue.php');
	require_once(__DIR__.'/../Data/IntegerValue.php');
	require_once(__DIR__.'/../Data/DateTimeValue.php');
	require_once(__DIR__.'/../Data/DateValue.php');
	require_once(__DIR__.'/../Data/DecimalValue.php');
	require_once(__DIR__.'/../Data/StringValue.php');

	use KrameWork\Caching\IDataCache;
	use KrameWork\Data\CurrencyValue;
	use KrameWork\Data\Value;

	/**
	 * Class SQLReportRunner
	 * Encapsulates executing and caching data from SQL
	 * @author docpify <morten@runsafe.no>
	 */
	abstract class ReportRunner
	{
		/**
		 * ReportRunner constructor.
		 * @param IDataCache $cache The cache location for the report data
		 * @param string $key The key to access the cached report data
		 * @param int $cacheTTL The number of seconds the report should remain cached
		 */
		public function __construct(protected IDataCache $cache, protected string $key, protected int $cacheTTL = 300) {}

		/**
		 * Executes the report, return the data to be processed and stored in cache.
		 * Override this to implement your report.
		 * @return (array<string, mixed>|object)[]
		 */
		protected abstract function run(): array;

		/**
		 * Executes the report if necessary and returns the cached result set.
		 * @return ReportResults The data set returned by the SQL
		 */
		public function data(): ReportResults {
			$data = $this->cache->__get($this->key);
			if ($data instanceof ReportResults)
				return $data;

			$data = new ReportResults($this->postProcess($this->run()));
			$this->cache->store($this->key, $data, $this->cacheTTL);
			return $data;
		}

		/**
		 * Flushes cached data, forcing a fresh run of the report
		 */
		public function clear(): void {
			$this->cache->__unset($this->key);
		}

		/**
		 * @return ReportColumn[]
		 */
		public abstract function columns(): array;

		protected function currency(): ?string {
			return null;
		}

		/**
		 * @return \Closure[] Filters in the format function(&$row)
		 */
		protected function getFilters(): array {
			$filters = [];
			foreach ($this->columns() as $key => $col) {
				switch ($col->type) {
					case ReportColumn::COL_CURRENCY:
						$filters[] = $this->makeCurrencyFilter($key, $col);
						break;
					case ReportColumn::COL_DECIMAL:
					case ReportColumn::COL_INTEGER:
					case ReportColumn::COL_DATETIME:
					case ReportColumn::COL_DATE:
						$filters[] = $this->makeFilter($key, 'KrameWork\\Data\\'. $col->type . 'Value');
						break;
				}
			}
			return $filters;
		}

		/**
		 * @param string $key Column name
		 * @param string $class Column container class
		 * @return \Closure
		 */
		protected function makeFilter(string $key, string $class): \Closure {
			return function (&$row) use ($key, $class) {
				if (!isset($row->$key) || $row->$key === '' || $row->$key instanceof Value)
					return;
				$row->{$key} = new $class($row->{$key});
			};
		}

		protected function makeCurrencyFilter(string $key, ReportColumn $col): \Closure {
			$fallback = $col->currency ?? $this->currency();
			$field = $col->currencyField;
			return function (&$row) use ($key, $field, $fallback) {
				$value = $row->$key ?? null;
				if ($value === '' || !(\is_int($value) || \is_float($value) || \is_string($value)))
					return;

				$code = $field === null ? null : ($row->$field ?? null);
				if (!\is_string($code) || $code === '')
					$code = $fallback;

				$row->{$key} = new CurrencyValue($value, $code);
			};
		}

		/**
		 * Format data values for presentation based on column type
		 * @param (array<string, mixed>|object)[] $data
		 * @return ReportRow[]
		 */
		protected function postProcess(array $data): array {
			$filters = $this->getFilters();
			$output = [];
			foreach ($data as $row) {
				foreach ($filters as $filter)
					$filter($row);
				$output[] = new ReportRow($row);
			}
			return $output;
		}
	}
