<?php
	namespace KrameWork\HTTP;
	require_once(__DIR__ . '/HTTPHeader.php');

	/**
	 * Class HSTSHeader
	 * HTTP Strict Transport Security.
	 *
	 * @package KrameWork\Security
	 * @author Kruithne <kruithne@gmail.com>
	 */
	class HSTSHeader extends HTTPHeader {
		/**
		 * HSTSHeader constructor.
		 *
		 * @api __construct
		 * @param int $maxAge How long user-agents will redirect to HTTPS in seconds.
		 * @param bool $includeSubDomains Upgrade requests for sub-domains.
		 * @param bool $preload Include on HSTSHeader preload list.
		 * @link https://hstspreload.org/
		 */
		public function __construct(int $maxAge = 63072000, bool $includeSubDomains = true, bool $preload = true) {
			$this->maxAge = $maxAge;
			$this->includeSubDomains = $includeSubDomains;
			$this->preload = $preload;
		}

		/**
		 * Set how long user-agents will redirect to HTTPS in seconds.
		 * Minimum accepted value by browsers is two months (63072000).
		 *
		 * @api setMaxAge
		 * @param int $maxAge Time period HTTPs will be supported.
		 */
		public function setMaxAge(int $maxAge): void {
			$this->maxAge = $maxAge;
		}

		/**
		 * Upgrade requests for sub-domains.
		 *
		 * @api setIncludeSubDomains
		 * @param bool $include Include sub-domains.
		 */
		public function setIncludeSubDomains(bool $include): void {
			$this->includeSubDomains = $include;
		}

		/**
		 * Include on HSTSHeader preload lists.
		 *
		 * @api setPreload
		 * @param bool $preload Add to preload lists.
		 */
		public function setPreload(bool $preload): void {
			$this->preload = $preload;
		}

		/**
		 * Get the field name for this header.
		 *
		 * @api getFieldName
		 * @return string
		 */
		public function getFieldName(): string {
			return 'Strict-Transport-Security';
		}

		/**
		 * Get the field value for this header.
		 *
		 * @api getFieldValue
		 * @return string
		 */
		public function getFieldValue(): string {
			$value = 'max-age=' . $this->maxAge;

			if ($this->includeSubDomains)
				$value .= '; includeSubDomains';

			if ($this->preload)
				$value .= '; preload';

			return $value;
		}

		/**
		 * @var int
		 */
		protected int $maxAge;

		/**
		 * @var bool
		 */
		protected bool $includeSubDomains;

		/**
		 * @var bool
		 */
		protected bool $preload;
	}