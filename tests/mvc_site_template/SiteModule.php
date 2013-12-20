<?php
	class SiteModule extends KW_Module
	{
		public function __construct($title, $content)
		{
			$this->template = new SiteTemplate($title, $content);
		}

		public function renderModule()
		{
			return $this->template;
		}

		private $template;
	}
?>