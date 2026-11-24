<?php
	namespace KrameWork\Reporting;

	use KrameWork\Data\Value;

	/**
	 * Class ResultRow
	 * Encapsulates a result row of formatted data, with support for JSON serialization
	 * @author docpify <morten@runsafe.no>
	 * @implements \Iterator<string, mixed>
	 * @implements \ArrayAccess<int|string, mixed>
	 */
	class ReportRow implements \JsonSerializable, \Iterator, \ArrayAccess
	{
		/**
		 * ReportRow constructor.
		 * @param array<string, mixed>|object|null $data The underlying result row
		 */
		public function __construct(private array|object|null $data) {}

		function jsonSerialize(): mixed {
			$serialize = [];
			foreach ($this->keys() as $field) {
				$value = $this->getValue($field);
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
			return $this->getValue($this->key());
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
			return $this->keys()[$this->position];
		}

		/**
		 * @link http://php.net/manual/en/iterator.valid.php
		 */
		public function valid(): bool {
			return \count($this->keys()) > $this->position;
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
			if (\is_array($this->data))
				return isset($this->data[$k]);

			return isset($this->data->$k);
		}

		/**
		 * @link http://php.net/manual/en/arrayaccess.offsetget.php
		 */
		public function offsetGet(mixed $offset): mixed {
			$k = is_int($offset) ? $this->getKeyAt($offset) : $offset;
			return $k ? $this->getValue($k) : null;
		}

		/**
		 * @link http://php.net/manual/en/arrayaccess.offsetset.php
		 */
		public function offsetSet(mixed $offset, mixed $value): void {
			$k = is_int($offset) ? $this->getKeyAt($offset) : $offset;
			if (!$k)
				return;

			if (\is_array($this->data))
				$this->data[$k] = $value;
			else if (\is_object($this->data))
				$this->data->$k = $value;
		}

		/**
		 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
		 */
		public function offsetUnset(mixed $offset): void {
			$k = is_int($offset) ? $this->getKeyAt($offset) : $offset;
			if (\is_array($this->data))
				unset($this->data[$k]);
			else if (\is_object($this->data))
				unset($this->data->$k);
		}

		/**
		 * @param int $index
		 * @return string|null
		 */
		private function getKeyAt(int $index): ?string {
			return $this->keys()[$index] ?? null;
		}

		/**
		 * Get the value of a field from the row data.
		 * @param string $key Name of the field.
		 * @return mixed
		 */
		private function getValue(string $key): mixed {
			if (\is_array($this->data))
				return $this->data[$key] ?? null;

			return $this->data->$key ?? null;
		}

		/**
		 * Get the keys of the row data, indexed on first use.
		 * @return list<string>
		 */
		private function keys(): array {
			if ($this->keys !== null)
				return $this->keys;

			if (\is_array($this->data))
				$this->keys = \array_keys($this->data);
			else if (\is_object($this->data))
				$this->keys = \array_keys(\get_object_vars($this->data));
			else
				$this->keys = [];

			return $this->keys;
		}

		/**
		 * @var list<string>|null Numerically indexed keys for iteration purposes
		 */
		private ?array $keys = null;

		/**
		 * @var int
		 */
		private int $position = 0;
	}
