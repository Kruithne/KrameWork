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
		public function __construct(protected string $file) {
			if (!\is_file($file))
				throw new InvalidTemplateException('Template source does not exist.');

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
		 * @throws InvalidTemplateException
		 */
		public function __toString():string {
			\ob_start();

			try {
				// Sandbox template execution.
				new class ($this->file, $this->data) {
					/**
					 * Template sandbox constructor.
					 *
					 * @api __construct
					 * @param string $file Path to a template file.
					 * @param array<string, mixed> $data Template data.
					 */
					public function __construct(private string $file, private array $data) {
						$this->run();
					}

					private function run(): void {
						\extract($this->data);
						require($this->file);
					}
				};
			} finally {
				$output = \ob_get_clean();
			}

			if ($output === false)
				throw new InvalidTemplateException('Template closed the output buffer.');

			return $output;
		}

		/**
		 * @var array<string, mixed>
		 */
		protected array $data;
	}