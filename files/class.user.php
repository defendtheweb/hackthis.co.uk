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
            $st = $db->prepare('SELECT username, score, status, priv.site_priv, priv.pm_priv, priv.forum_priv, priv.news_priv
                    FROM users u
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    WHERE u.user_id = :user_id');
            $st->execute(array(':user_id' => $this->uid));
            $st->setFetchMode(PDO::FETCH_INTO, $this);
            $st->fetch();
        }

        private function salt() {
            $rand = array();
            for ($i = 0; $i < 8; $i += 1) {
                $rand[] = pack('S', mt_rand(0, 0xffff));
            }
            $rand[] = substr(microtime(), 2, 6);
            $rand = sha1(implode('', $rand), true);
            $salt = '$2a$' . sprintf('%02d', 10) . '$';
            $salt .= strtr(substr(base64_encode($rand), 0, 22), array('+' => '.'));
            return $salt;
        }

        public function login($user, $pass) {
            global $db;
            $st = $db->prepare('SELECT user_id, password
                    FROM users
                    WHERE username = :u');
            $st->execute(array(':u' => $user));
            $row = $st->fetch();

            // Check if users details exist
            if ($row) {
                if ($row->password == crypt($pass, $row->password)) {
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
            }

            return $this->loggedIn;
        }

        public function register() {
            crypt($pass, $this->salt());
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