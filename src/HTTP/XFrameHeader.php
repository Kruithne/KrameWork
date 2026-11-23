<?php
	namespace KrameWork\HTTP;
	require_once(__DIR__ . '/HTTPHeader.php');

	/**
	 * Class XFrameHeader
	 * X-Framing-Option header, for use with CSP frame-ancestors directive.
	 *
	 * @package KrameWork\Security\HTTP
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class XFrameHeader extends HTTPHeader
	{
		const DENY = 'DENY';
		const SAME_ORIGIN = 'SAMEORIGIN';

		/**
		 * XFrameHeader constructor.
		 *
		 * @api __construct
		 * @param string $option Framing option for this header.
		 */
		public function __construct(protected string $option = self::DENY) {}

		/**
		 * Set the framing option for this header.
		 * Use the constants provided by the XFrameHeader class.
		 *
		 * @api setOption
		 * @param string $option Directive for this header.
		 */
		public function setOption(string $option): void {
			$this->option = $option;
		}

		/**
		 * Get the field name for this header.
		 *
		 * @api getFieldName
		 * @return string
		 */
		public function getFieldName(): string {
			return 'X-Frame-Options';
		}

		/**
		 * Get the field value for this header.
		 *
		 * @api getFieldValue
		 * @return string
		 */
		public function getFieldValue(): string {
			return $this->option;
		}
	}