<?php
	namespace KrameWork\HTTP;

	require_once(__DIR__ . '/HTTPHeader.php');

	/**
	 * Class CSPHeader
	 * Content Security Policy generator.
	 *
	 * @package KrameWork\Security
	 */
	class CSPHeader extends HTTPHeader
	{
		const SOURCE_NONE = '\'none\''; // Nothing allowed.
		const SOURCE_SELF = '\'self\''; // Same-domain only.
		const SOURCE_INLINE = '\'unsafe-inline\''; // Inline allowed.
		const SOURCE_EVAL = '\'unsafe-eval\''; // Inline allowed (with eval).
		const SOURCE_HTTPS = 'https:'; // Apply rule to HTTPS protocol.
		const SOURCE_DATA = 'data:'; // Apply rule to DATA protocol.

		const DIRECTIVE_DEFAULT = 'default-src'; // Fallback.
		const DIRECTIVE_BASE = 'base-uri'; // <base> URI.
		const DIRECTIVE_CHILD = 'child-src'; // Workers/embedded frames.
		const DIRECTIVE_CONNECT = 'connect-src'; // XHR, WebSockets, EventSource.
		const DIRECTIVE_FONT = 'font-src'; // Fonts
		const DIRECTIVE_FORM = 'form-action'; // Valid endpoints for forms.
		const DIRECTIVE_ANCESTORS = 'frame-ancestors'; // frame, iframe, embed, applet
		const DIRECTIVE_IMAGE = 'img-src'; // Images
		const DIRECTIVE_MEDIA = 'media-src'; // Video/audio.
		const DIRECTIVE_OBJECT = 'object-src'; // Flash/multimedia plug-ins.
		const DIRECTIVE_PLUGIN = 'plugin-types'; // Invokable plug-in types.
		const DIRECTIVE_REPORT = 'report-uri'; // URL for browser to report to.
		const DIRECTIVE_STYLE = 'style-src'; // Stylesheets.
		const DIRECTIVE_SCRIPT = 'script-src'; // Scripts.
		const DIRECTIVE_UPGRADE = 'upgrade-insecure-requests'; // Upgrade HTTP to HTTPS

		/**
		 * CSPHeader constructor.
		 *
		 * @api __construct
		 * @param array<string, string|string[]>|null $arr Initial policy input.
		 */
		public function __construct(?array $arr = null) {
			$this->directives = [];
			$this->directives[self::DIRECTIVE_DEFAULT] = self::SOURCE_SELF;

			if (\is_array($arr))
				$this->fromArray($arr);
		}

		/**
		 * Add directive/source pairs from an array.
		 *
		 * @api fromArray
		 * @param array<string, string|string[]> $arr Directive/source pair array.
		 */
		public function fromArray(array $arr): void {
			foreach ($arr as $directive => $source)
				$this->add($directive, $source);
		}

		/**
		 * Add a directive to this policy.
		 *
		 * @api add
		 * @param string[]|string $directives Directive (use CSPHeader:: constants).
		 * @param string[]|string $source Source directive.
		 */
		public function add(array|string $directives, array|string $source): void {
			if (\is_array($source))
				$source = \implode(' ', $source);

			foreach (\is_array($directives) ? $directives : [$directives] as $directive)
				$this->directives[$directive] = $source;
		}

		/**
		 * Get the field name for this header.
		 *
		 * @api getFieldName
		 * @return string
		 */
		public function getFieldName(): string {
			return 'Content-Security-Policy';
		}

		/**
		 * Get the field value for this header.
		 *
		 * @api getFieldValue
		 * @return string
		 */
		public function getFieldValue(): string {
			$parts = [];
			foreach ($this->directives as $directive => $source)
				$parts[] = $directive . ' ' . $source;

			return \implode('; ', $parts);
		}

		/**
		 * @var array<string, string>
		 */
		private array $directives;
	}