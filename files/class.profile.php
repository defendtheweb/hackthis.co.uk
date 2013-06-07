<?php
    class profile {
        public function __construct($username, $uid=false) {
            global $db, $user, $app;

            if ($uid) {
                $where = 'u.user_id';
            } else
                $where = 'u.username';

            $st = $db->prepare("SELECT u.user_id as uid, u.username, u.score, u.email, profile.*, activity.joined, activity.last_active, friends.status AS friends, friends.user_id AS friend
                    FROM users u
                    LEFT JOIN users_profile profile
                    ON u.user_id = profile.user_id
                    LEFT JOIN users_activity activity
                    ON u.user_id = activity.user_id
                    LEFT JOIN users_friends friends
                    ON (friends.user_id = u.user_id AND friends.friend_id = :user) OR (friends.user_id = :user AND friends.friend_id = u.user_id)
                    WHERE {$where} = :profile");
            $st->execute(array(':profile' => $username, ':user' => $user->uid));
            $st->setFetchMode(PDO::FETCH_INTO, $this);
            $res = $st->fetch();

            if (!$res)
                return false;

            if ($uid)
                return true;

            $st = $db->prepare('SELECT users_medals.medal_id, medals.label, medals.description, medals_colours.colour
                    FROM users_medals
                    INNER JOIN medals
                    ON users_medals.medal_id = medals.medal_id
                    INNER JOIN medals_colours
                    ON medals.colour_id = medals_colours.colour_id
                    WHERE users_medals.user_id = :uid');
            $st->execute(array(':uid' => $this->uid));
            $this->medals = $st->fetchAll();

            $this->feed = $this->getFeed();
            $this->social = $this->getSocial();
        }

        public function getFeed() {
            global $app;
            $return = array();

            $feed = $app->feed->get(0, $this->uid);

            foreach($feed as $item) {
                switch($item->type) {
                    case 'comment':
                        $icon = 'comments';
                        $string = "Commented on <a href='{$item->slug}'>{$item->title}</a>";
                        break;
                    case 'favourite':
                        $icon = 'heart';
                        $string = "Favourited <a href='{$item->slug}'>{$item->title}</a>";
                        break;
                    case 'level':
                        $icon = 'good';
                        $string = "Completed something";
                        break;
                    case 'friend':
                        $icon = 'addfriend';
                        $string = "<a href='/user/{$item->username_2}'>{$item->username_2}</a> became friends with <a href='/user/{$this->username}'>{$this->username}</a>";
                        break;
                    case 'medal':
                        $icon = 'trophy colour-' . $item->colour;
                        $string = 'Awarded ' . $item->label;
                        break;
                    case 'article':
                        $icon = 'books';
                        $string = "<a href='{$item->slug}'>{$item->title}</a> was published";
                        break;
                    case 'join':
                        $icon = 'user';
                        $string = 'Joined HackThis!!';
                        break;
                    default:
                        $icon = 'warning';
                        $string = 'N/A';
                }

                array_push($return, array('icon'=>$icon, 'string'=>$string, 'time'=>$item->timestamp));
            }
            return $return;
        }

        public function getSocial() {
            global $app;
            $return = array();
            if (isset($this->website)) {
                $this->website = $app->utils->repairUri($this->website);
                array_push($return, array('icon'=>'globe', 'uri'=>$this->website));
            }


            return $return;
        }

        function printItem($key, $value, $time=false) {
            if (!$key || !$value)
                return;

            global $app;
            if ($time) {
                $value = '<time datetime="' . date('c', strtotime($value)) . '">' . date('d/m/Y', strtotime($value)) . '</time>';
            } else {
                $value = $app->parse($value, false, false);
            }
            return "                    <li><span class='strong'>{$key}:</span> {$value}</li>\n";
        }

        public function getDob() {
            if (!$this->show_dob)
                return false;

            $dob = strtotime($this->dob);

            if (date('dm', $dob) === date('dm'))
                return 'Today';
            if (date('dm', $dob) === date('dm', strtotime('tomorrow')))
                return 'Tomorrow';

            if ($this->show_dob == 1)
                return date('jS M', $dob);

            return date('jS M, Y', $dob);
        }

        public function addFriend() {
            global $db, $user;
            $status = ($user->uid === $this->uid);

            $st = $db->prepare('INSERT INTO users_friends (`user_id`, `friend_id`, `status`)
                    VALUES (:uid, :uid2, :status)');
            $st->execute(array(':uid' => $user->uid, ':uid2' => $this->uid, ':status' => $status));

            // check if row created, else it already exists
            if (!$st->rowCount()) {
                $st = $db->prepare('UPDATE users_friends SET `status` = 1
                                    WHERE `user_id` = :uid2 AND friend_id = :uid AND `status` = 0');
                $st->execute(array(':uid' => $user->uid, ':uid2' => $this->uid));
            }

            return $st->rowCount();
        }

        public function removeFriend() {
            global $db, $user;

            $st = $db->prepare('DELETE FROM users_friends
                                WHERE (user_id = :uid AND friend_id = :uid2) OR
                                (user_id = :uid2 AND friend_id = :uid)');
            $st->execute(array(':uid' => $user->uid, ':uid2' => $this->uid));

            return $st->rowCount();
        }
    }
?>