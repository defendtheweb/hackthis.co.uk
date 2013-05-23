<?php
    class articles {
        function __construct() {

        }

        public function get_articles($category_id) {
            global $db, $user;

            // Group by required for count
            $st = $db->prepare('SELECT a.article_id AS id, username, title, slug, body, submitted, updated,
                        COALESCE(comments.count, 0) AS comments,
                        COALESCE(favourites.count, 0) AS favourites,
                        COALESCE(user_favourites.count, 0) AS favourited
                    FROM articles a
                    LEFT JOIN users
                    ON users.user_id = a.user_id
                    LEFT JOIN 
                        ( SELECT article_id, COUNT(*) AS count FROM articles_comments WHERE deleted IS NULL GROUP BY article_id) comments
                    ON a.article_id = comments.article_id
                    LEFT JOIN 
                        ( SELECT article_id, COUNT(*) AS count FROM articles_favourites GROUP BY article_id) favourites
                    ON a.article_id = favourites.article_id
                    LEFT JOIN 
                        ( SELECT article_id, COUNT(*) AS count FROM articles_favourites WHERE user_id = :uid GROUP BY article_id) user_favourites
                    ON a.article_id = user_favourites.article_id
                    WHERE a.category_id = :cat_id
                    GROUP BY a.article_id
                    ORDER BY submitted DESC');
            $st->execute(array(':cat_id' => $category_id, ':uid' => $user->uid));
            $result = $st->fetchAll();

            return $result;
        }

        public function get_article($slug) {
            global $db, $user;

            // Group by required for count
            $st = $db->prepare('SELECT a.article_id AS id, username, title, slug, body, submitted, updated,
                        COALESCE(comments.count, 0) AS comments,
                        COALESCE(favourites.count, 0) AS favourites,
                        COALESCE(user_favourites.count, 0) AS favourited
                    FROM articles a
                    LEFT JOIN users
                    ON users.user_id = a.user_id
                    LEFT JOIN 
                        ( SELECT article_id, COUNT(*) AS count FROM articles_comments WHERE deleted IS NULL GROUP BY article_id) comments
                    ON a.article_id = comments.article_id
                    LEFT JOIN 
                        ( SELECT article_id, COUNT(*) AS count FROM articles_favourites GROUP BY article_id) favourites
                    ON a.article_id = favourites.article_id
                    LEFT JOIN 
                        ( SELECT article_id, COUNT(*) AS count FROM articles_favourites WHERE user_id = :uid GROUP BY article_id) user_favourites
                    ON a.article_id = user_favourites.article_id
                    WHERE a.slug = :slug
                    GROUP BY a.article_id
                    ORDER BY submitted DESC');
            $st->execute(array(':slug' => $slug, ':uid' => $user->uid));
            $result = $st->fetch();

            return $result;
        }

        public function update_article($id, $changes, $updated=true) {
            if (!is_array($changes)) return false;

            global $db, $user;

            //Check privilages
            if (!$user->loggedIn)
                return false;

            // Get field list
            $fields = '';
            $values = array();

            foreach ($changes as $field=>$change) {
                $fields .= "`$field` = ?,";
                $values[] = $change;
            }

            $fields = rtrim($fields, ',');

            $query  = "UPDATE articles SET ".$fields;
            if ($updated)
                $query .= ",updated=NOW()";
            $query .= " WHERE article_id=?";
            $values[] = $id;

            $st = $db->prepare($query);
            $res = $st->execute($values); 

            return $res;
        }

        public function get_comments($article_id, $parent_id=0, $bbcode=true) {
            global $app, $db, $user;

            // Group by required for count
            $st = $db->prepare('SELECT comments.comment_id as id, comments.comment, comments.deleted,
                               DATE_FORMAT(comments.time, \'%Y-%m-%dT%T+01:00\') as `time`,
                               coalesce(users.username, 0) as username, MD5(users.username) as `image`
                    FROM articles_comments comments
                    LEFT JOIN users
                    ON users.user_id = comments.user_id
                    WHERE article_id = :article_id AND parent_id = :parent_id
                    ORDER BY `time` DESC');
            $st->execute(array(':article_id' => $article_id, ':parent_id' => $parent_id));
            $result = $st->fetchAll();

            foreach($result as $key=>$comment) {
                if (!$comment->deleted) {
                    if ($bbcode)
                        $comment->comment = $app->parse($comment->comment);

                    if ($comment->username === $user->username)
                        $comment->owner = true;
                } else {
                    unset($comment->username);
                    unset($comment->comment);
                    unset($comment->image);
                    unset($comment->score);
                }

                $replies = $this->get_comments($article_id, $comment->id);
                if ($replies) {
                    $comment->replies = $replies;
                    unset($comment->deleted);
                } else if ($comment->deleted) {
                    //array_splice($result, $key, 1);
                    unset($result[$key]);
                }
            }

            //unset can make non-consequative associated array which gets converted to an object in JSON
            $result = array_values($result);

            return $result;
        }

        public function get_comment($comment_id, $bbcode=true) {
            global $app, $db, $user;

            // Group by required for count
            $st = $db->prepare('SELECT comments.comment_id as id, comments.comment, DATE_FORMAT(comments.time, \'%Y-%m-%dT%T+01:00\') as `time`, users.username, users.score, MD5(users.username) as `image`
                    FROM articles_comments comments
                    LEFT JOIN users
                    ON users.user_id = comments.user_id
                    WHERE comment_id = :comment_id
                    ORDER BY `time` DESC');
            $st->execute(array(':comment_id' => $comment_id));
            $result = $st->fetchAll();

            foreach($result as $comment) {
                $comment->comment = $app->parse($comment->comment, $bbcode);

                if ($comment->username === $user->username)
                    $comment->owner = true;
            }

            return $result;
        }

        public function add_comment($comment, $article_id, $parent_id=0) {
            global $app, $db, $user;

            // Check privilages
            if (!$user->loggedIn)
                return false;

            $st = $db->prepare('INSERT INTO articles_comments (`article_id`, `parent_id`, `user_id`, `comment`) VALUES (:article_id, :parent_id, :user_id, :body)');
            $result = $st->execute(array(':article_id' => $article_id,':parent_id' => $parent_id, ':user_id' => $user->uid, ':body' => $comment));
            if (!$result)
                return false;

            $comment_id = $db->lastInsertId();

            $notified = array($user->uid);

            // Update parents author
            if ($parent_id != 0) {
                $st = $db->prepare('SELECT user_id AS author FROM articles_comments WHERE comment_id = :parent_id LIMIT 1');
                $st->execute(array(':parent_id' => $parent_id));
                $result = $st->fetch();
                
                if ($result) {
                    if (!in_array($result->author, $notified)) {
                        array_push($notified, $result->author);
                        $app->notifications->add($result->author, 6, $user->uid, $comment_id);
                    }
                }
            }

            // Check for mentions
            preg_match_all("/(?:(?<=\s)|^)@(\w*[A-Za-z_]+\w*)/", $comment, $mentions);
            foreach($mentions[1] as $mention) {
                $st = $db->prepare('SELECT user_id FROM users WHERE username = :username LIMIT 1');
                $st->execute(array(':username' => $mention));
                $result = $st->fetch();
                
                if ($result) {
                    if (!in_array($result->user_id, $notified)) {
                        array_push($notified, $result->user_id);
                        $app->notifications->add($result->user_id, 7, $user->uid, $comment_id);
                    }
                }
            }


            return $this->get_comment($comment_id);
        }

        public function delete_comment($comment_id) {
            global $app, $db, $user;

            // Check privilages
            if (!$user->loggedIn)
                return false;

            $st = $db->prepare('UPDATE articles_comments SET deleted = :uid WHERE comment_id = :id AND user_id = :uid LIMIT 1');
            $st->execute(array(':id' => $comment_id, ':uid' => $user->uid));

            return ($st->rowCount() > 0);
        }

        public function favourite($article_id) {
            global $db, $user;

            // Check privilages
            if (!$user->loggedIn)
                return false;

            $st = $db->prepare('INSERT INTO articles_favourites (`article_id`, `user_id`) VALUES (:article_id, :uid)');
            $result = $st->execute(array(':article_id' => $article_id, ':uid' => $user->uid));
            return $result;
        }

        public function unfavourite($article_id) {
            global $db, $user;

            // Check privilages
            if (!$user->loggedIn)
                return false;

            $st = $db->prepare('DELETE FROM articles_favourites WHERE `article_id` = :article_id AND `user_id` = :uid LIMIT 1');
            $result = $st->execute(array(':article_id' => $article_id, ':uid' => $user->uid));
            return $result;
        }
    }
?>