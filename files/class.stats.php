<?php
    class stats {
        function users_activity($user, $login = false) {
            global $db;

            $sql = 'UPDATE users_activity
                    SET last_active = NOW()';

            if ($login) {
                $sql .= ', last_login = current_login, current_login = NOW(), login_count = login_count + 1';
            }

            $sql .= ' WHERE user_id = :user_id';

            $st = $db->prepare($sql);
            $st->execute(array(':user_id' => $user->uid));
        }
    }
?>