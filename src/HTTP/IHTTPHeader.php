<?php
	namespace KrameWork\HTTP;

	/**
	 * Interface IHTTPHeader
	 * Represents a HTTP header.
	 *
	 * @package KrameWork\Security
	 */
	interface IHTTPHeader
	{
		/**
		 * Apply this header to the current response.
		 *
		 * @api apply
		 */
		public function apply(): void;

		/**
		 * Get the field name for this header.
		 *
		 * @api getFieldName
		 * @return string
		 */
		public function getFieldName():string;

		/**
		 * Get the field value for this header.
		 *
		 * @api getFieldValue
		 * @return string
		 */
		public function getFieldValue():string;

		/**
		 * Get the compiled header string.
		 *
		 * @api __toString()
		 * @return string
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
		 */
		public function __toString():string;
	}