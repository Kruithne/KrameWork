<?php
	abstract class KW_JSONService
	{
		public function __construct($origin = '*', $method = 'GET, POST')
		{
			header('Access-Control-Allow-Origin: '.$origin);
			header('Access-Control-Allow-Methods: '.$method);
			header('Access-Control-Allow-Headers: Content-Type, Cookie');
			header('Access-Control-Allow-Credentials: true');
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
			if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
				die();

			$request = json_decode(file_get_contents('php://input'));
			$response = $this->process($request);
			header('Content-Type: application/json;charset=UTF-8');
			echo json_encode($response);
			die();
		}

		abstract public function process($request);
	}
?>
