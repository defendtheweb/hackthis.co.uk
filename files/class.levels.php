<?php
    class levels {
        private $app;
        private $list;
        private $level;

        public function __construct($app) {
            $this->app = $app;
        }

        public function getList() {
            if (isset($this->list))
                return $this->list;

            $st = $this->app->db->prepare('SELECT CONCAT(levels_groups.title, " Level ", levels.name) as `title`, levels.name, levels_groups.title as `group`,
                    LOWER(CONCAT("/levels/", CONCAT_WS("/", levels_groups.title, levels.name))) as `uri`,
                    IF(users_levels.completed > 0, 1, 0) as `completed`
                    FROM levels
                    INNER JOIN levels_groups
                    ON levels_groups.title = levels.group
                    LEFT JOIN users_levels
                    ON users_levels.user_id = :uid AND users_levels.level_id = levels.level_id
                    ORDER BY levels_groups.order ASC, levels.name ASC');
            $st->bindValue(':uid', $this->app->user->uid);
            $st->execute();
            $this->list = $st->fetchAll();

            return $this->list;
        }

        public function getLevel($group, $level) {
            $before_after_sql = 'SELECT `name`, LOWER(CONCAT("/levels/", CONCAT_WS("/", levels_groups.title, levels.name))) as `uri`
                FROM levels
                INNER JOIN levels_groups
                ON levels_groups.title = levels.group
                WHERE `group` = :group
                ORDER BY `name`';

            $sql = "SELECT levels.level_id, `group`, CONCAT(`group`, ' Level ', levels.name) as `title`,
                IF(users_levels.completed > 0, 1, 0) as `completed`, users_levels.completed as `completed_time`, `started`,
                IFNULL(users_levels.attempts, 0) as `attempts`,
                users_levels_count.`count`, users_levels_first.`username` AS first_user, users_levels_last.`username` AS last_user,
                users_levels_first.`completed` AS first_completed, users_levels_last.`completed` AS last_completed,
                levels_before.uri AS `level_before_uri`, levels_after.uri AS `level_after_uri`
                FROM levels
                INNER JOIN levels_groups
                ON levels_groups.title = levels.group
                LEFT JOIN ({$before_after_sql} DESC) levels_before
                ON levels_before.name < levels.name
                LEFT JOIN ({$before_after_sql} ASC) levels_after
                ON levels_after.name > levels.name
                LEFT JOIN users_levels
                ON users_levels.user_id = :uid AND users_levels.level_id = levels.level_id
                LEFT JOIN (SELECT level_id, count(*) AS `count` FROM users_levels WHERE completed > 0 GROUP BY level_id) users_levels_count
                ON users_levels_count.level_id = levels.level_id
                LEFT JOIN (SELECT username, level_id, completed FROM users_levels LEFT JOIN users ON users.user_id = users_levels.user_id WHERE completed > 0 ORDER BY completed ASC LIMIT 1) users_levels_first
                ON users_levels_first.level_id = levels.level_id
                LEFT JOIN (SELECT username, level_id, completed FROM users_levels LEFT JOIN users ON users.user_id = users_levels.user_id WHERE completed > 0 ORDER BY completed DESC LIMIT 1) users_levels_last
                ON users_levels_first.level_id = levels.level_id
                WHERE levels.name = :level AND levels.group = :group";

            $st = $this->app->db->prepare($sql);
            $st->execute(array(':level'=>$level, ':group'=>$group, ':uid'=>$this->app->user->uid));
            $level = $st->fetch();        

            if ($level)
                $this->levelView($level->level_id);
            else
                return false;

            //Build level data
            $sql = 'SELECT `key`, `value`, users.username
                    FROM levels_data
                    LEFT JOIN users
                    ON levels_data.value = users.user_id AND levels_data.key = "author"
                    WHERE level_id = :lid';
            $st = $this->app->db->prepare($sql);
            $st->execute(array(':lid'=>$level->level_id));
            $data = $st->fetchAll();

            $level->data = array();

            foreach($data as $d) {
                //Find all non-value entries
                foreach($d as $k=>$v) {
                    if ($v && $k !== 'key' && $k !== 'value')
                        $d->value = $v;
                }

                $level->data[$d->key] = $d->value;
            }

            // Set page details
            $this->app->page->title = ucwords($level->title);

            return $level;
        }

        function levelView($level_id) {
            $st = $this->app->db->prepare('INSERT IGNORE INTO users_levels (`user_id`, `level_id`) VALUES (:uid, :lid)');
            $st->execute(array(':lid'=> $level_id, ':uid' => $this->app->user->uid));
        }

        function check($level) {
            if (!isset($level->data['answer']))
                return false;

            $answers = json_decode($level->data['answer']);

            $attempted = false;
            $correct = false;
            foreach($answers AS $answer) {
                if (strtolower($answer->method) == 'post') {
                    if (isset($_POST[$answer->name])) {
                        $attempted = true;
                            if ($_POST[$answer->name] === $answer->value)
                                $correct = true;
                            else {
                                $correct = false;
                                break;
                            }
                    }
                } else if (strtolower($answer->method) == 'get') {
                    if (isset($_GET[$answer->name])) {
                        $attempted = true;
                            if ($_POST[$answer->name] === $answer->value)
                                $correct = true;
                            else {
                                $correct = false;
                                break;
                            }
                    }
                }
            }

            if ($attempted) {
                $level->attempt = $correct;

                // Woo they did it, have they done it before?
                if (!$level->completed) {
                    $level->attempts = $level->attempts + 1;
                    if ($correct) {
                        $level->completed = true;
                        $level->last_user = $this->app->user->username;
                        $level->last_completed = "now";
                        $st = $this->app->db->prepare('UPDATE users_levels SET completed = NOW(), attempts=attempts+1 WHERE level_id = :lid AND user_id = :uid');
                        $st->execute(array(':lid'=> $level->level_id, ':uid' => $this->app->user->uid));
                    } else {
                        // Record attempt
                        $st = $this->app->db->prepare('UPDATE users_levels SET attempts=attempts+1 WHERE level_id = :lid AND user_id = :uid');
                        $st->execute(array(':lid'=> $level->level_id, ':uid' => $this->app->user->uid));
                    }
                }
            }

            return $correct;
        }
    }
?>