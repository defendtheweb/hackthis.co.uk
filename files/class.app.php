<?php
	class app {
		function __construct() {
			//load configuration file
			require('config.php');
			if (!isset($config) || !is_array($config))
				throw new Exception('Config error');

			$this->config = $config;
		}

		public function config($key){
			return $this->config[$key];
		}
	}
?>