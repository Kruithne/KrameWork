<?php
	abstract class KW_JSONObject extends KW_JSONService
	{
		/**
		 * Calls __get on the underlying data container.
		 *
		 * @param $name string
		 * @return mixed
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		function __get($name)
		{
			return $this->data->__get($name);
		}

		/**
		 * Calls __set on the underlying data container.
		 *
		 * @param $name string
		 * @param $value mixed
		 * @return void
		 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
		 */
		function __set($name, $value)
		{
			$this->data->__set($name, $value);
		}

		/**
		 * Calls _invoke on the underlying data container.
		 *
		 * @return mixed
		 * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
		 */
		function __invoke($arr)
		{
			$this->data->__invoke($arr);
		}

		/**
		 * @param $request
		 * @return KW_DataContainer
		 */
		public function process($request)
		{
			$this->work($request);
			return $this->data;
		}

		abstract function work($request);

		/**
		 * @var KW_DataContainer
		 */
		protected $data;
	}
?>