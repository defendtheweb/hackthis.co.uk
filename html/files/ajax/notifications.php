<?php
    require_once('init.php');

    $userId = $user->uid;

    if (isset($_GET['events'])) {
        // Get items
        $st = $db->prepare("SELECT notification_id AS id, users.user_id, item_id, type, UNIX_TIMESTAMP(users_notifications.time) AS timestamp, seen, username
            FROM users_notifications
            LEFT JOIN users
            ON (users_notifications.from_id = users.user_id)
            WHERE users_notifications.user_id = :user_id
            ORDER BY users_notifications.time DESC
            LIMIT 5");

        $st->execute(array(':user_id' => $userId));
        $result = $st->fetchAll();

        // Loop items, get details and create images
        foreach ($result as $res) {
            if ($res->type == 1 || $res->type == 2) {
                // status
                $st = $db->prepare("SELECT status
                    FROM users_friends
                    WHERE user_id = :friend_id AND friend_id = :user_id
                    LIMIT 1");
                $st->execute(array(':user_id' => $userId, ':friend_id' => $res->user_id));
                $st->setFetchMode(PDO::FETCH_INTO, $res);
                $st->fetch();
            } else if ($res->type == 3) {
                // label, colour
                $st = $db->prepare("SELECT medals.label, medals_colours.colour
                    FROM medals
                    LEFT JOIN medals_colours
                    ON medals.colour_id = medals_colours.colour_id
                    WHERE medal_id = :item_id
                    LIMIT 1");
                $st->execute(array(':item_id' => $res->item_id));
                $st->setFetchMode(PDO::FETCH_INTO, $res);
                $st->fetch();
            } else if ($res->type == 6 || $res->type == 7) {
                // uri, title
                $st = $db->prepare("SELECT articles.title, CONCAT_WS('/', articles_categories.slug, articles.slug) AS slug
                    FROM articles_comments
                    LEFT JOIN articles
                    ON articles_comments.article_id = articles.article_id
                    LEFT JOIN articles_categories
                    ON articles_categories.category_id = articles.category_id
                    WHERE comment_id = :item_id
                    LIMIT 1");
                $st->execute(array(':item_id' => $res->item_id));
                $st->setFetchMode(PDO::FETCH_INTO, $res);
                $st->fetch();

                $res->slug = "/{$res->slug}#comment-{$res->item_id}";
            }

            unset($res->id);
            unset($res->item_id);
            unset($res->user_id);

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