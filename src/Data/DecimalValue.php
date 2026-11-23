<?php
	namespace KrameWork\Data;
	require_once(__DIR__ . '/Value.php');

	/**
	 * Class DecimalValue
	 * A decimal value
	 * @author docpify <morten@runsafe.no>
	 */
	class DecimalValue extends Value
	{
		/**
		 * DecimalValue constructor.
		 * @param float|mixed $value Value to be cast to float and encapsulated
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
			if ($to instanceof Value)
				$b = $to->real();
			else
				$b = $to;

			if ($b === null)
				return $a === null ? 0 : 1;
			else
				$b = (float) $b;
			if ($a === null)
				return -1;

			return $a <=> $b;
		}

		/**
		 * @return string The encapsulated value as presented by a string
		 */
		public function __toString(): string {
			return \number_format($this->value, 2, ',', ' ');
		}
	}