<?php
    class forum {
        private $app;
        private $error;
        private $banned = array('ccv', 'sell', 'passport', 'visa');

        public function __construct($app) {
            $this->app = $app;
        }

        public function getLatest($limit = 3) {
            $sql = "SELECT threads.title, threads.slug, threads.closed,
                users.username AS author, posts.count-1 as `count`, latest.posted AS latest, latest.username AS latest_author, IF (forum_users.viewed >= latest, 1, 0) AS `viewed`, forum_users.watching
                FROM forum_threads threads
                LEFT JOIN users
                ON users.user_id = threads.owner
                LEFT JOIN (SELECT thread_id, max(posted) AS `latest`, count(*) AS `count` FROM forum_posts WHERE deleted = 0 GROUP BY thread_id) posts
                ON posts.thread_id = threads.thread_id
                LEFT JOIN (SELECT thread_id, users.username, posted FROM forum_posts LEFT JOIN users ON users.user_id = author WHERE deleted = 0 ORDER BY posted DESC LIMIT 1) latest
                ON latest.thread_id = threads.thread_id AND latest.posted = posts.latest
                LEFT JOIN forum_users
                ON threads.thread_id = forum_users.thread_id AND forum_users.user_id = :uid

                WHERE threads.deleted = 0
                ORDER BY latest DESC
                LIMIT :limit";

            $st = $this->app->db->prepare($sql);
            $st->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $st->bindValue(':uid', $this->app->user->uid);
            $st->execute();
            $result = $st->fetchAll();

            foreach ($result AS &$res) {
                $res->title = $this->app->parse($res->title, false);
            }

            return $result;
        }

        public function isThread($slug) {
            $st = $this->app->db->prepare("SELECT thread_id AS id
                     FROM forum_threads
                     WHERE slug = :slug");
            $st->execute(array(':slug'=>$slug));
            return $st->fetch();
        }

        public function getSections($parent=null) {
            if ($parent == null) {
                $sql =  "SELECT section_id AS id, title, slug
                         FROM forum_sections
                         WHERE ISNULL(parent_id)";
                $sql .= "ORDER BY section_id ASC";
                $st = $this->app->db->prepare($sql);
                $st->execute(array(':parent'=>$parent));
                $result = $st->fetchAll();
            } else {
                $st = $this->app->db->prepare('SELECT section_id AS id, title, slug
                                    FROM forum_sections
                                    WHERE parent_id = :parent
                                    ORDER BY section_id ASC');
                $st->execute(array(':parent'=>$parent));
                $result = $st->fetchAll();
            }

            foreach($result as $res) {
                $children = $this->getSections($res->id);
                if ($children)
                    $res->children = $children;
            }

            return $result;
        }

        public function printThreadPost($post, $first=false, $last=false) {

            $post->body = $this->app->parse($post->body);
            if (isset($post->signature)) {
                $post->signature = $this->app->parse($post->signature);
            }

            $return = "        <li class='row clr' data-id='{$post->post_id}'";
            if ($last) {
                $return .= ' id="latest">';
            } else {
                $return .= '>';
            }


            $return .= "            <div id='post-{$post->post_id}' class='col span_5 post_header'>";

            if ($post->username):
                if ($post->donator):

                    $return .= <<< POST
                <div class="label corner">
                    <i class="icon-heart"></i>
                </div>

POST;

                endif;

                $posted = date('c', strtotime($post->posted));
                $tmp1 = $first?'rel="author" itemprop="author"':'';
                $tmp2 = $first?'itemprop="datePublished"':'';
                $return .= <<< POST
                <a href="/user/{$post->username}" class="user" {$tmp1}>
                    {$post->username}<br/>
                    <img class='mobile-hide' src="{$post->image}" width="60" height="60" alt="{$post->username}'s profile picture">
                </a>
                <br/>
                <ul class='plain'>
                    <li class='highlight'><i class='icon-clock'></i> <time class='short' {$tmp2} pubdate datetime="{$posted}">{$this->app->utils->timeSince($post->posted, true)}</time></li>
                    <li class='mobile-hide'><i class='icon-trophy'></i> {$post->score}</li>
                    <li class='mobile-hide'><i class='icon-chat'></i> {$post->posts}</li>
                </ul>
                <br/>

POST;

            else: // Deleted user

                $return .= <<< POST
                <div class='strong dark'>[deleted user]</div>
                <br/>

POST;

            endif;
            if ($post->user_id === $this->app->user->uid):
                if (!$first):

                    $return .= <<< POST
                <a href='?edit={$post->post_id}' class='button icon'><i class='icon-edit'></i></a>
                <a href='#' class='button icon remove'><i class='icon-trash'></i></a>

POST;

                else:

                    $return .= <<< POST
                <a href='?edit={$post->post_id}' class='button'><i class='icon-edit'></i> Edit post</a>

POST;

                endif;
            elseif ($this->app->user->forum_priv > 1):
                if (!$first):

                    $return .= <<< POST
                <a href='/admin/forum.php?post={$post->post_id}&edit' class='button icon'><i class='icon-edit'></i></a>
                <a href='/admin/forum.php?post={$post->post_id}&remove' class='button icon'><i class='icon-trash'></i></a>

POST;

                else:

                    $return .= <<< POST
                <a href='/admin/forum.php?post={$post->post_id}&edit' class='button'><i class='icon-edit'></i> Edit post</a>

POST;

                endif;
            else:

                if ($post->flag <= 0) {
                    $return .= "                <a href='#' class='button flag'><i class='icon-flag'></i> Flag post</a>";
                } else {
                    $return .= "                <a href='#' class='button flagged'><i class='icon-flag'></i> Flagged</a>";
                }

            endif;

            $return .= <<< POST
            </div>
            <article class="col span_19 post_content">
                <div class="karma small mobile-hide">
POST;

            if (!$this->app->user->loggedIn || $post->user_id === $this->app->user->uid):

                $return .= <<< POST
                <span>{$post->karma}</span>
POST;
                if ($first):
                    $return .= ' <a class="dark" href="/faq#karma"><i class="icon-help"></i></a>';
                endif;

            else:

                $cancel_down = $post->user_karma < 0?'karma-cancel':'';
                $cancel_up = $post->user_karma > 0?'karma-cancel':'';

                if ($this->app->user->karma_priv >= 2)
                    $return .= "<a href='#' class='karma karma-down {$cancel_down}'><i class='icon-caret-down'></i></a>";
                $return .= "<span>{$post->karma}</span>";
                if ($this->app->user->karma_priv >= 1)
                    $return .= "<a href='#' class='karma karma-up {$cancel_up}'><i class='icon-caret-up'></i></a>";

                if ($first) {
                    $return .= ' <a class="dark" href="/faq#karma"><i class="icon-help"></i></a>';
                }

            endif;

            $tmp1 = $first?'itemprop="articleBody"':'';
            $return .= <<< POST
                </div>
                <div class="post_body" {$tmp1}>
                    {$post->body}

POST;

            if (isset($post->signature)) {
                $return .= "                    <div class='post_signature'>
                        {$post->signature}
                    </div>\n";
            }

            $return .= <<< POST
                </div>
            </article>
        </li>

POST;

            print $return;


        }

        public function printSectionsList($cat, $menu = false, $current = null, $level = 1) {
            if ($menu) {
                $c = '';
                $t = 'title'.$level;
                if (isset($current->{$t}) && $current->{$t} == $cat->title)
                    $c = 'active ';
                if (isset($cat->children) && count($cat->children))
                    $c .= 'parent';

                echo "\t\t\t\t\t\t\t\t\t\t\t<li class='$c'><a href='/forum/{$cat->slug}'>{$cat->title}</a>";
                if (isset($cat->children) && count($cat->children)) {
                    echo "\n                                        <ul>\n";
                    $level++;
                    foreach($cat->children AS $child) {
                        $this->printSectionsList($child, $menu, $current, $level);
                    }
                    echo "                                        </ul>\n                                        ";
                }
                echo "</li>\n";
            } else {
                echo "<li data-value='{$cat->id}'>{$cat->title}\n";
                if (isset($cat->children) && count($cat->children)) {
                    echo "<ul>\n";
                    foreach($cat->children AS $child) {
                        $this->printSectionsList($child);
                    }
                    echo "</ul>\n";
                }
                echo "</li>\n";
            }
        }

        public function getSection($slug) { 
            $st = $this->app->db->prepare("SELECT t1.title as title1, t1.slug as slug1, t2.title as title2,
                t2.slug as slug2, t3.title as title3, t3.slug as slug3, t4.title as title4, t4.slug as slug4,
                current.section_id AS id, current.title AS title, current.slug AS slug, current.parent_id AS parent,
                child.section_id AS `child`
                FROM forum_sections AS t1
                LEFT JOIN forum_sections AS t2 ON t2.parent_id = t1.section_id
                LEFT JOIN forum_sections AS t3 ON t3.parent_id = t2.section_id
                LEFT JOIN forum_sections AS t4 ON t4.parent_id = t3.section_id
                LEFT JOIN forum_sections AS current ON current.slug = :slug
                LEFT JOIN forum_sections AS child ON current.section_id = child.parent_id
                WHERE isnull(t1.parent_id) AND (t1.slug = :slug OR t2.slug = :slug OR t3.slug = :slug OR t4.slug = :slug)");
            $st->execute(array(':slug'=>$slug));
            $result = $st->fetch();

            if ($result->slug1 == $result->slug) {
                unset($result->title2); unset($result->slug2);
                unset($result->title3); unset($result->slug3);
                unset($result->title4); unset($result->slug4);
            } else if ($result->slug2 == $result->slug) {
                unset($result->title3); unset($result->slug3);
                unset($result->title4); unset($result->slug4);
            } else if ($result->slug3 == $result->slug) {
                unset($result->title4); unset($result->slug4);
            }

            return $result;
        }

        public function getBreadcrumb($section, $thread = false, $divide='&gt;') {
            $crumb = '';
            if (isset($section->title1))
                $crumb .= '<a href="/forum/'.$section->slug1.'">'.$section->title1.'</a> ' . $divide . ' ';
            if (isset($section->title2))
                $crumb .= '<a href="/forum/'.$section->slug2.'">'.$section->title2.'</a> ' . $divide . ' ';
            if (isset($section->title3))
                $crumb .= '<a href="/forum/'.$section->slug3.'">'.$section->title3.'</a> ' . $divide . ' ';
            if (isset($section->title4))
                $crumb .= '<a href="/forum/'.$section->slug4.'">'.$section->title4.'</a> ' . $divide . ' ';

            return $crumb;
        }

        public function getThreadBreadcrumb($section, $thread, $divide='&gt;') {
            $crumb = '';
            for($i = 4; $i > 0; $i--):
                $title = 'title'.$i;
                $slug = 'slug'.$i;
                if (!isset($thread->{$title}))
                    continue;

                // Skip sections that match where the user currently is
                if (isset($section) && strlen($section->slug) >= strlen($thread->{$slug}))
                    continue;

                $crumb .=  "<a class='dark' href='/forum/".$thread->{$slug}."'>".$thread->{$title}."</a>";

                if ($i != 1)
                    $crumb .=  " &gt; ";            
            endfor;

            return $crumb;
        }

        public function getThreads($section=null, $page=1, $no_replies = false, $most_popular = false, $watching = false, $limit = 10) {
            $section_slug = '';
            if ($section)
                $section_slug = $section->slug;
                

            $sql = "SELECT SQL_CALC_FOUND_ROWS threads.title, threads.slug, threads.closed, threads.sticky,
                    users.username AS author, posts.count-1 as `count`, latest.posted AS latest,
                    latest.username AS latest_author, posts.voices, posts.created, t1.title as title1,
                    t1.slug as slug1, t2.title as title2,
                    t2.slug as slug2, t3.title as title3, t3.slug as slug3, t4.title as title4, t4.slug as slug4,
                    first.body, IF (forum_users.viewed >= latest, 1, 0) AS `viewed`, forum_users.watching
                    FROM forum_threads threads
                    LEFT JOIN users
                    ON users.user_id = threads.owner
                    LEFT JOIN (SELECT thread_id, max(posted) AS `latest`, min(posted) AS `created`, count(*) AS `count`, Count(Distinct author) AS `voices` FROM forum_posts WHERE deleted = 0 GROUP BY thread_id) posts
                    ON posts.thread_id = threads.thread_id
                    LEFT JOIN (SELECT thread_id, users.username, posted FROM forum_posts LEFT JOIN users ON users.user_id = author WHERE deleted = 0) latest
                    ON latest.thread_id = threads.thread_id AND latest.posted = posts.latest
                    LEFT JOIN (SELECT thread_id, body, posted FROM forum_posts WHERE deleted = 0) first
                    ON first.thread_id = threads.thread_id AND first.posted = posts.created
                    LEFT JOIN forum_users
                    ON threads.thread_id = forum_users.thread_id AND forum_users.user_id = :uid

                    LEFT JOIN forum_sections AS t1 ON t1.section_id = threads.section_id
                    LEFT JOIN forum_sections AS t2 ON t1.parent_id = t2.section_id
                    LEFT JOIN forum_sections AS t3 ON t2.parent_id = t3.section_id
                    LEFT JOIN forum_sections AS t4 ON t3.parent_id = t4.section_id

                    WHERE threads.slug LIKE CONCAT(:section_slug, '%') AND threads.deleted = 0 AND posts.count > 0";
            
            if ($no_replies)
                $sql .= ' AND posts.count = 1';

            if ($watching)
                $sql .= ' AND forum_users.watching = 1';            
            
            if ($most_popular)
                $sql .= " ORDER BY `count` DESC, `voices` DESC, latest DESC";
            else
                $sql .= " ORDER BY sticky DESC, latest DESC";

            if ($limit)
                $sql .= " LIMIT ". ($page-1)*$limit .", $limit";

            $st = $this->app->db->prepare($sql);
            $st->execute(array(':section_slug'=>$section_slug, ':uid'=>$this->app->user->uid));
            $threads = $st->fetchAll();

            foreach($threads AS $res) {
                $res->title = $this->app->parse($res->title, false);

                $res->blurb = $this->app->parse($res->body, false);

                if ($res->closed)
                    $res->title = '[closed] ' . $res->title;
                if ($res->sticky)
                    $res->title = '[sticky] ' . $res->title;

                for($i = 4; $i > 0; $i--) {
                    $title = 'title'.$i;
                    $slug = 'slug'.$i;
                    if (!$res->{$title}) {
                        unset($res->{$title});
                        unset($res->{$slug});
                    } else {
                        break;
                    }
                }
            }

            // Get total rows
            $st = $this->app->db->prepare('SELECT FOUND_ROWS() AS `count`');
            $st->execute();
            $result = $st->fetch();

            $result->threads = $threads;

            return $result;
        }

        public function newThread($section, $title, $body) {
            if (!$title || strlen($title) < 3)
                return "Title must be longer than three characters";

            // Check for spam - pretty much always new threads
            $regex = "/(". implode('|', $this->banned) .")/i";
            $matched = preg_match_all($regex, $title, $matches) + preg_match_all($regex, $body, $matches);
            if ($matched > 3) {
                // Could implement http://www.stopforumspam.com and ban user
                return "Banned words found in content";
            }


            $section_id = $section->id;
            $slug = $section->slug . '/' . $this->app->utils->generateSlug($title);
            try {
                $this->app->db->beginTransaction();

                $st = $this->app->db->prepare("INSERT INTO forum_threads (`section_id`, `title`, `slug`, `owner`)
                    VALUES (:section_id, :title, :slug, :uid)");
                $st->execute(array(':section_id'=>$section_id, ':title'=>$title, ':slug'=>$slug, ':uid'=>$this->app->user->uid));

                $thread_id = $this->app->db->lastInsertId();

                // Update slug
                $slug = $section->slug . '/' . $this->app->utils->generateSlug($thread_id . '-' .$title);
                $st = $this->app->db->prepare("UPDATE forum_threads SET `slug` = :slug WHERE thread_id = :tid");
                $st->execute(array(':tid'=>$thread_id, ':slug'=>$slug));


                $status = $this->newPost($thread_id, $body);
                if (!$status) {
                    $this->app->db->rollback();
                    return "Post content is too short";
                }
                // $st = $this->app->db->prepare("INSERT INTO forum_posts (`thread_id`, `body`, `author`)
                //     VALUES (:thread_id, :body, :uid)");
                // $st->execute(array(':thread_id'=>$thread_id, ':body'=>$body, ':uid'=>$this->app->user->uid));

                $this->app->db->commit();
            } catch(PDOExecption $e) {
                $this->app->db->rollback();
                return "There was an error!";
            }

            // Setup GA event
            $this->app->ssga->set_event('forum', 'thread.new', '/forum/' . $slug, $this->app->user->uid);
            $this->app->ssga->send();

            return '/forum/' . $slug;
        }

        public function closeThread($thread_id) {
            if (!$this->app->user->loggedIn)
                return false;

            $status = false;
            if ($this->app->user->forum_priv == 1) {
                $st = $this->app->db->prepare("SELECT thread_id
                                               FROM forum_threads
                                               WHERE thread_id = :pid AND owner = :uid");
                $st->execute(array(':pid'=>$thread_id, ':uid'=>$this->app->user->uid));
                if ($st->fetch()) {
                    $status = true;
                }
            } else if ($this->app->user->forum_priv > 1) {
                $status = true;
            }

            if (!$status)
                return false;

            $st = $this->app->db->prepare("UPDATE forum_threads
                                           SET closed = '1'
                                           WHERE thread_id = :pid");
            return $st->execute(array(':pid'=>$thread_id));            
        }


        public function getThread($thread_id, $page = 1, $limit = 10) {
            $st = $this->app->db->prepare("SELECT thread.thread_id AS `id`, thread.title, thread.slug, thread.deleted, thread.closed, thread.sticky, section.slug AS section_slug, replies.count AS replies, COALESCE(forum_users.watching, 0) AS `watching`
                FROM forum_threads thread
                LEFT JOIN forum_users
                ON forum_users.thread_id = thread.thread_id AND forum_users.user_id = :uid
                LEFT JOIN forum_sections section
                ON section.section_id = thread.section_id
                LEFT JOIN (SELECT `thread_id`, count(*)-1 AS `count` FROM forum_posts WHERE deleted = 0 GROUP BY `thread_id`) replies
                ON replies.thread_id = thread.thread_id
                WHERE thread.thread_id = :thread_id
                LIMIT 1");
            $st->execute(array(':thread_id'=>$thread_id, ':uid'=>$this->app->user->uid));
            $thread = $st->fetch();

            if (!$thread)
                return false;

            $thread->title = $this->app->parse($thread->title, false);
            if ($thread->closed)
                $thread->title = '[closed] ' . $thread->title;
            if ($thread->sticky)
                $thread->title = '[sticky] ' . $thread->title;

            // Get question
            $st = $this->app->db->prepare("SELECT post.post_id, users.user_id, users.username, post.body, post.posted, post.updated AS edited, profile.forum_signature AS signature,
                profile.gravatar, IF (profile.gravatar = 1, users.email , profile.img) as `image`,
                forum_posts.posts, users.score, coalesce(users_forum.karma, 0) AS `karma`, coalesce(user_karma.karma, 0) AS `user_karma`, user_karma.flag, (donate.medal_id IS NOT NULL) AS donator
                FROM forum_posts post
                LEFT JOIN users
                ON users.user_id = post.author
                LEFT JOIN users_profile profile
                ON users.user_id = profile.user_id
                LEFT JOIN users_medals donate
                ON users.user_id = donate.user_id AND donate.medal_id = 19
                LEFT JOIN (SELECT author, COUNT(*) AS `posts` FROM forum_posts WHERE deleted = 0 GROUP BY author) forum_posts
                ON forum_posts.author = post.author
                LEFT JOIN (SELECT post_id, SUM(karma) AS `karma` FROM users_forum GROUP BY post_id) users_forum
                ON users_forum.post_id = post.post_id
                LEFT JOIN (SELECT post_id, user_id, karma, flag FROM users_forum) user_karma
                ON user_karma.post_id = post.post_id AND user_karma.user_id = :uid
                WHERE post.thread_id = :thread_id AND post.deleted = 0
                ORDER BY `posted` ASC
                LIMIT 1");
            $st->execute(array(':thread_id'=>$thread_id, ':uid'=>$this->app->user->uid));
            $thread->question = $st->fetch();

            // Get questioners image
            if (isset($thread->question->image)) {
                $gravatar = isset($thread->question->gravatar) && $thread->question->gravatar == 1;
                $thread->question->image = profile::getImg($thread->question->image, 60, $gravatar);
            } else
                $thread->question->image = profile::getImg(null, 60);


            $thread->p_start = (($page-1)*$limit)+1;            

            // Get replies
            $st = $this->app->db->prepare("SELECT post.post_id, users.user_id, users.username, post.body, post.posted, post.updated AS edited, profile.forum_signature AS signature,
                profile.gravatar, IF (profile.gravatar = 1, users.email , profile.img) as `image`,
                forum_posts.posts, users.score, coalesce(users_forum.karma, 0) AS `karma`, coalesce(user_karma.karma, 0) AS `user_karma`, user_karma.flag, (donate.medal_id IS NOT NULL) AS donator
                FROM forum_posts post
                LEFT JOIN users
                ON users.user_id = post.author
                LEFT JOIN users_profile profile
                ON users.user_id = profile.user_id
                LEFT JOIN users_medals donate
                ON users.user_id = donate.user_id AND donate.medal_id = 19
                LEFT JOIN (SELECT author, COUNT(*) AS `posts` FROM forum_posts WHERE deleted = 0 GROUP BY author) forum_posts
                ON forum_posts.author = post.author
                LEFT JOIN (SELECT post_id, SUM(karma) AS `karma` FROM users_forum GROUP BY post_id) users_forum
                ON users_forum.post_id = post.post_id
                LEFT JOIN (SELECT post_id, user_id, karma, flag FROM users_forum) user_karma
                ON user_karma.post_id = post.post_id AND user_karma.user_id = :uid
                WHERE post.thread_id = :thread_id AND post.deleted = 0
                ORDER BY `posted` ASC
                LIMIT :l1, :l2");
            $st->bindValue(':thread_id', $thread_id);
            $st->bindValue(':uid', $this->app->user->uid);
            $st->bindValue(':l1', (int) $thread->p_start, PDO::PARAM_INT); 
            $st->bindValue(':l2', (int) $limit, PDO::PARAM_INT); 
            $st->execute();
            $thread->posts = $st->fetchAll();

            // Get posts images
            foreach($thread->posts AS $post) {
                if (isset($post->image)) {
                    $gravatar = isset($post->gravatar) && $post->gravatar == 1;
                    $post->image = profile::getImg($post->image, 50, $gravatar);
                } else
                    $post->image = profile::getImg(null, 50);
            }

            $thread->p_end = $thread->p_start + count($thread->posts) - 1;

            // Get section slug
            $thread->section = $this->getSection($thread->section_slug);

            //Update view status
            if ($this->app->user->loggedIn) {
                $st = $this->app->db->prepare("INSERT INTO forum_users (`user_id`, `thread_id`)
                        VALUES (:uid, :thread_id) ON DUPLICATE KEY UPDATE `viewed` = now()");
                $st->execute(array(':thread_id'=>$thread_id, ':uid'=>$this->app->user->uid));
            }

            return $thread;
        }

        public function newPost($thread_id, $body) {
            if (!$this->validatePost($body))
                return false;

            $st = $this->app->db->prepare("INSERT INTO forum_posts (`thread_id`, `body`, `author`)
                VALUES (:thread_id, :body, :uid)");
            $status = $st->execute(array(':thread_id'=>$thread_id, ':body'=>$body, ':uid'=>$this->app->user->uid));

            if ($status) {
                $post_id = $this->app->db->lastInsertId();

                // Setup GA event
                $this->app->ssga->set_event('forum', 'post.new', $post_id);
                $this->app->ssga->send();

                $notified = array($this->app->user->uid);

                // Check for mentions
                preg_match_all("/(?:(?<=\s)|^)@(\w*[0-9A-Za-z_.-]+\w*)/", $body, $mentions);
                foreach($mentions[1] as $mention) {
                    $st = $this->app->db->prepare('SELECT user_id FROM users WHERE username = :username LIMIT 1');
                    $st->execute(array(':username' => $mention));
                    $result = $st->fetch();
                    
                    if ($result) {
                        if (!in_array($result->user_id, $notified)) {
                            array_push($notified, $result->user_id);
                            $this->app->notifications->add($result->user_id, 'forum_mention', $this->app->user->uid, $post_id);
                        }
                    }
                }

                // Notify watchers
                $st = $this->app->db->prepare('SELECT forum_users.user_id AS author FROM forum_users
                                   WHERE thread_id = :thread_id AND watching = 1');
                $st->execute(array(':thread_id' => $thread_id));
                $watchers = $st->fetchAll();
                
                if ($watchers) {
                    foreach($watchers AS $watcher) {
                        if (!in_array($watcher->author, $notified)) {
                            array_push($notified, $watcher->author);
                            $this->app->notifications->add($watcher->author, 'forum_post', $this->app->user->uid, $post_id);
                        }
                    }
                }
            
                // Update view status
                $st = $this->app->db->prepare("INSERT INTO forum_users (`user_id`, `thread_id`, `watching`)
                        VALUES (:uid, :thread_id, 1) ON DUPLICATE KEY UPDATE `watching` = 1");
                $st->execute(array(':thread_id'=>$thread_id, ':uid'=>$this->app->user->uid));

                // Add to socket feed
                $st = $this->app->db->prepare('SELECT title, slug
                                    FROM forum_threads
                                    WHERE thread_id = :thread_id');
                $st->execute(array(':thread_id' => $thread_id));
                $thread = $st->fetch();

                $this->app->feed->call($this->app->user->username, 'forum_post', $thread->title, '/forum/'.$thread->slug);



                // Check for forum medal
                $st = $this->app->db->prepare('SELECT COUNT(post_id) AS posts FROM forum_posts
                                   WHERE deleted = 0 AND author = :uid');
                $st->execute(array(':uid' => $this->app->user->uid));
                $res = $st->fetch();

                if ($res->posts == 50) {
                    $this->app->user->awardMedal('forum');
                } else if ($res->posts == 100) {
                    $this->app->user->awardMedal('forum', 2);
                } else if ($res->posts == 1000) {
                    $this->app->user->awardMedal('forum', 3);
                }
            }

            return $status;
        }

        public function deletePost($post_id) {
            if (!$this->app->user->loggedIn)
                return false;

            if ($this->app->user->forum_priv == 1) {
                $st = $this->app->db->prepare("SELECT post_id
                                               FROM forum_posts
                                               WHERE post_id = :pid AND author = :uid");
                $st->execute(array(':pid'=>$post_id, ':uid'=>$this->app->user->uid));
                $status = $st->fetch();
            } else if ($this->app->user->forum_priv > 1) {
                $status = true;
            } else {
                $status = false;
            }

            if ($status) {
                $st = $this->app->db->prepare("UPDATE forum_posts
                                               SET deleted = 1
                                               WHERE post_id = :pid
                                               LIMIT 1");
                $st->execute(array(':pid'=>$post_id));

                // Check for forum medal
                $st = $this->app->db->prepare('SELECT COUNT(forum_posts.post_id) AS posts FROM forum_posts
                                    INNER JOIN forum_posts tmp
                                    ON tmp.post_id = :pid AND forum_posts.author = tmp.author
                                    WHERE forum_posts.deleted = 0');
                $st->execute(array(':pid'=>$post_id));
                $res = $st->fetch();

                if ($res->posts == 9) {
                    $this->app->user->removeMedal('karma', 1);
                } else if ($res->posts == 49) {
                    $this->app->user->removeMedal('forum', 1);
                } else if ($res->posts == 99) {
                    $this->app->user->removeMedal('forum', 2);
                    $this->app->user->removeMedal('karma', 2);
                } else if ($res->posts == 999) {
                    $this->app->user->removeMedal('forum', 3);
                }
            }

            return $status;
        }

        public function getPost($post_id, $thread_id=null) {
            if (!$this->app->user->loggedIn)
                return false;

            if ($this->app->user->forum_priv >= 1) {
                if (isset($thread_id)) {
                    $st = $this->app->db->prepare("SELECT post_id, body, author
                                                   FROM forum_posts
                                                   WHERE post_id = :pid AND thread_id = :tid AND deleted = 0");
                    $st->execute(array(':pid'=>$post_id, ':tid'=>$thread_id));
                } else {
                    $st = $this->app->db->prepare("SELECT post_id, body, author
                                                   FROM forum_posts
                                                   WHERE post_id = :pid");
                    $st->execute(array(':pid'=>$post_id));                    
                }
                $post = $st->fetch();

                if (!$post)
                    return false;

                if ($post->author === $this->app->user->uid) {
                    $post->owner = true;
                } else if ($this->app->user->forum_priv > 1) {
                    $post->owner = false;
                } else {
                    return false;
                }

            } else {
                return false;
            }

            return $post;
        }

        public function editPost($post_id, $thread_id, $body) {
            if (!$this->getPost($post_id, $thread_id))
                return false;

            if (!$this->validatePost($body))
                return false;

            $st = $this->app->db->prepare("UPDATE forum_posts
                                           SET body = :body, updated = now()
                                           WHERE post_id = :pid
                                           LIMIT 1");
            return $st->execute(array(':pid'=>$post_id, ':body'=>$body));
        }

        public function findPost($thread_id, $post_id) {
            $st = $this->app->db->prepare("SELECT COUNT(`post_id`) AS `posts`
                                           FROM forum_posts
                                           WHERE post_id < :pid AND thread_id = :tid AND deleted = 0");
            $st->execute(array(':pid'=>$post_id, ':tid'=>$thread_id));
            $posts = $st->fetch();

            return ceil($posts->posts/10);           
        }

        public function watchThread($thread_id, $watch=true) {
            if ($watch) $watch = '1'; else $watch = '0';

            $st = $this->app->db->prepare("UPDATE forum_users SET `watching` = :watch
                WHERE `user_id` = :uid AND `thread_id` = :thread_id");
            $status = $st->execute(array(':thread_id'=>$thread_id, ':uid'=>$this->app->user->uid, ':watch'=>$watch));

            return $status;
        }

        public function giveKarma($positive = true, $post_id, $cancel=false) {
            $value = $positive?1:-1;

            // Check if user has privilages to give this karma
            if (!$positive && $this->app->user->karma_priv < 2)
                return false;
            if ($this->app->user->karma_priv < 1)
                return false;

            // Does post exist? Is user owner of post?
            $st = $this->app->db->prepare("SELECT author
                                           FROM forum_posts
                                           WHERE post_id = :pid");
            $st->execute(array(':pid'=>$post_id));
            $result = $st->fetch();

            if (!$result || $result->author === $this->app->user->uid)
                return false;

            if (!$cancel) {
                $st = $this->app->db->prepare("INSERT INTO users_forum (`user_id`, `post_id`, `karma`)
                        VALUES (:uid, :post_id, :value) ON DUPLICATE KEY UPDATE `karma` = :value, `time` = now()");
                $st->execute(array(':post_id'=>$post_id, ':uid'=>$this->app->user->uid, ':value'=>$value));

                // Give medals
                if ($positive) {
                    $this->app->user->awardMedal('rewarder');
                    $this->app->user->awardMedal('+ve', 1, $result->author);
                }
            } else {
                $st = $this->app->db->prepare("DELETE IGNORE FROM users_forum WHERE user_id = :uid
                                               AND post_id = :post_id LIMIT 1");
                $st->execute(array(':post_id'=>$post_id, ':uid'=>$this->app->user->uid));
            }

            return true;
        }

        public function flagPost($post_id) {
            $st = $this->app->db->prepare("INSERT INTO users_forum (`user_id`, `post_id`, `flag`)
                VALUES (:uid, :post_id, NOW()) ON DUPLICATE KEY UPDATE `flag` = NOW()");
            return $st->execute(array(':post_id'=>$post_id, ':uid'=>$this->app->user->uid));
        }


        public function getError() {
            return ($this->error)?$this->error:'Error making request';
        }

        //check user can post
        function validatePost($body, $edit=false) {
            if (!$this->app->user->loggedIn)
                return false;

            if ($this->app->user->forum_priv < 1) {
                $this->error = "You have been banned from posting messages";
                return false;
            }

            //check post length
            if (str_word_count($body) < 2) {
                $this->error = "Post content is too short";
                return false;
            }

            if (!$edit)  {
                //check when last post was made
                $st = $this->app->db->prepare('SELECT author, posted
                                               FROM forum_posts
                                               WHERE author = :uid AND posted > NOW() - INTERVAL 15 SECOND
                                               ORDER BY posted DESC
                                               LIMIT 1');
                $st->execute(array(':uid'=>$this->app->user->uid));
                if ($res = $st->fetch()) {

                    $left = 15 - (strtotime('now') - strtotime($res->posted));
                    $this->error = "You can only post a message once every 15 seconds. Please wait {$left} seconds and try again.";
                    return false;
                }
            }

            return true;
        }
    }
?>