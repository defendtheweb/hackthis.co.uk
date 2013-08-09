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
            if ($res->diff == -1)
                $consecutive = 1;
            else if ($res->diff == 0)
                $consecutive = 0;

            $sql = 'UPDATE users_activity
                    SET last_active = NOW()';

            if ($login) {
                $sql .= ', last_login = current_login, current_login = NOW(), login_count = login_count + 1';
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
    }
?>