<?php
	abstract class KW_JSONObject extends KW_JSONService
	{
		/**
		 * KW_JSONObject constructor.
		 * @param string $origin
		 * @param string $method
		 */
		public function __construct($origin = '*', $method = 'GET, POST')
		{
			$this->data = new KW_DataContainer();
			parent::__construct($origin, $method);
		}

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
		 * Calls __unset on the underlying data container.
		 * @param string $name
		 */
		function __unset($name)
		{
			$this->data->__unset($name);
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
		 * @return IDataContainer
		 */
		public function process($request)
		{
			$this->work($request);
			return $this->data;
		}

		abstract function work($request);

		/**
		 * @var IDataContainer
		 */
		protected $data;
	}
?>
