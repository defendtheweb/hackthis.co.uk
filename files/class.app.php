<?php
	class app {
		public $bbcode;

		function __construct() {
			global $custom_css, $custom_js;

			//load configuration file
			require('config.php');
			if (!isset($config) || !is_array($config))
				throw new Exception('Config error');

			$this->config = $config;
			$this->config['cache'] = $this->config['path'] . "/files/cache/";

			$this->utils = new utils();

			if (!is_array($custom_css))
				$custom_css = Array();
			if (!is_array($custom_js))
				$custom_js = Array();

			require('vendor/nbbc.php');
			$this->bbcode = new BBCode;
			array_push($custom_css, 'bbcode.scss');
			array_push($custom_js, 'bbcode.js');
			//$this->bbcode->SetDetectURLs(true);

			$this->stats = new stats();

			$this->notifications = new notifications();
			$this->feed = new feed();

			//get version number
			$this->cache = new cache($this);
			$this->version = substr($this->cache->get('version'), 1);
		}

		public function config($key) {
			return $this->config[$key];
		}

		public function parse($text, $bbcode=true, $mentions=true) {
			return $this->utils->parse($text, $bbcode, $mentions);
		}
	}
?>