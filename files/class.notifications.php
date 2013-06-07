<?php
    class notifications {
        public function add($to, $type, $from, $item) {
            global $db;

            $st = $db->prepare('INSERT INTO users_notifications (`user_id`, `type`, `from_id`, `item_id`) VALUES (:to, :type, :from, :item)');
            $result = $st->execute(array(':to' => $to, ':type' => $type, ':from' => $from, ':item' => $item));
           
            return $result;
        }

        public function getCounts() {
            global $db, $user;

            // Get event count
            $st = $db->prepare("SELECT count(notification_id) AS count
                FROM users_notifications
                WHERE users_notifications.user_id = :user_id AND seen = 0");
            $st->execute(array(':user_id' => $user->uid));
            $result = $st->fetch();

            $eventCount = $result->count ? (int) $result->count : 0;

            $st = $db->prepare("SELECT count(pm_users.pm_id) as count
                FROM pm_users
                INNER JOIN pm_messages
                ON message_id = (SELECT message_id FROM pm_messages WHERE pm_id = pm_users.pm_id AND (seen IS NULL || time > seen) ORDER BY time DESC LIMIT 1)
                WHERE pm_users.user_id = :user_id");
            $st->execute(array(':user_id' => $user->uid));
            $result = $st->fetch();

            $pmCount = $result->count ? (int) $result->count : 0;

            return array("events"=>$eventCount, "pm"=>$pmCount);
        }

        public function getEvents($limit=5, $offset=0) {
            global $app, $db, $user;

            // Get items
            $st = $db->prepare("SELECT notification_id AS id, users.user_id AS uid, item_id, type, UNIX_TIMESTAMP(users_notifications.time) AS timestamp, seen, username
                FROM users_notifications
                LEFT JOIN users
                ON (users_notifications.from_id = users.user_id)
                WHERE users_notifications.user_id = :uid
                ORDER BY users_notifications.time DESC
                LIMIT :offset, :limit");

            $st->bindParam(":uid", $user->uid);
            $st->bindParam(":offset", $offset, PDO::PARAM_INT);
            $st->bindParam(":limit", $limit, PDO::PARAM_INT);
            $st->execute();
            $result = $st->fetchAll();

            // Loop items, get details and create images
            foreach ($result as &$res) {
                if ($res->type == 'friend') {
                    // status
                    $st = $db->prepare("SELECT status
                        FROM users_friends
                        WHERE user_id = :friend_id AND friend_id = :uid
                        LIMIT 1");
                    $st->execute(array(':uid' => $user->uid, ':friend_id' => $res->uid));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();
                } else if ($res->type == 'medal') {
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
                } else if ($res->type == 'comment_reply' || $res->type == 'comment_mention') {
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
                } else if ($res->type == 'article') {
                    // uri, title
                    $st = $db->prepare("SELECT articles.title, CONCAT_WS('/', articles_categories.slug, articles.slug) AS slug
                        FROM articles
                        LEFT JOIN articles_categories
                        ON articles_categories.category_id = articles.category_id
                        WHERE article_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();

                    $res->slug = "/{$res->slug}";
                }

                // Parse title
                if (isset($res->title)) {
                    $res->title = $app->parse($res->title, false);
                }

                unset($res->id);
                unset($res->item_id);

                if ($res->type != 'friend')
                    unset($res->uid);

                if (isset($res->username))
                    $res->img = md5($res->username);
            }

            // Mark items as seen
            $st = $db->prepare("UPDATE users_notifications
                SET seen = '1'
                WHERE user_id = :uid");
            $st->execute(array(':uid' => $user->uid));

            return $result;
        }

        public function getPms() {
            global $db, $user, $app;
            // Get items
            $st = $db->prepare("SELECT pm.pm_id, title, username, UNIX_TIMESTAMP(time) as timestamp, IF (time < seen, 1, 0) AS seen
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
            $st->execute(array(':user_id' => $user->uid));
            $result = $st->fetchAll();

            // Loop items and create images
            foreach ($result as $res) {
                if (isset($res->username))
                    $res->img = md5($res->username);

                $res->title = $app->parse($res->title, false);
            }

            return $result;
        }

        public function getText($event) {
            global $app;
            if ($event->type === 'comment_reply') {
                $icon = 'comments';
                $text = $app->utils->username_link($event->username) . ' replied to your comment on ';
            } else if ($event->type === 'comment_mention') {
                $icon = 'comments';
                $text = $app->utils->username_link($event->username) . ' mentioned you in a comment on ';
            } else if ($event->type === 'article') {
                $icon = 'books';
                $text = 'Your article has been published ';
            } else if ($event->type === 'medal') {
                $icon = 'trophy colour-' . $event->colour;
                $text = "You have been awarded
                         <a href='/medals.php'><div class='medal medal-{$event->colour}'>{$event->label}</div></a>";
            } else if ($event->type === 'friend') {
                $icon = 'user';
                if ($event->status == 0)
                    $text = $app->utils->username_link($event->username) . ' sent you a friend request';
                else
                    $text = 'You accepted a friend request from ' . $app->utils->username_link($event->username);
            } else if ($event->type === 'friend_accepted') {
                $icon = 'addfriend';
                $text = $app->utils->username_link($event->username) . ' accepted your friend request';
            }

            if (isset($event->slug)) {
                $text .= "<a href='{$event->slug}'>{$event->title}</a>";
            }


            $return = "<i class='icon-{$icon}'></i> {$text}";

            return $return;
        }
    }
?>