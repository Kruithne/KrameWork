<?php
	class KW_ErrorReport
	{
		/**
		 * Constructs an error report object.
		 * @param IErrorHint[] $hints Hint sources to inject in the report
		 */
		public function __construct($hints = array())
		{
			$this->addInfo($this->formatValue('Time', date('l jS \of F Y h:i:s A')));
			$this->hints = $hints;
		}

		/**
		 * Adds information to the report.
		 *
		 * @param string|array $info A string or array section to add to the report.
		 */
		public function addInfo($info)
		{
			$this->data[] = $info;
		}

		/**
		 * Adds a key/value combination to the report using the built-in format.
		 *
		 * @param string $key The key for the value.
		 * @param string $value They value.
		 */
		public function __set($key, $value)
		{
			$this->addInfo($this->formatValue($key, $value));
		}

		/**
		 * Pushes helpful information into the array given.
		 *
		 * @param array $array The array to push the information into.
		 */
		private function bundleReportInformation(&$array)
		{
			$array[] = array(
				$this->formatValue('PHP Version', PHP_VERSION),
				$this->formatValue('Server OS', PHP_OS)
			);

			if($this->hints)
				foreach($this->hints as $hint)
					$array[] = $this->formatValue($hint->getErrorHintLabel(), $hint->getErrorHint());

			if (isset($_SERVER['SERVER_NAME']))
			{
				if (isset($_SERVER['REQUEST_URI']))
				{
					switch ($_SERVER['SERVER_PORT'])
					{
						default:
						case 80: $urh = 'http'; break;
						case 443: $urh = 'https'; break;
					}

					$array[] = $this->formatValue('URI', $urh . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
				}
				else
				{
					$array[] = $this-formatValue('VHost', $_SERVER['SERVER_NAME']);
				}
			}

			if (isset($_SERVER['HTTP_REFERER']))
				$array[] = $this->formatValue('Referer', $_SERVER['HTTP_REFERER']);

			if (isset($_SERVER['REQUEST_TIME']))
				$array[] = $this->formatValue('Request duration', time() - $_SERVER['REQUEST_TIME']);

			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				$ip = explode(':',$_SERVER['HTTP_X_FORWARDED_FOR']);
				$array[] = $this->formatValue('Client', gethostbyaddr($ip[0]) . ' [' . $ip[0] . ']');

				if (isset($_SERVER['REMOTE_ADDR']))
					$array[] = $this->formatValue('Proxy server', gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' [' . $_SERVER['REMOTE_ADDR'] . ']');
			}
			else if (isset($_SERVER['REMOTE_ADDR']))
			{
				$array[] = $this->formatValue('Client', gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' [' . $_SERVER['REMOTE_ADDR'] . ']');
			}

			if (isset($_SERVER['REMOTE_USER']))
				$array[] = $this->formatValue('Remote user', $_SERVER['REMOTE_USER']);

			if (isset($_SERVER['HTTP_USER_AGENT']))
				$array[] = $this->formatValue('Browser', $_SERVER['HTTP_USER_AGENT']);

			if (KrameSystem::sessionIsStarted())
				$array[] = array('SESSION' => $this->bundleArray($_SESSION));

			$array[] = array('GET' => $this->bundleArray($_GET));
			$array[] = array('POST' => $this->bundleArray($_POST));
		}

		/**
		 * Format a key/value into a simple string.
		 *
		 * @param mixed $key The key for the string.
		 * @param mixed $value The value for the string.
		 * @return string Formatted string.
		 */
		private function formatValue($key, $value)
		{
			switch ($key)
			{
				case 'Type':  $this->type = $value; break;
				case 'Error': $this->error = $value; break;
				case 'File':  $this->file = $value; break;
				case 'Line':  $this->line = $value; break;
				case 'trace': return 'Stacktrace: ' . $this->formatStacktrace($value);
			}
			return $key . ' -> ' . $value;
		}
	
		/**
		 * Format a stacktrace
		 * @param array $stack A stacktrace as returned by debug_backtrace()
		 * @return string A formatted stacktrace
		 */
		private function formatStacktrace($stack)
		{
			$this->stack = $stack;
			$trace = '';

			if (count($stack) == 0)
				return $trace;

			error_log('_____ begin stack frame dump _____');
			
			foreach ($stack as $i => $frame)
			{
				if ($this->blackListed($frame))
					continue;

				$args = '';
				if (isset($frame['args']) && is_array($frame['args']))
				{
					foreach ($frame['args'] as &$arg)
					{
						if ($args != '')
							$args .= ',';

						if (is_array($arg))
							$args .= 'Array[' . count($arg) . ']';

						elseif (is_object($arg))
							$args .= get_class($arg);

						elseif (is_numeric($arg))
							$args .= $arg;

						else
							$args .= '\'' . (strlen($arg) > 100 ? substr($arg,0,100) . '...' : $arg) . '\'';
					}
				}

				if (isset($frame['class']) && !empty($frame['class']))
					$func = $frame['class'] . '::' . $frame['function'];
				else
					$func = $frame['function'];

				$out = sprintf('#%d %s(%s) called at [%s:%d]', $i, $func, $args, isset($frame['file']) ? $frame['file'] : 'unknown', isset($frame['line']) ? $frame['line'] : 0);
				error_log($out);
				$trace .= $out . "\n";
			}
			error_log('_____ end stack frame dump _____');

			return $trace;
		}

		/**
		 * Format the report as JSON for sending to the developer
		 * @return string JSON error report
		 */
		public function getJSONReport()
		{
			$report = (object)array(
				'type' => $this->type,
				'error' => $this->error,
				'file' => $this->file,
				'line' => $this->line,
				'trace' => array()
			);

			foreach ($this->stack as $frame)
			{
				if ($this->blackListed($frame))
					continue;

				switch ($frame['function'])
				{
					case 'trigger_error':
						$report->trace[] = (object)array('func' => 'USER ERROR RAISED');
						break;

					case '__get':
					case '__set':
					case '__call':
						$report->tracep[] = (object)array(
							'func' => $frame['class'] . '->' . $frame['args'][0],
							'file' => $frame['file'],
							'line' => $frame['line'],
							'args' => $frame['args']
						);
						break;

					default:
						$report->tracep[] = (object)array(
							'func' => (isset($frame['class']) ? $frame['class'] : 'GLOBAL') . '::' . $frame['function'],
							'file' => $frame['file'],
							'line' => $frame['line'],
							'args' => $frame['args']
						);
						break;
				}
			}
			return json_encode($report);
		}

		/**
		 * Format the report as HTML for showing to the developer
		 * @return string HTML error report
		 */
		public function getHTMLReport()
		{
			$trace = '';
			foreach ($this->stack as $frame)
			{
				if ($this->blackListed($frame))
					continue;

				switch ($frame['function'])
				{
					case 'trigger_error':
						$trace .= sprintf('<span class="func">USER ERROR RAISED</span><br />');
						break;

					case '__get':
					case '__set':
					case '__call':
						$trace .= sprintf(
							'<p class="frame">at <span class="func">%3$s->%4$s</span> in <span class="path">%5$s/</span><span class="file">%1$s</span>:<span class="line">%2$d</span></p>',
							basename($frame['file']), $frame['line'], $frame['class'], $frame['args'][0], dirname($frame['file'])
						);
						break;

					default:
						$trace .= sprintf(
							'<p class="frame">at <span class="func">%3$s::%4$s</span> in <span class="path">%5$s/</span><span class="file">%1$s</span>:<span class="line">%2$d</span></p>',
							isset($frame['file']) ? basename($frame['file']) : 'unknown',
							isset($frame['line']) ? $frame['line'] : '',
							isset($frame['class']) ? $frame['class'] : 'GLOBAL',
							$frame['function'],
							isset($frame['file']) ? dirname($frame['file']) : ''
						);
						break;
				}
			}
			return sprintf(
				'<div class="error-report"><span class="type">%s</span> <span class="message">%s</span><p class="source">in <span class="path">%s/</span><span class="file">%s</span>:<span class="line">%s</span></p><p class="stacktrace">%s</p></div>',
				$this->type, $this->error, dirname($this->file), basename($this->file), $this->line, $trace
			);
		}

		/**
		 * Check if a stack frame is blacklisted
		 *
		 * @param array $frame a stack frame from debug_backtrace
		 * @return bool Frame should be ignored
		 */
		private function blacklisted($frame)
		{
			$function = $frame['function'];
			if ($function == 'handleError' || $function == 'handleException')
				if (isset($frame['class']) && $frame['class'] == 'KW_ErrorHandler')
					return true;

			return false;
		}

		/**
		 * Traverses down the array and generates a report section for it.
		 *
		 * @param array $source The source array.
		 * @param array|null $array The report section.
		 * @param null|string $main_key The current key in traversing context.
		 * @return array The section in it's current state.
		 */
		private function bundleArray($source, $array = null, $main_key = null)
		{
			if ($array === null)
				$array = array();

			foreach ($source as $key => $value)
			{
				if (is_array($value) || is_object($value))
				{
					$node_key = is_string($key) ? $key : gettype($value);
					$new_key = ($main_key === null ? $node_key : $main_key . '/' . $node_key);
					return $this->bundleArray($value, $array, $new_key);
				}
				else
				{
					$new_key = $main_key === null ? $key : $main_key . '/' . $key;
					$array[] = $this->formatValue($new_key, $value);
				}
			}
			return $array;
		}

		/**
		 * Returns the report all nice and formatted.
		 *
		 * @return string Formatted report string.
		 */
		public function __toString()
		{
			$output = new StringBuilder();
			$data = $this->data;

			$this->bundleReportInformation($data);
			$this->prepareOutputData($data, $output);

			return (string) $output;
		}

		/**
		 * Loops through the array given, appending everything in sections (split by array) to the output.
		 *
		 * @param mixed $data
		 * @param StringBuilder $output
		 */
		private function prepareOutputData($data, $output)
		{
			if (count($data))
			{
				foreach ($data as $key => $node)
				{
					if (is_array($node))
					{
						$output->append("\r\n");

						if (is_string($key))
							$output->append($key . ":\r\n");

						$this->prepareOutputData($node, $output);
					}
					else
					{
						$output->append($node)->append("\r\n");
					}
				}
			}
			else
			{
				$output->append("No data inside array.\r\n");
			}
		}

		/**
		 * Set the subject for this error report.
		 *
		 * @param string $subject Subject to set.
		 */
		public function setSubject($subject)
		{
			$this->subject = $subject;
		}

		/**
		 * Get the subject for this error report.
		 *
		 * @return string|null Subject for this report, will be null if not yet set.
		 */
		public function getSubject()
		{
			return $this->subject;
		}

		/**
		 * @var array
		 */
		private $data = array();

		/**
		 * @var string
		 */
		private $subject;

		/**
		 * @var string
		 */
		private $type;

		/**
		 * @var string
		 */
		private $error;

		/**
		 * @var string
		 */
		private $file;

		/**
		 * @var string
		 */
		private $line;

		/**
		 * @var array
		 */
		private $stack;

		/**
		 * @var IErrorHint[]
		 */
		private $hints;
	}
?>
