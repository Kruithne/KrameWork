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
			if ($var instanceof IMaskable)
				return '(masked) ' . $var->asMask();

			if (\is_object($var))
				return '(' . \get_class($var) . ') ' . ($var instanceof \Stringable ? (string) $var : 'object');

			if (\is_string($var))
				return '(string) (' . \strlen($var) . ') "' . $var . '"';

			if (\is_array($var))
				return '(array) ' . \count($var) . ' items';

			if (\is_bool($var))
				return '(boolean) ' . ($var ? 'true' : 'false');

			if (\is_scalar($var))
				return '(' . \gettype($var) . ') ' . $var;

			return '(' . \gettype($var) . ') ' . \print_r($var, true);
		}
	}