<?php
    class feed {
        public function get($last=0) {
            global $app, $db, $user;
            $st = $db->prepare('SELECT username, feed.user_id, feed.type, feed.item_id, UNIX_TIMESTAMP(feed.time) AS timestamp
                    FROM users_feed feed
                    LEFT JOIN users
                    ON feed.user_id = users.user_id
                    WHERE feed.time > FROM_UNIXTIME(:last)
                    ORDER BY time DESC');
            $st->bindValue(':last', $last);
            $st->execute();
            $result = $st->fetchAll();

            // Loop items, get details and create images
            foreach ($result as $res) {
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
                } else if ($res->type == 'comment') {
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
    }
?>