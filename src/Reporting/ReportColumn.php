<?php
	namespace KrameWork\Reporting;

	/**
	 * @property string label
	 * @property string type
	 * @author docpify <morten@runsafe.no>
	 */
	final class ReportColumn implements \JsonSerializable
	{
		const COL_NONE = '';
		const COL_STRING = 'String';
		const COL_DECIMAL = 'Decimal';
		const COL_INTEGER = 'Integer';
		const COL_DATETIME = 'Datetime';
		const COL_DATE = 'Date';
		const COL_CUSTOM = 'Custom';
		const COL_CURRENCY = 'Currency';

		/**
		 * SQLReportColumn constructor.
		 * @param string $label Column label, as presented to the user
		 * @param string $type Column type, use SQLReportColumn COL_ constants.
		 * @throws \Exception
		 */
		public function __construct(string $label, $type = self::COL_STRING) {
			switch ($type) {
				case self::COL_NONE:
				case self::COL_STRING:
				case self::COL_DECIMAL:
				case self::COL_INTEGER:
				case self::COL_DATETIME:
				case self::COL_DATE:
				case self::COL_CUSTOM:
				case self::COL_CURRENCY:
					break;
				default:
					throw new \Exception('Invalid type "' . $type . '" specified for column');
			}

			$this->label = $label;
			$this->type = $type;
		}

		public $label;
		public $type;

		public function jsonSerialize(): mixed {
			return ['Label' => $this->label, 'Type' => $this->type];
		}
	}