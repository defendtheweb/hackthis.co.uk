<?php
	class user {
		public $loggedIn = false;

		public function __construct() {
			//Check if user is logging in
			if (isset($_GET['logout'])) {
				$this->logout();
			}

			// Check if user is logged in
			if (isset($_SESSION['uid'])) {
				// Quick hijacking check
				if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent_id'] !== md5($_SERVER['HTTP_USER_AGENT'])) {
					$this->logout();
				} else {		
					$this->loggedIn = true;
					$this->uid = $_SESSION['uid'];
					$this->get_details();
				}
			} else {
				//Check if user is logging in
				if (isset($_GET['login'])) {
					$user = $_POST['username'];
					$pass = $_POST['password'];
					$this->login($user, $pass);
				}
			}
		}

		private function get_details() {
			global $db;
			$st = $db->prepare('SELECT username, score, status, login_count
					FROM users u
					LEFT OUTER JOIN users_activity activity
					ON u.user_id = activity.user_id
					WHERE u.user_id = :user_id');
			$st->execute(array(':user_id' => $this->uid));
			$st->setFetchMode(PDO::FETCH_INTO, $this);
			$st->fetch();
		}

		public function login($user, $pass) {
			// TODO Generate password hash
			$pass = sha1($pass);

			global $db;
			$st = $db->prepare('SELECT user_id
					FROM users
					WHERE username = :u AND password = :p');
			$st->execute(array(':u' => $user, ':p' => $pass));
			$row = $st->fetch();

			// Check if users details exist
			if ($row) {
				$this->loggedIn = true;
				$this->uid = $row->user_id;

				//session_regenerate_id();
				$_SESSION['uid'] = $this->uid;

				// Basic hijacking prevention
				$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['user_agent_id'] = md5($_SERVER['HTTP_USER_AGENT']);
				
				// Redirect user back to where they came from
				header("location: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
			}

			return $this->loggedIn;
		}

		public function logout() {
			$this->loggedIn = false;
			session_regenerate_id(true);
			
			// Redirect user back to index page
			header("Location: /");
		}

		public function __toString() {
			return (isset($this->username)) ? $this->username : '';
		}
	}
?>