<?php
    class articles {
        public static function getCategories($parent=null, $news=true) {
            global $db, $user;

            if ($parent == null) {
                $sql =  "SELECT category_id AS id, title, slug
                         FROM articles_categories
                         WHERE ISNULL(parent_id)";
                if (!$news)
                    $sql .= " AND category_id != 0 ";
                $sql .= "ORDER BY title ASC";
                $st = $db->prepare($sql);
                $st->execute(array(':parent'=>$parent));
                $result = $st->fetchAll();
            } else {
                $st = $db->prepare('SELECT category_id AS id, title, slug
                                    FROM articles_categories
                                    WHERE parent_id = :parent
                                    ORDER BY title ASC');
                $st->execute(array(':parent'=>$parent));
                $result = $st->fetchAll();
            }

            foreach($result as $res) {
                $children = articles::getCategories($res->id, $news);
                if ($children)
                    $res->children = $children;
            }

            return $result;
        }

        public static function printCategoryList($cat, $menu = false, $parent_str = "", $current_section = null, $current_cat = null) {
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

                echo "<li class='$c'><a href='/articles/{$cat->slug}'>";
                echo "{$cat->title}</a>\n";
                if (isset($cat->children) && count($cat->children)) {
                    echo "<ul>\n";
                    foreach($cat->children AS $child) {
                        articles::printCategoryList($child, $menu, $cat->title, $current_section, $current_cat);
                    }
                    echo "</ul>\n";
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
            if ($slug == 'me') {
                $category = (object) array('id' => 'me', 'title' => 'My Articles');
                return $category;
            }

            global $db;

            //Get category id
            $st = $db->prepare('SELECT category_id AS id, title, slug, parent_id AS parent FROM articles_categories
                                WHERE slug = :slug LIMIT 1');
            $st->execute(array(':slug' => $slug));
            $result = $st->fetch();

            if (!$result)
                return false;

            return $result;
        }

        public function getArticles($cat_id=null, $limit=2, $page=1) {
            global $db, $user;

            // Group by required for count
            $sql = 'SELECT a.article_id AS id, users.username, a.title, a.slug, a.body,
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
            if ($cat_id !== null && $cat_id !== 'me')
                $sql .= ' WHERE a.category_id = :cat_id ';
            else if ($cat_id == 'me') {
                $cat_id = $user->uid;
                $sql .= ' WHERE a.user_id = :cat_id ';
            } else {
                $cat_id = 0;
                $sql .= ' WHERE a.category_id != :cat_id ';
            }

            $sql .= 'GROUP BY a.article_id
                    ORDER BY submitted DESC
                    LIMIT :limit1, :limit2';
            $st = $db->prepare($sql);
            $st->bindValue(':cat_id', $cat_id);
            $st->bindValue(':uid', $user->uid);
            $st->bindValue(':limit1', ($page-1)*$limit, PDO::PARAM_INT);
            $st->bindValue(':limit2', $limit, PDO::PARAM_INT);
            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }

        public function getArticle($slug, $news=false) {
            global $db, $user;

            // Group by required for count
            $st = $db->prepare('SELECT a.article_id AS id, users.username, a.title, a.slug, a.body,
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
            $st->execute(array(':slug' => $slug, ':uid' => $user->uid));
            $result = $st->fetch();

            if (!$result || ($news !== 'all' && ($news && $result->cat_id != 0) || (!$news && $result->cat_id == 0)))
                return false;

            if (!$news) {
                $st = $db->prepare('SELECT a.title, CONCAT(IF(a.category_id = 0, "/news/", "/articles/"), a.slug) AS uri
                                    FROM articles a
                                    WHERE a.category_id = :cat_id AND submitted < :sub
                                    ORDER BY submitted DESC
                                    LIMIT 1');
                $st->execute(array(':cat_id' => $result->cat_id, ':sub' => $result->submitted));
                $result->next = $st->fetch();
            }

            //increment read count
            $st = $db->prepare('UPDATE articles SET views = views + 1 WHERE article_id = :aid');
            $st->execute(array(':aid' => $result->id));

            return $result;
        }

        public function updateArticle($id, $changes, $updated=true) {
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

        public function getHotArticles($limit=5) {
            global $app, $db, $user;

            $st = $db->prepare('SELECT a.title, SUM(IFNULL(favourites.count*10,0)+IFNULL(comments.count,0)) as total,
                                CONCAT(IF(a.category_id = 0, "/news/", "/articles/"), a.slug) AS slug
                                FROM articles a
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

            return $result;
        }


        public function getComments($article_id, $parent_id=0, $bbcode=true) {
            global $app, $db, $user;

            // Group by required for count
            $st = $db->prepare('SELECT comments.comment_id as id, comments.comment, comments.deleted,
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
                        $comment->comment = $app->parse($comment->comment);

                    if ($comment->username === $user->username)
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
                    $comment->image = profile::getImg($comment->image, 28, $gravatar);
                } else
                    $comment->image = profile::getImg(null, 28);

                unset($comment->gravatar);
            }

            //unset can make non-consequative associated array which gets converted to an object in JSON
            $result = array_values($result);

            return $result;
        }

        public function getComment($comment_id, $bbcode=true) {
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

        public function addComment($comment, $article_id, $parent_id=0) {
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
                $st = $db->prepare('SELECT comments.user_id AS author FROM articles_comments comments
                                   INNER JOIN users
                                   ON users.user_id = comments.user_id
                                   WHERE comments.comment_id = :parent_id LIMIT 1');
                $st->execute(array(':parent_id' => $parent_id));
                $result = $st->fetch();
                
                if ($result) {
                    if (!in_array($result->author, $notified)) {
                        array_push($notified, $result->author);
                        $app->notifications->add($result->author, 'comment_reply', $user->uid, $comment_id);
                    }
                }
            }

            // Check for mentions
            preg_match_all("/(?:(?<=\s)|^)@(\w*[0-9A-Za-z_.-]+\w*)/", $comment, $mentions);
            foreach($mentions[1] as $mention) {
                $st = $db->prepare('SELECT user_id FROM users WHERE username = :username LIMIT 1');
                $st->execute(array(':username' => $mention));
                $result = $st->fetch();
                
                if ($result) {
                    if (!in_array($result->user_id, $notified)) {
                        array_push($notified, $result->user_id);
                        $app->notifications->add($result->user_id, 'comment_mention', $user->uid, $comment_id);
                        $app->feed->add($result->user_id, 'comment_mention', $comment_id);
                    }
                }
            }


            return $this->getComment($comment_id);
        }

        public function deleteComment($comment_id) {
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

        public function setupTOC($body){
            //Add href tags
            $pattern = '/\<h(1|2)\>(.+?)\<\/h(1|2)\>/';
            function process($matches) {
                global $app;
                $slug = $app->utils->generateSlug($matches[2]);
                
                $match = $matches[0];
                $match = substr($matches[0],0,3) . " id='$slug'" . substr($matches[0],3);
                return $match;
            }

            return preg_replace_callback($pattern, 'process', $body);
        }

        public function getTOC($body) {
            $pattern = '/\<h(1|2)\>(.+?)\<\/h(1|2)\>/';
            preg_match_all($pattern, $body, $matches);
            return $matches;
        }
    }
?>