<?php
    class donations {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function getAll() {
            $st = $this->app->db->prepare("SELECT users.username, donations.amount, donations.time
                FROM users_donations donations
                LEFT JOIN users
                ON users.user_id = donations.user_id
                ORDER BY `time` DESC");

            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }

    }
?>    