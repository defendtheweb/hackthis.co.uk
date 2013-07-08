<?php
	class cache {
		function __construct($app) {
			$this->app = $app;
		}

		function get($file) {
			$file = $this->app->config['cache'] . $file;
			$fh = @fopen($file, "rb");
			if (!$fh)
				return false;
			$data = fread($fh, filesize($file));
			fclose($fh);

			return $data;
		}
	}
?>