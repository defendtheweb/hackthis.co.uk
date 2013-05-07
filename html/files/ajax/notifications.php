<?php
    require_once('init.php');

    $userId = $user->uid;

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

        $st->execute(array(':user_id' => $userId));
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
        $st->execute(array(':user_id' => $userId));

        $result = json_encode($result);
        // Remove null entries
        echo preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $result);

    } else if (isset($_GET['pm'])) {

        // Get items
        $st = $db->prepare("SELECT pm.pm_id, title, username, message, UNIX_TIMESTAMP(time) as timestamp, IF (time < seen, 1, 0) AS seen
            FROM pm
            INNER JOIN pm_users
            ON pm.pm_id = pm_users.pm_id
            INNER JOIN pm_messages
            ON message_id = (SELECT message_id FROM pm_messages WHERE pm_id = pm.pm_id ORDER BY time DESC LIMIT 1)
            INNER JOIN users
            ON pm_messages.user_id = users.user_id
            WHERE pm_users.user_id = :user_id
            ORDER BY time DESC
            LIMIT 5");
        $st->execute(array(':user_id' => $userId));
        $result = $st->fetchAll();

        // Loop items and create images
        foreach ($result as $res) {
            if (isset($res->username))
                $res->img = md5($res->username);
        } 

        echo json_encode($result);

    } else {
        // Get event count
        $st = $db->prepare("SELECT count(notification_id) AS count
            FROM users_notifications
            WHERE users_notifications.user_id = :user_id AND seen = 0");
        $st->execute(array(':user_id' => $userId));
        $result = $st->fetch();

        $eventCount = $result->count ? (int) $result->count : 0;

        $st = $db->prepare("SELECT count(pm_users.pm_id) as count
            FROM pm_users
            INNER JOIN pm_messages
            ON message_id = (SELECT message_id FROM pm_messages WHERE pm_id = pm_users.pm_id AND (seen IS NULL || time > seen) ORDER BY time DESC LIMIT 1)
            WHERE pm_users.user_id = :user_id");
        $st->execute(array(':user_id' => $userId));
        $result = $st->fetch();

        $pmCount = $result->count ? (int) $result->count : 0;


        echo json_encode(array("events"=>$eventCount, "pm"=>$pmCount));

    }
?>