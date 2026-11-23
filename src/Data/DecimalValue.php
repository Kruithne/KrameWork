<?php
	namespace KrameWork\Data;
	require_once(__DIR__ . '/Value.php');

	/**
	 * Class DecimalValue
	 * A decimal value
	 * @author docpify <morten@runsafe.no>
	 * @extends Value<float|null>
	 */
	class DecimalValue extends Value
	{
		public static string $decimalSeparator = ',';
		public static string $thousandsSeparator = ' ';

		/**
		 * DecimalValue constructor.
		 * @param int|float|string|null $value Value to be cast to float and encapsulated
		 */
		public function __construct(mixed $value) {
			parent::__construct($value === null ? null : (float) $value);
		}

		/**
		 * Utility function for sorting a list of values
		 * @param $to Value|mixed A value to compare against
		 * @return int|float Sorting index
		 */
		public function compare(mixed $to): int|float {
			$a = $this->value;
			$b = $to instanceof Value ? $to->real() : $to;

			if (!\is_scalar($b))
				return $a === null ? 0 : 1;
			if ($a === null)
				return -1;

			return $a <=> (float) $b;
		}

		/**
		 * @return string The encapsulated value as presented by a string
		 */
		public function __toString(): string {
			return $this->format(2);
		}

		protected function format(int $decimals): string {
			if ($this->value === null)
				return '';

			return \number_format($this->value, $decimals, static::$decimalSeparator, static::$thousandsSeparator);
		}
	}