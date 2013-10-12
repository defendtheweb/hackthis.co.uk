<?php
    class page {
        public $title = '';
        public $meta = array();

        public function __construct() {
            global $page_title;

            if (isset($page_title))
                $this->title = $page_title;
        }
    }
?>