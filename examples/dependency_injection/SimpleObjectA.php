<?php
	class SimpleObjectA
	{
		public function __construct(SimpleObjectB $object)
		{
			print("ObjectA parameter result: " . $object);
		}
	}
?>