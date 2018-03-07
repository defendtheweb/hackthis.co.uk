<?php
    class feed {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function get($last=0, $user_id=null) {
            if (!isset($user_id)) {
                $st = $this->app->db->prepare('SELECT username, feed.user_id, feed.type, feed.item_id, feed.time AS timestamp
                        FROM users_feed feed
                        LEFT JOIN users
                        ON feed.user_id = users.user_id
                        WHERE feed.type != "friend" AND feed.type != "comment_mention" AND feed.time > :last
                        ORDER BY time DESC
                        LIMIT 10');
                $st->bindValue(':last', $last);
                $st->execute();
                $result = $st->fetchAll();
            } else {
                $st = $this->app->db->prepare('SELECT feed.feed_id, feed.user_id, feed.type, feed.item_id, feed.time AS timestamp
                        FROM users_feed feed
                        WHERE user_id = :user_id AND feed.time > :last
                        ORDER BY time DESC');
                $st->bindValue(':last', $last);
                $st->bindValue(':user_id', $user_id);
                $st->execute();
                $result = $st->fetchAll();
            }

            // Loop items, get details and create images
            foreach ($result as $key=>&$res) {
                if ($res->type == 'friend') {
                    // status
                    $st = $this->app->db->prepare("SELECT username as username_2
                        FROM users
                        WHERE user_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();
                } if ($res->type == 'level') {
                    // status
                    $st = $this->app->db->prepare("SELECT LOWER(CONCAT('/levels/', CONCAT_WS('/', levels_groups.title, levels.name))) as `uri`,
                        CONCAT(levels_groups.title, ' ', levels.name) as `title`
                        FROM levels
                        INNER JOIN levels_groups
                        ON levels_groups.title = levels.group
                        WHERE level_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();
                } else if ($res->type == 'medal') {
                    // label, colour
                    $st = $this->app->db->prepare("SELECT medals.label, medals_colours.colour
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
                    $st = $this->app->db->prepare("SELECT users.username, articles.title, CONCAT(IF(articles.category_id = 0, '/news/', '/articles/'), articles.slug) AS uri
                        FROM articles_comments
                        LEFT JOIN articles
                        ON articles_comments.article_id = articles.article_id
                        LEFT JOIN users
                        ON articles_comments.user_id = users.user_id
                        WHERE comment_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $n = $st->fetch();

                    if (!$n)
                        unset($result[$key]);
                    else
                        $res->uri = "{$res->uri}#comment-{$res->item_id}";
                } else if ($res->type == 'forum_post' || $res->type == 'forum_mention') {
                    // uri, title
                    $st = $this->app->db->prepare("SELECT users.username, forum_threads.title, CONCAT('/forum/', forum_threads.`slug`) AS uri
                        FROM forum_posts
                        LEFT JOIN forum_threads
                        ON forum_threads.thread_id = forum_posts.thread_id
                        LEFT JOIN users
                        ON forum_posts.author = users.user_id
                        WHERE forum_posts.post_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $n = $st->fetch();


                    if (!$n)
                        unset($result[$key]);
                    else
                        $res->uri = "{$res->uri}?post={$res->item_id}";
                } else if ($res->type == 'article' || $res->type == 'favourite') {
                    // uri, title
                    $st = $this->app->db->prepare("SELECT articles.title, articles.category_id, CONCAT(IF(articles.category_id = 0, '/news/', '/articles/'), articles.slug) AS uri
                        FROM articles
                        WHERE article_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $status = $st->fetch();

                    if ($status === false) {
                        unset($result[$key]);
                        continue;
                    }

                    if ($res->category_id == 0 && $res->type == 'article')
                        $res->type = 'news';
                    unset($res->category_id);
                }

                // Parse title
                if (isset($res->title)) {
                    $res->title = $this->app->parse($res->title, false);
                }

                unset($res->item_id);                
                unset($res->user_id);

                $res->timestamp = $this->app->utils->fdate($res->timestamp);
            }

            return $result;
        }

        public function call($username, $type, $title=null, $uri=null) {
            if (!function_exists('curl_version'))
                return;

            $ch = curl_init();

            $s = $this->app->config['socket'];
            curl_setopt($ch, CURLOPT_URL, $s['address'] . '/feed?api=' . $s['key']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 
                http_build_query(array('username' => $username, 'type' => $type, 'title' => $title, 'uri' => $uri, 'timestamp' => date('c'))));

            curl_exec($ch);

            curl_close ($ch);

            // If article post on twitter and facebook
            // if ($type == 'article' || $type == 'news') {
            //     // TWITTER
            //     require_once('vendor/twitteroauth.php');

            //     $config = $this->app->config('twitter');
            //     $tConsumerKey       = $config['key'];
            //     $tConsumerSecret    = $config['secret'];
            //     $tAccessToken       = $config['access-key'];
            //     $tAccessTokenSecret = $config['access-secret'];
                        
            //     $tweet = new TwitterOAuth($tConsumerKey, $tConsumerSecret, $tAccessToken, $tAccessTokenSecret);
                 
            //     $message = "{$title} - https://www.hackthis.co.uk{$uri}" ;

            //     $msg = $tweet->post('statuses/update', array('status' => $message));

            //     // FACEBOOK
            //     $config = $this->app->config('facebook');
            //     $args = array(
            //         'message' => "New article: {$title}",
            //         'link' => "https://www.hackthis.co.uk{$uri}",
            //         'access_token' => urlencode($config['token']),
            //     );

            //     $ch = curl_init();
            //     $url = 'https://graph.facebook.com/me/feed';
            //     curl_setopt($ch, CURLOPT_URL, $url);
            //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //     curl_setopt($ch, CURLOPT_HEADER, false);
            //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //     curl_setopt($ch, CURLOPT_POST, true);
            //     curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
            //     $result = curl_exec($ch);
            //     curl_close($ch);
            // }
        }

        public function remove($id) {
            $st = $this->app->db->prepare("DELETE FROM users_feed
                WHERE user_id = :user_id AND feed_id = :item_id
                LIMIT 1");
            $result = $st->execute(array(':item_id' => $id, ':user_id' => $this->app->user->uid));

            return $result;
        }
    }
?>
