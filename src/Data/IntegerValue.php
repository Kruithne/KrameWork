<?php
	namespace KrameWork\Data;
	require_once (__DIR__.'/Value.php');

	/**
	 * Class IntegerValue
	 * An integer value
	 * @author docpify <morten@runsafe.no>
	 */
	class IntegerValue extends Value
	{
		/**
		 * IntegerValue constructor.
		 * @param int|mixed $value A value to be cast to int and encapsulated
		 */
		public function __construct($value) {
			parent::__construct($value === null ? null : (int) $value);
		}

		/**
		 * Utility function for sorting a list of values
		 * @param $to Value|mixed A value to compare against
		 * @return int Sorting index
		 */
		public function compare($to) {
			$a = $this->value;
			if ($to instanceof Value)
				$b = $to->real();
			else
				$b = $to;

			if ($b === null)
				return $a === null ? 0 : 1;
			else
				$b = (int) $b;
			if ($a === null)
				return -1;

			return $a - $b;
		}
	}