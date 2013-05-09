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
    }
?>