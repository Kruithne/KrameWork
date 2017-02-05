<?php
	abstract class KW_Enum
	{
		/**
		 * Retrieves a key->value array representing this enums values.
		 *
		 * @return array The constants for this enum.
		 */
		private static function values()
		{
			if (self::$constants == null)
			{
				$classObject = new ReflectionClass(get_called_class());
				self::$constants = $classObject->getConstants();
			}
			return self::$constants;
		}

		/**
		 * Get the value of a key in the enum object.
		 *
		 * @param string $name The key to check.
		 * @param bool $caseSensitive Should we be case-sensitive?
		 * @return object|null The value from the enum, null if it does not exist.
		 */
		public static function valueOf($name, $caseSensitive = false)
		{
			$constants = self::values();

			if ($caseSensitive)
			{
				return array_key_exists($name, $constants) ? $constants[$name] : null;
			}
			else
			{
				$name = strtolower($name);

				foreach ($constants as $constant => $value)
					if (strtolower($constant) == $name)
						return $value;

				return null;
			}
		}

		/**
		 * @var array
		 */
		private static $constants;
	}
