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

            // Connect to database
            $this->connectDB($this->config['db']);

            // Create page object
            $this->page = new page();

            $this->utils = new utils($this);

            $this->stats = new stats($this);

            // Create user object
            $this->user = new user($this);

            $this->notifications = new notifications($this);
            $this->feed = new feed($this);

            //get version number
            $this->cache = new cache($this);
            $this->version = substr($this->cache->get('version'), 1);

            // Create level object
            $this->levels = new levels($this);
            // Create articles object
            $this->articles = new articles($this);

            if (!is_array($custom_css))
                $custom_css = Array();
            if (!is_array($custom_js))
                $custom_js = Array();

            require('vendor/nbbc.php');
            $this->bbcode = new BBCode;
            array_push($custom_css, 'bbcode.scss');
            array_push($custom_js, 'bbcode.js');
            //$this->bbcode->SetDetectURLs(true);
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
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $this->db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
            } catch(PDOException $e) {
                die($e->getMessage());
            }
        }

        public function generateCSRFKey($key) {
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


        public function awardMedal($medalId, $uid) {
            $st = $this->db->prepare('INSERT IGNORE INTO users_medals (`user_id`, `medal_id`) VALUES (:uid, :medalId)');
            $result = $st->execute(array(':medalId' => $medalId, ':uid' => $uid));

            return (bool) $result;
        }

        public function parse($text, $bbcode=true, $mentions=true) {
            return $this->utils->parse($text, $bbcode, $mentions);
        }
    }
?>