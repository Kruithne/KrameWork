<?php
	class AutoService extends KW_AutoService
	{
		public function __construct()
		{
			parent::__construct('*',['one','two','error']);
		}

		public function one($arg)
		{
			return 'In AutoService::one('.$arg.')';
		}

		public function two()
		{
			return 'In AutoService::two()';
		}

		public function error($msg)
		{
			throw new Exception($msg);
		}
	}
?>
