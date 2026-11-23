<?php
	namespace KrameWork\MVC;

	class InvalidTemplateException extends \Exception {}

	class Template
	{
		/**
		 * Template constructor.
		 *
		 * @api __construct
		 * @param string $file Path to a template file.
		 * @throws InvalidTemplateException
		 */
		public function __construct(string $file) {
			if (!\is_file($file))
				throw new InvalidTemplateException('Template source does not exist.');

			$this->file = $file;
			$this->data = [];
		}

		/**
		 * Obtain a value stored by this template.
		 * Returns null if the key does not exist in the template.
		 *
		 * @api __get
		 * @param string $key Key to get the value for.
		 * @return mixed
		 */
		public function __get(string $key): mixed {
			return $this->data[$key] ?? null;
		}

		/**
		 * Set a value to be stored by this template.
		 *
		 * @api __set
		 * @param string $key Key to store the value with.
		 * @param mixed $value Value to store.
		 */
		public function __set(string $key, mixed $value): void {
			$this->data[$key] = $value;
		}

		/**
		 * Render this template and return it as a string.
		 *
		 * @api __toString
		 * @return string
		 */
		public function __toString():string {
			\ob_start();

			// Sandbox template execution.
			new class ($this->file, $this->data) {
				public function __construct(string $file, array $data) {
					$this->file = $file;
					$this->data = $data;

					$this->run();
				}

				private function run(): void {
					\extract($this->data);
					require($this->file);
				}

				private string $file;
				private array $data;
			};

			return \ob_get_clean();
		}

		/**
		 * @var array
		 */
		protected array $data;

		/**
		 * @var string
		 */
		protected string $file;
	}