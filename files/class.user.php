<?php
    /**
     * Member based functions
     *
     * @author HackThis!! <admin@hackthis.co.uk>
     * @todo Check if all functions need to be public
     */

    class user {
        /**
         * Reference to global application object
         */
        private $app;
        /**
         * Tracks if current user is logged in
         */
        public $loggedIn = false;
        /**
         * Tracks if current user has any admin privileges
         */
        public $admin = false;

        /**
         * Constructor
         *
         * Builds object and performs certain tasks based on GET/POST parameters; including but not limited to registering, logging in and logging out users.
         *
         * @param app $app Global application object
         * 
         * @return void
         */
        public function __construct($app) {
            $this->app = $app;
            $app->user = $this;

            //Check if user is logging in
            if (isset($_GET['logout']) && $_GET['logout'] == $_SESSION['csrf_basic']) {
                $this->logout();
            }

            //Check if user is registering in
            if (isset($_GET['register'])) {
                $this->reg_error = $this->register();
            }

            // Check if user is logged in
            if (isset($_SESSION['uid'])) {
                // Quick hijacking check, unless basic+ 2
                if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
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
                } else {
                    // Check there autologin cookie
                    $this->checkRememberToken();
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

        /**
         * Loads all user data into object
         *
         * Also handles checking medals scores/consecutive logins/karma
         *
         * @todo Split functionality into separate functions e.g. medal checks
         */
        private function get_details() {
            $this->app->stats->users_activity($this);

            $st = $this->app->db->prepare('SELECT username, score, email, (oauth_id IS NOT NULL) as connected,
                    IFNULL(site_priv, 1) as site_priv, IFNULL(pm_priv, 1) as pm_priv, IFNULL(forum_priv, 1) as forum_priv, IFNULL(pub_priv, 1) as pub_priv, verified, IFNULL(`posts`.posts, 0) AS `posts`,
                    profile.gravatar, profile.img as `image`,
                    activity.consecutive, activity.consecutive_most, activity.joined
                    FROM users u
                    LEFT JOIN users_profile profile
                    ON u.user_id = profile.user_id
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    LEFT JOIN users_activity activity
                    ON u.user_id = activity.user_id
                    LEFT JOIN (SELECT COUNT(post_id) AS `posts`, author FROM forum_posts WHERE deleted = 0 GROUP BY author) `posts`
                    ON `posts`.author = u.user_id
                    WHERE u.user_id = :user_id');
            $st->execute(array(':user_id' => $this->uid));
            $st->setFetchMode(PDO::FETCH_INTO, $this);
            $st->fetch();

            if ($this->site_priv > 1 ||
                $this->pm_priv > 1 ||
                $this->forum_priv > 1 ||
                $this->pub_priv > 1)
                    $this->admin = true;


            if (isset($this->gravatar) && $this->gravatar == 1) {
                // If user is currently using gravatar but has uploaded an image previously
                if (isset($this->image))
                    $this->image_old = profile::getImg($this->image, 75, 0);

                $this->image = profile::getImg($this->email, 100, 1);
            } else if (isset($this->image))
                $this->image = profile::getImg($this->image, 100, 0);
            else
                $this->image = profile::getImg(null, 100);

            // Check score and award medal?
            if ($this->score >= $this->app->max_score)
                $this->score_perc = 100;
            else
                $this->score_perc = $this->score/$this->app->max_score * 100;

            if ($this->score >= 5000) {
                $this->awardMedal('score', 3);
            } else if ($this->score >= 2500) {
                $this->awardMedal('score', 2);
            } else if ($this->score >= 1000) {
                $this->awardMedal('score');
            }

            // Check consecutive logins
            if ($this->consecutive <= 7)
                $consecutive_target = 7;
            elseif ($this->consecutive <= 14)
                $consecutive_target = 14;
            else
                $consecutive_target = 30;

            if ($this->consecutive >= $consecutive_target)
                $this->consecutive_perc = 100;
            else
                $this->consecutive_perc = $this->consecutive/$consecutive_target * 100;

            if ($this->consecutive == 7) {
                $this->awardMedal('visits');
            } else if ($this->consecutive == 14) {
                $this->awardMedal('visits', 2);
            } else if ($this->consecutive == 30) {
                $this->awardMedal('visits', 3);
            }

            // Veteran medal
            $joined = strtotime($this->joined);
            $target = strtotime('-1 year');
            if ($joined < $target) {
                $this->awardMedal('veteran', 2);
                $this->awardMedal('veteran', 1);
            } else {
                $target = strtotime('-1 month');
                if ($joined < $target) {
                    $this->awardMedal('veteran', 1);
                }
            }

            // Is donator / karma priv?
            $this->karma_priv = 0;
            $st = $this->app->db->prepare('SELECT medals.medal_id, medals.colour_id, medals.label FROM medals INNER JOIN users_medals ON medals.medal_id = users_medals.medal_id WHERE (label = :label1 OR label = :label2) AND users_medals.user_id = :uid');
            $st->execute(array(':uid' => $this->uid, ':label1' => 'donator', ':label2' => 'karma'));
            $res = $st->fetchAll();
            foreach($res AS $medal) {
                if (strcasecmp($medal->label, 'donator') === 0)
                    $this->donator = true;

                if (strcasecmp($medal->label, 'karma') === 0) {
                    $this->karma_priv++;
                }
            }

            if ($this->karma_priv == 0) {
                if ($this->score >= 500 && $this->posts >= 10) {
                    $this->awardMedal('karma', 1);
                    $this->karma_priv++;
                }
            }

            if ($this->karma_priv == 1) {
                if ($this->score >= 3000 && $this->posts >= 100) {
                    $this->awardMedal('karma', 2);
                    $this->karma_priv++;
                }
            }


            // Get or make simple request token
            if (!isset($_SESSION['csrf_basic']) || !$_SESSION['csrf_basic']) {
                $_SESSION['csrf_basic'] = substr(md5(uniqid(rand(), true)), 0, 16);
            }
            $this->csrf_basic = $_SESSION['csrf_basic'];
        }

        /**
         * Generate random salt for use in hashes
         */
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
            $st = $this->app->db->prepare('SELECT u.user_id, u.password, u.old_password, IFNULL(priv.site_priv, 1) as site_priv
                    FROM users u
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    WHERE username = :u');
            $st->execute(array(':u' => $user));
            $row = $st->fetch();

            // Check if users details exist
            $this->login_error = 'Invalid login details';
            if ($row) {
                if ($row->old_password == 1) {
                    $user = strtolower($user);
                    $userhash = md5($user[0]."h97".md5(md5($pass))."t77Ds");

                    if ($row->password === $userhash) {
                        // Store new password
                        $hash = crypt($pass, $this->salt());
                        $st = $this->app->db->prepare('UPDATE users SET password = :hash, old_password = 0 WHERE user_id = :uid LIMIT 1');
                        $status = $st->execute(array(':uid' => $row->user_id, ':hash' => $hash));

                        if (!$row->site_priv) {
                            $this->login_error = 'Account has been banned';
                            return false;
                        }

                        $this->loggedIn = true;
                        $this->uid = $row->user_id;

                        // Setup GA event
                        $this->app->ssga->set_event('user', 'login', 'default', $this->uid);
                        $this->app->ssga->send();

                        $this->createSession();
                    }
                } else {
                    if ($row->password == crypt($pass, $row->password)) {

                        if (!$row->site_priv) {
                            $this->login_error = 'Account has been banned';
                            return false;
                        }

                        $this->loggedIn = true;
                        $this->uid = $row->user_id;

                        // Setup GA event
                        $this->app->ssga->set_event('user', 'login', 'default', $this->uid);
                        $this->app->ssga->send();

                        $this->createSession();
                    }
                }
            }

            return $this->loggedIn;
        }

        public function oauth($provider, $id) {
            if ($provider === 'facebook') {
                $redirect = urlencode($this->app->config('domain').'/?facebook');

                $fb = $this->app->config('facebook');
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

                // Is user logged in?
                if ($this->loggedIn) {
                    //Connect to existing account
                    $st = $this->app->db->prepare('INSERT INTO users_oauth (`uid`, `provider`)
                            VALUES (:fid, "facebook")');
                    $result = $st->execute(array(':fid' => $fid));
                    if (!$result) {
                        $this->connect_msg = 'Facebook account already connected to another user';
                        return false;
                    }
                    $oauth_id = $this->app->db->lastInsertId();

                    $st = $this->app->db->prepare('UPDATE users SET oauth_id = :oauth
                            WHERE user_id = :uid LIMIT 1');
                    $result = $st->execute(array(':oauth' => $oauth_id, ':uid' => $this->uid));
                    $this->connect_msg = 'Connected, you can now login using your Facebook account or password';
                    $this->connected = true;
                } else { 
                    //Login or register
                    //lookup fid
                    $st = $this->app->db->prepare('SELECT u.user_id, IFNULL(priv.site_priv, 1) as site_priv
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

                        // Setup GA event
                        $this->app->ssga->set_event('user', 'login', 'OAuth', $this->uid);
                        $this->app->ssga->send();

                        $this->createSession();
                    } else {
                        //Assume this is a registration
                        $this->login_error = 'Registration needed - ' . $fid;                 

                        // name - $token_details->name;
                        // username - $token_details->username;
                        // gender - $token_details->gender;
                        // email - $token_details->email;

                        // Add to DB - create oauth entry
                        $st = $this->app->db->prepare('INSERT INTO users_oauth (`uid`, `provider`)
                                VALUES (:fid, "facebook")');
                        $result = $st->execute(array(':fid' => $fid));
                        if (!$result) {
                            $this->login_error = 'OAuth ID has already been registered';
                            $this->reg_error = 'OAuth ID has already been registered';
                            return false;
                        }
                        $oauth_id = $this->app->db->lastInsertId();

                        // Create user
                        $st = $this->app->db->prepare('INSERT INTO users (`username`, `oauth_id`, `email`, `verified`)
                                VALUES (:u, :oid, :email, 1)');
                        $result = $st->execute(array(':u' => $token_details->username, ':oid' => $oauth_id, ':email' => $token_details->email));
                        if (!$result) {
                            $this->login_error = 'Username or email already registered';
                            $this->reg_error = 'Username or email already registered';
                            return false;
                        }
                        $uid = $this->app->db->lastInsertId();

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

                        $st = $this->app->db->prepare('INSERT INTO users_profile (`user_id`, `name`, `show_name`, `gender`)
                                VALUES (:uid, :name, 0, :gender)');
                        $result = $st->execute(array(':uid' => $uid, ':name' => $token_details->name, ':gender' => $gender));
                        if (!$result) {
                            $this->login_error = 'Error registring #3';
                            $this->reg_error = 'Error registring #3';
                            return false;
                        }

                        // Login user
                        $this->loggedIn = true;
                        $this->uid = $uid;

                        // Add to feed
                        $this->app->feed->call($token_details->username, 'join');

                        // Setup GA event
                        $this->app->ssga->set_event('user', 'register', 'OAuth', $uid);
                        $this->app->ssga->send();

                        // Add to log
                        $this->app->log->add('users', 'Register [FB] [' . $token_details->username . ']');

                        $this->createSession();

                        return false;
                    }
                }     
            }
        }

        private function createSession() {
            if ($this->loggedIn && isset($this->uid)) {
                //session_regenerate_id();
                $_SESSION['uid'] = $this->uid;

                // Basic hijacking prevention
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent_id'] = md5($_SERVER['HTTP_USER_AGENT']);
                
                $this->regenerateRememberToken();

                $this->app->stats->users_activity($this, true);

                // Set cookie to say they are already a registered user
                setcookie("member", true, time()+60*60*24*30, '/');

                // Redirect user back to where they came from
                header("location: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
            }     
        }

        private function checkRememberToken() {
            $existing = md5($_COOKIE['autologin']);
            $extra = md5($_SERVER['HTTP_USER_AGENT']);

            $st = $this->app->db->prepare('SELECT user_id, extra FROM users_data WHERE type = "autologin" AND value = :value AND DATEDIFF(`time`, NOW()) >= -7 LIMIT 1');
            $st->execute(array(':value' => $existing));
            $row = $st->fetch();
            if ($row && $row->extra === $extra) {
                // Login user
                $this->loggedIn = true;
                $this->uid = $row->user_id;

                // Setup GA event
                $this->app->ssga->set_event('user', 'login', 'auto', $this->uid);
                $this->app->ssga->send();

                $this->createSession();
            }
        }

        private function regenerateRememberToken() {
            // delete existing token based on cookie
            if (isset($_COOKIE['autologin'])) {
                $existing = $_COOKIE['autologin'];
                $st = $this->app->db->prepare('DELETE FROM users_data WHERE user_id = :uid AND type = "autologin" AND value = :value LIMIT 1');
                $result = $st->execute(array(':uid' => $this->uid, ':value' => $existing));
            }

            $token = openssl_random_pseudo_bytes(64);
            $extra = md5($_SERVER['HTTP_USER_AGENT']);

            $st = $this->app->db->prepare('INSERT INTO users_data (`user_id`, `type`, `value`, `extra`)
                    VALUES (:uid, :type, :value, :extra)');
            $result = $st->execute(array(':uid' => $this->uid, ':type' => 'autologin', ':value' => md5($token), ':extra' => $extra));
            if (!$result) {
                $this->regenerateRememberToken();
            }

            setcookie('autologin', $token, time()+60*60*24*7, '/', 'hackthis.co.uk', true, true);
        }

        public function register() {
            //Input check
            $username = $_POST['reg_username'];
            if (!$this->app->utils->check_user($username))
                return "Invalid username";

            $st = $this->app->db->prepare('SELECT username FROM users WHERE username=?');
            $st->bindParam(1, $username);
            $st->execute();
            if ($st->fetch(PDO::FETCH_ASSOC))
                return "Username already in use";

            $pass = $_POST['reg_password'];
            if (!isset($pass) || strlen($pass) < 5)
                return "Invalid password";
            if ($pass !== $_POST['reg_password_2'])
                return "Passwords don't match";

            $hash = crypt($pass, $this->salt());

            $email = $_POST['reg_email'];
            if (!$this->app->utils->check_email($email))
                return "Invalid email address";

            $st = $this->app->db->prepare('SELECT username FROM users WHERE email=?');
            $st->bindParam(1, $email);
            $st->execute();
            if ($st->fetch(PDO::FETCH_ASSOC))
                return "Email already in use";

            // Check if IP has created more than 10 accounts
            $st = $this->app->db->prepare('SELECT count(*) AS `count` FROM users_registration WHERE ip=?');
            $st->bindParam(1, ip2long($_SERVER['REMOTE_ADDR']));
            $st->execute();
            $res = $st->fetch();
            if ($res && $res->count >= 10) {
                $this->app->log->add('users', 'Limit reached');
                return "You have reached your account limit";
            }

            // Add to DB
            $st = $this->app->db->prepare('INSERT INTO users (`username`, `password`, `email`)
                    VALUES (:u, :p, :e)');
            $result = $st->execute(array(':u' => $username, ':p' => $hash, ':e' => $email));

            if (!$result)
                return "Error creating account";

            $uid = $this->app->db->lastInsertId();

            // Login user
            $this->loggedIn = true;
            $this->uid = $uid;
            $this->email = $email;

            // Send email
            $this->sendVerficationEmail(true);

            // Add to feed
            $this->app->feed->call($username, 'join');

            // Setup GA event
            $this->app->ssga->set_event('user', 'register', 'default', $uid);
            $this->app->ssga->send();

            // Add to log
            $this->app->log->add('users', 'Register [' . $username . ']');
            $st = $this->app->db->prepare('INSERT INTO users_registration (`user_id`, `ip`)
                    VALUES (:u, :i)');
            $result = $st->execute(array(':u' => $uid, ':i' => ip2long($_SERVER['REMOTE_ADDR'])));

            $this->createSession();
        }

        public function logout() {
            $uid = $_SESSION['uid'];

            if (isset($uid)) {
                // Setup GA event
                $this->app->ssga->set_event('user', 'logout', 'default', $uid);
                $this->app->ssga->send();
            }

            $this->loggedIn = false;
            session_destroy();
            session_regenerate_id(true);

            // Remove auto login
            if (isset($_COOKIE['autologin'])) {
                $existing = $_COOKIE['autologin'];
                $st = $this->app->db->prepare('DELETE FROM users_data WHERE user_id = :uid AND type = "autologin" AND value = :value LIMIT 1');
                $result = $st->execute(array(':uid' => $uid, ':value' => $existing));
                setcookie('autologin', '', time()-1000, '/', 'hackthis.co.uk', true, true);
            }
            
            // Redirect user back to index page
            header("Location: /");
        }


        public function delete($password, $token) {
            if (!$this->app->checkCSRFKey("deleteAccount", $token))
                return "Invalid request";

            $st = $this->app->db->prepare('SELECT u.user_id, u.password
                    FROM users u
                    WHERE user_id = :uid');
            $st->execute(array(':uid' => $this->uid));
            $row = $st->fetch();

            // Check if users details exist
            if ($row) {
                if ($row->password == crypt($password, $row->password)) {

                    $this->app->db->beginTransaction();

                    try {
                        $st = $this->app->db->prepare('DELETE FROM users
                                WHERE user_id = :uid
                                LIMIT 1');
                        $st->execute(array(':uid' => $this->uid));

                        // Setup GA event
                        $this->app->ssga->set_event('user', 'delete', 'default', $this->uid);
                        $this->app->ssga->send();

                        // Add to log
                        $this->app->log->add('users', 'Deleted [' . $this->username . ']');

                        $this->app->db->commit();
                        return true;
                    } catch (PDOException $e) {
                        $this->app->db->rollback();
                        print_r($e);
                        return "There was a problem";
                    }
                } else {
                    return 'Invalid password';
                }
            } else {
                return 'Invalid password';
            }

            return "There was an error with the request";
        }


        /* MISC */
        public function __get($property) {
            // check for admin privilages
            if ($property === "admin_priv") {
                return ($this->site_priv > 1 ||
                        $this->pm_priv > 1 ||
                        $this->forum_priv > 1 ||
                        $this->pub_priv > 1);
            }
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

        public function updateForum($signature, $token) {
            if (!$this->app->checkCSRFKey("settings", $token))
                return "Invalid request";

            $st = $this->app->db->prepare('INSERT INTO users_profile (`user_id`, `forum_signature`) VALUES (:uid, :signature) ON DUPLICATE KEY UPDATE forum_signature = :signature');
            $result = $st->execute(array(':signature' => $signature, ':uid' => $this->uid));

            if ($result)
                return true;
            else
                return "Error updating profile";
        }

        public function update($changes) {
            if (!count($changes))
                return false;

            if (!$this->app->checkCSRFKey("settings", $changes['token']))
                return "Invalid request";

            $updates = array();

            // Name
            if ($changes['name'])
                $updates['name'] = $changes['name'];
            $updates['show_name'] = (isset($changes['display_name'])?'1':'0');

            // Email
            if ($changes['email'] && $this->app->utils->check_email($changes['email'])) {
                $st = $this->app->db->prepare('UPDATE users SET email = :email WHERE user_id = :uid');
                $changed = $st->execute(array(':email' => $changes['email'], ':uid' => $this->uid));

                if (!$changed)
                    return "Email already in use";
            } else {
                return "Invalid email address";
            }
            $updates['show_email'] = (isset($changes['display_email'])?'1':'0');

            // Gender
            switch($changes['gender']) {
                case 'm': $updates['gender'] = 'male'; break;
                case 'f': $updates['gender'] = 'female'; break;
                case 'a': $this->awardMedal('alien'); $updates['gender'] = 'alien'; break;
                default: return "Invalid gender";
            }
            $updates['show_gender'] = (isset($changes['display_gender'])?'1':'0');

            // About
            if ($changes['about'])
                $updates['about'] = $changes['about'];

            if ($changes['dob']) {
                $date = DateTime::createFromFormat("d/m/Y", $changes['dob']);
                if ($date == false)
                    return "Invalid date format";
                $updates['dob'] = $date->format('Y-m-d');
            }
            if ($changes['show_dob'] === '0' || $changes['show_dob'] === '1' || $changes['show_dob'] === '2')
                $updates['show_dob'] = $changes['show_dob'];

            // INSERT IGNORE to create profile
            $st = $this->app->db->prepare('INSERT IGNORE INTO users_profile (`user_id`) VALUES (:uid)');
            $st->execute(array(':uid' => $this->uid));

            // Build query
            $fields = '';
            $values = array();

            print_r($updates);
            foreach ($updates as $field=>$update) {
                $fields .= "`$field` = ?,";
                $values[] = $update;
            }

            $fields = rtrim($fields, ',');

            $query  = "UPDATE users_profile SET ".$fields;
            $query .= " WHERE user_id=?";
            $values[] = $this->uid;

            $st = $this->app->db->prepare($query);
            $res = $st->execute($values);

            return true;
        }

        public function setImagePath($path) {
            if ($path === 'gravatar') {
                $st = $this->app->db->prepare('INSERT INTO users_profile (`user_id`, `gravatar`) VALUES (:uid, 1) ON DUPLICATE KEY UPDATE gravatar = 1');
                $result = $st->execute(array(':uid' => $this->uid));
            } else if ($path === 'current') {
                $st = $this->app->db->prepare('INSERT INTO users_profile (`user_id`) VALUES (:uid) ON DUPLICATE KEY UPDATE gravatar = 0');
                $result = $st->execute(array(':uid' => $this->uid));
            } else if ($path === 'default') {
                $st = $this->app->db->prepare('INSERT INTO users_profile (`user_id`) VALUES (:uid) ON DUPLICATE KEY UPDATE gravatar = 0, img = NULL');
                $result = $st->execute(array(':uid' => $this->uid));
            } else {
                $st = $this->app->db->prepare('INSERT INTO users_profile (`user_id`, `img`) VALUES (:uid, :path) ON DUPLICATE KEY UPDATE img = :path, gravatar = 0');
                $result = $st->execute(array(':path' => $path, ':uid' => $this->uid));
            }

            $this->awardMedal('Cheese');
        }

        /**
         * Store key=>value pair of data for a given user
         * @param string  $type    Key
         * @param string  $value   Value
         * @param int     $uid     User id, if null the current users id will be used
         * @param boolean $replace If an existing value for the given key is found for this user should it be replaced
         * @return boolean Successes of insert
         */
        public function setData($type, $value, $uid = null, $replace = false) {
            if (!$uid)
                $uid = $this->uid;

            if ($replace) {
                $st = $this->app->db->prepare('DELETE FROM users_data WHERE user_id = :uid AND `type` = :type');
                $st->execute(array(':uid' => $uid, ':type' => $type));                
            }

            $st = $this->app->db->prepare('INSERT INTO users_data (`user_id`, `type`, `value`) VALUES (:uid, :type, :value)');
            return $st->execute(array(':uid' => $uid, ':type' => $type, ':value' => $value));
        }

        /**
         * Get key=>value pair of data for a given user
         *
         * @param string $type Data type which much match a set data type in the database schema
         * @param int $uid The corresponding user to check against. If 0 then the current logged in users id is used, if greater than 0 it is taken as the users id
         * @param boolean $interval Should the given token be checked against its life span (10 minutes)
         *
         * @return string Value of entry, if exists
         */
        public function getData($type, $uid=0,  $interval=false) {
            if ($uid === 0) {
                $uid = $this->uid;
            }

            if ($uid) {
                $sql = 'SELECT `value`
                        FROM users_data
                        WHERE `type` = :type AND users.user_id = :uid';
                if ($interval)
                    $sql .= ' AND `time` > date_sub(now(), interval 10 minute)';
                $sql .= ' LIMIT 1';

                $st = $this->app->db->prepare($sql);
                $st->execute(array(':type' => $type, ':uid' => $uid));
                $row = $st->fetch();
            }

            if ($row) {
                return $row->value;
            } else {
               return false;
            }
        }


        /**
         * Validates if the supplied user data token is valid
         *
         * @param string $type Data type which much match a set data type in the database schema
         * @param string $value The value to be checked
         * @param boolean $interval Should the given token be checked against its life span (10 minutes)
         * @param int $uid The corresponding user to check against. If 0 then the current logged in users id is used, if greater than 0 it is taken as the users id. If NULL is used then the token is checked against all users
         *
         * @return int If correct the users id is returned else false
         */
        public function checkData($type, $value, $interval=false, $uid=0) {
            if ($uid === 0)
                $uid = $this->uid;

            if (!$uid) {
                $sql = 'SELECT users.user_id, users.username, users.email
                        FROM users_data
                        INNER JOIN users
                        ON users.user_id = users_data.user_id
                        WHERE `type` = :type AND `value` = :value';
                if ($interval)
                    $sql .= ' AND `time` > date_sub(now(), interval 10 minute)';
                $sql .= ' LIMIT 1';

                $st = $this->app->db->prepare($sql);
                $st->execute(array(':type' => $type, ':value' => $value));
                $row = $st->fetch();
            } else {
                $sql = 'SELECT users.user_id, users.username, users.email
                        FROM users_data
                        INNER JOIN users
                        ON users.user_id = users_data.user_id
                        WHERE `type` = :type AND `value` = :value AND users.user_id = :uid';
                if ($interval)
                    $sql .= ' AND `time` > date_sub(now(), interval 10 minute)';
                $sql .= ' LIMIT 1';

                $st = $this->app->db->prepare($sql);
                $st->execute(array(':type' => $type, ':uid' => $uid, ':value' => $value));
                $row = $st->fetch();
            }

            if ($row) {
                return $row;
            } else {
                return false;
            }
        }

        /**
         * Delete all data for a given key and user
         * @param  int $type Key
         * @param  int $uid  User id, if null the current users id will be used
         * 
         * @return boolean Successes of delete
         */
        public function removeData($type, $uid = null) {
            if (!$uid)
                $uid = $this->uid;

            $st = $this->app->db->prepare('DELETE FROM users_data WHERE user_id = :uid AND `type` = :type');
            return $st->execute(array(':uid' => $uid, ':type' => $type));
        }


        public function request($user) {
            if (!$this->app->checkCSRFKey("requestDetails", $_POST['token']))
                return "Invalid request";
            
            if (strlen($user) < 3)
                return "Details not found";

            if ($user == 'keeper' || $user == 'keeper00@gmail.com') {
                $this->app->log->add('keeper', $_SERVER['REMOTE_ADDR']);
                return true;
            }

            // Find users details
            $st = $this->app->db->prepare('SELECT user_id, username, email, password
                    FROM users
                    WHERE username = :user OR email = :user
                    LIMIT 1');
            $st->execute(array(':user' => $user));
            $row = $st->fetch();

            if (!$row) {
                return "Details not found";
            }

            if (!$row->password) {
                return "OAuth only account";
            }

            $token = $this->generateRequest($row->user_id);

            // Send email
            // $body = "We received a request for your HackThis!! account details.<br/><br/>Username: {$row->username}<br/>To reset your password, click on this link: <a href='http://www.hackthis.co.uk/?request={$token}'>http://www.hackthis.co.uk/?request={$token}</a><br/><br/>If you feel you have received this message in error, delete this email. Your password can only be reset via this email.";
            // $this->app->email->queue($row->email, "Password request", $body);

            $data = array('token' => $token, 'username' => $row->username);
            $this->app->email->queue($row->email, 'password', json_encode($data));

            return true;
        }

        public function generateRequest($uid=0) {
            $token = md5(openssl_random_pseudo_bytes(32));
            $this->setData('reset', $token, $uid, true);
            return $token;
        }

        public function checkRequest($request, $limit=true) {
            return $this->checkData("reset", $request, $limit, null);
        }

        public function changePassword($pass, $pass2, $uid = null) {
            if (!$this->app->checkCSRFKey("changePassword", $_POST['token']))
                return "Invalid request";

            if (!$uid)
                $uid = $this->uid;

            if (!isset($pass) || strlen($pass) < 5)
                return "Invalid password";
            if ($pass !== $pass2)
                return "Passwords don't match";

            $hash = crypt($pass, $this->salt());
            $st = $this->app->db->prepare('UPDATE users SET password = :hash, old_password = 0 WHERE user_id = :uid LIMIT 1');
            $status = $st->execute(array(':uid' => $uid, ':hash' => $hash));

            if ($status) {
                $this->removeData('reset', $uid);
                return true;
            } else {
                return "Something went wrong";
            }
        }

        /**
         * Update a users email notification settings
         * @param  integer $uid         User id, if 0 then the current users id will be used
         * @param  boolean $unsubscribe Unsubscribe from all emails or use POST variables
         * @return boolean              Successes of update
         */
        public function changeNotificationSettings($uid = 0, $unsubscribe = false) {
            if ($uid === 0) {
                $uid = $this->uid;
            }

            if ($unsubscribe) {
                $pm = 0;
                $friend = 0;
                $forum_reply = 0;
                $forum_mention = 0;
                $news = 0;
            } else {
                if (isset($_POST['pm'])) {
                    $pm = ($_POST['pm'] == '1')?1:0;
                } else return false;

                if (isset($_POST['friend'])) {
                    $friend = ($_POST['friend'] == '1')?1:0;
                } else return false;

                if (isset($_POST['forum_reply'])) {
                    $forum_reply = ($_POST['forum_reply'] == '1')?1:0;
                } else return false;

                if (isset($_POST['forum_mention'])) {
                    $forum_mention = ($_POST['forum_mention'] == '1')?1:0;
                } else return false;

                if (isset($_POST['news'])) {
                    $news = ($_POST['news'] == '1')?1:0;
                } else return false;
            }

            $st = $this->app->db->prepare('INSERT INTO users_settings
                    (`user_id`, `email_pm`, `email_forum_reply`, `email_forum_mention`, `email_friend`, `email_news`)
                    VALUES
                    (:uid, :pm, :forum_reply, :forum_mention, :friend, :news)
                    ON DUPLICATE KEY UPDATE
                    `email_pm` = :pm, `email_forum_reply` = :forum_reply, `email_forum_mention` = :forum_mention
                    , `email_friend` = :friend, `email_news` = :news');

            return $st->execute(array(':uid' => $uid, ':pm' => $pm, ':forum_reply' => $forum_reply, ':forum_mention' => $forum_mention, ':friend' => $friend, ':news' => $news));
        }

        public function sendVerficationEmail($new=false) {
            $token = md5(openssl_random_pseudo_bytes(32));
            $this->setData('verification', $token, $this->uid, true);

            // Send email
            // $body = "Click on the following link to verify your e-mail address:<br/><a style='color:#ffffff; text-decoration: none;' href='https://www.hackthis.co.uk/settings/account.php?verify={$token}'>https://www.hackthis.co.uk/settings/account.php?verify={$token}</a>";

            // if ($new) {
            //     $body = "Thank you for signing up for a <a style='color:#ffffff; text-decoration: none;' href='https://www.hackthis.co.uk/'>HackThis!!</a> account.<br/><br/>" . $body;
            // }

            // $this->app->email->queue($this->email, "Confirm your email address", $body);

            $data = array('new' => $new, 'token' => $token);
            $this->app->email->queue($this->email, 'email_confirmation', json_encode($data));

            return true;
        }

        public function confirmVerification($code) {
            if ($this->checkData("verification", $code)) {
                $st = $this->app->db->prepare('UPDATE users SET verified = 1 WHERE user_id = :uid LIMIT 1');
                $st->execute(array(':uid' => $this->uid));
                return true;
            } else {
                return false;
            }
        }




        public function awardMedal($label, $colour=1, $uid=null) {
            if (!$uid)
                $uid = $this->uid;

            $st = $this->app->db->prepare('INSERT IGNORE INTO users_medals (`user_id`, `medal_id`) SELECT :uid, medal_id FROM medals WHERE label = :label AND colour_id = :colour');
            $result = $st->execute(array(':label' => $label, ':colour' => $colour, ':uid' => $uid));

            if ($st->rowCount()) {
                if ($uid == $this->uid) {
                    // Add to feed
                    $this->app->feed->call($this->username, 'medal', $label, $colour);
                } else {
                    // Lookup username
                    $st = $this->app->db->prepare('SELECT username FROM users WHERE user_id = :uid');
                    $st->execute(array(':uid' => $uid));
                    $result = $st->fetch();

                    $this->app->feed->call($result->username, 'medal', $label, $colour);
                }
            }

            return (bool) $result;
        }

        public function removeMedal($label, $colour=1, $uid=null) {
            if (!$uid)
                $uid = $this->uid;

            $st = $this->app->db->prepare('SELECT medal_id FROM medals WHERE label = :label AND colour_id = :colour');
            $st->execute(array(':label' => $label, ':colour' => $colour));
            $result = $st->fetch();

            if ($result) {
                $st = $this->app->db->prepare('DELETE IGNORE FROM users_medals WHERE user_id = :uid AND medal_id = :mid');
                $result = $st->execute(array(':uid' => $uid, ':mid' => $result->medal_id));
            }

            return (bool) $result;
        }
    }
?>
