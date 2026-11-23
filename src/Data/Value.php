<?php
	namespace KrameWork\Data;

	/**
	 * Class Value
	 * A generic value
	 * @author docpify <morten@runsafe.no>
	 */
	class Value
	{
		/**
		 * Value constructor.
		 * @param $value mixed The encapsulated value
		 */
		public function __construct(mixed $value) {
			$this->value = $value;
		}

		/**
		 * @return mixed The encapsulated value
		 */
		public function real(): mixed {
			return $this->value;
		}

		/**
		 * @return mixed The value to be encoded into JSON
		 */
		public function json(): mixed {
			return $this->value;
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
			if ($a === null)
				return -1;

			return \strnatcasecmp((string)$a, (string)$b);
		}

		/**
		 * @return string The encapsulated value as presented by a string
		 */
		public function __toString(): string {
			return (string)$this->value;
		}

		/**
		 * @var mixed The encapsulated value
		 */
		protected mixed $value = null;
	}