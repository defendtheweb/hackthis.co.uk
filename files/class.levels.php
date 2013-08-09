<?php
    class levels {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function getList() {
            $st = $this->app->db->prepare('SELECT CONCAT(levels_groups.title, " Level ", levels.name) as `title`, levels.name, levels_groups.title as `group`,
            	    LOWER(CONCAT("/levels/", CONCAT_WS("/", levels_groups.title, levels.name))) as `uri`,
            	    IF(users_levels.completed > 0, 1, 0) as `completed`
                    FROM levels
                    INNER JOIN levels_groups
                    ON levels_groups.title = levels.group
                    LEFT JOIN users_levels
                    ON users_levels.user_id = :uid AND users_levels.level_id = levels.level_id
                    ORDER BY levels_groups.order ASC');
            $st->bindValue(':uid', $this->app->user->uid);
            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }
    }
?>