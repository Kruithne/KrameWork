<?php
	class SimpleModule extends KW_Module
	{
		public function __construct()
		{
			$this->template = new KW_Template('template_file.php');
			$this->template->title = 'Page Title';
			$this->template->content = 'This is some content, how nifty!';
		}

		public function renderModule()
		{
			return $this->template;
		}

		protected $template;
	}
?>