<?php
	abstract class KW_JSONService
	{
		public function __construct()
		{
			header('Access-Control-Allow-Methods: GET, POST');
			header('Access-Control-Allow-Origin: https://lab-public.runsafe.no');
			header('Access-Control-Allow-Headers: Content-Type, Cookie');
			header('Access-Control-Allow-Credentials: true');
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
			if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
				die();

			$request = json_decode(file_get_contents('php://input'));
			$response = (object)$this->process($request);
			header('Content-Type: application/json;charset=UTF-8');
			echo json_encode($response);
			die();
		}

		abstract public function process($request);
	}
?>
