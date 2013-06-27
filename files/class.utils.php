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

        public function fdate($datetimestring = '1970-01-01 00:00:00', $format = 'c') {
            $dt = new DateTime($datetimestring);
            return $dt->format($format);
        }

        public function timeSince($date, $short=false) {
            $date = strtotime($date);
            $diff = time() - $date;
            
            if (!$diff)
                return "secs" . (!$short?' ago':'');

            date('d-m-Y', $date);

            $isSameDay = (date('d-m-Y', $date) === date('d-m-Y'));

            if ($isSameDay) {
                if ($diff < 60)
                    return "secs" . (!$short?' ago':'');
                else if ($diff < 3600) {
                    $n = floor($diff/60);
                    return "{$n} min" . ($n==1?'':'s') . (!$short?' ago':''); 
                } else {
                    $n = floor($diff/3600);
                    return "{$n} hour" . ($n==1?'':'s') . (!$short?' ago':''); 
                }
            } else if ($short) {
                return date('d/m', $date);
            } else {
                $yesterday = (date('d-m-Y', $date) === date('d-m-Y', strtotime("yesterday")));

                if ($yesterday)
                    return "Yesterday";
                else {
                    $thisWeek = ($date > strtotime("-6 days"));
                    if ($thisWeek)
                        return date('l', $date);
                    else
                        return date('F j, Y', $date);
                }
            }
        }
    }
?>