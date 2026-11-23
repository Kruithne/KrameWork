<?php
	namespace KrameWork\Data;
	require_once(__DIR__ . '/Value.php');

	/**
	 * Class DateValue
	 * A date value
	 * @author docpify <morten@runsafe.no>
	 */
	class DateValue extends Value
	{
		/**
		 * DateValue constructor.
		 * @param string|int|null $value String to be converted to a timestamp, or a unix timestamp
		 */
		public function __construct(mixed $value) {
			parent::__construct(\is_numeric($value) || $value === null ? $value : \strtotime($value));
		}

		/**
		 * Convert the timestamp into a format suitable for exporting in JSON
		 * @return null|string The timestamp formatted according to ISO 8601, or null
		 */
		public function json(): ?string {
			// Use ISO 8601 format to support moment.js client side
			return $this->value === null ? null : \date('c', $this->value);
		}

		/**
		 * Utility function for sorting a list of values
		 * @param $to Value|mixed A value to compare against
		 * @return int|float Sorting index
		 */
		public function compare(mixed $to): int|float {
			// Dates should be compared without time
			$a = !\is_numeric($this->value) ? null : \date('Ymd', $this->value);
			if ($to instanceof DateValue)
				$b = !\is_numeric($to->real()) ? null : \date('Ymd', $to->real());
			else
				$b = $to;

			if ($b === null)
				return $a === null ? 0 : 1;
			else if (!\is_numeric($b))
				$b = \date('Ymd', \strtotime($b));

			if ($a === null)
				return -1;

			return $a - $b;
		}

		/**
		 * @return string The encapsulated value as presented by a string
		 */
		public function __toString(): string {
			return \date('d.m.Y', $this->value);
		}
	}