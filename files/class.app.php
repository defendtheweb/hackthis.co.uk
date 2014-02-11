<?php
    class app {
        public $bbcode;
        public $max_score = 4000;

        function __construct() {
            global $custom_css, $custom_js;

            //load configuration file
            require('config.php');
            if (!isset($config) || !is_array($config))
                throw new Exception('Config error');

            $this->config = $config;
            $this->config['cache'] = $this->config['path'] . "/files/cache/";
            $this->config['log'] = $this->config['path'] . "/files/log/";

            // Connect to database
            $this->connectDB($this->config['db']);

            //get version number
            $this->cache = new cache($this);
            $this->log = new log($this);
            $this->version = substr($this->cache->get('version'), 1);

            //get max score
            $this->max_score = $this->getMaxScore();


            // Setup google events class
            require('vendor/class.ss-ga.php');
            if (isset($this->config['ssga-ua'])) {
                $this->ssga = new ssga($this->config['ssga-ua'], $this->config['domain']);
            } else {
                $this->ssga = new ssga();
            }

            // Create page object
            $this->page = new page();

            $this->utils = new utils($this);

            $this->stats = new stats($this);
            $this->feed = new feed($this);
            $this->ticker = new ticker($this);
            $this->email = new email($this);

            // Create user object
            $this->user = new user($this);

            $this->notifications = new notifications($this);

            // Create level object
            $this->levels = new levels($this);
            // Create articles object
            $this->articles = new articles($this);
            // Create forum object
            $this->forum = new forum($this);

            if (!is_array($custom_css))
                $custom_css = Array();
            if (!is_array($custom_js))
                $custom_js = Array();

            require('vendor/nbbc.php');
            $this->bbcode = new BBCode;
            array_push($custom_css, 'bbcode.scss');
            array_push($custom_js, 'bbcode.js');
            $this->bbcode->SetDetectURLs(true);
        }

        public function config($key) {
            return $this->config[$key];
        }

        private function connectDB($config) {
            // Connect to database
            try {
                $dsn = "{$config['driver']}:host={$config['host']}";
                $dsn .= (!empty($config['port'])) ? ';port=' . $config['port'] : '';
                $dsn .= ";dbname={$config['database']}";
                $this->db = new PDO($dsn, $config['username'], $config['password']);
                // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $this->db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
            } catch(PDOException $e) {
                die($e->getMessage());
            }
        }

        private function getMaxScore() {
            $score = $this->cache->get('maxscore', 60);

            if (!$score) {
                // Level total
                $sql = 'SELECT SUM(`value`) AS `score`
                        FROM levels
                        INNER JOIN levels_data
                        ON levels_data.level_id = levels.level_id AND levels_data.key = "reward"';
                $st = $this->db->prepare($sql);
                $st->execute();
                $levels = $st->fetch();

                // Medal total
                $sql = 'SELECT SUM(`reward`) AS `score`
                        FROM medals
                        INNER JOIN medals_colours
                        ON medals_colours.colour_id = medals.colour_id';
                $st = $this->db->prepare($sql);
                $st->execute();
                $medals = $st->fetch();

                $score = $levels->score + $medals->score;

                $this->cache->set('maxscore', $score);
            }

            return $score;
        }

        public function generateCSRFKey($key, $reuseKey=false) {
            if ($reuseKey && isset($_SESSION['csrf_' . $key]))
                return $_SESSION['csrf_' . $key];
            
            $token = base64_encode( openssl_random_pseudo_bytes(16));
            $_SESSION[ 'csrf_' . $key ] = $token;
            return $token;
        }

        public function checkCSRFKey($key, $value) {
            if (!isset($_SESSION['csrf_' . $key]))
                return false;
            if (!$value)
                return false;

            if ($_SESSION['csrf_' . $key] !== $value)
                return false;

            unset($_SESSION['csrf_' . $key]); 
            return true;
        }

        public function parse($text, $bbcode=true, $mentions=true) {
            return $this->utils->parse($text, $bbcode, $mentions);
        }
    }
?>
