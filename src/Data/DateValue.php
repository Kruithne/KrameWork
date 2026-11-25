<?php
	namespace KrameWork\Data;
	require_once(__DIR__ . '/Value.php');

	/**
	 * Class DateValue
	 * A date value
	 * @extends Value<int|null>
	 */
	class DateValue extends Value
	{
		/**
		 * DateValue constructor.
		 * @param string|int|float|null $value String to be converted to a timestamp, or a unix timestamp
		 */
		public function __construct(mixed $value) {
			if ($value !== null && !\is_numeric($value))
				$value = \strtotime($value);

			parent::__construct(\is_numeric($value) ? (int) $value : null);
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
			if (!($to instanceof DateValue))
				$to = new DateValue(\is_string($to) || \is_int($to) || \is_float($to) ? $to : null);

			$a = $this->value;
			$b = $to->real();

			if ($b === null)
				return $a === null ? 0 : 1;
			if ($a === null)
				return -1;

			// Dates should be compared without time
			return (int) \date('Ymd', $a) - (int) \date('Ymd', $b);
		}

		/**
		 * @return string The encapsulated value as presented by a string
		 */
		public function __toString(): string {
			return $this->value === null ? '' : \date('d.m.Y', $this->value);
		}
	}