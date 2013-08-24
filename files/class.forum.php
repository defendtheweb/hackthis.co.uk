<?php
    class forum {
        private $app;

        public function __construct($app) {
            $this->app = $app;
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

        public function printSectionsList($cat, $menu = false, $current, $level = 1) {
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

        public function getBreadcrumb($section, $divide='&gt;') {
            $crumb = '';
            if (isset($section->title1))
                $crumb .= '<a href="/forum/'.$section->slug1.'">'.$section->title1.'</a> ';
            if (isset($section->title2))
                $crumb .= $divide.' <a href="/forum/'.$section->slug2.'">'.$section->title2.'</a> ';
            if (isset($section->title3))
                $crumb .= $divide.' <a href="/forum/'.$section->slug3.'">'.$section->title3.'</a> ';
            if (isset($section->title4))
                $crumb .= $divide.' <a href="/forum/'.$section->slug4.'">'.$section->title4.'</a> ';

            return $crumb . '<br/>';
        }

        public function getThreads($section) {
            $st = $this->app->db->prepare("SELECT threads.title, threads.slug, threads.closed, threads.sticky,
                users.username AS author, posts.count, latest.posted AS latest, latest.username AS latest_author, posts.voices, posts.created
                FROM forum_threads threads
                LEFT JOIN users
                ON users.user_id = threads.owner
                LEFT JOIN (SELECT thread_id, max(posted) AS `latest`, min(posted) AS `created`, count(*) AS `count`, Count(Distinct author) AS `voices` FROM forum_posts WHERE deleted = 0 GROUP BY thread_id) posts
                ON posts.thread_id = threads.thread_id
                LEFT JOIN (SELECT thread_id, users.username, posted FROM forum_posts LEFT JOIN users ON users.user_id = author WHERE deleted = 0) latest
                ON latest.thread_id = threads.thread_id AND latest.posted = posts.latest
                WHERE threads.deleted = 0 AND posts.count > 0
                ORDER BY sticky DESC, latest DESC");
            $st->execute();
            $result = $st->fetchAll();

            foreach($result AS $res) {
                if ($res->closed)
                    $res->title = '[closed] ' . $res->title;
                if ($res->sticky)
                    $res->title = '[sticky] ' . $res->title;
            }

            return $result;
        }
    }
?>