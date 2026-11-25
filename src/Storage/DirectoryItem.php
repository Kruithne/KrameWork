<?php
	namespace KrameWork\Storage;

	use KrameWork\Utils\StringUtil;
	require_once(__DIR__ . '/../Utils/StringUtil.php');

	/**
	 * Interface IDirectoryItem
	 *
	 * @package KrameWork\Storage
	 */
	interface IDirectoryItem
	{
		/**
		 * Return the name of this directory item.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName():string;

		/**
		 * Return the full path of this directory item.
		 *
		 * @api getPath
		 * @return string
		 */
		public function getPath():string;

		/**
		 * Check if this directory item exists.
		 *
		 * @api exists
		 * @return bool
		 */
		public function exists():bool;

		/**
		 * Check if the directory item is valid.
		 *
		 * @api isValid
		 * @return bool
		 */
		public function isValid():bool;

		/**
		 * Attempt to delete the directory item.
		 *
		 * @api delete
		 * @return bool
		 */
		public function delete():bool;
	}

	/**
	 * Class DirectoryItem
	 * Base class for file-system wrapper classes.
	 *
	 * @package KrameWork\Storage
	 */
	abstract class DirectoryItem implements IDirectoryItem
	{
		/**
		 * DirectoryItem constructor.
		 *
		 * @api __construct
		 * @param string $source Path to the directory item.
		 */
		public function __construct(string $source) {
			$this->path = StringUtil::formatDirectorySlashes($source, true);
			$this->name = \basename($source);
		}

		/**
		 * Check if this directory item exists.
		 *
		 * @api exists
		 * @return bool
		 */
		public function exists():bool {
			return \file_exists($this->path);
		}

		/**
		 * Return the name of this directory item.
		 *
		 * @api getName
		 * @return string
		 */
		public function getName():string {
			return $this->name;
		}

		/**
		 * Return the full path of this directory item.
		 *
		 * @api getPath
		 * @return string
		 */
		public function getPath():string {
			return $this->path;
		}

		/**
		 * @var string
		 */
		protected string $path = '';

		/**
		 * @var string
		 */
		protected string $name = '';
	}