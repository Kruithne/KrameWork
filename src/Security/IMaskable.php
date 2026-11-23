<?php
	namespace KrameWork\Security;

	/**
	 * Interface IMaskable
	 * Object that can be represented as a masked string.
	 *
	 * @package KrameWork\Security
	 * @author Kruithne <kruithne@gmail.com>
	 */
	interface IMaskable
	{
		/**
		 * Get the masked value of this object.
		 *
		 * @api asMask
		 * @param string $char Mask character.
		 * @return string
		 */
		public function asMask(string $char = '*'):string;
	}