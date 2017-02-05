<?php
	class SimpleModule extends SiteModule
	{
		public function __construct()
		{
			$page_template = new KW_Template('template_file.php');
			$page_template->what = 'sun';
			parent::__construct('Random Page', $page_template);
		}
	}
