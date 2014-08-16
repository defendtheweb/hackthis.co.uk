<?php
    class stats {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        function users_activity($user, $login = false) {
            // Was the last activity yesterday?
            $consecutive = false;
            $sql = 'SELECT DATEDIFF(last_active, CURDATE()) as `diff`
                    FROM users_activity
                    WHERE user_id = :uid';
            $st = $this->app->db->prepare($sql);
            $st->execute(array(':uid' => $user->uid));
            $res = $st->fetch();

            $newday = false;
            if ($res) {
                if ($res->diff <= -1) {
                    $newday = true;
                }

                if ($res->diff == -1)
                    $consecutive = 1;
                else if ($res->diff == 0)
                    $consecutive = 0;
            }

            $sql = 'UPDATE users_activity
                    SET last_active = NOW()';

            if ($login) {
                $sql .= ', last_login = current_login, current_login = NOW(), login_count = login_count + 1';
            }
            if ($newday) {
                $sql .= ', days = days + 1';
            }
            if ($consecutive === false) {
                $sql .= ', consecutive = 1';
            } else if ($consecutive === 1) {
                $sql .= ", consecutive = consecutive + 1, consecutive_most = IF (consecutive_most < consecutive, consecutive, consecutive_most)";
            }

            $sql .= ' WHERE user_id = :user_id';

            $st = $this->app->db->prepare($sql);
            $st->execute(array(':user_id' => $user->uid));
        }


        public function getLeaderboard($limit=10) {
            $widget = ($limit == 10);

            // Is there cache
            if ($widget && $cache = $this->app->cache->get('scoreboard', 1)) {
                return json_decode($cache);
            }

            $sql = 'SELECT users.user_id, username, score, (users_medals.user_id IS NOT NULL) AS donator, profile.gravatar,
                    IF (profile.gravatar = 1, users.email, profile.img) as `image`
                    FROM users
                    LEFT JOIN users_profile profile
                    ON users.user_id = profile.user_id
                    LEFT JOIN users_priv
                    ON users_priv.user_id = users.user_id
                    LEFT JOIN users_medals
                    ON users.user_id = users_medals.user_id AND users_medals.medal_id = (SELECT medal_id FROM medals WHERE label = "Donator")
                    WHERE show_leaderboard = 1
                    ORDER BY score DESC, user_id ASC
                    LIMIT '.$limit;

            $st = $this->app->db->prepare($sql);
            $st->execute();
            $board = $st->fetchAll();

            $found = false;
            for ($n = 0; $n < ($widget?3:count($board)); $n++) {
                $user = $board[$n];
                if (isset($user->image)) {
                    $gravatar = isset($user->gravatar) && $user->gravatar == 1;
                    $user->image = profile::getImg($user->image, $widget?18:22, $gravatar);
                } else
                    $user->image = profile::getImg(null, $widget?18:22);

                if ($user->user_id == $this->app->user->uid) {
                    $user->highlight = true;
                    $found = true;
                }
            }

            if (!$widget && !$found) {
                // find users position
                $sql = 'SELECT COUNT(user_id) AS `position` FROM users WHERE score > :score';
                $st = $this->app->db->prepare($sql);
                $st->execute(array(':score' => $this->app->user->score));
                $result = $st->fetch();
                $result->extra = true;
                $result->highlight = true;
                $result->score = $this->app->user->score;
                $result->username = $this->app->user->username;
                $result->donator = $this->app->user->donator;
                $result->image = $this->app->user->image;

                $board[$limit] = $result;
            }


            // Cache
            if ($widget) {
                $this->app->cache->set('scoreboard', json_encode($board));
            }

            return $board;
        }


        /**
         * Get a list of the users who have been active in the last n minutes
         * @param  int $since Number of minutes to include
         * @return object List of online users and a count
         */
        public function getOnlineList($since = 5) {
            // check cache
            $online = $this->app->cache->get('online', 1);

            if (!$online) {
                $st = $this->app->db->prepare("SELECT u.user_id, u.username, u.score,
                        if (priv.site_priv = 2, true, false) AS `admin`, IF (priv.forum_priv = 2, true, false) AS `moderator`,
                        activity.last_active, IF (users_medals.user_id, true, false) AS `donator`
                        FROM users u
                        LEFT JOIN users_profile profile
                        ON u.user_id = profile.user_id
                        LEFT JOIN users_priv priv
                        ON u.user_id = priv.user_id
                        LEFT JOIN users_activity activity
                        ON u.user_id = activity.user_id
                        LEFT JOIN medals
                        ON medals.label = 'donator'
                        LEFT JOIN users_medals
                        ON users_medals.medal_id = medals.medal_id AND users_medals.user_id = u.user_id
                        WHERE activity.last_active > (NOW() - INTERVAL :since MINUTE) AND show_online = 1
                        ORDER BY activity.last_active DESC");
                $st->bindValue(':since', (int) $since, PDO::PARAM_INT);
                $st->execute();

                $online = $st->fetchAll();

                $this->app->cache->set('online', json_encode($online));

                // check if it beats the highscore
                $most = $this->app->cache->get('online_record');
                if ($most) {
                    $most = json_decode($most);
                }

                if (!$most || $most->count < count($online)) {
                    $most = new stdClass();
                    $most->count = count($online);
                    $most->date = date('c');

                    $this->app->cache->set('online_record', json_encode($most));
                }
            } else {
                $online = json_decode($online);
            }

            return $online;
        }
    }
?>