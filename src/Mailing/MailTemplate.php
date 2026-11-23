<?php
	namespace KrameWork\Mailing;

	/**
	 * Class FileNotFoundException
	 *
	 * @package KrameWork\Mailing
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class FileNotFoundException extends \Exception {}

	/**
	 * Class MailTemplate
	 * Used to load an e-mail body from a templated file.
	 *
	 * @package KrameWork\Mailing
	 * @author Kruithne (kruithne@gmail.com)
	 */
	class MailTemplate
	{
		/**
		 * MailTemplate constructor.
		 *
		 * @api
		 * @param string $file Template file to use.
		 * @param array $subs Substitutes for the template.
		 * @throws FileNotFoundException
		 */
		public function __construct(string $file, array $subs) {
			if (!\is_file($file))
				throw new FileNotFoundException('Provided template is not a real file.');

			$data = \file_get_contents($file);
			if ($data === false)
				throw new FileNotFoundException('Provided template file cannot be accessed.');

			$this->raw = $data;
			$this->subs = $subs;
		}

		/**
		 * Compile the template to a string.
		 *
		 * @internal
		 * @return string
		 */
		private function compile(): string {
			if ($this->compiled === null) {
				$comp = $this->raw;
				foreach ($this->subs as $key => $value)
					$comp = \str_replace('{' . $key . '}', $value, $comp);

				$this->compiled = $comp;
			}
			return $this->compiled;
		}

		/**
		 * Return the compiled template.
		 * @return string
		 */
		public function __toString(): string {
			return $this->compile();
		}

		/**
		 * @var string
		 */
		private string $raw;

		/**
		 * @var array
		 */
		private array $subs;

		/**
		 * @var string|null
		 */
		private ?string $compiled = null;
	}