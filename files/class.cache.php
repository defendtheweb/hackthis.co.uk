<?php
	class cache {
		function __construct($app) {
			$this->app = $app;
		}

		function get($file, $freshness=null) {
			$file = $this->app->config['cache'] . $file;
			$data = false;
			if (file_exists($file) && (!$freshness || filemtime($file) > (time() - (60 * $freshness )))) {
				$fh = @fopen($file, "rb");
				if (!$fh)
					return false;
				$data = stream_get_contents($fh, filesize($file));
				fclose($fh);
			}

			return $data;
		}

		function set($file, $content) {
			$file = preg_replace("/[^A-Za-z0-9_-]/", '', $file);

			if (($fp = @fopen($this->app->config['cache'] . $file, 'w')) !== false) {
				fwrite($fp, $content);
				fclose($fp);
			} else {
				echo "Error saving cache...";
			}
		}
	}
?>