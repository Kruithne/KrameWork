<?php
	class KW_ErrorReport
	{
		/**
		 * Constructs an error report object.
		 */
		public function __construct()
		{
			$this->addInfo($this->formatValue('Time', date('l jS \of F Y h:i:s A')));
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
			if ($key == 'trace')
				return 'Stacktrace: ' . $this->formatStacktrace($value);
			return $key . ' -> ' . $value;
		}
	
		/**
		 * Format a stacktrace
		 * @param array $stack A stacktrace as returned by debug_backtrace()
		 * @return string A formatted stacktrace
		 */
		private function formatStacktrace($stack)
		{
			$trace = '';
			if (count($stack) == 0)
				return;
			error_log('_____ begin stack frame dump _____');
			foreach ($stack as $i => $step)
			{
				$args = '';
				if (isset($step['args']) && is_array($step['args']))
				{
					foreach ($step['args'] as &$arg)
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

				if (isset($step['class']) && !empty($step['class']))
					$func = $step['class'] . '::' . $step['function'];
				else
					$func = $step['function'];

				$out = sprintf('#%d %s(%s) called at [%s:%d]', $i, $func, $args, isset($step['file']) ? $step['file'] : 'unknown', isset($step['line']) ? $step['line'] : 0);
				error_log($out);
				$trace .= $out . "\n";
			}
			error_log('_____ end stack frame dump _____');

			return $trace;
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
	}
?>
