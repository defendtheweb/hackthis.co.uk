<?php
    class page {
        public $title = '';

        public function __construct() {
            global $page_title;

            if (isset($page_title))
                $this->title = $page_title;
        }
    }
?>