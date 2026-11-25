<?php
	namespace KrameWork;

	use KrameWork\Utils\StringUtil;
	require_once(__DIR__ . '/Utils/StringUtil.php');

	class InvalidSourcePathException extends \Exception {}

	/**
	 * Class AutoLoader
	 * Handles the automatic loading of files based on class initiation.
	 *
	 * @package KrameWork
	 */
	class AutoLoader
	{
		const RECURSIVE_SOURCING = 0x1;
		const INCLUDE_WORKING_DIRECTORY = 0x2;
		const INCLUDE_KRAMEWORK_DIRECTORY = 0x4;
		const DEFAULT_FLAGS = self::INCLUDE_WORKING_DIRECTORY | self::INCLUDE_KRAMEWORK_DIRECTORY;

		/**
		 * AutoLoader constructor.
		 *
		 * @api __construct
		 * @param array<int|string, string> $sources List of sources (strings) or namespace/source key-value array.
		 * @param string[] $extensions Allowed extensions.
		 * @param int $flags Flags to control auto-loading.
		 * @throws InvalidSourcePathException
		 */
		public function __construct(?array $sources = null, ?array $extensions = null, int $flags = self::DEFAULT_FLAGS) {
			$this->sources = [];
			$this->extensions = [];

			$this->setExtensions($extensions ?? ['php']); // Set extensions.
			$this->addSources($sources ?? []); // Pre-compute source paths/maps.

			if ($flags & self::INCLUDE_KRAMEWORK_DIRECTORY)
				$this->sources[] = ['KrameWork', __DIR__];

			if ($flags & self::INCLUDE_WORKING_DIRECTORY) {
				$cwd = \getcwd();
				if ($cwd !== false)
					$this->sources[] = $cwd;
			}

			// Register this auto-loader instance with PHP.
			spl_autoload_register([$this, 'loadClass']);

			$this->flags = $flags;
			$this->enabled = true;
		}

		/**
		 * Set the extensions which can be loaded by this auto loader.
		 *
		 * @api setExtensions
		 * @param string[] $extensions Extensions to be loaded.
		 */
		public function setExtensions(array $extensions): void {
			// Remove any leading periods from extensions.
			foreach ($extensions as $ext)
				$this->extensions[] = \ltrim($ext, ".");
		}

		/**
		 * Add sources to this auto-loader.
		 *
		 * @api addSources
		 * @param array<int|string, string> $sources Namespace => Path mapping.
		 * @throws InvalidSourcePathException
		 */
		public function addSources(array $sources): void {
			foreach ($sources as $sourceName => $sourcePath) {
				// Verify source path.
				$real = \realpath(StringUtil::formatDirectorySlashes($sourcePath));
				if ($real === false)
					throw new InvalidSourcePathException('Invalid source path: ' . $sourcePath);

				if (\is_string($sourceName)) {
					// Convert namespace separators if needed.
					if (\DIRECTORY_SEPARATOR == '/')
						$sourceName = \str_replace('\\', \DIRECTORY_SEPARATOR, $sourceName);

					$this->sources[] = [$sourceName, $real];
				} else {
					$this->sources[] = $real;
				}
			}
		}

		/**
		 * Attempt to load a given class.
		 *
		 * @api loadClass
		 * @param string $className Name of the class to load.
		 */
		public function loadClass(string $className): void {
			if (!$this->enabled)
				return;

			$className = StringUtil::formatDirectorySlashes($className);
			$queue = $this->sources;

			$i = 0;
			$queueSize = \count($queue);

			while ($i < $queueSize) {
				$class = $className;

				$directory = $queue[$i++];

				if (\is_array($directory)) {
					list($namespace, $path) = $directory;
					$namespaceLen = \strlen($namespace);

					if (\strncmp($class, $namespace, $namespaceLen) !== 0)
						continue;

					$class = \trim(\substr($class, $namespaceLen), \DIRECTORY_SEPARATOR);
					$directory = $path;
				}

				foreach ($this->extensions as $ext) {
					$file = $directory . \DIRECTORY_SEPARATOR . $class . '.' . $ext;
					if (\file_exists($file)) {
						require_once($file);
						return;
					}
				}

				if ($this->flags & self::RECURSIVE_SOURCING) {
					if ($handle = \opendir($directory)) {
						while (($entry = \readdir($handle)) !== false) {
							if ($entry == '.' || $entry == '..')
								continue;

							$path = $directory . \DIRECTORY_SEPARATOR . $entry;
							if (\is_dir($path)) {
								\array_push($queue, $path);
								$queueSize++;
							}
						}

						\closedir($handle);
					}
				}
			}
		}

		/**
		 * Disable this auto-loader, preventing it from loading classes.
		 *
		 * @api disable
		 */
		public function disable(): void {
			$this->enabled = false;
		}

		/**
		 * Enable this auto-loader, allowing it to load classes.
		 *
		 * @api enable
		 */
		public function enable(): void {
			$this->enabled = true;
		}

		/**
		 * @var (string|array{string, string})[]
		 */
		private array $sources;

		/**
		 * @var string[]
		 */
		private array $extensions;

		/**
		 * @var int
		 */
		private int $flags;

		/**
		 * @var bool
		 */
		private bool $enabled;
	}