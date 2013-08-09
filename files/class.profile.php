<?php
    class profile {
        private $app;

        public function __construct($username, $uid=false) {
            global $app;
            $this->app = $app;

            if ($uid) {
                $st = $this->app->db->prepare("SELECT u.user_id as uid, u.username, u.score, u.email, profile.forum_signature,
                                    friends.status AS friends, friends.user_id AS friend, profile.gravatar,
                                    IF (profile.gravatar = 1, u.email , profile.img) as `image`,
                                    IF (priv.site_priv = 2, true, false) AS admin, IF(priv.forum_priv = 2, true, false) AS moderator
                                    FROM users u
                                    LEFT JOIN users_profile profile
                                    ON u.user_id = profile.user_id
                                    LEFT JOIN users_friends friends
                                    ON (friends.user_id = u.user_id AND friends.friend_id = :user) OR (friends.user_id = :user AND friends.friend_id = u.user_id)
                                    LEFT JOIN users_priv priv
                                    ON u.user_id = priv.user_id
                                    WHERE u.user_id = :profile");
                $st->execute(array(':profile' => $username, ':user' => $this->app->user->uid));
                $st->setFetchMode(PDO::FETCH_INTO, $this);
                $res = $st->fetch();
            } else {
                $st = $this->app->db->prepare("SELECT u.user_id as uid, u.username, u.score, u.email, profile.*, activity.joined,
                                    activity.last_active, friends.status AS friends, friends.user_id AS friend, profile.gravatar,
                                    IF (profile.gravatar = 1, u.email , profile.img) as `image`,
                                    IF(priv.site_priv = 2, true, false) AS admin, IF(priv.forum_priv = 2, true, false) AS moderator
                                    FROM users u
                                    LEFT JOIN users_profile profile
                                    ON u.user_id = profile.user_id
                                    LEFT JOIN users_activity activity
                                    ON u.user_id = activity.user_id
                                    LEFT JOIN users_friends friends
                                    ON (friends.user_id = u.user_id AND friends.friend_id = :user) OR (friends.user_id = :user AND friends.friend_id = u.user_id)
                                    LEFT JOIN users_priv priv
                                    ON u.user_id = priv.user_id
                                    WHERE u.username = :profile");
                $st->execute(array(':profile' => $username, ':user' => $this->app->user->uid));
                $st->setFetchMode(PDO::FETCH_INTO, $this);
                $res = $st->fetch();
            }

            if (!$res)
                return false;

            if (isset($this->image)) {
                $gravatar = isset($this->gravatar) && $this->gravatar == 1;
                $this->image = profile::getImg($this->image, 198, $gravatar);
            } else
                $this->image = profile::getImg(null, 198);


            if ($uid)
                return true;


            $st = $this->app->db->prepare('SELECT users_medals.medal_id, medals.label, medals.description, medals_colours.colour
                    FROM users_medals
                    INNER JOIN medals
                    ON users_medals.medal_id = medals.medal_id
                    INNER JOIN medals_colours
                    ON medals.colour_id = medals_colours.colour_id
                    WHERE users_medals.user_id = :uid');
            $st->execute(array(':uid' => $this->uid));
            $this->medals = $st->fetchAll();

            $st = $this->app->db->prepare('SELECT u.username, users_friends.status, u.score, profile.gravatar, IF (profile.gravatar = 1, u.email , profile.img) as `image`
                    FROM users_friends as friends
                    INNER JOIN users u
                    ON u.user_id = IF(friends.user_id = :uid, friends.friend_id, friends.user_id)
                    LEFT JOIN users_profile profile
                    ON u.user_id = profile.user_id
                    LEFT JOIN users_friends
                    ON (users_friends.user_id = u.user_id AND users_friends.friend_id = :user) OR (users_friends.user_id = :user AND users_friends.friend_id = u.user_id)
                    WHERE friends.status = 1 AND (friends.user_id = :uid OR friends.friend_id = :uid)
                    ORDER BY u.username');
            $st->execute(array(':uid' => $this->uid, ':user' => $this->app->user->uid));
            $this->friendsList = $st->fetchAll();

            if (isset($this->about))
                $this->about = $this->app->parse($this->about);

            $this->lastfm = $this->app->parse($this->lastfm,false);

            $this->feed = $this->getFeed();
            $this->social = $this->getSocial();

            $this->owner = ($this->app->user->uid === $this->uid);
        }

        public function getFeed() {
            $return = array();

            $feed = $this->app->feed->get(0, $this->uid);

            foreach($feed as $item) {
                switch($item->type) {
                    case 'comment':
                        $icon = 'comments';
                        $string = "Commented on <a href='{$item->uri}'>{$item->title}</a>";
                        break;
                    case 'comment_mention':
                        $icon = 'comments';
                        $string = "Mentioned by <a href='/user/{$item->username}'>{$item->username}</a> on <a href='{$item->uri}'>{$item->title}</a>";
                        break;
                    case 'favourite':
                        $icon = 'heart';
                        $string = "Favourited <a href='{$item->uri}'>{$item->title}</a>";
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
                        $string = "Awarded <div class='medal medal-{$item->colour}'>$item->label</div>";
                        break;
                    case 'article':
                        $icon = 'books';
                        $string = "<a href='{$item->uri}'>{$item->title}</a> was published";
                        break;
                    case 'news':
                        $icon = 'article';
                        $string = "<a href='{$item->uri}'>{$item->title}</a> was published";
                        break;
                    case 'join':
                        $icon = 'user';
                        $string = 'Joined HackThis!!';
                        break;
                    default:
                        $icon = 'warning';
                        $string = 'N/A';
                }

                array_push($return, array('id'=>$item->feed_id, 'icon'=>$icon, 'string'=>$string, 'time'=>$item->timestamp));
            }
            return $return;
        }

        public function getSocial() {
                        $return = array();
            if (isset($this->website)) {
                $this->website = $this->app->utils->repairUri($this->website);
                array_push($return, array('icon'=>'globe', 'uri'=>$this->website));
            }


            return $return;
        }

        function printItem($key, $value, $time=false) {
                        if (!$key || !$value)
                return;

                        if ($time) {
                $value = '<time datetime="' . date('c', strtotime($value)) . '">' . $this->app->utils->timeSince($value) . '</time>';
            } else {
                $value = $this->app->parse($value, false, false);
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
            $status = ($this->app->user->uid === $this->uid);

            $error = false;
            try {
                $st = $this->app->db->prepare('INSERT INTO users_friends (`user_id`, `friend_id`, `status`)
                        VALUES (:uid, :uid2, :status)');
                $st->execute(array(':uid' => $this->app->user->uid, ':uid2' => $this->uid, ':status' => $status));
            } catch (Exception $e) {
                $error = true;
            }

            // check if row created, else it already exists
            if ($error || !$st->rowCount()) {
                $st = $this->app->db->prepare('UPDATE users_friends SET `status` = 1
                                    WHERE `user_id` = :uid2 AND friend_id = :uid AND `status` = 0');
                $st->execute(array(':uid' => $this->app->user->uid, ':uid2' => $this->uid));
            }

            return $st->rowCount();
        }

        public function removeFriend() {
            
            $st = $this->app->db->prepare('DELETE FROM users_friends
                                WHERE (user_id = :uid AND friend_id = :uid2) OR
                                (user_id = :uid2 AND friend_id = :uid)');
            $st->execute(array(':uid' => $this->app->user->uid, ':uid2' => $this->uid));

            return $st->rowCount();
        }

        public static function getMusic($id) {
            
            if (!isset($id))
                return false;

            $lfm = $this->app->config('lastfm');
            $uri = "http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user={$id}&limit=5&api_key={$lfm['public']}&format=json";

            $data = @file_get_contents($uri);
            if (!$data)
                return false;

            $data = json_decode($data);
            if (isset($data->error))
                return false;

            $tracks = $data->recenttracks->track;
            $return = array();
            foreach($tracks as $track) {
                $tmp = array();
                $tmp['artist'] = $track->artist->{'#text'};
                $tmp['song'] = $track->name;
                array_push($return, $tmp);
            }


            return $return;
        }

        public static function getImg($img, $size, $gravatar=false) {
            $default = "/users/images/{$size}/1:1/no_pic.jpg";

            if (!$img)
                return $default;

            if ($gravatar) {
                return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($img))) . "?d=http://www.hackthis.co.uk/users/images/no_pic.jpg&s=" . $size;
            } else {
                return "/users/images/{$size}/1:1/{$img}";
            }
        }
    }
?>