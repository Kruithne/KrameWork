<?php
	namespace KrameWork\Reporting;

	/**
	 * Class SQLReportResults
	 * @property string $hash
	 * @property array|ReportRow[] $data
	 */
	class ReportResults
	{
		/**
		 * ReportResults constructor.
		 * @param array|ReportRow[] $data The contents of the report
		 */
		public function __construct(public array $data) {
			$this->hash = \sha1(\serialize($data));
		}

		public string $hash;
	}