<?php
	class app {
		function __construct() {
			//load configuration file
			require('config.php');
			if (!isset($config) || !is_array($config))
				throw new Exception('Config error');

			$this->config = $config;

			$this->utils = new utils();

			$this->custom_css = Array();
			$this->custom_js = Array();

			require('vendor/nbbc.php');
			$this->bbcode = new BBCode;
			array_push($this->custom_css, 'bbcode.css');
			$this->bbcode->SetDetectURLs(true);
		}

		public function config($key) {
			return $this->config[$key];
		}
	}
?>