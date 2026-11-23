<?php
	namespace KrameWork\HTTP;
	require_once(__DIR__ . '/IHTTPHeader.php');

	/**
	 * Class HTTPHeader
	 * Base class for HTTP headers.
	 *
	 * @package KrameWork\Security\HTTP
	 * @author Kruithne <kruithne@gmail.com>
	 */
	abstract class HTTPHeader implements IHTTPHeader
	{
		/**
		 * Apply this header to the current response.
		 *
		 * @api apply
		 */
		public function apply() {
			\header((string) $this);
		}

		/**
		 * Get the compiled header string.
		 *
		 * @api __toString
		 * @return string
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		public function __toString(): string {
			return $this->getFieldName() . ': ' . $this->getFieldValue();
		}

	}