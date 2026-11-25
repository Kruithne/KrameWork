<?php
	namespace KrameWork\Data;

	/**
	 * Class Value
	 * A generic value
	 * @template T of scalar|null
	 */
	class Value
	{
		/**
		 * Value constructor.
		 * @param T $value The encapsulated value
		 */
		public function __construct(protected mixed $value) {}

		/**
		 * @return T The encapsulated value
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
			$b = $to instanceof Value ? $to->real() : $to;

			if (!\is_scalar($b))
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
	}