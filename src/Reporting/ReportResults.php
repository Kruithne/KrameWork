<?php
	namespace KrameWork\Reporting;

	/**
	 * Class SQLReportResults
	 * @property string $hash
	 * @property array|ReportRow[] $data
	 * @author docpify <morten@runsafe.no>
	 */
	class ReportResults
	{
		/**
		 * ReportResults constructor.
		 * @param array|ReportRow[] $data The contents of the report
		 */
		public function __construct(array $data) {
			$this->data = $data;
			$this->hash = \sha1(\serialize($data));
		}

		public string $hash;
		public array $data;
	}