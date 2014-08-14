<?php
    class admin {

        public function __construct($app) {
            $this->app = $app;
        }

        public function getUnreadTickets() {
            $sql = "SELECT `mod_contact`.*, COUNT(a.message_id) AS `replies` FROM `mod_contact`
                    LEFT JOIN `mod_contact` a
                    ON a.parent_id = `mod_contact`.message_id
                    WHERE `mod_contact`.`parent_id` IS NULL AND (`mod_contact`.flag IS NULL OR `mod_contact`.flag < 1)
                    GROUP BY `mod_contact`.`message_id`
                    HAVING `replies` < 1";
            $st = $this->app->db->prepare($sql);
            $st->execute();
            $count = $st->rowCount();

            return $count;
        }

        public function getLatestForumFlags() {
            $sql = "SELECT MAX(users_forum.flag) AS `latest`, COUNT(users_forum.post_id) AS `flags`, users.username, forum_threads.thread_id, forum_threads.slug, forum_threads.title, forum_posts.post_id, forum_posts.body
                    FROM users_forum
                    INNER JOIN forum_posts
                    ON users_forum.post_id = forum_posts.post_id
                    INNER JOIN forum_threads
                    ON forum_posts.thread_id = forum_threads.thread_id
                    INNER JOIN users
                    ON users.user_id = forum_posts.author
                    WHERE flag > 0 AND forum_posts.deleted = 0 AND forum_threads.deleted = 0
                    GROUP BY users_forum.post_id
                    ORDER BY `flags` DESC, `latest` DESC
                    LIMIT 15";
            $st = $this->app->db->prepare($sql);
            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }

        public function getLatestArticleSubmissions() {
            $sql = "SELECT articles_draft.article_id, articles_draft.title, articles_draft.time, articles_categories.title AS `category`, users.username
                    FROM articles_draft
                    INNER JOIN articles_categories
                    ON articles_categories.category_id = articles_draft.category_id
                    INNER JOIN users
                    ON users.user_id = articles_draft.user_id
                    WHERE articles_draft.note IS NULL
                    ORDER BY `time` DESC
                    LIMIT 5";
            $st = $this->app->db->prepare($sql);
            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }

    }
?>