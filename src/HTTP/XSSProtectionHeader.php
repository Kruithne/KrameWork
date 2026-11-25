<?php
	namespace KrameWork\HTTP;
	require_once(__DIR__ . '/HTTPHeader.php');

	/**
	 * Class XSSProtectionHeader
	 * Header to enable legacy X-XSS detection/prevention.
	 *
	 * @package KrameWork\Security\HTTP
	 */
	class XSSProtectionHeader extends HTTPHeader
	{
		/**
		 * Get the field name for this header.
		 *
		 * @api getFieldName
		 * @return string
		 */
		public function getFieldName(): string {
			return 'X-XSS-Protection';
		}

		/**
		 * Get the field value for this header.
		 *
		 * @api getFieldValue
		 * @return string
		 */
		public function getFieldValue(): string {
			return '1; mode=block';
		}
	}