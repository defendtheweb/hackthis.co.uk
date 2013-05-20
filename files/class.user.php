<?php
    class user {
        public $loggedIn = false;

        public function __construct() {
            //Check if user is logging in
            if (isset($_GET['logout'])) {
                $this->logout();
            }

            //Check if user is registering in
            if (isset($_GET['register'])) {
                $this->reg_error = $this->register();
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
                if (isset($_GET['login']) && isset($_POST['username']) && isset($_POST['password'])) {
                    $user = $_POST['username'];
                    $pass = $_POST['password'];
                    $this->login($user, $pass);
                }
            }
        }

        private function get_details() {
            global $db, $app;
            $st = $db->prepare('SELECT username, score, status,
                    IFNULL(site_priv, 1) as site_priv, IFNULL(pm_priv, 1) as pm_priv, IFNULL(forum_priv, 1) as forum_priv, IFNULL(pub_priv, 0) as pub_priv
                    FROM users u
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    WHERE u.user_id = :user_id');
            $st->execute(array(':user_id' => $this->uid));
            $st->setFetchMode(PDO::FETCH_INTO, $this);
            $st->fetch();

            $app->stats->users_activity($this);
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
            $st = $db->prepare('SELECT u.user_id, u.password, IFNULL(priv.site_priv, 1) as site_priv
                    FROM users u
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    WHERE username = :u');
            $st->execute(array(':u' => $user));
            $row = $st->fetch();

            // Check if users details exist
            $this->login_error = 'Invalid login details';
            if ($row) {
                if ($row->password == crypt($pass, $row->password)) {

                    if (!$row->site_priv) {
                        $this->login_error = 'Account has been banned';
                        return false;
                    }

                    $this->loggedIn = true;
                    $this->uid = $row->user_id;
                    $this->create_session();
                }
            }

            return $this->loggedIn;
        }

        private function create_session() {
            global $app;
            if ($this->loggedIn && isset($this->uid)) {
                //session_regenerate_id();
                $_SESSION['uid'] = $this->uid;

                // Basic hijacking prevention
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent_id'] = md5($_SERVER['HTTP_USER_AGENT']);
                
                $app->stats->users_activity($this, true);

                // Redirect user back to where they came from
                header("location: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
            }     
        }

        public function register() {
            global $db, $app;

            //Input check
            $username = $_POST['reg_username'];
            if (!$app->utils->check_user($username))
                return "Invalid username";

            $pass = $_POST['reg_password'];
            if (!isset($pass) || strlen($pass) < 3)
                return "Invalid password";
            if ($pass !== $_POST['reg_password_2'])
                return "Passwords don't match";

            $hash = crypt($pass, $this->salt());

            $email = $_POST['reg_email'];
            if (!$app->utils->check_email($email))
                return "Invalid email address";

            // Add to DB
            $st = $db->prepare('INSERT INTO users (`username`, `password`, `email`)
                    VALUES (:u, :p, :e)');
            $result = $st->execute(array(':u' => $username, ':p' => $hash, ':e' => $email));

            if (!$result)
                return "Error creating account";

            $uid = $db->lastInsertId();

            // Login user
            $this->loggedIn = true;
            $this->uid = $uid;

            $this->create_session();
        }

        public function logout() {
            $this->loggedIn = false;
            session_regenerate_id(true);
            
            // Redirect user back to index page
            header("Location: /");
        }

        public function __get($property) {
            // check for admin privilages
            if (substr($property, 0, 6) === "admin_") {
                $property = substr($property, 6);
                return ($this->$property > 1);
            }

            if (property_exists($this, $property))
                return $this->$property;
        }

        public function __toString() {
            return (isset($this->username)) ? $this->username : '';
        }
    }
?>