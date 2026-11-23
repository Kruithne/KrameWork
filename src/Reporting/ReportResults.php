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
		 * @param $data array|ReportRow[] The contents of the report
		 */
		public function __construct($data) {
			$this->data = $data;
			$this->hash = \sha1(\serialize($data));
		}

		public $hash;
		public $data;
	}