<?php
    class utils {

        public function repairUri($uri) {
            if ($ret = parse_url($uri)) {
                if (!isset($ret["scheme"]))
                   $uri = "http://{$uri}";
            } else {
                $uri = false;
            }
            return $uri;
        }

        public function username_link($username) {
            return "<a href='/user/{$username}'>{$username}</a>";
        }

        public function check_user($str) {
            if (strlen($str) <= 16 && strlen($str) > 3) {
                $allowed = !preg_match('/[^0-9A-Za-z_.-]/', $str);

                if ($str[0] == ".")
                    return false;

                if ($str[strlen($str)-1] == ".")
                    return false;
                       
                return $allowed;
            } else {
                return false;
            }
        }

        public function check_email($email) {
            // First, we check that there's one @ symbol, 
            // and that the lengths are right.
            if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
            // Email invalid because wrong number of characters 
            // in one section or wrong number of @ symbols.
                return false;
            }
            // Split it into sections to make life easier
            $email_array = explode("@", $email);
            $local_array = explode(".", $email_array[0]);
            for ($i = 0; $i < sizeof($local_array); $i++) {
                if (!preg_match("/^(([A-Za-z0-9!#$%&'*+=?^_`{|}~-][A-Za-z0-9!#$%&
                     ?'*+=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
                        return false;
                }
            }
            // Check if domain is IP. If not, 
            // it should be valid domain name
            if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) {
                    $domain_array = explode(".", $email_array[1]);
                    if (sizeof($domain_array) < 2) {
                        return false; // Not enough parts to domain
                    }
                    for ($i = 0; $i < sizeof($domain_array); $i++) {
                        if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
                             ?([A-Za-z0-9]+))$/", $domain_array[$i])) {
                                return false;
                        }
                    }
            }
            return true;
        }

        public function search_users($username, $limit=0) {
            global $db, $user;

            $username .= '%';

            $sql = 'SELECT u.username, IFNULL(friends.status, 0) AS friends
                    FROM users u
                    LEFT JOIN users_friends friends
                    ON (friends.user_id = u.user_id AND friends.friend_id = :user) OR (friends.user_id = :user AND friends.friend_id = u.user_id)
                    WHERE u.username LIKE :username AND u.user_id != :user
                    ORDER BY friends DESC, u.username
                    LIMIT :limit';
            $st = $db->prepare($sql);
            $st->bindValue(':username', $username);
            $st->bindValue(':user', $user->uid);
            $st->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $st->execute();

            return $st->fetchAll();
        }
    }
?>