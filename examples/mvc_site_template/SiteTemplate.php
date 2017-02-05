<?php
	class SiteTemplate extends KW_Module
	{
		public function __construct($title, $content)
		{
			$this->template = new KW_Template('site_template_file.php');
			$this->template->title = $title;
			$this->template->content = $content;
		}

		public function renderModule()
		{
			return $this->template;
		}

		protected $template;
	}
