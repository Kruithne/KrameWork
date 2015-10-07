<?php
	class SimpleService extends KW_JSONService
	{
		public function process($request)
		{
			// Place logic to route handle requests here, returned object/array is serialized to json automatically
			return array('request' => $request, 'result' => 'demo');
		}
	}
?>
