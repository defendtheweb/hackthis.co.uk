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
            if ($res->diff <= -1) {
                $newday = true;
            }

            if ($res->diff == -1)
                $consecutive = 1;
            else if ($res->diff == 0)
                $consecutive = 0;

            $sql = 'UPDATE users_activity
                    SET last_active = NOW()';

            if ($login) {
                $sql .= ', last_login = current_login, current_login = NOW(), login_count = login_count + 1';
            }
            if ($newday) {
                $sql .= ', days = days + 1';
            }
            if ($consecutive === false) {
                $sql .= ', consecutive = 0';
            } else if ($consecutive === 1) {
                $sql .= ", consecutive = consecutive + 1, consecutive_most = IF (consecutive_most < consecutive, consecutive, consecutive_most)";
            }

            $sql .= ' WHERE user_id = :user_id';

            $st = $this->app->db->prepare($sql);
            $st->execute(array(':user_id' => $user->uid));
        }


        public function getLeaderboard($type="score", $limit=10, $position=false) {
            // Is there cache
            if ($cache = $this->app->cache->get('scoreboard', 1)) {
                return json_decode($cache);
            }


            $sql = 'SELECT username, score, (users_medals.user_id IS NOT NULL) AS donator, profile.gravatar,
                    IF (profile.gravatar = 1, users.email, profile.img) as `image`
                    FROM users
                    LEFT JOIN users_profile profile
                    ON users.user_id = profile.user_id
                    LEFT JOIN users_medals
                    ON users.user_id = users_medals.user_id AND users_medals.medal_id = (SELECT medal_id FROM medals WHERE label = "Donator")
                    ORDER BY score DESC
                    LIMIT 10';

            $st = $this->app->db->prepare($sql);
            $st->execute();
            $board = $st->fetchAll();

            for ($n = 0; $n < 3; $n++) {
                $user = $board[$n];
                if (isset($user->image)) {
                    $gravatar = isset($user->gravatar) && $user->gravatar == 1;
                    $user->image = profile::getImg($user->image, $n==0?30:18, $gravatar);
                } else
                    $user->image = profile::getImg(null, $n==0?30:18);
            }

            // Cache
            $this->app->cache->set('scoreboard', json_encode($board));

            return $board;
        }
    }
?>