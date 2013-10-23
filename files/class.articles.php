<?php
    class articles {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function getCategories($parent=null, $news=true) {
            if ($parent == null) {
                $sql =  "SELECT category_id AS id, title, slug
                         FROM articles_categories
                         WHERE ISNULL(parent_id)";
                if (!$news)
                    $sql .= " AND category_id != 0 ";
                $sql .= "ORDER BY title ASC";
                $st = $this->app->db->prepare($sql);
                $st->execute(array(':parent'=>$parent));
                $result = $st->fetchAll();
            } else {
                $st = $this->app->db->prepare('SELECT category_id AS id, title, slug
                                    FROM articles_categories
                                    WHERE parent_id = :parent
                                    ORDER BY title ASC');
                $st->execute(array(':parent'=>$parent));
                $result = $st->fetchAll();
            }

            foreach($result as $res) {
                $children = $this->getCategories($res->id, $news);
                if ($children)
                    $res->children = $children;
            }

            return $result;
        }

        public function printCategoryList($cat, $menu = false, $parent_str = "", $current_section = null, $current_cat = null) {
            if ($menu) {
                if ($parent_str) {
                    $cat->title = str_ireplace($parent_str, '', $cat->title);
                }
                $cat->title = ucfirst(trim($cat->title));

                $c = '';
                if ($current_section === $cat->id)
                    $c = 'active ';
                if ($current_cat === $cat->id)
                    $c = 'current active ';
                if (isset($cat->children) && count($cat->children))
                    $c .= 'parent';

                echo "                                        <li class='$c'><a href='/articles/{$cat->slug}'>";
                echo "{$cat->title}</a>";
                if (isset($cat->children) && count($cat->children)) {
                    echo "\n                                        <ul>\n";
                    foreach($cat->children AS $child) {
                        $this->printCategoryList($child, $menu, $cat->title, $current_section, $current_cat);
                    }
                    echo "                                        </ul>\n                                        ";
                }
                echo "</li>\n";
            } else {
                echo "<li data-value='{$cat->id}'>{$cat->title}\n";
                if (isset($cat->children) && count($cat->children)) {
                    echo "<ul>\n";
                    foreach($cat->children AS $child) {
                        articles::printCategoryList($child);
                    }
                    echo "</ul>\n";
                }
                echo "</li>\n";
            }
        }

        public function getCategory($slug) { 
            //Get category id
            $st = $this->app->db->prepare('SELECT category_id AS id, title, slug, parent_id AS parent FROM articles_categories
                                WHERE slug = :slug LIMIT 1');
            $st->execute(array(':slug' => $slug));
            $result = $st->fetch();

            if (!$result)
                return false;

            return $result;
        }

        public function getArticles($cat_id=null, $limit=2, $page=1) {
            // Group by required for count
            $sql = 'SELECT SQL_CALC_FOUND_ROWS a.article_id AS id, users.username, a.title, a.slug, a.body, a.thumbnail,
                        submitted, updated, a.category_id AS cat_id, categories.title AS cat_title, categories.slug AS cat_slug,
                        CONCAT(IF(a.category_id = 0, "/news/", "/articles/"), a.slug) AS uri,
                        COALESCE(comments.count, 0) AS comments,
                        COALESCE(favourites.count, 0) AS favourites,
                        COALESCE(user_favourites.count, 0) AS favourited
                    FROM articles a
                    LEFT JOIN articles_categories categories
                    ON a.category_id = categories.category_id
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
                    ON a.article_id = user_favourites.article_id';
            if ($cat_id !== null)
                $sql .= ' WHERE a.category_id = :cat_id ';
            else {
                $cat_id = 0;
                $sql .= ' WHERE a.category_id != :cat_id ';
            }

            $sql .= 'GROUP BY a.article_id
                    ORDER BY submitted DESC';

            if ($limit !== null)
                $sql .= ' LIMIT :limit1, :limit2';

            $st = $this->app->db->prepare($sql);
            $st->bindValue(':cat_id', $cat_id);
            $st->bindValue(':uid', $this->app->user->uid);

            if ($limit !== null) {
                $st->bindValue(':limit1', ($page-1)*$limit, PDO::PARAM_INT);
                $st->bindValue(':limit2', $limit, PDO::PARAM_INT);
            }
            $st->execute();
            $result = $st->fetchAll();

            // Get total rows
            $st = $this->app->db->prepare('SELECT FOUND_ROWS() AS `count`');
            $st->execute();
            $count = $st->fetch();
            $count = $count->count;

            foreach ($result AS $res) {
                //is this a video piece?
                $res->body = preg_replace_callback("/\[youtube\]([a-zA-Z0-9_-]*)\[\/youtube\]/", function($match) use ($res) {
                    $res->video = $match[1];
                }, $res->body);
            }

            return array('articles'=>$result, 'total'=>$count, 'page'=>$page);
        }

        public function getArticle($slug, $news=false) {
            // Group by required for count
            $st = $this->app->db->prepare('SELECT a.article_id AS id, users.username, a.title, a.slug, a.body, a.thumbnail,
                                    submitted, updated, a.category_id AS cat_id, categories.title AS cat_title, categories.slug AS cat_slug,
                                    categories.parent_id AS parent,
                                    CONCAT(IF(a.category_id = 0, "/news/", "/articles/"), a.slug) AS uri,
                                    COALESCE(comments.count, 0) AS comments,
                                    COALESCE(favourites.count, 0) AS favourites,
                                    COALESCE(user_favourites.count, 0) AS favourited
                                FROM articles a
                                LEFT JOIN articles_categories categories
                                ON a.category_id = categories.category_id
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
            $st->execute(array(':slug' => $slug, ':uid' => $this->app->user->uid));
            $result = $st->fetch();

            if (!$result || ($news !== 'all' && ($news && $result->cat_id != 0) || (!$news && $result->cat_id == 0)))
                return false;

            if (!$news) {
                $st = $this->app->db->prepare('SELECT a.title, CONCAT(IF(a.category_id = 0, "/news/", "/articles/"), a.slug) AS uri
                                    FROM articles a
                                    WHERE a.category_id = :cat_id AND submitted < :sub
                                    ORDER BY submitted DESC
                                    LIMIT 1');
                $st->execute(array(':cat_id' => $result->cat_id, ':sub' => $result->submitted));
                $result->next = $st->fetch();
            }

            //increment read count
            $st = $this->app->db->prepare('UPDATE articles SET views = views + 1 WHERE article_id = :aid');
            $st->execute(array(':aid' => $result->id));

            return $result;
        }

        public function updateArticle($id, $changes, $updated=true, $draft=false) {
            if (!is_array($changes)) return false;

            //Check privilages
            if (!$this->app->user->loggedIn)
                return false;

            // Get field list
            $fields = '';
            $values = array();

            foreach ($changes as $field=>$change) {
                $fields .= "`$field` = ?,";
                $values[] = $change;
            }

            $fields = rtrim($fields, ',');

            if ($draft)
                $query  = "UPDATE articles_draft SET note = NULL, ".$fields;
            else
                $query  = "UPDATE articles SET ".$fields;

            if (!$draft && $updated)
                $query .= ",updated=NOW()";
            $query .= " WHERE article_id=?";
            $values[] = $id;

            if ($draft || !$this->app->user->admin_pub_priv) {
                $query .= " AND user_id = ?";
                $values[] = $this->app->user->uid;
            }

            $st = $this->app->db->prepare($query);
            $res = $st->execute($values); 

            return $res;
        }

        public function getHotArticles($limit=5) {
            $st = $this->app->db->prepare('SELECT a.title, SUM(IFNULL(favourites.count*10,0)+IFNULL(comments.count,0)) as total,
                                CONCAT(IF(a.category_id = 0, "/news/", "/articles/"), a.slug) AS slug,
                                a.body, a.thumbnail, cat.title AS `category`
                                FROM articles a
                                LEFT JOIN articles_categories cat
                                ON cat.category_id = a.category_id
                                LEFT JOIN 
                                    ( SELECT article_id, COUNT(*) AS count FROM articles_comments WHERE deleted IS NULL GROUP BY article_id) comments
                                ON a.article_id = comments.article_id
                                LEFT JOIN 
                                    ( SELECT article_id, COUNT(*) AS count FROM articles_favourites GROUP BY article_id) favourites
                                ON a.article_id = favourites.article_id
                                WHERE a.category_id != 0
                                GROUP BY a.article_id
                                ORDER BY total DESC, submitted DESC
                                LIMIT 5');
            $st->execute();
            $result = $st->fetchAll();

            foreach ($result AS $res) {
                //is this a video piece?
                $res->body = preg_replace_callback("/\[youtube\]([a-zA-Z0-9_-]*)\[\/youtube\]/", function($match) use ($res) {
                    $res->video = $match[1];
                }, $res->body);
            }

            return $result;
        }


        /*
         * USERS ARTICLES
         */
        public function getMyArticles($approved=true, $limit=2, $page=1, $admin=false) {
            // Group by required for count
            if ($approved) {
                $sql = 'SELECT SQL_CALC_FOUND_ROWS a.article_id AS id, users.username, a.title, a.slug,
                            submitted, updated, a.category_id AS cat_id, categories.title AS cat_title,
                            CONCAT(IF(a.category_id = 0, "/news/", "/articles/"), a.slug) AS uri,
                            COALESCE(comments.count, 0) AS comments,
                            COALESCE(favourites.count, 0) AS favourites,
                            SUM(IFNULL(favourites.count*10,0)+IFNULL(comments.count,0)) as total
                        FROM articles a
                        LEFT JOIN articles_categories categories
                        ON a.category_id = categories.category_id
                        LEFT JOIN users
                        ON users.user_id = a.user_id
                        LEFT JOIN 
                            ( SELECT article_id, COUNT(*) AS count FROM articles_comments WHERE deleted IS NULL GROUP BY article_id) comments
                        ON a.article_id = comments.article_id
                        LEFT JOIN 
                            ( SELECT article_id, COUNT(*) AS count FROM articles_favourites GROUP BY article_id) favourites
                        ON a.article_id = favourites.article_id
                        WHERE a.user_id = :uid
                        GROUP BY a.article_id
                        ORDER BY total DESC, submitted DESC
                        LIMIT :limit1, :limit2';
            } else {
                $sql = 'SELECT SQL_CALC_FOUND_ROWS a.article_id AS id, a.title, a.note,
                            `time`, categories.title AS cat_title
                        FROM articles_draft a
                        LEFT JOIN articles_categories categories
                        ON a.category_id = categories.category_id';
                if (!$admin)
                    $sql .= ' WHERE a.user_id = :uid';
                $sql .= ' ORDER BY `time` DESC
                        LIMIT :limit1, :limit2';           
            }

            $st = $this->app->db->prepare($sql);
            if (!$admin)
                $st->bindValue(':uid', $this->app->user->uid);
            $st->bindValue(':limit1', ($page-1)*$limit, PDO::PARAM_INT);
            $st->bindValue(':limit2', $limit, PDO::PARAM_INT);
            $st->execute();
            $result = $st->fetchAll();

            // Get total rows
            $st = $this->app->db->prepare('SELECT FOUND_ROWS() AS `count`');
            $st->execute();
            $count = $st->fetch();
            $count = $count->count;

            return array('articles'=>$result, 'total'=>$count, 'page'=>$page);
        }

        public function getMyArticle($id) {
            // Group by required for count
            $sql = 'SELECT a.user_id, a.article_id AS id, a.title, a.body, a.note, a.time AS `submitted`,
                        a.category_id AS cat_id, categories.title AS cat_title, categories.slug AS cat_slug,
                        categories.parent_id AS parent, users.username
                    FROM articles_draft a
                    LEFT JOIN articles_categories categories
                    ON a.category_id = categories.category_id
                    LEFT JOIN users
                    ON users.user_id = a.user_id
                    WHERE a.article_id = :id';

            if (!$this->app->user->admin_pub_priv)
                $sql .= ' AND a.user_id = :uid';

            $sql .= ' LIMIT 1';
            $st = $this->app->db->prepare($sql);
            if (!$this->app->user->admin_pub_priv)
                $st->execute(array(':id' => $id, ':uid' => $this->app->user->uid));
            else
                $st->execute(array(':id' => $id));
            $result = $st->fetch();

            if (!$result)
                return false;

            return $result;
        }

        public function submitArticle($title, $body, $category) {
            if (!$title || !$body)
                return false;

            // Group by required for count
            $st = $this->app->db->prepare('INSERT INTO articles_draft (`user_id`,`title`,`category_id`,`body`)
                                VALUES (:uid,:title,:cat_id,:body)');
            $result = $st->execute(array(':uid' => $this->app->user->uid, ':title' => $title, ':cat_id' => $category, ':body' => $body));

            if (!$result)
                return false;

            return $this->app->db->lastInsertId();          
        }

        public function acceptArticle($article_id) {
            // Find article
            $sql = 'SELECT a.user_id, a.article_id AS id, a.category_id, a.title, a.body
                    FROM articles_draft a
                    WHERE a.article_id = :id';
            $st = $this->app->db->prepare($sql);
            $st->execute(array(':id' => $article_id));
            $result = $st->fetch();
            if (!$result)
                return false;

            if ($result->user_id === $this->app->user->uid && !$this->app->user->admin_site_priv)
                return "You cannot accept your own articles";


            $result->slug = $this->app->utils->generateSlug($result->title);

            $this->app->db->beginTransaction();
            try {
                // Insert article
                $st = $this->app->db->prepare('INSERT INTO articles (`user_id`,`title`,`slug`,`category_id`,`body`)
                                    VALUES (:uid,:title,:slug,:cat_id,:body)');
                $st->execute(array(':uid' => $result->user_id, ':title' => $result->title, ':slug' => $result->slug, ':cat_id' => $result->category_id, ':body' => $result->body));

                // Remove draft
                $st = $this->app->db->prepare('DELETE FROM `articles_draft`
                                               WHERE article_id = :id
                                               LIMIT 1');
                $st->execute(array(':id' => $result->id));

                $this->app->db->commit();
            } catch(PDOException $e) {
                $this->app->db->rollBack();
                return "Something went horribly wrong";
            }

            // Add to Feed
            if ($result->category_id == 0)
                $slug = '/news/' . $result->slug;
            else
                $slug = '/articles/' . $result->slug;
            $this->app->feed->call($result->user_id, 'article', $result->slug, $slug);

            return true;
        }


        /*
         * COMMENTS
         */

        public function getComments($article_id, $parent_id=0, $bbcode=true) {
            // Group by required for count
            $st = $this->app->db->prepare('SELECT comments.comment_id as id, comments.comment, comments.deleted,
                               DATE_FORMAT(comments.time, \'%Y-%m-%dT%T+01:00\') as `time`,
                               coalesce(users.username, 0) as username, users_profile.gravatar,
                               IF (users_profile.gravatar = 1, users.email , users_profile.img) as `image`
                    FROM articles_comments comments
                    LEFT JOIN users
                    ON users.user_id = comments.user_id
                    LEFT JOIN users_profile
                    ON users_profile.user_id = users.user_id
                    WHERE article_id = :article_id AND parent_id = :parent_id
                    ORDER BY `time` DESC');
            $st->execute(array(':article_id' => $article_id, ':parent_id' => $parent_id));
            $result = $st->fetchAll();

            foreach($result as $key=>$comment) {
                if (!$comment->deleted) {
                    if ($bbcode)
                        $comment->comment = $this->app->parse($comment->comment);

                    if ($comment->username === $this->app->user->username)
                        $comment->owner = true;
                } else {
                    unset($comment->username);
                    unset($comment->comment);
                    unset($comment->image);
                    unset($comment->score);
                }

                $replies = $this->getComments($article_id, $comment->id);
                if ($replies) {
                    $comment->replies = $replies;
                    unset($comment->deleted); 
                } else if ($comment->deleted) {
                    //array_splice($result, $key, 1);
                    unset($result[$key]);
                }
                unset($comment->deleted);

                // Set image
                if (isset($comment->image)) {
                    $gravatar = isset($comment->gravatar) && $comment->gravatar == 1;
                    $comment->image = profile::getImg($comment->image, 40, $gravatar);
                } else
                    $comment->image = profile::getImg(null, 40);

                unset($comment->gravatar);
            }

            //unset can make non-consequative associated array which gets converted to an object in JSON
            $result = array_values($result);

            return $result;
        }

        public function getComment($comment_id, $bbcode=true) {
            // Group by required for count
            $st = $this->app->db->prepare('SELECT comments.comment_id as id, comments.comment, DATE_FORMAT(comments.time, \'%Y-%m-%dT%T+01:00\') as `time`, users.username, users.score, users_profile.gravatar,
                    IF (users_profile.gravatar = 1, users.email , users_profile.img) as `image`
                    FROM articles_comments comments
                    LEFT JOIN users
                    ON users.user_id = comments.user_id
                    LEFT JOIN users_profile
                    ON users_profile.user_id = users.user_id
                    WHERE comment_id = :comment_id
                    ORDER BY `time` DESC');
            $st->execute(array(':comment_id' => $comment_id));
            $result = $st->fetchAll();
 
            foreach($result as $comment) {
                $comment->comment = $this->app->parse($comment->comment, $bbcode);

                if ($comment->username === $this->app->user->username)
                    $comment->owner = true;

                // Set image
                if (isset($comment->image)) {
                    $gravatar = isset($comment->gravatar) && $comment->gravatar == 1;
                    $comment->image = profile::getImg($comment->image, 40, $gravatar);
                } else
                    $comment->image = profile::getImg(null, 40);
            }

            return $result;
        }

        public function addComment($comment, $article_id, $parent_id=0) {
            // Check privilages
            if (!$this->app->user->loggedIn)
                return false;

            $st = $this->app->db->prepare('INSERT INTO articles_comments (`article_id`, `parent_id`, `user_id`, `comment`) VALUES (:article_id, :parent_id, :user_id, :body)');
            $result = $st->execute(array(':article_id' => $article_id,':parent_id' => $parent_id, ':user_id' => $this->app->user->uid, ':body' => $comment));
            if (!$result)
                return false;

            $comment_id = $this->app->db->lastInsertId();

            $notified = array($this->app->user->uid);

            // Update parents author
            if ($parent_id != 0) {
                $st = $this->app->db->prepare('SELECT comments.user_id AS author FROM articles_comments comments
                                   INNER JOIN users
                                   ON users.user_id = comments.user_id
                                   WHERE comments.comment_id = :parent_id LIMIT 1');
                $st->execute(array(':parent_id' => $parent_id));
                $result = $st->fetch();
                
                if ($result) {
                    if (!in_array($result->author, $notified)) {
                        array_push($notified, $result->author);
                        $this->app->notifications->add($result->author, 'comment_reply', $this->app->user->uid, $comment_id);
                    }
                }
            }

            // Check for mentions
            preg_match_all("/(?:(?<=\s)|^)@(\w*[0-9A-Za-z_.-]+\w*)/", $comment, $mentions);
            foreach($mentions[1] as $mention) {
                $st = $this->app->db->prepare('SELECT user_id FROM users WHERE username = :username LIMIT 1');
                $st->execute(array(':username' => $mention));
                $result = $st->fetch();
                
                if ($result) {
                    if (!in_array($result->user_id, $notified)) {
                        array_push($notified, $result->user_id);
                        $this->app->notifications->add($result->user_id, 'comment_mention', $this->app->user->uid, $comment_id);
                    }
                }
            }


            // Add to feed
            $st = $this->app->db->prepare('SELECT articles.title, CONCAT(IF(articles.category_id = 0, "/news/", "/articles/"), articles.slug) AS uri
                                    FROM articles_comments
                                    INNER JOIN articles
                                    ON articles.article_id = articles_comments.article_id
                                    WHERE articles_comments.comment_id = :comment_id LIMIT 1');
            $st->execute(array(':comment_id' => $comment_id));
            $article = $st->fetch();
            $this->app->feed->call($this->app->user->username, 'comment', $article->title, $article->uri.'#comment-'.$comment_id);


            return $this->getComment($comment_id);
        }

        public function deleteComment($comment_id) {
            // Check privilages
            if (!$this->app->user->loggedIn)
                return false;

            $st = $this->app->db->prepare('UPDATE articles_comments SET deleted = :uid WHERE comment_id = :id AND user_id = :uid LIMIT 1');
            $st->execute(array(':id' => $comment_id, ':uid' => $this->app->user->uid));

            return ($st->rowCount() > 0);
        }

        public function favourite($article_id) {
            // Check privilages
            if (!$this->app->user->loggedIn)
                return false;

            $st = $this->app->db->prepare('INSERT INTO articles_favourites (`article_id`, `user_id`) VALUES (:article_id, :uid)');
            $result = $st->execute(array(':article_id' => $article_id, ':uid' => $this->app->user->uid));

            // Add to feed
            $st = $this->app->db->prepare('SELECT articles.title, CONCAT(IF(articles.category_id = 0, "/news/", "/articles/"), articles.slug) AS uri
                                    FROM articles
                                    WHERE article_id = :article_id
                                    LIMIT 1');
            $st->execute(array(':article_id' => $article_id));
            $article = $st->fetch();
            $this->app->feed->call($this->app->user->username, 'favourite', $article->title, $article->uri);

            return $result;
        }

        public function unfavourite($article_id) {
            // Check privilages
            if (!$this->app->user->loggedIn)
                return false;

            $st = $this->app->db->prepare('DELETE FROM articles_favourites WHERE `article_id` = :article_id AND `user_id` = :uid LIMIT 1');
            $result = $st->execute(array(':article_id' => $article_id, ':uid' => $this->app->user->uid));
            return $result;
        }

        public function setupTOC($body){
            //Add href tags
            $pattern = '/\<h(1|2)\>(.+?)\<\/h(1|2)\>/';
            return preg_replace_callback($pattern, array($this, 'setupTOCProcess'), $body);
        }

        public function setupTOCProcess($matches) {
            $slug = $this->app->utils->generateSlug($matches[2]);
            
            $match = $matches[0];
            $match = substr($matches[0],0,3) . " id='$slug'" . substr($matches[0],3);
            return $match;
        }

        public function getTOC($body) {
            $pattern = '/\<h(1|2)\>(.+?)\<\/h(1|2)\>/';
            preg_match_all($pattern, $body, $matches);
            return $matches;
        }




        public function getContributors() {
            $st = $this->app->db->prepare('SELECT COUNT(article_id) AS `count`, users.username, users.user_id FROM articles
                                           INNER JOIN users
                                           ON users.user_id = articles.user_id
                                           GROUP BY articles.user_id
                                           ORDER BY `count` DESC');
            $st->execute();
            $result = $st->fetchAll();

            foreach ($result AS $res) {
                $st = $this->app->db->prepare('SELECT title, slug, body, CONCAT(IF(articles.category_id = 0, "/news/", "/articles/"), articles.slug) AS uri
                                               FROM articles
                                               WHERE user_id = :uid
                                               ORDER BY submitted DESC');
                $st->execute(array(':uid' => $res->user_id));
                $res->articles = $st->fetchAll();
            }

            return $result;
        }
    }
?>