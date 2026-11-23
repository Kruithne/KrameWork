<?php
	namespace KrameWork\Runtime;
	
	class DebugException extends \Exception {
		/**
		 * @param string $message
		 * @param mixed $data
		 * @param int $code
		 * @param \Throwable|null $previous
		 */
		public function __construct(string $message, mixed $data, int $code = 0, ?\Throwable $previous = null) {
			parent::__construct($message, $code, $previous);
			$this->data = $data;
		}

		/**
		 * Retrieve the debugging data provided by this exception.
		 * @return mixed
		 */
		public function getDebugData(): mixed {
			return $this->data;
		}

		/**
		 * @var mixed
		 */
		private mixed $data = null;
	}