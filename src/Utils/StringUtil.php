<?php
	namespace KrameWork\Utils;
	use KrameWork\Security\IMaskable;

	require_once(__DIR__ . '/../Security/IMaskable.php');

	/**
	 * Class StringUtil
	 * String manipulation utilities.
	 *
	 * @package KrameWork\Utils
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class StringUtil
	{
		/**
		 * Check if a string starts with another string.
		 *
		 * @api startsWith
		 * @param string $haystack String to search.
		 * @param string $needle What the string should start with.
		 * @return bool
		 */
		static function startsWith(string $haystack, string $needle):bool {
			return \strncmp($haystack, $needle, \strlen($needle)) === 0;
		}

		/**
		 * Check if a string ends with another string.
		 * Adapted from http://stackoverflow.com/a/834355/6997644
		 *
		 * @api endsWith
		 * @param string $haystack String to search.
		 * @param string $needle What the string should end with.
		 * @return bool
		 */
		static function endsWith(string $haystack, string $needle):bool {
			$length = \strlen($needle);
			return (\substr($haystack, -$length, $length) === $needle);
		}

		/**
		 * Convert all slashes in a string to match the environment directory separator.
		 *
		 * @api formatDirectorySlashes
		 * @param string $path Path to clean.
		 * @param bool $trimTrail If true, trailing spaces/slashes will be trimmed.
		 * @return string
		 */
		static function formatDirectorySlashes(string $path, bool $trimTrail = false):string {
			$clean = \str_replace(\DIRECTORY_SEPARATOR == '/' ? '\\' : '/', \DIRECTORY_SEPARATOR, $path);
			return $trimTrail ? \rtrim($clean, "\t\n\r\0\x0B\\/") : $clean;
		}

		/**
		 * Get the base class-name from a namespace string.
		 *
		 * @api namespaceBase
		 * @param string $namespace Namespace path.
		 * @return string
		 */
		static function namespaceBase(string $namespace):string {
			$parts = \explode('\\', $namespace);
			return $parts[\count($parts) - 1];
		}

		/**
		 * Represent a variable as a pretty string.
		 *
		 * @api variableAsString
		 * @param mixed $var Variable to represent.
		 * @return string
		 */
		static function variableAsString(mixed $var):string {
			$type = \gettype($var);
			if ($type == 'object') {
				if ($var instanceof IMaskable) {
					$type = 'masked';
					$var = $var->asMask();
				} else {
					$type = \get_class($var);
					if (!\method_exists($var, '__toString'))
						$var = 'object';
				}
			} elseif ($type == 'string') {
				$length = \strlen($var);
				$var = "({$length}) \"{$var}\"";
			} elseif ($type == 'array') {
				$var = \count($var) . ' items';
			} elseif (\is_bool($var)) {
				$var = $var ? 'true' : 'false';
			}

			return "({$type}) {$var}";
		}
	}