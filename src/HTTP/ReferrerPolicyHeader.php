<?php
	namespace KrameWork\HTTP;

	/**
	 * Class ReferrerPolicyHeader
	 * HTTP Referrer Policy Header.
	 *
	 * @package KrameWork\HTTP
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class ReferrerPolicyHeader extends HTTPHeader
	{
		const EMPTY = '';
		const ORIGIN = 'origin';
		const UNSAFE_URL = 'unsafe-url';
		const NO_REFERRER = 'no-referrer';
		const SAME_ORIGIN = 'same-origin';
		const STRICT_ORIGIN = 'strict-origin';
		const ORIGIN_CROSS_ORIGIN = 'origin-when-cross-origin';
		const NO_REFERRER_DOWNGRADE = 'no-referrer-when-downgrade';
		const STRICT_CROSS_ORIGIN = 'strict-origin-when-cross-origin';

		/**
		 * ReferrerPolicyHeader constructor.
		 *
		 * @api __construct
		 * @param string $value Initial value, use class constants.
		 */
		public function __construct(private string $value = self::EMPTY) {}

		/**
		 * Set the value of this header.
		 * Use the constants provided by the ReferrerPolicyHeader class.
		 *
		 * @api setValue
		 * @param string $value Value for the header.
		 */
		public function setValue(string $value): void {
			$this->value = $value;
		}

		/**
		 * Get the field name for this header.
		 *
		 * @api getFieldName
		 * @return string
		 */
		public function getFieldName(): string {
			return 'Referrer-Policy';
		}

		/**
		 * Get the field value for this header.
		 *
		 * @api getFieldValue
		 * @return string
		 */
		public function getFieldValue(): string {
			return $this->value;
		}
	}