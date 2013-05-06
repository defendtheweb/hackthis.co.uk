<?php
    require_once('init.php');

    $user_id = 2;

    if (isset($_GET['events'])) {
        // Get items
        $st = $db->prepare("SELECT notification_id AS id, type, UNIX_TIMESTAMP(users_notifications.time) AS timestamp, seen, username, label, colour, users_friends.status
            FROM users_notifications
            LEFT JOIN users
            ON (users_notifications.from_id = users.user_id)

            LEFT JOIN users_friends
            ON (users_friends.user_id = users.user_id AND friend_id = :user_id)

            LEFT JOIN medals
            ON (item_id = medal_id)
            LEFT JOIN medals_colours
            ON (medals.colour_id = medals_colours.colour_id)

            WHERE users_notifications.user_id = :user_id
            ORDER BY users_notifications.time DESC
            LIMIT 5");

        $st->execute(array(':user_id' => $user_id));
        $result = $st->fetchAll();

        // Loop items and create images
        foreach ($result as $res) {
            if (isset($res->username))
                $res->img = md5($res->username);
        }

        // Mark items as seen
        $st = $db->prepare("UPDATE users_notifications
            SET seen = '1'
            WHERE user_id = :user_id");
        $st->execute(array(':user_id' => $user_id));

        $result = json_encode($result);
        // Remove null entries
        echo preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $result);

    } else if (isset($_GET['pm'])) {
        // Get items
        $st = $db->prepare("SELECT pm.pm_id, title, username, message, UNIX_TIMESTAMP(MAX(time)) as timestamp, 1 as seen
            FROM pm
            INNER JOIN pm_users
            ON pm.pm_id = pm_users.pm_id
            INNER JOIN pm_messages
            ON pm.pm_id = pm_messages.pm_id
            INNER JOIN users
            ON pm_messages.user_id = users.user_id
            WHERE pm_users.user_id = :user_id
            GROUP BY pm_messages.pm_id
            LIMIT 5");
        $st->execute(array(':user_id' => $user_id));
        $result = $st->fetchAll();

        // Loop items and create images
        foreach ($result as $res) {
            if (isset($res->username))
                $res->img = md5($res->username);
        } 

        echo json_encode($result);
    }
?>