<?php
	namespace KrameWork\Reporting;

	use KrameWork\Caching\IDataCache;
	use KrameWork\Database\Driver\Generic;

	/**
	 * Class SQLReportRunner
	 * Encapsulates executing and caching data from SQL
	 * @author docpify <morten@runsafe.no>
	 * @package KrameWork\Reporting
	 */
	abstract class SQLReport extends ReportRunner
	{
		/**
		 * SQLReport constructor.
		 * @api __construct
		 * @param IDataCache $cache A cache to hold report data
		 * @param Generic $db A database access object ie. Database
		 * @param string $sql An SQL Query
		 * @param array $param Query parameters
		 * @param int $cacheTTL Number of seconds to store the results in cache.
		 */
		public function __construct(IDataCache $cache, Generic $db, string $sql, array $param = [], int $cacheTTL = 300) {
			$this->db = $db;
			$this->sql = $sql;
			$this->param = $param;
			parent::__construct($cache, \sha1(\serialize([$db, $sql, $param])), $cacheTTL);
		}

		/**
		 * Executes the report, storing the results in APC
		 * @api run
		 */
		protected function run() {
			return $this->db->getAll($this->sql, $this->param);
		}

		/**
		 * Format data values for presentation based on column type
		 * @param \stdClass[] $data
		 * @return ReportRow[]
		 */
		protected function postProcess(array $data) {
			$filters = $this->getFilters();
			$output = [];
			foreach ($data as $row) {
				foreach ($filters as $filter)
					$filter($row);
				$output[] = new ReportRow($row);
			}
			return $output;
		}

		/**
		 * @var Generic Database engine
		 */
		private $db;

		/**
		 * @var string SQL Query
		 */
		private $sql;

		/**
		 * @var array SQL Parameters
		 */
		private $param;
	}
