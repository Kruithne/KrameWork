<?php
	namespace KrameWork\Reporting;

	use KrameWork\Data\Value;

	/**
	 * Class ResultRow
	 * Encapsulates a result row of formatted data, with support for JSON serialization
	 * @author docpify <morten@runsafe.no>
	 */
	class ReportRow implements \JsonSerializable, \Iterator, \ArrayAccess
	{
		/**
		 * ReportRow constructor.
		 * @param array|object|null $data The underlying result row
		 */
		public function __construct(array|object|null $data) {
			$this->data = $data;
		}

		function jsonSerialize(): mixed {
			$serialize = [];
			$this->indexKeys();
			foreach ($this->keys as $field) {

				$value = null;
				if(is_array($this->data))
					$value = $this->data[$field];
				else if(is_object($this->data))
					$value = $this->data->$field;

				if ($value instanceof Value)
					$serialize[$field] = $value->json();
				else
					$serialize[$field] = $value;
			}
			return $serialize;
		}

		/**
		 * @link http://php.net/manual/en/iterator.current.php
		 */
		public function current(): mixed {
			$k = $this->key();
			return $this->data->$k;
		}

		/**
		 * @link http://php.net/manual/en/iterator.next.php
		 */
		public function next(): void {
			$this->position++;
		}

		/**
		 * @link http://php.net/manual/en/iterator.key.php
		 */
		public function key(): mixed {
			return $this->getKeyAt($this->position);
		}

		/**
		 * @link http://php.net/manual/en/iterator.valid.php
		 */
		public function valid(): bool {
			$this->indexKeys();
			return $this->data && \count($this->keys) > $this->position;
		}

		/**
		 * @link http://php.net/manual/en/iterator.rewind.php
		 */
		public function rewind(): void {
			$this->position = 0;
		}

		/**
		 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
		 */
		public function offsetExists(mixed $offset): bool {
			$k = is_int($offset) ? $this->getKeyAt($offset) : $offset;
			return $this->data && isset($this->data->$k);
		}

		/**
		 * @link http://php.net/manual/en/arrayaccess.offsetget.php
		 */
		public function offsetGet(mixed $offset): mixed {
			$k = is_int($offset) ? $this->getKeyAt($offset) : $offset;
			return $k ? $this->data->$k : null;
		}

		/**
		 * @link http://php.net/manual/en/arrayaccess.offsetset.php
		 */
		public function offsetSet(mixed $offset, mixed $value): void {
			$k = is_int($offset) ? $this->getKeyAt($offset) : $offset;
			if($k)
				$this->data->$k = $value;
		}

		/**
		 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
		 */
		public function offsetUnset(mixed $offset): void {
			$k = is_int($offset) ? $this->getKeyAt($offset) : $offset;
			unset($this->data->$k);
		}

		/**
		 * @param int $index
		 * @return int|string|null
		 */
		private function getKeyAt(int $index): int|string|null {
			$this->indexKeys();
			return isset($this->keys[$index]) ? $this->keys[$index] : null;
		}

		private function indexKeys(): void {
			if ($this->keys == null)
			{
				if (is_array($this->data))
					$this->keys = \array_keys($this->data);
				else if (is_object($this->data))
					$this->keys = \array_keys(\get_object_vars($this->data));
				else
					$this->keys = [];
			}
		}

		/**
		 * @var array|null Numerically indexed keys for iteration purposes
		 */
		private ?array $keys = null;

		/**
		 * @var array|object|null The data contained in the row
		 */
		private array|object|null $data = null;

		/**
		 * @var int
		 */
		private int $position = 0;
	}
