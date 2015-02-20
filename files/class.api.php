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
                $this->privileges = array('user.profile');
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

            switch ($method) {
                case 'irc.log': $this->logIrc(); break;
                case 'user.login': $this->user('login'); break;
                case 'user.profile': $this->user('profile'); break;
            }

            $this->respond(400);
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
                case 'profile': $response->profile = $this->userProfile(); break;
                case 'login': $response = $this->userLogin(); break;
            }

            $this->respond(200, $response);
        }

        private function userProfile() {
            $profile = new profile($_GET['user'], true); ;

            unset($profile->email);

            return $profile;
        }

        private function userLogin() {
            // No idea yet
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
            // if ($this->app->config('api') == $key) {
            //     $this->privileges = array('irc.*', 'user.profile');
            //     return true;
            // }

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
            $subject = strtok($privilege, '.');

            if ($subject = 'public') {
                return true;
            }

            $globalPrivilege = $subject . '.*';

            if (!in_array($privilege, $this->privileges) &&
                !in_array($globalPrivilege, $this->privileges)) {
                $this->respond(403);
            }
        }

    }
?>
