<?php
	namespace KrameWork\Runtime;
	
	class DebugException extends \Exception {
		/**
		 * @param string $message
		 * @param mixed $data
		 * @param int $code
		 * @param \Throwable|null $previous
		 */
		public function __construct(string $message, private mixed $data, int $code = 0, ?\Throwable $previous = null) {
			parent::__construct($message, $code, $previous);
		}

		/**
		 * Retrieve the debugging data provided by this exception.
		 * @return mixed
		 */
		public function getDebugData(): mixed {
			return $this->data;
		}
	}