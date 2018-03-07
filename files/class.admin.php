<?php
    class admin {

        private $forum_reasons_posts = array('This post is not relevant to the thread. If you need help or want to post something that has not been discussed then please create a new thread. If you want to ask a user a specific question unrelated to the current topic please use the PM system.',
                                            'This post is primarily an answer or has far more detail than is necessary to be helpful.',
                                            'This post is primarily an advertisement with no disclosure. It is not useful or relevant, but promotional. If you are interested in advertising on our platform please contact us.',
                                            'This post has severe formatting or content problems. Please be more considered when posting in future.',
                                            'The communities first and only language is English. If you are feel you need to talk in another language please find another member who can speak that language and contact them directly via PM.',
                                            'This post refers to a post that longer exists and is being removed just to tidy things up. Don\'t worry about this report.');
        private $forum_reasons_threads = array('This thread is not relevant to this site. If you want to ask a user a specific question unrelated to the site topic please use the PM system.',
                                            'This thread is primarily an answer or has far more detail than is necessary to be helpful.',
                                            'This thread is primarily an advertisement with no disclosure. It is not useful or relevant, but promotional. If you are interested in advertising on our platform please contact us.',
                                            'This thread has severe formatting or content problems. Please be more considered when posting in future.',
                                            'The communities first and only language is English. If you are feel you need to talk in another language please find another member who can speak that language and contact them directly via PM.',
                                            'This thread has been removed to tidy things up. Don\'t worry about this report.');

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

        public function getLatestForumFlags($limit = true) {
            $sql = "SELECT MAX(forum_posts_flags.time) AS `latest`, COUNT(forum_posts_flags.post_id) AS `flags`, forum_posts_flags.reason, users.username, forum_threads.thread_id, forum_threads.slug, forum_threads.title, forum_posts.post_id, forum_posts.body
                    FROM forum_posts_flags
                    INNER JOIN forum_posts
                    ON forum_posts_flags.post_id = forum_posts.post_id
                    INNER JOIN forum_threads
                    ON forum_posts.thread_id = forum_threads.thread_id
                    INNER JOIN users
                    ON users.user_id = forum_posts.author
                    WHERE forum_posts.deleted = 0 AND forum_threads.deleted = 0
                    GROUP BY forum_posts_flags.post_id
                    ORDER BY `flags` DESC, `latest` DESC";
            if ($limit) $sql .= " LIMIT 5";

            $st = $this->app->db->prepare($sql);
            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }

        public function getLatestArticleSubmissions($limit = true) {
            $sql = "SELECT articles_draft.article_id, articles_draft.title, articles_draft.time, articles_categories.title AS `category`, users.username
                    FROM articles_draft
                    INNER JOIN articles_categories
                    ON articles_categories.category_id = articles_draft.category_id
                    INNER JOIN users
                    ON users.user_id = articles_draft.user_id
                    WHERE articles_draft.note IS NULL
                    ORDER BY `time` DESC";
            if ($limit) $sql .= " LIMIT 5";
                    
            $st = $this->app->db->prepare($sql);
            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }




        // Forum
        public function removeForumThread($thread_id, $reason, $extra) {
            // Delete post
            $deleted = $this->app->forum->deleteThread($thread_id);
            if (!$deleted) {
                return false;
            }

            if (isset($this->forum_reasons_threads[(int)$reason-1])) {
                $reason = $this->forum_reasons_threads[(int)$reason-1];
            } else {
                $reason = $extra;
            }

            // Add to reports
            $st = $this->app->db->prepare("INSERT INTO mod_reports (`user_id`, `type`, `about`, `subject`, `body`)
                    VALUES (:uid, 'forum_thread', :pid, 'Deleted thread', :body)");
            $status = $st->execute(array(':pid'=>$thread_id, ':uid'=>$this->app->user->uid, ':body'=>$reason));

            $id = $this->app->db->lastInsertId();

            // Notify user
            $st = $this->app->db->prepare("SELECT owner FROM forum_threads WHERE thread_id = :tid");
            $st->execute(array(':tid'=>$thread_id));
            $thread = $st->fetch();
            $this->app->notifications->add($thread->owner, 'mod_report', $this->app->user->uid, $id);

            // Remove flags and award users who flagged
            $st = $this->app->db->prepare("SELECT post_id FROM forum_posts WHERE thread_id = :tid ORDER BY posted ASC LIMIT 1");
            $st->execute(array(':tid'=>$thread->id));
            $res = $st->fetch();
            $this->app->forum->removeFlags($res->post_id, true);

            return true;
        }

        public function removeForumPost($post_id, $reason, $extra) {
            // Delete post
            $deleted = $this->app->forum->deletePost($post_id);
            if (!$deleted) {
                return false;
            }

            if (isset($this->forum_reasons_posts[(int)$reason-1])) {
                $reason = $this->forum_reasons_posts[(int)$reason-1];
            } else {
                $reason = $extra;
            }

            // Add to reports
            $st = $this->app->db->prepare("INSERT INTO mod_reports (`user_id`, `type`, `about`, `subject`, `body`)
                    VALUES (:uid, 'forum', :pid, 'Deleted post', :body)");
            $status = $st->execute(array(':pid'=>$post_id, ':uid'=>$this->app->user->uid, ':body'=>$reason));

            $id = $this->app->db->lastInsertId();

            // Notify user
            $st = $this->app->db->prepare("SELECT author FROM forum_posts WHERE post_id = :pid");
            $st->execute(array(':pid'=>$post_id));
            $post = $st->fetch();
            $this->app->notifications->add($post->author, 'mod_report', $this->app->user->uid, $id);

            // Remove flags and award users who flagged
            // $st = $app->db->prepare("SELECT post_id FROM forum_posts WHERE thread_id = :tid ORDER BY posted ASC LIMIT 1");
            // $st->execute(array(':tid'=>$thread->id));
            // $res = $st->fetch();
            // $app->forum->removeFlags($res->post_id, true);

            // Reward anyone who flagged post
            $this->app->forum->removeFlags($post_id, true);

            return true;
        }
    }
?>