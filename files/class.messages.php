<?php
    class messages {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function getCount($unread=true) {
            $st = $this->app->db->prepare("SELECT count(pm_users.pm_id) as count
                FROM pm_users
                INNER JOIN pm_messages
                ON message_id = (SELECT message_id FROM pm_messages WHERE pm_id = pm_users.pm_id AND (seen IS NULL || time > seen) ORDER BY time DESC LIMIT 1)
                WHERE pm_users.user_id = :user_id");
            $st->execute(array(':user_id' => $this->app->user->uid));
            $result = $st->fetch();

            return $result ? (int) $result->count : 0;
        }

        public function getAll($size=28, $limit=true) {
            $sql = "SELECT pm.pm_id, pm_messages.user_id as lastSender, message, time as timestamp, IF (time <= seen, 1, 0) AS seen
                   FROM pm
                   INNER JOIN pm_users
                   ON pm.pm_id = pm_users.pm_id
                   INNER JOIN pm_messages
                   ON message_id = (SELECT message_id FROM pm_messages WHERE pm_id = pm.pm_id ORDER BY time DESC LIMIT 1)
                   WHERE pm_users.user_id = :user_id AND (pm_users.deleted IS NULL OR time > pm_users.deleted)
                   ORDER BY time DESC";

            if ($limit)
                $sql .= " LIMIT 5";

            // Get items
            $st = $this->app->db->prepare($sql);
            $st->execute(array(':user_id' => $this->app->user->uid));
            $result = $st->fetchAll();

            // Loop items and create images
            foreach ($result as $res) {
                // Get assosiated users
                $st = $this->app->db->prepare("SELECT username, profile.gravatar, IF (profile.gravatar = 1, users.email , profile.img) as `image`
                                   FROM pm_users
                                   INNER JOIN users
                                   ON pm_users.user_id = users.user_id
                                   LEFT JOIN users_profile profile
                                   ON profile.user_id = users.user_id
                                   WHERE pm_users.pm_id = :pm_id AND pm_users.user_id != :user_id
                                   ORDER BY username DESC");
                $st->execute(array(':pm_id' => $res->pm_id, ':user_id' => $this->app->user->uid));
                $res->users = $st->fetchAll();

                // Profile images
                foreach ($res->users as $u) {
                    if (isset($u->image)) {
                        $gravatar = isset($u->gravatar) && $u->gravatar == 1;
                        $u->img = profile::getImg($u->image, $size, $gravatar);
                    } else
                    $u->img = profile::getImg(null, $size);

                    unset($u->image);
                    unset($u->gravatar);
                }

                if (!count($res->users)) {
                    $tmp = new stdClass();
                    $tmp->username = $this->app->user->username;
                    $tmp->img = $this->app->user->image;
                    array_push($res->users, $tmp);
                }

                $res->message = $this->app->parse(substr($res->message, 0, 75), false);

                if ($res->lastSender == $this->app->user->uid) {
                    $res->message = '<i class="icon-reply"></i> '. $res->message;
                }
                unset($res->lastSender);

                //time
                $res->timestamp = $this->app->utils->fdate($res->timestamp);
            }

            return $result;
        }

        public function getConvo($id, $limit=true) {
            $sql = "SELECT message, messages.time as timestamp, IF (messages.time <= seen, 1, 0) AS seen,
                   username, profile.gravatar, IF (profile.gravatar = 1, users.email , profile.img) as `image`
                   FROM pm_messages messages
                   INNER JOIN pm_users
                   ON messages.pm_id = pm_users.pm_id AND pm_users.user_id = :uid
                   INNER JOIN users
                   ON messages.user_id = users.user_id
                   LEFT JOIN users_profile profile
                   ON profile.user_id = users.user_id
                   WHERE messages.pm_id = :pm_id AND (pm_users.deleted IS NULL OR messages.time > pm_users.deleted)
                   ORDER BY messages.time DESC";
            if ($limit) {
                $sql .= ' LIMIT 5';
            }

            // Get items
            $st = $this->app->db->prepare($sql);
            $st->execute(array(':uid' => $this->app->user->uid, ':pm_id' => $id));
            $result = $st->fetchAll();

            //flip array
            $result = array_reverse($result);

            // Mark thread as seen
            $st = $this->app->db->prepare("UPDATE pm_users SET `seen` = NOW() WHERE user_id = :uid AND pm_id = :pm_id LIMIT 1");
            $st->execute(array(':uid' => $this->app->user->uid, ':pm_id' => $id));

            // Loop items and create images
            foreach ($result as $res) {
                if (isset($res->image)) {
                    $gravatar = isset($res->gravatar) && $res->gravatar == 1;
                    $res->img = profile::getImg($res->image, 28, $gravatar);
                } else
                    $res->img = profile::getImg(null, 28);

                unset($res->image);
                unset($res->gravatar);

                $res->message = $this->app->parse($res->message);

                //time
                $res->timestamp = $this->app->utils->fdate($res->timestamp);
            }

            return $result;
        }

        public function getConvoUsers($id) {
            
            $st = $this->app->db->prepare("SELECT username
                               FROM pm_users
                               INNER JOIN users
                               ON pm_users.user_id = users.user_id
                               WHERE pm_users.pm_id = :pm_id AND pm_users.user_id != :user_id
                               ORDER BY username DESC");
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $st->execute(array(':pm_id' => $id, ':user_id' => $this->app->user->uid));
            $result = $st->fetchAll();

            if (!count($result))
                $result = array(array("username"=>$this->app->user->username));

            return $result;
        }

        public function deleteConvo($id) {
            // Mark thread as deleted
            $st = $this->app->db->prepare("UPDATE pm_users SET `deleted` = NOW() WHERE user_id = :uid AND pm_id = :pm_id LIMIT 1");
            return $st->execute(array(':uid' => $this->app->user->uid, ':pm_id' => $id));
        }

        public function newMessage($to, $body, $pm_id=null) {
            
            $body = trim($body);
            if ($body === '') {
                $this->error = '1.1';
                return false;
            }

            if ($to !== null) {
                $recipients = array_unique(array_map("StrToLower", array_filter(preg_split('/[\ \n\,]+/', $to))));

                $tmp = $recipients;
                array_push($tmp, $this->app->user->username);

                //Check if conversation already exists
                $plist = ':id_'.implode(',:id_', array_keys($tmp)); // placeholder list for IN
                $sql = "SELECT COUNT(pm_users.user_id) as `count`, pm_id, pm_users_2.total AS `total`
                        FROM pm_users
                        LEFT JOIN (SELECT pm_id AS `id`, COUNT(*) as `total` FROM pm_users GROUP BY pm_id) as pm_users_2
                        ON pm_users.pm_id = pm_users_2.id
                        LEFT JOIN users
                        ON users.user_id = pm_users.user_id
                        WHERE username IN ($plist)
                        GROUP BY pm_id
                        HAVING `count` = :n AND `total` = :n";
                $params = array_combine(explode(",", $plist), $tmp);
                $params[':n'] = count($tmp);
                $st = $this->app->db->prepare($sql);
                $st->execute($params);
                $result = $st->fetchAll();

                if (count($result) === 1)
                    return $this->newMessage(null, $body, $result[0]->pm_id);

                //Start transaction
                $this->app->db->beginTransaction();

                //Create thread
                $st = $this->app->db->prepare('INSERT INTO pm VALUES ()');
                $result = $st->execute();
                if (!$result) {
                    $this->app->db->rollBack();
                    $this->error = '1.2';
                    return false;
                }

                $pm_id = $this->app->db->lastInsertId();

                //Add recipients
                try {
                    $st = $this->app->db->prepare('INSERT INTO pm_users (`pm_id`, `user_id`)
                                        SELECT :pm_id, u.user_id
                                        FROM users u
                                        LEFT OUTER JOIN users_blocks
                                        ON users_blocks.user_id = u.user_id AND users_blocks.blocked_id = :uid
                                        WHERE username = :name AND users_blocks.user_id IS NULL');
                    $st->bindParam(':pm_id', $pm_id);
                    $st->bindParam(':name', $name);
                    $st->bindParam(':uid', $this->app->user->uid);

                    $count = 0;

                    foreach($recipients as $rec) {
                        $name = $rec;
                        $st->execute();
                        $count += $st->rowCount();
                    }

                    if ($count < 1) {
                        $this->app->db->rollBack();
                        $this->error = '1.3';
                        return false;
                    }
                } catch(PDOException $e) {
                    $this->app->db->rollBack();
                    $this->error = '1.2';
                    return false;
                } 

                //Add sender
                $st = $this->app->db->prepare('INSERT INTO pm_users (`pm_id`, `user_id`, `seen`)
                                    VALUES (:pm_id, :uid, NOW())
                                    ON DUPLICATE KEY UPDATE `seen` = NOW()');
                $result = $st->execute(array(':pm_id' => $pm_id, ':uid' => $this->app->user->uid));
                if (!$result) {
                    $this->app->db->rollBack();
                    $this->error = '1.2';
                    return false;
                }

                //Insert message
                $st = $this->app->db->prepare('INSERT INTO pm_messages (`pm_id`, `user_id`, `message`)
                                    VALUES (:pm_id, :uid, :body)');
                $result = $st->execute(array(':pm_id' => $pm_id, ':uid' => $this->app->user->uid, ':body' => $body));
                if (!$result) {
                    $this->app->db->rollBack();
                    $this->error = '1.2';
                    return false;
                }

                $this->app->db->commit();
            } else {
                $body = trim($body);
                if ($body === '') {
                    $this->error = '1.1';
                    return false;
                }

                //Check if blocked by any user
                $st = $this->app->db->prepare('SELECT :pm_id
                                               FROM pm_users
                                               INNER JOIN users_blocks
                                               ON users_blocks.user_id = pm_users.user_id AND users_blocks.blocked_id = :uid
                                               WHERE pm_id = :pm_id');
                $st->execute(array(':pm_id' => $pm_id, ':uid' => $this->app->user->uid));
                if ($st->rowCount()) {
                    $this->error = '1.4';
                    return false;                  
                }

                //Lookup privilages
                $st = $this->app->db->prepare('INSERT INTO pm_messages (`pm_id`, `user_id`, `message`)
                                    SELECT :pm_id, :uid, :body FROM pm_users
                                    WHERE user_id = :uid AND pm_id = :pm_id');
                $st->execute(array(':pm_id' => $pm_id, ':uid' => $this->app->user->uid, ':body' => $body));
                if (!$st->rowCount()) {
                    $this->error = '1.4';
                    return false;
                }

                $st = $this->app->db->prepare("UPDATE pm_users SET `seen` = NOW() WHERE user_id = :uid AND pm_id = :pm_id LIMIT 1");
                $st->execute(array(':uid' => $this->app->user->uid, ':pm_id' => $pm_id));
            }

            $this->lastInserted = $pm_id;
            return true;
        }

        function getError($code=null) {
            if ($code == null)
                return $this->error;

            switch($code) {
                case '1.1': return 'Missing message body';
                case '1.2': return 'Error creating conversation';
                case '1.3': return 'No valid recipients found';
                case '1.4': return 'You do not have permission to reply to this conversation';
                default: return 'Error';
            }
        }

        function getLastInserted() {
            return $this->lastInserted;
        }
    }
?>