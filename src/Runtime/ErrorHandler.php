<?php
	/*
	 * Copyright (c) 2017 Kruithne (kruithne@gmail.com)
	 * https://github.com/Kruithne/KrameWork
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in all
	 * copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	 * SOFTWARE.
	 */

	namespace KrameWork\Runtime;

	use KrameWork\Runtime\ErrorDispatchers\IBufferDispatcher;
	use KrameWork\Runtime\ErrorDispatchers\IErrorDispatcher;
	use KrameWork\Runtime\ErrorFormatters\IErrorFormatter;
	use KrameWork\Runtime\ErrorReports\ErrorReport;
	use KrameWork\Runtime\ErrorTypes\ExceptionError;
	use KrameWork\Runtime\ErrorTypes\IError;
	use KrameWork\Runtime\ErrorTypes\RuntimeError;
	use KrameWork\Utils\StringUtil;

	require_once(__DIR__ . '/ErrorDispatchers/IBufferDispatcher.php');
	require_once(__DIR__ . '/ErrorDispatchers/IErrorDispatcher.php');
	require_once(__DIR__ . '/ErrorReports/ErrorReport.php');
	require_once(__DIR__ . '/ErrorTypes/ExceptionError.php');
	require_once(__DIR__ . '/ErrorTypes/RuntimeError.php');
	require_once(__DIR__ . '/../Utils/StringUtil.php');

	/**
	 * Class ErrorHandler
	 * Module in charge of intercepting, formatting and dispatching errors.
	 *
	 * @package KrameWork\Runtime
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class ErrorHandler
	{
		const SANITIZE_PATTERNS = [
			'/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
			'/\b(?:\d{1,3}\.){3}\d{1,3}\b/',
			'/\b(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}\b/'
		];

		const SANITIZE_REPLACEMENTS = ['[e-mail address]', '[IPv4 address]', '[IPv6 address]'];

		/**
		 * ErrorHandler constructor.
		 *
		 * @api __construct
		 * @param IErrorDispatcher|null $dispatcher Dispatcher to use.
		 * @param IErrorFormatter|null $formatter Formatter for the dispatcher.
		 */
		public function __construct($dispatcher = null, $formatter = null) {
			$this->dispatch = [];

			if ($dispatcher instanceof IErrorDispatcher && $formatter instanceof IErrorFormatter)
				$this->addDispatch($dispatcher, $formatter);

			$this->previousErrorLevel = \error_reporting();
			$this->level = $this->previousErrorLevel;
			\error_reporting(\E_ALL);

			$this->previousErrorHandler = \set_error_handler([$this, 'catchRuntimeError']);
			$this->previousExceptionHandler = \set_exception_handler([$this, 'catchException']);

			$this->errorCount = 0;
			$this->maxErrors = 10;

			$this->active = true;
		}

		/**
		 * Set the error reporting level for this handler.
		 *
		 * @api setErrorLevel
		 * @param int $level Error level.
		 */
		public function setErrorLevel(int $level) {
			$this->level = $level;
		}

		/**
		 * Add a dispatcher to this error handler, with a linked formatter.
		 *
		 * @api addDispatch
		 * @param IErrorDispatcher $dispatcher Dispatcher to send reports.
		 * @param IErrorFormatter $formatter Formatter for dispatcher to use.
		 */
		public function addDispatch(IErrorDispatcher $dispatcher, IErrorFormatter $formatter) {
			$this->dispatch[] = [$dispatcher, $formatter];
		}

		/**
		 * Set the maximum amount of errors to occur before the error
		 * handler will terminate the script.
		 *
		 * @api setMaxErrors
		 * @param int $max Maximum error threshold.
		 */
		public function setMaxErrors(int $max) {
			$this->maxErrors = $max;
		}

		public function setRedactedKeys(array $keys) {
			$this->redactedKeys = $keys;
		}

		public function setReportSensitive(bool $enabled) {
			$this->reportSensitive = $enabled;
		}

		public function setSanitize(bool $enabled) {
			$this->sanitize = $enabled;
		}

		public static function sanitize(string $input): string {
			return \preg_replace(self::SANITIZE_PATTERNS, self::SANITIZE_REPLACEMENTS, $input) ?? $input;
		}

		/**
		 * Disable this error handler, restoring handlers/levels to their
		 * state when this error handler was created.
		 *
		 * @api deactivate
		 */
		public function deactivate() {
			$this->active = false;
			\set_error_handler($this->previousErrorHandler);
			\set_exception_handler($this->previousExceptionHandler);
			\error_reporting($this->previousErrorLevel);
			unset($this->report, $this->dispatcher);
		}

		/**
		 * Temporarily disable all error handlers, to avoid leaking sensitive information.
		 *
		 * @api suspend
		 */
		public static function suspend() {
			self::$suspended++;
		}

		/**
		 * Resume the error handlers once sensitive data is out of scope.
		 *
		 * @api resume
		 */
		public static function resume() {
			if (self::$suspended > 0)
				self::$suspended--;
		}

		/**
		 * Log an exception that has been handled.
		 *
		 * @api logException
		 * @param \Throwable $exception An exception to report
		 */
		public function logException(\Throwable $exception)
		{
			$this->catch(new ExceptionError($exception), false);
		}

		/**
		 * Register a debug provider with this error handler.
		 *
		 * @api addHook
		 * @param IDebugHook $hook An object implementing the IDebugHook interface
		 */
		public function addHook(IDebugHook $hook) {
			$this->hooks[] = $hook;
		}

		/**
		 * Register a formatter to use to return a custom error page to the user on a core error.
		 *
		 * @api setCoreErrorFormatter
		 * @param IErrorFormatter $formatter Custom error page provider.
		 */
		public function setCoreErrorFormatter(IErrorFormatter $formatter) {
			$this->coreErrorFormatter = $formatter;
		}

		/**
		 * Catches a normal error thrown during runtime.
		 *
		 * @internal
		 * @param int $type Type of error that occurred.
		 * @param string $message Message describing the error.
		 * @param string $file File which the error occurred in.
		 * @param int $line Line of code the error occurred on.
		 */
		public function catchRuntimeError($type, $message, $file, $line) {
			if (($type & $this->level)) {
				$this->catch(new RuntimeError($type, $message, $file, $line), false);
			}
		}

		/**
		 * Catches an exception thrown during runtime.
		 *
		 * @internal
		 * @param \Throwable $exception The exception which occurred.
		 */
		public function catchException(\Throwable $exception) {
			$this->catch(new ExceptionError($exception), false);
		}

		/**
		 * Catches PHP core errors.
		 * To enable usage, check the Runtime\ErrorHandler.md document.
		 *
		 * @internal
		 * @param string $buffer PHP output buffer.
		 * @return string
		 */
		public function catchCoreError(string $buffer) {
			if (!$this->active || self::$suspended)
				return $buffer;

			if (\preg_match('/<!--\[INTERNAL_ERROR\](.*)-->/Us', $buffer, $parts)) {
				if (!\headers_sent())
					header('HTTP/1.0 500 Internal Error');

				return $this->handleCoreError($parts);
			}

			return $buffer;
		}

		/**
		 * Handles a PHP core error.
		 *
		 * @internal
		 * @param $data array
		 * @return string
		 */
		private function handleCoreError($data) {
			$error = $data[1]; // Error message.
			$errorObj = null;

			\preg_match('/(.*) error: (.*) in (.*) on line (.*)/', $error, $parts); // Error details.

			$nParts = \count($parts);
			if ($nParts == 5) {
				$error = $parts[1] . ' - ' . $error;
				$errorObj = new RuntimeError(\E_CORE_ERROR, $error, $parts[3], $parts[4]);
			} else {
				$error = 'Internal Error (' . $nParts . ') : ' . $error;
				$errorObj = new RuntimeError(\E_CORE_ERROR, $error, __FILE__, __LINE__);
			}

			$trace = $this->filterStacktrace($errorObj->getTrace());
			$debug = $this->getDebug();

			foreach ($this->dispatch as $dispatch) {
				if ($dispatch[0] instanceof IBufferDispatcher)
					continue;
				$output = $this->formatError($errorObj, $trace, $debug, $dispatch[1]);
				$dispatch[0]->dispatch($output);
			}

			// Return empty response to client if no core error response template is defined.
			if(!$this->coreErrorFormatter)
				return '';

			return $this->formatError($errorObj, $trace, $debug, $this->coreErrorFormatter);
		}

		/**
		 * Catch, format and dispatch an error.
		 *
		 * @internal
		 * @param IError $error
		 * @param bool $terminate Terminate script after error is dispatched.
		 */
		private function catch(IError $error, bool $terminate) {
			if (!$this->active || self::$suspended)
				return;

			$trace = $this->filterStacktrace($error->getTrace());
			$debug = $this->getDebug();

			foreach ($this->dispatch as $dispatch) {
				$output = $this->formatError($error, $trace, $debug, $dispatch[1]);
				if ($dispatch[0]->dispatch($output))
					$terminate = true;
			}

			// Terminate script execution if needed.
			if ($terminate || $this->errorCount++ >= $this->maxErrors) {
				if (!\headers_sent())
					\header('HTTP/1.0 500 Internal Error');

				die();
			}
		}

		/**
		 * Execute a set of dispatchers on an error
		 *
		 * @internal
		 * @param IError $error
		 * @param bool $terminate Terminate script after error is dispatched.
		 * @return string
		 */
		private function formatError(IError $error, array $trace, array $debug, IErrorFormatter $formatter) {
			$formatter->beginReport();
			$formatter->reportError($error);
			$formatter->reportDebug($debug);
			$formatter->reportStacktrace($trace);
			$this->packReport($formatter);
			$report = $formatter->generate();

			if (!$this->sanitize)
				return $report;

			return new ErrorReport($report->getError(), $report->getContentType(), $report->getExtension(), self::sanitize((string) $report));
		}

		/**
		 * Filter error handling out of a stacktrace.
		 *
		 * @internal
		 * @param array $trace Trace to filter.
		 * @return array
		 */
		private function filterStacktrace(array $trace):array {
			$startIndex = 0;

			foreach ($trace as $frame) {
				$class = $frame['class'] ?? '';
				if ($class == '' || !StringUtil::startsWith($class, 'KrameWork\Runtime'))
					break;
				// Skip core error catcher callback method
				if ($class == 'KrameWork\Runtime\ErrorHandler' && $frame['function'] == 'catchCoreError')
					$startIndex++;
				$startIndex++;
			}

			return $startIndex == 0 ? $trace : \array_slice($trace, $startIndex);
		}

		/**
		 * Get debug data from registered hooks
		 *
		 * @internal
		 * @return array Key/Value pairs of debug data
		 */
		private function getDebug(): array {
			$debug = [];
			foreach ($this->hooks as $hook)
			{
				try
				{
					$debug += $hook->getDebug();
				}
				catch(\Throwable $e)
				{
					$debug[\get_class($hook)] = '['.\get_class($e).'::'.$e->getMessage().']';
				}
			}
			return $debug;
		}

		/**
		 * Pack the report with data useful for debugging.
		 *
		 * @internal
		 * @param IErrorFormatter $report Report to pack.
		 */
		private function packReport(IErrorFormatter $report) {
			// CLI arguments, if available.
			if (\PHP_SAPI == 'cli') {
				global $argv;
				$report->reportArray('CLI Arguments', $argv);
			}

			$report->reportArray('$_SERVER', $this->redact($_SERVER));
			$report->reportArray('$_POST', $this->redact($_POST));
			$report->reportArray('$_GET', $this->redact($_GET));
			$report->reportArray('$_FILES', $_FILES);

			if ($this->reportSensitive) {
				$report->reportArray('$_COOKIE', $this->redact($_COOKIE));
				$report->reportString('Raw Request Content', \file_get_contents('php://input'));
			}
		}

		private function redact(array $data): array {
			foreach ($data as $key => $value) {
				if ($this->isRedactedKey((string) $key))
					$data[$key] = '[REDACTED]';
				elseif (\is_array($value))
					$data[$key] = $this->redact($value);
			}

			return $data;
		}

		private function isRedactedKey(string $key): bool {
			foreach ($this->redactedKeys as $redactedKey) {
				if (\stripos($key, $redactedKey) !== false)
					return true;
			}

			return false;
		}

		/**
		 * @var int
		 */
		protected $level;

		/**
		 * @var bool
		 */
		protected $active;

		/**
		 * @var array
		 */
		protected $dispatch;

		/**
		 * @var string
		 */
		protected $previousErrorHandler;

		/**
		 * @var string
		 */
		protected $previousExceptionHandler;

		/**
		 * @var int
		 */
		protected $previousErrorLevel;

		/**
		 * @var int
		 */
		protected $maxErrors;

		/**
		 * @var int
		 */
		protected $errorCount;

		/**
		 * @var IDebugHook[]
		 */
		protected $hooks = [];

		protected $redactedKeys = ['pass', 'pwd', 'secret', 'token', 'auth', 'cookie', 'session', 'key'];

		protected $reportSensitive = false;

		protected $sanitize = false;

		/**
		 * @var IErrorFormatter
		 */
		protected $coreErrorFormatter;

		/**
		 * @var int
		 */
		protected static $suspended = 0;
	}
