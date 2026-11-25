<?php
	namespace KrameWork\Data;
	require_once (__DIR__.'/Value.php');

	/**
	 * Class IntegerValue
	 * An integer value
	 * @extends Value<int|null>
	 */
	class IntegerValue extends Value
	{
		/**
		 * IntegerValue constructor.
		 * @param int|float|string|null $value A value to be cast to int and encapsulated
		 */
		public function __construct(mixed $value) {
			parent::__construct($value === null ? null : (int) $value);
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

			return $a - (int) $b;
		}
	}