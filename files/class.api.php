<?php
    class api {

        //=====================================================
        // CONSTRUCTOR
        //=====================================================
        public function __construct($app, $key) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 00:00:00 GMT');
            header('Content-type: application/json');

            $this->app = $app;

            $this->checkKey($key);

            /*
             * When no API key is present, use users privileges
             */
            if (!isset($this->privileges) && $this->app->user->loggedIn) {
                // Check if user is banned from the site
                if ($this->app->user->site_priv < 1) {
                    $this->respond(401);
                }

                // Check the CSRF is being used
                if (!isset($_GET['ajax_csrf_token']) || $_GET['ajax_csrf_token'] != $this->app->user->csrf_basic) {
                    $this->respond(400);
                }

                $this->privileges = array();

                array_push($this->privileges, 'user.profile');

                if ($this->app->user->site_priv > 1) {
                    array_push($this->privileges, 'user.admin.*');
                }
                if ($this->app->user->forum_priv > 1 || $this->app->user->site_priv > 1) {
                    array_push($this->privileges, 'forum.admin.*');
                }
            }

            if (!isset($this->privileges)) {
                $this->respond(401);
            }
        }

        public function handleRequest($method, $data) {
            if (!isset($method)) {
                $this->respond(400);
            }

            // Check privileges
            $this->hasPrivilege($method);

            $subject = explode('.', $method);
            $method = substr($method, strlen($subject[0])+1);

            switch ($subject[0]) {
                case 'irc': $this->logIrc(); break;
                case 'user': $this->user($method); break;
                default: $this->respond(400);
            }

            // switch ($method) {
            //     case 'irc.log': $this->logIrc(); break;
            //     case 'user.login': $this->user('login'); break;
            //     case 'user.login.gauth': $this->user('login.gauth'); break;
            //     case 'user.register': $this->user('register'); break;
            //     case 'user.profile': $this->user('profile'); break;
            //     case 'user.admin.priv': $this->user('admin.priv'); break;
            //     case 'user.admin.priv': $this->user('admin.priv'); break;
            //     default: $this->respond(400);
            // }
        }


        //=====================================================
        // REQUEST HANDLERS
        //=====================================================

        //-----------------------------------------------------
        // User
        //-----------------------------------------------------
        private function user($request) {
            $response = new stdClass();

            switch ($request) {
                case 'login': $response = $this->userLogin(); break;
                case 'login.gauth': $response = $this->userLoginGAuth(); break;
                case 'register': $response = $this->userRegister(); break;
                case 'profile': $response->profile = $this->userProfile(); break;

                case 'admin.priv': $this->userAdminPriv(); break;
                case 'admin.medal': $response->status = $this->userAdminMedal(); break;
                default: $this->respond(400);
            }

            $this->respond(200, $response);
        }

        private function userProfile() {
            $profile = new profile($_GET['user'], true);

            unset($profile->email);

            return $profile;
        }

        private function userLogin() {
            $response = new stdClass();

            $response->valid = $this->app->user->login($_POST['username'], $_POST['password']);

            if ($response->valid) {
                $this->app->user->get_details();
                $response->uid = $this->app->user->uid;
                $response->username = $this->app->user->username;
            } else {
                if ($this->app->user->g_auth) {
                    $response->error = "Google Authentication code required";
                    $response->g_auth = $this->app->user->g_auth;
                } else {
                    $response->error = $this->app->user->login_error;
                }
            }


            return $response;
        }

        private function userLoginGAuth() {
            $response = new stdClass();

            if(is_numeric($_POST['code']) && is_numeric($_POST['gauth'])) {
                $response->valid = $this->app->user->googleAuth($_POST['code'], $_POST['gauth']);
            } else {
                $response->valid = false;
            }

            if ($response->valid) {
                $this->app->user->get_details();
                $response->uid = $this->app->user->uid;
                $response->username = $this->app->user->username;
            } else {
                $response->error = $this->app->user->login_error;
            }

            return $response;
        }

        private function userRegister() {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];

            $response = new stdClass();

            $registration = $this->app->user->register($username, $password, $email);

            if (is_numeric($registration)) {
                $response->valid = true;

                $this->app->user->get_details();
                $response->uid = $this->app->user->uid;
                $response->username = $this->app->user->username;
            } else {
                $response->valid = false;
                $response->error = $registration;
            }

            return $response;
        }

        private function userAdminPriv() {
            $user_id = $_POST['uid'];
            $priv = $_POST['priv'];
            $priv_value = $_POST['priv_value'];
            $this->app->admin->setModeratorPriv($user_id, $priv, $priv_value);
        }

        private function userAdminMedal() {
            $user_id = $_POST['uid'];
            $medal = $_POST['medal'];
            $medal_value = $_POST['medal_value'];
            
            if ($medal == 'contributor' || $medal == 'helper') {
                if ($medal_value == 1) {
                    return $this->app->user->awardMedal($medal, 4, $user_id);
                } else {
                    return $this->app->user->removeMedal($medal, 4, $user_id);
                }
            }

            return "error";
        }


        //-----------------------------------------------------
        // IRC
        //-----------------------------------------------------
        private function logIrc() {
           if (!isset($_POST['nick']) || !isset($_POST['chan']) || !isset($_POST['msg']))
                throw new Exception('Missing data fields');

            $_POST['msg'] = preg_replace('/\x01/', '', $_POST['msg']);

            $st = $this->app->db->prepare('INSERT INTO irc_logs (`nick`, `channel`, `log`)
                    VALUES (:nick, :chan, :msg)');
            $result = $st->execute(array(':nick' => $_POST['nick'], ':chan' => $_POST['chan'], ':msg' => $_POST['msg']));


            // Calculate stats
            $st = $this->app->db->prepare('INSERT INTO irc_stats (`nick`, `lines`, `words`, `chars`)
                    VALUES (:nick, :lines, :words, :chars)
                    ON DUPLICATE KEY UPDATE `lines`=`lines`+:lines, `words`=`words`+:words, `chars`=`chars`+:chars, `time`=NOW()');

            $st->bindValue(':nick', $_POST['nick'], PDO::PARAM_INT);
            $st->bindValue(':lines', 1, PDO::PARAM_INT);
            $st->bindValue(':words', str_word_count($_POST['msg']), PDO::PARAM_INT);
            $st->bindValue(':chars', strlen($_POST['msg']), PDO::PARAM_INT);
            $result = $st->execute();

            $this->respond(200);
        }

        //=====================================================
        // HELPER FUNCTIONS
        //=====================================================
        private function respond($status, $data=null) {
            if (!$data) {
                $data = new stdClass();
            }

            switch($status) {
                case 200: header('HTTP/1.0 200 OK', true, 200); break;
                case 201: header('HTTP/1.0 201 Created', true, 201); break;
                case 400: header('HTTP/1.0 400 Bad Request', true, 400); break;
                case 401: header('HTTP/1.0 401 Unauthorized', true, 401); break;
                case 403: header('HTTP/1.0 403 Forbidden', true, 403); break;
            }

            if ($status < 300 && !isset($data->status)) {
                $data->status = "ok";
            } else if ($status > 300 && !isset($data->status)) {
                $data->status = "error";
            }

            if (!isset($data->message)) {
                switch($status) {
                    case 400: $data->message = "Invalid request"; break;
                    case 401: $data->message = "Invalid API key"; break;
                    case 403: $data->message = "You do not have privileges to access this method"; break;
                }
            }

            echo json_encode($data);
            die();
        }

        private function checkKey($key) {
            $st = $this->app->db->prepare('SELECT privileges FROM api_clients WHERE `key` = :key LIMIT 1');
            $st->execute(array(':key' => $key));
            $result = $st->fetch();
            if (!$result) {
                return false;
            }

            $this->privileges = json_decode($result->privileges);
            return true;
        }


        private function hasPrivilege($privilege) {
            // get subject
            $subject = explode('.', $privilege);

            if ($subject[1] == 'admin') {
                $globalPrivilege = $subject[0] . '.admin.*';
            } else {
                $globalPrivilege = $subject[0] . '.*';
            }

            if (!in_array($privilege, $this->privileges) &&
                !in_array($globalPrivilege, $this->privileges)) {
                $this->respond(403);
            }
        }

    }
?>



