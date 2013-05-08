<?php
    class articles {
        function __construct() {

        }

        public function get_articles($category_id) {
            global $db;

            // Group by required for count
            $st = $db->prepare('SELECT username, title, slug, body, submitted, updated, count(comments.comment_id) as comments
                    FROM articles
                    LEFT JOIN users
                    ON users.user_id = articles.user_id
                    LEFT JOIN articles_comments as comments
                    ON articles.article_id = comments.article_id
                    WHERE articles.category_id = :cat_id
                    GROUP BY articles.article_id
                    ORDER BY submitted DESC');
            $st->execute(array(':cat_id' => $category_id));
            $result = $st->fetchAll();

            return $result;
        }

        public function get_article($slug) {
            global $db;

            // Group by required for count
            $st = $db->prepare('SELECT username, title, slug, body, submitted, updated, count(comments.comment_id) as comments
                    FROM articles
                    LEFT JOIN users
                    ON users.user_id = articles.user_id
                    LEFT JOIN articles_comments as comments
                    ON articles.article_id = comments.article_id
                    WHERE articles.slug = :slug
                    GROUP BY articles.article_id
                    ORDER BY submitted DESC');
            $st->execute(array(':slug' => $slug));
            $result = $st->fetch();

            return $result;
        }
    }
?>