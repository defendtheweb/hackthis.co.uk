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

			$this->stats = new stats();

			$this->notifications = new notifications();
		}

		public function config($key) {
			return $this->config[$key];
		}

		public function parse($text, $bbcode=true, $mentions=true) {
			if ($bbcode)
				$text = $this->bbcode->Parse($text);
			else
				$text = htmlspecialchars($text);

			if ($mentions) {
				$text = preg_replace_callback("/(?:(?<=\s)|^)@(\w*[A-Za-z_]+\w*)/", array($this, 'mentions_callback'), $text);
			}

			return $text;
		}

		private function mentions_callback($matches) {
			global $db;
			$mention = $matches[1];

            $st = $db->prepare('SELECT username FROM users WHERE username = :username LIMIT 1');
            $st->execute(array(':username' => $mention));
            
            if ($res = $st->fetch())
            	return "<a href='/user/{$res->username}'>{$res->username}</a>";

            return $matches[0];
		}
	}
?>