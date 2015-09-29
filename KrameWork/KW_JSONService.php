<?php
	abstract class KW_JSONService
	{
		public function __construct()
		{
			$this->request = json_decode(file_get_contents('php://input'));
			$this->response = (object)array();
		}

		public function __tostring()
		{
		}

		public function respond()
		{
			header('Content-Type: application/json;charset=UTF-8');
			echo json_encode($this->response);
			die();
		}

		protected $request;
		protected $response;
	}
?>
