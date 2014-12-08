<?php
    class levels {
        private $app;
        private $list;
        private $level;

        public function __construct($app) {
            $this->app = $app;
        }

        public function getList($uid=null, $category=null) {
            // Check cache
            $levels = json_decode($this->app->cache->get('level_list', 10));

            if (!$levels) {
                $sql = 'SELECT levels.level_id AS `id`, CONCAT(levels_groups.title, " Level ", levels.name) as `title`, levels.name, levels_groups.title as `group`,
                        LOWER(CONCAT("/levels/", CONCAT_WS("/", levels_groups.title, levels.name))) as `uri`, levels_completed.completed as `total_completed`,
                        levels_data.value AS `reward`
                        FROM levels
                        INNER JOIN levels_groups
                        ON levels_groups.title = levels.group
                        LEFT JOIN levels_data
                        ON levels_data.level_id = levels.level_id AND levels_data.key = "reward"
                        LEFT JOIN (SELECT COUNT(user_id) AS `completed`, level_id FROM users_levels WHERE completed > 0 AND user_id != 69 GROUP BY level_id) `levels_completed`
                        ON levels_completed.level_id = levels.level_id 
                        ORDER BY levels_groups.order ASC, levels.level_id ASC';

                $st = $this->app->db->prepare($sql);
                $st->execute();
                $levels = $st->fetchAll();

                $this->app->cache->set('level_list', json_encode($levels));
            }

            // Get list of completed levels
            $sql = 'SELECT level_id, IF(users_levels.completed > 0, 2, 1) as `completed` FROM users_levels WHERE user_id = :uid';
            $st = $this->app->db->prepare($sql);
            $st->bindValue(':uid', $uid?$uid:$this->app->user->uid);
            $st->execute();
            $user_levels = $st->fetchAll();

            // Create list
            $list = array();
            foreach ($levels AS &$level) {
                // Check filter
                if ($category && trim(strtolower(str_replace('+', '', $level->group))) != trim($category)) {
                    continue;
                }

                // Assign progress based on $users_levels
                $level->progress = 0;
                foreach ($user_levels AS $l) {
                    if ($l->level_id == $level->id) {
                        $level->progress = $l->completed;
                        break;
                    }
                }

                if (!array_key_exists($level->group, $list)) {
                    $list[$level->group] = new stdClass();
                    $list[$level->group]->levels = array();
                }
                array_push($list[$level->group]->levels, $level);
            }

            return $list;
        }

        public function getGroups() {
            $st = $this->app->db->prepare('SELECT title FROM levels_groups ORDER BY `order` ASC, `title` ASC');
            $st->execute();
            return $st->fetchAll();       
        }

        public function getLevelFromID($id) {
            $st = $this->app->db->prepare('SELECT `group`, `name` FROM levels WHERE level_id = :id LIMIT 1');
            $st->bindValue(':id', $id);
            $st->execute();
            $res = $st->fetch();

            if ($res)
                return $this->getLevel($res->group, $res->name, true);
            else
                return false;
        }

        public function getLevel($group, $name, $noSkip=false) {
            $before_after_sql = 'SELECT `level_id`, `name`, LOWER(CONCAT("/levels/", CONCAT_WS("/", levels_groups.title, levels.name))) as `uri`
                FROM levels
                INNER JOIN levels_groups
                ON levels_groups.title = levels.group
                WHERE `group` = :group
                ORDER BY level_id';

            $sql = "SELECT levels.level_id, levels.name, levels_groups.title AS `group`, CONCAT(`group`, ' Level ', levels.name) as `title`,
                IF(users_levels.completed > 0, 1, 0) as `completed`, users_levels.completed as `completed_time`, `started`,
                IFNULL(users_levels.attempts, 0) as `attempts`,
                levels_before.uri AS `level_before_uri`, levels_after.uri AS `level_after_uri`
                FROM levels
                INNER JOIN levels_groups
                ON levels_groups.title = levels.group
                LEFT JOIN ({$before_after_sql} DESC) levels_before
                ON levels_before.level_id < levels.level_id
                LEFT JOIN ({$before_after_sql} ASC) levels_after
                ON levels_after.level_id > levels.level_id
                LEFT JOIN users_levels
                ON users_levels.user_id = :uid AND users_levels.level_id = levels.level_id
                WHERE levels.name = :level AND levels.group = :group";

            $st = $this->app->db->prepare($sql);
            $st->execute(array(':level'=>$name, ':group'=>$group, ':uid'=>$this->app->user->uid));
            $level = $st->fetch();        

            if ($level) {
                //Check if user has access
                if (isset($level->level_before_uri) && strtolower($level->group) == 'main') {
                    $sql = 'SELECT IF(users_levels.completed > 0, 1, 0) as `completed` FROM levels
                            LEFT JOIN users_levels
                            ON users_levels.user_id = :uid AND users_levels.level_id = levels.level_id
                            WHERE `group` = :group AND levels.level_id < :level_id
                            ORDER BY levels.level_id DESC
                            LIMIT 1';
                            
                    $st = $this->app->db->prepare($sql);
                    $st->execute(array(':level_id'=>$level->level_id, ':group'=>$group, ':uid'=>$this->app->user->uid));
                    $previous = $st->fetch();

                    if (!$noSkip && (!$previous || !$previous->completed)) {
                        header("Location: $level->level_before_uri?skipped");
                        die();
                    }
                }

                $this->levelView($level->level_id);
            } else
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

            if (isset($level->data['code'])) {
                $level->data['code'] = json_decode($level->data['code']);
            }

            // Set page details
            $this->app->page->title = ucwords($level->title);

            // Get stats
            $sql = "SELECT COUNT(user_id) AS `completed` FROM users_levels WHERE completed > 0 AND level_id = :lid AND user_id != 69";
            $st = $this->app->db->prepare($sql);
            $st->execute(array(':lid'=>$level->level_id));
            $result = $st->fetch();
            $level->count = $result->completed;

            // // // Get latest
            // $sql = "SELECT username, completed FROM users_levels INNER JOIN users ON users.user_id = users_levels.user_id WHERE completed > 0 AND level_id = :lid AND users.user_id != 69 ORDER BY completed DESC LIMIT 1";
            $sql = "SELECT username, completed FROM users INNER JOIN (SELECT `user_id`, `completed` FROM users_levels WHERE completed > 0 AND level_id = :lid AND user_id != 69 ORDER BY `completed` DESC LIMIT 1) `a` ON `a`.user_id = users.user_id;";
	    $st = $this->app->db->prepare($sql);
            $st->execute(array(':lid'=>$level->level_id));
            $result = $st->fetch();
            $level->last_completed = $result->completed;
            $level->last_user = $result->username;

            // // // Get first
            // $sql = "SELECT username, completed FROM users_levels INNER JOIN users ON users.user_id = users_levels.user_id WHERE completed > 0 AND level_id = :lid AND users.user_id != 69 ORDER BY completed ASC LIMIT 1";
            $sql = "SELECT username, completed FROM users INNER JOIN (SELECT `user_id`, `completed` FROM users_levels WHERE completed > 0 AND level_id = :lid AND user_id != 69 ORDER BY `completed` ASC LIMIT 1) `a` ON `a`.user_id = users.user_id;";
	    $st = $this->app->db->prepare($sql);
            $st->execute(array(':lid'=>$level->level_id));
            $result = $st->fetch();
            $level->first_completed = $result->completed;
            $level->first_user = $result->username;

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
    $incorrect = 0;
    foreach($answers AS $answer) {
        $valid = false;

        if (strtolower($answer->method) == 'post') {
            if (isset($_POST[$answer->name])) {
                $attempted = true;
                if (isset($answer->type) && $answer->type == 'regex') {
                    if (preg_match($answer->value, $_POST[$answer->name])) {
                        $valid = true;
                        if ($incorrect === 0) {
                            $correct = true;
                        }
                    } else {
                        $correct = false;
                    }
                } else if ($_POST[$answer->name] === $answer->value) {
                    $valid = true;
                    if ($incorrect === 0) {
                        $correct = true;
                    }
                } else {
                    $correct = false;
                }
            }
        } else if (strtolower($answer->method) == 'get') {
            if (isset($_GET[$answer->name])) {
                $attempted = true;
                if ($answer->type && $answer->type == 'regex') {
                    if (preg_match($answer->value, $_GET[$answer->name])) {
                        $valid = true;
                        if ($incorrect === 0) {
                            $correct = true;
                        }
                    } else {
                        $correct = false;
                    }
                } else if ($_GET[$answer->name] === $answer->value) {
                    $valid = true;
                    if ($incorrect === 0) {
                        $correct = true;
                    }
                } else {
                    $correct = false;
                }
            }
        }

        if (!$valid) {
            $incorrect++;
        }
    }

    if ($attempted) {
        $level->attempt = $correct;
        $this->attempt($level, $correct);

        if ($level->level_id == 53) {
            $level->errorMsg = (3 - $incorrect) . ' out of 3 answers correct';
        }
    }

    return $correct;
}


        function attempt($level, $correct=false) {
            if (!$level->completed) {
                $level->attempts = $level->attempts + 1;
                $level->completed_time = 'now';
                if ($correct) {
                    $level->completed = true;
                    $level->count++;
                    $level->last_user = $this->app->user->username;
                    $level->last_completed = "now";
                    //Update user score (temporary)
                    $this->app->user->score = $this->app->user->score + $level->data['reward'];
                    $st = $this->app->db->prepare('UPDATE users_levels SET completed = NOW(), attempts=attempts+1 WHERE level_id = :lid AND user_id = :uid');
                    $st->execute(array(':lid'=> $level->level_id, ':uid' => $this->app->user->uid));

                    // Setup GA event
                    $this->app->ssga->set_event('level', 'completed', $level->level_id, $this->app->user->uid);
                    $this->app->ssga->send();

                    // Send feed thingy
                    $this->app->feed->call($this->app->user->username, 'level', ucwords($level->group.' '.$level->name), '/levels/'.strtolower($level->group).'/'.strtolower($level->name));
                    
                    // Update WeChall
                    file_get_contents("http://wechall.net/remoteupdate.php?sitename=ht&username=".$this->app->user->username);
                } else {
                    // Record attempt
                    $st = $this->app->db->prepare('UPDATE users_levels SET attempts=attempts+1 WHERE level_id = :lid AND user_id = :uid');
                    $st->execute(array(':lid'=> $level->level_id, ':uid' => $this->app->user->uid));
                }
            }
        }

        function user_data($level_id, $data=null) {
            if ($data !== null) {
                $st = $this->app->db->prepare('INSERT INTO users_levels_data (`user_id`, `level_id`, `data`) VALUES (:uid, :lid, :data) ON DUPLICATE KEY UPDATE `data` = :data, `time` = now()');
                return $st->execute(array(':lid' => $level_id, ':uid' => $this->app->user->uid, ':data' => $data));
            } else {
                $st = $this->app->db->prepare('SELECT * FROM users_levels_data WHERE `user_id` = :uid AND `level_id` = :lid');
                $st->execute(array(':lid' => $level_id, ':uid' => $this->app->user->uid));
                return $st->fetch();
            }
        }



        // ADMIN FUNCTIONS
        function addCategory($title) {
            if (!$this->app->user->admin_site_priv)
                return false;

            $st = $this->app->db->prepare('INSERT INTO levels_groups (`title`) VALUES (:title)');
            return $st->execute(array(':title'=> $title));
        }

        function editLevel($id, $new = false) {
            if (!$this->app->user->admin_site_priv)
                return false;


            $changes = array();

            if (!$new) {
                if (!$this->app->checkCSRFKey("level-editor", $_POST['token']))
                    return false;

                if (isset($_POST['category']) && strlen($_POST['category'])) {
                    $group = $_POST['category'];

                    $st = $this->app->db->prepare('UPDATE IGNORE levels SET `group` = :g WHERE level_id = :id LIMIT 1');
                    $res = $st->execute(array(':id'=> $id, ':g'=>$group));
                }
            }

            if (isset($_POST['reward']) && is_numeric($_POST['reward'])) {
                $changes['reward'] = $_POST['reward'];
            }
            if (isset($_POST['description']) && strlen($_POST['description'])) {
                $changes['description'] = $_POST['description'];
            }
            if (isset($_POST['hint']) && strlen($_POST['hint'])) {
                $changes['hint'] = $_POST['hint'];
            }
            if (isset($_POST['solution']) && strlen($_POST['solution'])) {
                $changes['solution'] = $_POST['solution'];
            }

            foreach($changes AS $change=>$value) {
                $st = $this->app->db->prepare('INSERT INTO levels_data (`level_id`, `key`, `value`) VALUES (:id, :k, :v) ON DUPLICATE KEY UPDATE `value` = :v');
                $res = $st->execute(array(':id'=> $id, ':k'=>$change, ':v'=>$value));
                if (!$res)
                    return false;
            }

            return true;
        }

        function newLevel() {
            if (!$this->app->user->admin_site_priv)
                return false;

            if (!$this->app->checkCSRFKey("level-editor", $_POST['token']))
                return false;

            // Create level
            try {
                $st = $this->app->db->prepare('INSERT INTO levels (`name`, `group`) VALUES (:name, :group)');
                $status = $st->execute(array(':name'=> $_POST['name'], ':group' => $_POST['category']));
            } catch(PDOExecption $e) { 
                return false;
            }

            if (!$status)
                return false;

            $id = $this->app->db->lastInsertId(); 

            // Insert data
            $this->editLevel($id, true);

            // Return level id
            return $id;
        }

        function editLevelForm($id) {
            if (!$this->app->user->admin_site_priv)
                return false;

            if (!$this->app->checkCSRFKey("level-editor", $_POST['token']))
                return false;

            $form = null;

            // Is it JSON?
            if (isset($_POST['form_method'])) {
                $form = array();
                $form['method'] = $_POST['form_method'];
                $form['fields'] = array();

                $f_types = $_POST['form_type'];
                $f_names = $_POST['form_name'];
                $f_labels = $_POST['form_label'];

                foreach($f_types as $key => $value) {
                    echo $value . "<br/>";
                    if ($f_names[$key] && $f_labels[$key]) {
                        $field = new stdClass;
                        $field->type = $value;
                        $field->name = $f_names[$key];
                        $field->label = $f_labels[$key];
                        
                        array_push($form['fields'],$field);
                    }
                }

                if (count($form['fields']))
                    $form = json_encode($form);
            } else {
                if (isset($_POST['form']))
                    $form = $_POST['form'];
            }

            if ($form) {
                $st = $this->app->db->prepare('INSERT INTO levels_data (`level_id`, `key`, `value`) VALUES (:id, :k, :v) ON DUPLICATE KEY UPDATE `value` = :v');
                $status = $st->execute(array(':id'=> $id, ':k' => 'form', ':v' => $form));
            }

            // Do answers
            $answers = array();

            $a_methods = $_POST['answer_method'];
            $a_names = $_POST['answer_name'];
            $a_values = $_POST['answer_value'];

            foreach($a_methods as $key => $value) {
                if ($a_names[$key] && $a_values[$key]) {
                    $answer = new stdClass;
                    $answer->method = $value;
                    $answer->name = $a_names[$key];
                    $answer->value = $a_values[$key];
                    
                    array_push($answers, $answer);
                }
            }

            if (count($answers)) {
                $answers = json_encode($answers);       
                if ($answers) {
                    $st = $this->app->db->prepare('INSERT INTO levels_data (`level_id`, `key`, `value`) VALUES (:id, :k, :v) ON DUPLICATE KEY UPDATE `value` = :v');
                    $status = $st->execute(array(':id'=> $id, ':k' => 'answer', ':v' => $answers));
                }
            }

            return true;
        }
    }
?>
