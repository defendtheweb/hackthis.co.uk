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

            global $db;

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
    }
?>