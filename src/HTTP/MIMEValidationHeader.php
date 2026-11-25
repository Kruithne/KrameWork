<?php
	namespace KrameWork\HTTP;
	require_once(__DIR__ . '/HTTPHeader.php');

	/**
	 * Class MIMEValidationHeader
	 * Validate resource content type before loading.
	 * Basic XSS prevention header for Chrome, Firefox and IE.
	 *
	 * @package KrameWork\Security\HTTP
	 */
	class MIMEValidationHeader extends HTTPHeader
	{
		/**
		 * Get the field name for this header.
		 *
		 * @api getFieldName
		 * @return string
		 */
		public function getFieldName(): string {
			return 'X-Content-Type-Options';
		}

		/**
		 * Get the field value for this header.
		 *
		 * @api getFieldValue
		 * @return string
		 */
		public function getFieldValue(): string {
			return 'nosniff';
		}
	}