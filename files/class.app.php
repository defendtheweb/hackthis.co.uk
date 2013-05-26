<?php
	class app {
		private $bbcode;

		function __construct() {
			global $custom_css, $custom_js;

			//load configuration file
			require('config.php');
			if (!isset($config) || !is_array($config))
				throw new Exception('Config error');

			$this->config = $config;

			$this->utils = new utils();

			if (!is_array($custom_css))
				$custom_css = Array();
			if (!is_array($custom_js))
				$custom_js = Array();

			require('vendor/nbbc.php');
			$this->bbcode = new BBCode;
			array_push($custom_css, 'bbcode.css');
			$this->bbcode->SetDetectURLs(true);

			$this->stats = new stats();

			$this->notifications = new notifications();
		}

		public function config($key) {
			return $this->config[$key];
		}

		public function parse($text, $bbcode=true, $mentions=true) {
			if ($bbcode) {
				$text = $this->bbcode->Parse($text);
				if ($mentions) {
					$text = preg_replace_callback("/(?:(?<=\s)|^)@(\w*[A-Za-z_]+\w*)/", array($this, 'mentions_callback'), $text);
				}
			} else {
				$text = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $text); // Strip bbcode
				$text = htmlspecialchars($text);
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