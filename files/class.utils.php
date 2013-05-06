<?php
    class utils {
        function __construct() {

        }

        public function username_link($username) {
            return "<a href='/user/{$username}'>{$username}</a>";
        }
    }
?>