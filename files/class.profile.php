<?php
    class profile {
        private $app;
        private $linkTypes = array(
                                "twitter\." => "twitter",
                                "facebook\." => "facebook",
                                "github\." => "github-2",
                                "youtube\." => "youtube",
                                "reddit\." => "reddit",
                                "soundcloud\." => "soundcloud",
                                "dribbble\." => "dribbble",
                                "deviantart\." => "deviantart",
                                "flickr\." => "flickr",
                                "plus\.google\." => "google-plus",
                                "last\.fm" => "lastfm",
                                "stackoverflow\." => "stackoverflow",
                                "pinterest\." => "pinterest"
                             );

        // If $uid just get the basic user info ... for ajax stuff
        public function __construct($username, $public=false) {
            global $app;
            $this->app = $app;

            if ($public) {
                $st = $this->app->db->prepare("SELECT u.user_id as uid, u.username, u.score, u.email, profile.show_email, profile.about, profile.forum_signature,
                    friends.status AS friends, profile.gravatar,
                    IF (profile.gravatar = 1, u.email , profile.img) as `image`,
                    IF (priv.site_priv = 2, true, false) AS admin, IF(priv.forum_priv = 2, true, false) AS moderator,
                    coalesce(priv.site_priv, 1) AS `site_priv`, coalesce(priv.pm_priv, 1) AS `pm_priv`, coalesce(priv.forum_priv, 1) AS `forum_priv`, coalesce(priv.pub_priv, 1) AS `pub_priv`
                    FROM users u
                    LEFT JOIN users_profile profile
                    ON u.user_id = profile.user_id
                    LEFT JOIN users_friends friends
                    ON (friends.user_id = u.user_id AND friends.friend_id = :user) OR (friends.user_id = :user AND friends.friend_id = u.user_id)
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    WHERE u.user_id = :profile or u.username = :profile");
                $st->execute(array(':profile' => $username, ':user' => $this->app->user->uid));
                $st->setFetchMode(PDO::FETCH_INTO, $this);
                $res = $st->fetch();

                if (!$res) {
                    return false;
                }

                // is this user allowed to see that stuff?
                if (!$this->app->user->admin_site_priv && !$this->show_email) {
                    unset($this->email);
                }
                unset($this->show_email);

                if (isset($this->image)) {
                    $gravatar = isset($this->gravatar) && $this->gravatar == 1;
                    $this->image = profile::getImg($this->image, 198, $gravatar);
                } else {
                    $this->image = profile::getImg(null, 198);
                }
                unset($this->gravatar);

                if (!$this->app->user->admin_site_priv) {
                    unset($this->site_priv);
                    unset($this->pm_priv);
                    unset($this->forum_priv);
                    unset($this->pub_priv);
                }

                if ($this->friends === null) {
                    unset($this->friends);
                }
            } else {
                $st = $this->app->db->prepare("SELECT u.user_id as uid, u.username, u.score, u.email, profile.*, activity.joined,
                    activity.last_active, friends.status AS friends, friends.user_id AS friend, profile.gravatar,
                    IF (profile.gravatar = 1, u.email , profile.img) as `image`,
                    IF(priv.site_priv = 2, true, false) AS admin, IF(priv.forum_priv = 2, true, false) AS moderator,
                    forum_posts.posts, articles.articles, (donated.user_id IS NOT NULL) AS donator, (users_blocks.user_id IS NOT NULL) AS blocked, (users_blocks_me.user_id IS NOT NULL) AS blockedMe, karma.karma
                    FROM users u
                    LEFT JOIN users_profile profile
                    ON u.user_id = profile.user_id
                    LEFT JOIN users_activity activity
                    ON u.user_id = activity.user_id
                    LEFT JOIN users_friends friends
                    ON (friends.user_id = u.user_id AND friends.friend_id = :user) OR (friends.user_id = :user AND friends.friend_id = u.user_id)
                    LEFT JOIN users_blocks 
                    ON users_blocks.user_id = :user AND users_blocks.blocked_id = u.user_id
                    LEFT JOIN users_blocks users_blocks_me
                    ON users_blocks_me.user_id = u.user_id AND users_blocks_me.blocked_id = :user
                    LEFT JOIN (SELECT author, COUNT(*) AS `posts` FROM forum_posts WHERE deleted = 0 GROUP BY author) forum_posts
                    ON forum_posts.author = u.user_id
                    LEFT JOIN (SELECT user_id, COUNT(*) AS `articles` FROM articles GROUP BY user_id) articles
                    ON articles.user_id = u.user_id
                    LEFT JOIN users_priv priv
                    ON u.user_id = priv.user_id
                    LEFT JOIN users_medals donated
                    ON u.user_id = donated.user_id AND donated.medal_id = (SELECT medal_id FROM medals WHERE label = 'Donator')
                    LEFT JOIN (SELECT SUM(karma) AS karma, forum_posts.author FROM users_forum INNER JOIN forum_posts ON users_forum.post_id = forum_posts.post_id AND forum_posts.deleted = 0 GROUP BY forum_posts.author) karma
                    ON karma.author = u.user_id
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


            if ($public)
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

            $st = $this->app->db->prepare('SELECT u.user_id as uid, u.username, users_friends.status, u.score, profile.gravatar, IF (profile.gravatar = 1, u.email , profile.img) as `image`
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

            if (isset($this->about)) {
                $this->about_plain = $this->about;
                $this->about = $this->app->parse($this->about);
            }

            $this->feed = $this->getFeed();
            $this->links = $this->getLinks();

            $this->owner = ($this->app->user->uid === $this->uid);

            // Check score and award medal?
            if ($this->score >= $this->app->max_score)
                $this->score_perc = 100;
            else
                $this->score_perc = $this->score/$this->app->max_score * 100;
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
                        $string = "Completed <a href='{$item->uri}'>{$item->title}</a>";
                        break;
                    case 'friend':
                        $icon = 'addfriend';
                        $string = "<a href='/user/{$item->username_2}'>{$item->username_2}</a> became friends with <a href='/user/{$this->username}'>{$this->username}</a>";
                        break;
                    case 'medal':
                        $icon = 'trophy colour-' . $item->colour;
                        $item->label = strtolower($item->label);
                        $string = "Awarded <a href='/medals.php#{$item->label}' class='medal medal-{$item->colour}'>$item->label</a>";
                        break;
                    case 'article':
                        $icon = 'books';
                        $string = "<a href='{$item->uri}'>{$item->title}</a> was published";
                        break;
                    case 'news':
                        $icon = 'article';
                        $string = "<a href='{$item->uri}'>{$item->title}</a> was published";
                        break;
                    case 'forum_post':
                        $icon = 'chat';
                        $string = "Posted in <a href='{$item->uri}'>{$item->title}</a>";
                        break;
                    case 'forum_mention':
                        $icon = 'chat';
                        $string = "Mentioned by <a href='/user/{$item->username}'>{$item->username}</a> in <a href='{$item->uri}'>{$item->title}</a>";
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

        public function getLinks() {
            $return = array();
            if (!isset($this->website) || !$this->website) {
                return false;
            }

            if (!$websites = json_decode($this->website)) {
                $websites = array($this->website);
            }

            foreach($websites AS &$website) {
                if (!$website) {
                    unset($website);
                    continue;
                }

                $website = $this->app->utils->repairUri($website);
                $icon = "globe";

                foreach($this->linkTypes AS $tmptype => $tmpicon) {
                    $pattern = "/^https?:\/\/(www.)?{$tmptype}(.*)\/(.+)/";
                    if (preg_match($pattern, $website)) {
                        $icon = $tmpicon;
                        break;
                    }
                }

                array_push($return, array('icon'=>$icon, 'url'=>$website));
            }

            return $return;
        }

        function printItem($key, $value=0, $time=false, $uc=false) {
            if (!$key)
                return;

            if ($time) {
                $value = '<time class="forceSince" datetime="' . date('c', strtotime($value)) . '">' . $this->app->utils->timeSince($value, false, true) . '</time>';
            } else if (!$value) {
                $value = 0;
            } else if (is_numeric($value)) {
                $value = number_format($value);
            } else {
                if ($uc)
                    $value = ucfirst($value);
                $value = $this->app->parse($value, false, false);
            }
            echo "                    <li><span class='strong'>{$value}</span><span class='small'>{$key}</span></li>\n";
        }

        public function getDob() {
            if (!$this->show_dob || !$this->dob)
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

            // Check if user is blocked
            $st = $this->app->db->prepare('SELECT `user_id` FROM users_blocks WHERE `user_id` = :uid2 AND `blocked_id` = :uid');
            $st->execute(array(':uid' => $this->app->user->uid, ':uid2' => $this->uid));
            if ($st->fetch())
                return false;


            // try and make request, if fails there is already a pending request
            $error = false;
            try {
                $st = $this->app->db->prepare('INSERT INTO users_friends (`user_id`, `friend_id`, `status`)
                        VALUES (:uid, :uid2, :status)');
                $st->execute(array(':uid' => $this->app->user->uid, ':uid2' => $this->uid, ':status' => $status));
            } catch (Exception $e) {
                $error = true;
            }

            // check if row created, else it already exists - therefore accept pending request
            if ($error || !$st->rowCount()) {
                $st = $this->app->db->prepare('UPDATE users_friends SET `status` = 1
                                    WHERE `user_id` = :uid2 AND friend_id = :uid AND `status` = 0');
                $st->execute(array(':uid' => $this->app->user->uid, ':uid2' => $this->uid));
            } else {
                if ($st->rowCount()) {
                    // Inform other user
                    $st = $this->app->db->prepare('SELECT `user_id`, `username`, `email` FROM users WHERE `user_id` = :uid');
                    $st->execute(array(':uid' => $this->uid));
                    $res = $st->fetch();
                    if ($res) {
                        $data = array('username' => $res->username, 'from' => $this->app->user->username, 'image' => $this->app->user->image, 'score' => $this->app->user->score, 'posts' => $this->app->user->posts);
                        // $this->app->email->queue($res->email, 'friend', json_encode($data), $this->uid);
                        $this->app->email->mandrillSend($res->user_id, $this->app->user->user_id, 'friend-request', 'Friend request from ' . $this->app->user->username, $data);
                    }
                }
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



        /* STATIC FUNCTIONS */
        public static function getStats($uid, $type='posts') {
            global $app;
            
            $result = array("status"=>true);

            if ($type == 'levels') {
                $result['data'] = $app->levels->getList($uid);
            } else if ($type == 'posts') {
                $st = $app->db->prepare('SELECT date_format(posted, "%d/%m/%Y") AS `d`, COUNT(*) AS `c` FROM forum_posts
                    WHERE deleted = 0 AND author = :uid
                    GROUP BY `d`
                    ORDER BY `posted` ASC');
                $st->execute(array(':uid' => $uid));
                $result['graph'] = $st->fetchAll();

                $st = $app->db->prepare('SELECT posts.post_id, posts.body, posts.posted AS `time`, threads.title, CONCAT("/forum/", threads.slug) AS slug,
                    IF(section.priv_level, IF(users_levels.level_id, 1, 0),1) AS `access`
                    FROM forum_posts posts

                    INNER JOIN forum_threads threads
                    ON threads.thread_id = posts.thread_id

                    LEFT JOIN forum_sections AS section
                    ON section.section_id = threads.section_id

                    LEFT JOIN users_levels
                    ON users_levels.user_id = :uid2 AND users_levels.completed > 0 AND users_levels.level_id = section.priv_level

                    WHERE posts.deleted = 0 AND posts.author = :uid
                    AND (threads.section_id != 95 && (threads.section_id < 100 || threads.section_id > 233))
                    HAVING `access` > 0
                    ORDER BY posts.`posted` DESC');
                $st->execute(array(':uid' => $uid, ':uid2' => $app->user->uid));
                $result['data'] = $st->fetchAll();

                if ($result['data']) {
                    foreach ($result['data'] AS $post) {
                        $post->title = $app->parse($post->title, false);
                        $post->body = $app->parse($post->body, false);
                        $post->time = date('c', strtotime($post->time));
                    }
                }
            } else {
                $st = $app->db->prepare('SELECT date_format(submitted, "%d/%m/%Y") AS `d`, COUNT(*) AS `c` FROM articles
                    WHERE user_id = :uid
                    GROUP BY `d`
                    ORDER BY `submitted` ASC');
                $st->execute(array(':uid' => $uid));
                $result['graph'] = $st->fetchAll(); 

                $st = $app->db->prepare('SELECT articles.`submitted` AS `time`, articles.title, CONCAT(IF(articles.category_id = 0, "/news/", "/articles/"), articles.slug) AS slug
                    FROM articles
                    WHERE articles.user_id = :uid
                    ORDER BY articles.`submitted` DESC');
                $st->execute(array(':uid' => $uid));
                $result['data'] = $st->fetchAll();

                if ($result['data']) {
                    foreach ($result['data'] AS $post) {
                        $post->title = $app->parse($post->title, false);
                        $post->time = date('c', strtotime($post->time));
                    }
                }
            }

            return $result;
        }

        public static function blockUser($uid, $block=true) {
            global $app;

            if ($app->user->uid == $uid)
                return false;

            if ($block) {
                $st = $app->db->prepare('INSERT IGNORE INTO users_blocks (`user_id`, `blocked_id`)
                        VALUES (:uid, :uid2)');
            } else {
                $st = $app->db->prepare('DELETE FROM users_blocks WHERE user_id = :uid AND blocked_id = :uid2');
            }
            return $st->execute(array(':uid' => $app->user->uid, ':uid2' => $uid));
        }

        public static function getMusic($id) {
            global $app;
            if (!isset($id))
                return false;

            $lfm = $app->config('lastfm');
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
                return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($img))) . "?d=identicon&s=" . $size;
            } else {
                return "/users/images/{$size}/1:1/{$img}";
            }
        }
    }
?>
