<?php
    class user {
        private $app;
        public $loggedIn = false;
        public $admin = false;

        public function __construct($app) {
            $this->app = $app;
            $app->user = $this;

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
            $this->app->stats->users_activity($this);

            $st = $this->app->db->prepare('SELECT username, score, email, (oauth_id IS NOT NULL) as connected,
                    IFNULL(site_priv, 1) as site_priv, IFNULL(pm_priv, 1) as pm_priv, IFNULL(forum_priv, 1) as forum_priv, IFNULL(pub_priv, 0) as pub_priv, verified,
                    profile.gravatar, profile.img as `image`,
                    activity.consecutive, activity.consecutive_most, activity.joined
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
            } else {
                $target = strtotime('-1 month');
                if ($joined < $target) {
                    $this->awardMedal('veteran', 1);
                }
            }

            // Is donator?
            $st = $this->app->db->prepare('SELECT medal_id FROM medals WHERE label = :label');
            $st->execute(array(':label' => 'donator'));
            $this->donator = (boolean) $st->fetch();
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
            $st = $this->app->db->prepare('SELECT u.user_id, u.password, IFNULL(priv.site_priv, 1) as site_priv
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

                    // Setup GA event
                    $this->app->ssga->set_event('user', 'login', 'default', $this->uid);
                    $this->app->ssga->send();

                    $this->createSession();
                }
            }

            return $this->loggedIn;
        }

        public function oauth($provider, $id) {
            if ($provider === 'facebook') {
                $redirect = urlencode('http://dev.hackthis/?facebook');

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

                //Is user logged in?
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
                            $this->login_error = 'Error registering';
                            return false;
                        }
                        $oauth_id = $this->app->db->lastInsertId();

                        // Create user
                        $st = $this->app->db->prepare('INSERT INTO users (`username`, `oauth_id`, `email`, `verified`)
                                VALUES (:u, :oid, :email, 1)');
                        $result = $st->execute(array(':u' => $token_details->username, ':oid' => $oauth_id, ':email' => $token_details->email));
                        if (!$result) {
                            $this->login_error = 'Error registering';
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
                            $this->login_error = 'Error registering';
                            return false;
                        }

                        // Login user
                        $this->loggedIn = true;
                        $this->uid = $uid;

                        // Add to feed
                        $this->app->feed->call($username, 'join');

                        // Setup GA event
                        $this->app->ssga->set_event('user', 'register', 'OAuth', $uid);
                        $this->app->ssga->send();

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
                
                $this->app->stats->users_activity($this, true);

                // Set cookie to say they are already a registered user
                setcookie("member", true, time()+60*60*24*30);

                // Redirect user back to where they came from
                header("location: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
            }     
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

            $this->createSession();
        }

        public function logout() {
            if (isset($_SESSION['uid'])) {
                // Setup GA event
                $this->app->ssga->set_event('user', 'logout', 'default', $_SESSION['uid']);
                $this->app->ssga->send();
            }

            $this->loggedIn = false;
            session_regenerate_id(true);
            
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
                //$updates['email'] = $changes['email'];
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

        public function checkData($type, $value, $interval=false) {
            $sql = 'SELECT user_id
                    FROM users_data
                    WHERE `type` = :type AND `value` = :value AND user_id = :uid';
            if ($interval)
                $sql .= ' AND `time` > date_sub(now(), interval 10 minute)';
            $sql .= ' LIMIT 1';

            $st = $this->app->db->prepare($sql);
            $st->execute(array(':type' => $type, ':uid' => $this->uid, ':value' => $value));
            $row = $st->fetch();

            return $row;
        }

        public function request($user) {
            if (strlen($user) < 3)
                return "Details not found";

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

            $token = md5(openssl_random_pseudo_bytes(32));
            $this->setData('reset', $token, $row->user_id, true);

            // Send email
            $body = "We received a request for your HackThis!! account details.<br/><br/>Username: {$row->username}<br/>To reset your password, click on this link: <a href='http://www.hackthis.co.uk/?request={$token}'>http://www.hackthis.co.uk/?request={$token}</a><br/><br/>If you feel you have received this message in error, delete this email. Your password can only be reset via this email.";
            $this->app->email->queue($row->email, "Password request", $body);

            return true;
        }

        public function checkRequest($request) {
            return $this->checkData("reset", $request, true);
        }

        public function changePassword($pass, $pass2, $uid = null) {
            if (!$uid)
                $uid = $this->uid;

            if (!isset($pass) || strlen($pass) < 5)
                return "Invalid password";
            if ($pass !== $pass2)
                return "Passwords don't match";

            $hash = crypt($pass, $this->salt());
            $st = $this->app->db->prepare('UPDATE users SET password = :hash WHERE user_id = :uid LIMIT 1');
            $status = $st->execute(array(':uid' => $uid, ':hash' => $hash));

            if ($status) {
                $st = $this->app->db->prepare('DELETE FROM users_data WHERE user_id = :uid AND `type` = "reset"');
                $st->execute(array(':uid' => $uid));      
                return true;
            } else {
                return "Something went wrong";
            }
        }

        public function sendVerficationEmail($new=false) {
            $token = md5(openssl_random_pseudo_bytes(32));
            $this->setData('verification', $token, $this->uid, true);

            // Send email
            $body = "Click on the following link to verify your e-mail address:<br/><a style='color:#ffffff; text-decoration: none;' href='https://www.hackthis.co.uk/settings/account.php?verify={$token}'>https://www.hackthis.co.uk/settings/account.php?verify={$token}</a>";

            if ($new) {
                $body = "Thank you for signing up for a <a style='color:#ffffff; text-decoration: none;' href='https://www.hackthis.co.uk/'>HackThis!!</a> account.<br/><br/>" . $body;
            }

            $this->app->email->queue($this->email, "Confirm your email address", $body);

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

            if ($st->rowCount() && $uid == $this->uid) {
                // Add to feed
                $this->app->feed->call($this->username, 'medal', $label, $colour);
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