<?php
    class user {
        public $loggedIn = false;
        public $admin = false;

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

            //Login, register or connect via facebook
            if (isset($_GET['facebook'])) {
                if (isset($_GET['code'])) {
                    $this->oauth('facebook', $_GET['code']);
                } else if ($_GET['error_code'] == '200') {
                    $this->login_error = 'Request declined';
                    $this->connect_msg = 'Request declined';
                }
            }
        }

        private function get_details() {
            global $db, $app;

            $app->stats->users_activity($this);

            $st = $db->prepare('SELECT username, score, status, email, (oauth_id IS NOT NULL) as connected,
                    IFNULL(site_priv, 1) as site_priv, IFNULL(pm_priv, 1) as pm_priv, IFNULL(forum_priv, 1) as forum_priv, IFNULL(pub_priv, 0) as pub_priv,
                    profile.gravatar, IF (profile.gravatar = 1, u.email , profile.img) as `image`,
                    activity.consecutive, activity.consecutive_most
                    FROM users u
                    LEFT JOIN users_profile profile
                    ON u.user_id = profile.user_id
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    LEFT JOIN users_activity activity
                    ON u.user_id = activity.user_id
                    WHERE u.user_id = :user_id');
            $st->execute(array(':user_id' => $this->uid));
            $st->setFetchMode(PDO::FETCH_INTO, $this);
            $st->fetch();

            if ($this->site_priv > 1 ||
                $this->pm_priv > 1 ||
                $this->forum_priv > 1 ||
                $this->pub_priv > 1)
                    $this->admin = true;

            if (isset($this->image)) {
                $gravatar = isset($this->gravatar) && $this->gravatar == 1;
                $this->image = profile::getImg($this->image, 100, $gravatar);
            } else
                $this->image = profile::getImg(null, 100);
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
                    $this->createSession();
                }
            }

            return $this->loggedIn;
        }

        public function oauth($provider, $id) {
            global $db, $app;
            if ($provider === 'facebook') {
                $redirect = urlencode('http://dev.hackthis/?facebook');

                $fb = $app->config('facebook');
                $uri = "https://graph.facebook.com/oauth/access_token?client_id={$fb['public']}&redirect_uri={$redirect}&client_secret={$fb['secret']}&code={$id}";

                $content = @file_get_contents($uri);
                if (!$content) {
                    $this->login_error = 'Request declined';
                    $this->connect_error = 'Request declined';
                    return false;
                }

                parse_str($content, $values);

                $access_token = $values['access_token'];

                // get details
                $uri = "https://graph.facebook.com/me?access_token={$access_token}";
                $content = file_get_contents($uri);
                if (!$content) {
                    $this->login_error = 'Request declined';
                    $this->connect_msg = 'Request declined';
                    return false;
                }
                $token_details = json_decode($content);

                $fid = $token_details->id;

                //Is user logged in?
                if ($this->loggedIn) {
                    //Connect to existing account
                    $st = $db->prepare('INSERT INTO users_oauth (`uid`, `provider`)
                            VALUES (:fid, "facebook")');
                    $result = $st->execute(array(':fid' => $fid));
                    if (!$result) {
                        $this->connect_msg = 'Facebook account already connected to another user';
                        return false;
                    }
                    $oauth_id = $db->lastInsertId();

                    $st = $db->prepare('UPDATE users SET oauth_id = :oauth
                            WHERE user_id = :uid LIMIT 1');
                    $result = $st->execute(array(':oauth' => $oauth_id, ':uid' => $this->uid));
                    $this->connect_msg = 'Connected, you can now login using your Facebook account or password';
                    $this->connected = true;
                } else { 
                    //Login or register
                    //lookup fid
                    $st = $db->prepare('SELECT u.user_id, IFNULL(priv.site_priv, 1) as site_priv
                            FROM users_oauth oauth
                            INNER JOIN users u
                            ON oauth.id = u.oauth_id
                            LEFT JOIN users_priv priv
                            ON u.user_id = priv.user_id
                            WHERE oauth.uid = :fid AND oauth.provider = "facebook"');
                    $st->execute(array(':fid' => $fid));
                    $row = $st->fetch();   

                    if ($row) {
                        if (!$row->site_priv) {
                            $this->login_error = 'Account has been banned';
                            return false;
                        }

                        $this->loggedIn = true;
                        $this->uid = $row->user_id;
                        $this->createSession();
                    } else {
                        //Assume this is a registration
                        $this->login_error = 'Registration needed - ' . $fid;                 

                        // name - $token_details->name;
                        // username - $token_details->username;
                        // gender - $token_details->gender;
                        // email - $token_details->email;

                        // Add to DB - create oauth entry
                        $st = $db->prepare('INSERT INTO users_oauth (`uid`, `provider`)
                                VALUES (:fid, "facebook")');
                        $result = $st->execute(array(':fid' => $fid));
                        if (!$result) {
                            $this->login_error = 'Error registering';
                            return false;
                        }
                        $oauth_id = $db->lastInsertId();

                        // Create user
                        $st = $db->prepare('INSERT INTO users (`username`, `oauth_id`, `email`)
                                VALUES (:u, :oid, :email)');
                        $result = $st->execute(array(':u' => $token_details->username, ':oid' => $oauth_id, ':email' => $token_details->email));
                        if (!$result) {
                            $this->login_error = 'Error registering';
                            return false;
                        }
                        $uid = $db->lastInsertId();

                        // Create profile
                        switch($token_details->gender) {
                            case "male":
                                $gender = 'male';
                                break;
                            case "female":
                                $gender = 'female';
                                break;
                            default:
                                $gender = NULL;
                        }

                        $st = $db->prepare('INSERT INTO users_profile (`user_id`, `name`, `gender`)
                                VALUES (:uid, :name, :gender)');
                        $result = $st->execute(array(':uid' => $uid, ':name' => $token_details->name, ':gender' => $gender));
                        if (!$result) {
                            $this->login_error = 'Error registering';
                            return false;
                        }

                        // Login user
                        $this->loggedIn = true;
                        $this->uid = $uid;

                        $this->createSession();

                        return false;
                    }
                }     
            }
        }

        private function createSession() {
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

            $st = $db->prepare('SELECT username FROM users WHERE username=?');
            $st->bindParam(1, $username);
            $st->execute();
            if ($st->fetch(PDO::FETCH_ASSOC))
                return "Username already in use";

            $pass = $_POST['reg_password'];
            if (!isset($pass))
                return "Invalid password";
            if ($pass !== $_POST['reg_password_2'])
                return "Passwords don't match";

            $hash = crypt($pass, $this->salt());

            $email = $_POST['reg_email'];
            if (!$app->utils->check_email($email))
                return "Invalid email address";

            $st = $db->prepare('SELECT username FROM users WHERE email=?');
            $st->bindParam(1, $email);
            $st->execute();
            if ($st->fetch(PDO::FETCH_ASSOC))
                return "Email already in use";

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

            $this->createSession();
        }

        public function logout() {
            $this->loggedIn = false;
            session_regenerate_id(true);
            
            // Redirect user back to index page
            header("Location: /");
        }

        /* MISC */
        public function hideConnect() {
            global $db;

            $st = $db->prepare('UPDATE users SET `oauth_id` = 0 WHERE `user_id` = :uid');
            $result = $st->execute(array(':uid' => $this->uid));
           
            return $result;
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

        public function setImagePath($path) {
            global $db, $app;

            if ($path != 'gravatar') {
                $st = $db->prepare('INSERT INTO users_profile (`user_id`, `img`) VALUES (:uid, :path) ON DUPLICATE KEY UPDATE img = :path, gravatar = 0');
                $result = $st->execute(array(':path' => $path, ':uid' => $this->uid));
            } else {
                $st = $db->prepare('INSERT INTO users_profile (`user_id`, `gravatar`) VALUES (:uid, 1) ON DUPLICATE KEY UPDATE gravatar = 1');
                $result = $st->execute(array(':uid' => $this->uid));
            }

            $app->awardMedal(11, $this->uid);         
        }
    }
?>