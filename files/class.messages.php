<?php
class messages {
    public function getCount($unread=true) {
        global $db, $user;

        $st = $db->prepare("SELECT count(pm_users.pm_id) as count
            FROM pm_users
            INNER JOIN pm_messages
            ON message_id = (SELECT message_id FROM pm_messages WHERE pm_id = pm_users.pm_id AND (seen IS NULL || time > seen) ORDER BY time DESC LIMIT 1)
            WHERE pm_users.user_id = :user_id");
        $st->execute(array(':user_id' => $user->uid));
        $result = $st->fetch();

        return $result ? (int) $result->count : 0;
    }

    public function getAll($size=28, $limit=true) {
        global $db, $user, $app;

        $sql = "SELECT pm.pm_id, pm_messages.user_id as lastSender, message, time as timestamp, IF (time <= seen, 1, 0) AS seen
               FROM pm
               INNER JOIN pm_users
               ON pm.pm_id = pm_users.pm_id
               INNER JOIN pm_messages
               ON message_id = (SELECT message_id FROM pm_messages WHERE pm_id = pm.pm_id ORDER BY time DESC LIMIT 1)
               WHERE pm_users.user_id = :user_id
               ORDER BY time DESC";

        if ($limit)
            $sql .= " LIMIT 5";

        // Get items
        $st = $db->prepare($sql);
        $st->execute(array(':user_id' => $user->uid));
        $result = $st->fetchAll();

        // Loop items and create images
        foreach ($result as $res) {
            // Get assosiated users
            $st = $db->prepare("SELECT username, profile.gravatar, IF (profile.gravatar = 1, users.email , profile.img) as `image`
                               FROM pm_users
                               INNER JOIN users
                               ON pm_users.user_id = users.user_id
                               LEFT JOIN users_profile profile
                               ON profile.user_id = users.user_id
                               WHERE pm_users.pm_id = :pm_id AND pm_users.user_id != :user_id
                               ORDER BY username DESC");
            $st->execute(array(':pm_id' => $res->pm_id, ':user_id' => $user->uid));
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
                $tmp->username = $user->username;
                $tmp->img = $user->image;
                array_push($res->users, $tmp);
            }

            $res->message = $app->parse(substr($res->message, 0, 75), false);

            if ($res->lastSender == $user->uid) {
                $res->message = '<i class="icon-reply"></i> '. $res->message;
            }
            unset($res->lastSender);

            //time
            $res->timestamp = $app->utils->fdate($res->timestamp);
        }

        return $result;
    }

    public function getConvo($id, $limit=true) {
        global $db, $user, $app;

        $sql = "SELECT message, messages.time as timestamp, IF (messages.time <= seen, 1, 0) AS seen,
               username, profile.gravatar, IF (profile.gravatar = 1, users.email , profile.img) as `image`
               FROM pm_messages messages
               INNER JOIN pm_users
               ON messages.pm_id = pm_users.pm_id AND pm_users.user_id = :uid
               INNER JOIN users
               ON messages.user_id = users.user_id
               LEFT JOIN users_profile profile
               ON profile.user_id = users.user_id
               WHERE messages.pm_id = :pm_id
               ORDER BY messages.time DESC";
        if ($limit) {
            $sql .= ' LIMIT 5';
        }

        // Get items
        $st = $db->prepare($sql);
        $st->execute(array(':uid' => $user->uid, ':pm_id' => $id));
        $result = $st->fetchAll();

        //flip array
        $result = array_reverse($result);

        // Mark thread as seen
        $st = $db->prepare("UPDATE pm_users SET `seen` = NOW() WHERE user_id = :uid AND pm_id = :pm_id LIMIT 1");
        $st->execute(array(':uid' => $user->uid, ':pm_id' => $id));

        // Loop items and create images
        foreach ($result as $res) {
            if (isset($res->image)) {
                $gravatar = isset($res->gravatar) && $res->gravatar == 1;
                $res->img = profile::getImg($res->image, 28, $gravatar);
            } else
                $res->img = profile::getImg(null, 28);

            unset($res->image);
            unset($res->gravatar);

            $res->message = $app->parse($res->message);

            //time
            $res->timestamp = $app->utils->fdate($res->timestamp);
        }

        return $result;
    }

    public function getConvoUsers($id) {
        global $db, $user;

        $st = $db->prepare("SELECT username
                           FROM pm_users
                           INNER JOIN users
                           ON pm_users.user_id = users.user_id
                           WHERE pm_users.pm_id = :pm_id AND pm_users.user_id != :user_id
                           ORDER BY username DESC");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array(':pm_id' => $id, ':user_id' => $user->uid));
        $result = $st->fetchAll();

        if (!count($result))
            $result = array(array("username"=>$user->username));

        return $result;
    }

    public function newMessage($to, $body, $pm_id=null) {
        global $db, $user;

        if ($to !== null) {
            $recipients = preg_split('/[\ \n\,]+/', $to);

            //Start transaction
            $db->beginTransaction();

            //Create thread
            $st = $db->prepare('INSERT INTO pm VALUES ()');
            $result = $st->execute();
            if (!$result) {
                $db->rollBack();
                return false;
            }

            $pm_id = $db->lastInsertId();

            //Add recipients
            try {
                $st = $db->prepare('INSERT INTO pm_users (`pm_id`, `user_id`)
                                    SELECT :pm_id, u.user_id FROM users u WHERE username = :name');
                $st->bindParam(':pm_id', $pm_id);
                $st->bindParam(':name', $name);

                $count = 0;

                foreach($recipients as $rec) {
                    $name = $rec;
                    $st->execute();
                    $count += $st->rowCount();
                }

                if ($count < 1) {
                    $db->rollBack();
                    return false;
                }
            } catch(PDOException $e) {
                $db->rollBack();
                return false;
            } 

            //Add sender
            $st = $db->prepare('INSERT INTO pm_users (`pm_id`, `user_id`, `seen`)
                                VALUES (:pm_id, :uid, NOW())
                                ON DUPLICATE KEY UPDATE `seen` = NOW()');
            $result = $st->execute(array(':pm_id' => $pm_id, ':uid' => $user->uid,));
            if (!$result) {
                $db->rollBack();
                return false;
            }

            //Insert message
            $st = $db->prepare('INSERT INTO pm_messages (`pm_id`, `user_id`, `message`)
                                VALUES (:pm_id, :uid, :body)');
            $result = $st->execute(array(':pm_id' => $pm_id, ':uid' => $user->uid, ':body' => $body));
            if (!$result) {
                $db->rollBack();
                return false;
            }

            $db->commit();
        } else {
            //Lookup privilages
            $st = $db->prepare('INSERT INTO pm_messages (`pm_id`, `user_id`, `message`)
                                SELECT :pm_id, :uid, :body FROM pm_users
                                WHERE user_id = :uid AND pm_id = :pm_id');
            $result = $st->execute(array(':pm_id' => $pm_id, ':uid' => $user->uid, ':body' => $body));
            if (!$st->rowCount())
                return false;

            $st = $db->prepare("UPDATE pm_users SET `seen` = NOW() WHERE user_id = :uid AND pm_id = :pm_id LIMIT 1");
            $st->execute(array(':uid' => $user->uid, ':pm_id' => $pm_id));
        }

        return true;
    }
}
?>