<?php
    class articles {
        function __construct() {

        }

        public function get_articles($category_id) {
            global $db;

            // Group by required for count
            $st = $db->prepare('SELECT a.article_id AS id, username, title, slug, body, submitted, updated, count(comments.comment_id) as comments
                    FROM articles a
                    LEFT JOIN users
                    ON users.user_id = a.user_id
                    LEFT JOIN articles_comments as comments
                    ON a.article_id = comments.article_id
                    WHERE a.category_id = :cat_id
                    GROUP BY a.article_id
                    ORDER BY submitted DESC');
            $st->execute(array(':cat_id' => $category_id));
            $result = $st->fetchAll();

            return $result;
        }

        public function get_article($slug) {
            global $db;

            // Group by required for count
            $st = $db->prepare('SELECT a.article_id AS id, username, title, slug, body, submitted, updated, count(comments.comment_id) as comments
                    FROM articles a
                    LEFT JOIN users
                    ON users.user_id = a.user_id
                    LEFT JOIN articles_comments as comments
                    ON a.article_id = comments.article_id
                    WHERE a.slug = :slug
                    GROUP BY a.article_id
                    ORDER BY submitted DESC');
            $st->execute(array(':slug' => $slug));
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
            $st = $db->prepare('SELECT comments.comment_id as id, comments.comment, DATE_FORMAT(comments.time, \'%Y-%m-%dT%T+01:00\') as `time`, users.username, users.score, MD5(users.username) as `image`
                    FROM articles_comments comments
                    LEFT JOIN users
                    ON users.user_id = comments.user_id
                    WHERE article_id = :article_id AND parent_id = :parent_id
                    ORDER BY `time` DESC');
            $st->execute(array(':article_id' => $article_id, ':parent_id' => $parent_id));
            $result = $st->fetchAll();

            foreach($result as $comment) {
                if ($bbcode)
                    $comment->comment = $app->bbcode->Parse($comment->comment);

                if ($comment->username === $user->username)
                    $comment->owner = true;

                $replies = $this->get_comments($article_id, $comment->id);
                if ($replies)
                    $comment->replies = $replies;
            }

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
                if ($bbcode)
                    $comment->comment = $app->bbcode->Parse($comment->comment);

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

            // Update parents author
            if ($parent_id != 0) {
                $st = $db->prepare('SELECT user_id AS author FROM articles_comments WHERE comment_id = :parent_id');
                $st->execute(array(':parent_id' => $parent_id));
                $result = $st->fetch();
                
                if ($result)
                    $app->notifications->add($result->author, 6, $user->uid, $comment_id);
            }

            return $this->get_comment($comment_id);
        }
    }
?>