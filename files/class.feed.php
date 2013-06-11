<?php
    class feed {
        public function get($last=0, $user_id=null) {
            global $app, $db, $user;

            if (!isset($user_id)) {
                $st = $db->prepare('SELECT username, feed.user_id, feed.type, feed.item_id, UNIX_TIMESTAMP(feed.time) AS timestamp
                        FROM users_feed feed
                        LEFT JOIN users
                        ON feed.user_id = users.user_id
                        WHERE feed.type != "friend" AND feed.type != "comment_mention" AND feed.time > FROM_UNIXTIME(:last)
                        ORDER BY time DESC
                        LIMIT 10');
                $st->bindValue(':last', $last);
                $st->execute();
                $result = $st->fetchAll();
            } else {

                $st = $db->prepare('SELECT feed.user_id, feed.type, feed.item_id, UNIX_TIMESTAMP(feed.time) AS timestamp
                        FROM users_feed feed
                        WHERE user_id = :user_id AND feed.time > FROM_UNIXTIME(:last)
                        ORDER BY time DESC');
                $st->bindValue(':last', $last);
                $st->bindValue(':user_id', $user_id);
                $st->execute();
                $result = $st->fetchAll();                
            }

            // Loop items, get details and create images
            foreach ($result as &$res) {
                if ($res->type == 'friend') {
                    // status
                    $st = $db->prepare("SELECT username as username_2
                        FROM users
                        WHERE user_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
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
                } else if ($res->type == 'comment' || $res->type == 'comment_mention') {
                    // uri, title
                    $st = $db->prepare("SELECT users.username, articles.title, CONCAT_WS('/', articles_categories.slug, articles.slug) AS slug
                        FROM articles_comments
                        LEFT JOIN articles
                        ON articles_comments.article_id = articles.article_id
                        LEFT JOIN articles_categories
                        ON articles_categories.category_id = articles.category_id
                        LEFT JOIN users
                        ON articles_comments.user_id = users.user_id
                        WHERE comment_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();

                    $res->slug = "/{$res->slug}#comment-{$res->item_id}";
                } else if ($res->type == 'article' || $res->type == 'favourite') {
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
                unset($res->user_id);
            }

            return $result;
        }

        public function add($to, $type, $item) {
            global $db;

            $st = $db->prepare('INSERT INTO users_feed (`user_id`, `type`, `item_id`) VALUES (:to, :type, :item)');
            $result = $st->execute(array(':to' => $to, ':type' => $type, ':item' => $item));
           
            return $result;
        }
    }
?>