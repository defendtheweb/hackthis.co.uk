<?php
    class app {

        function __construct($minimal = false) {
            global $custom_css, $custom_js;

            //load configuration file
            require('config.php');
            if (!isset($config) || !is_array($config))
                throw new Exception('Config error');

            $this->config = $config;
            $this->config['cache'] = $this->config['path'] . "/files/cache/";
            $this->config['log'] = $this->config['path'] . "/files/logs/";

            // Connect to database
            $this->connectDB($this->config['db'], false);

            // Connect to LDAP
            $this->ldap = new ldap($this);

            //get version number
            $this->cache = new cache($this);
            $this->log = new log($this);
            $this->version = substr($this->cache->get('version'), 1);

            //get max score
            $this->max_score = $this->getMaxScore();


            if (!$minimal) {
                //set theme
                $this->getTheme();

                // Setup google events class
                require('vendor/class.ss-ga.php');
                if (isset($this->config['ssga-ua'])) {
                    $this->ssga = new ssga($this->config['ssga-ua'], $this->config['domain']);
                } else {
                    $this->ssga = new ssga();
                }

                $this->initTwig();

                // Create page object
                $this->page = new page();
            }

            $this->utils = new utils($this);

            $this->stats = new stats($this);
            $this->feed = new feed($this);
            $this->ticker = new ticker($this);
            $this->email = new email($this);

            // Create user object
            $this->user = new user($this);

            // Create admin object
            if ($this->user->admin_priv) {
                $this->admin = new admin($this);
            }

            $this->notifications = new notifications($this);

            // Create level object
            $this->levels = new levels($this);
            // Create articles object
            $this->articles = new articles($this);
            // Create forum object
            $this->forum = new forum($this);
            // Create RSS object
            $this->rss = new rss();

            if (!is_array($custom_css))
                $custom_css = Array();
            if (!is_array($custom_js))
                $custom_js = Array();

            array_push($custom_css, 'bbcode.scss');
            array_push($custom_js, 'bbcode.js');

            $this->initBBC();
        }

        public function config($key) {
            return $this->config[$key];
        }

        /**
         * Create database connection
         *
         * @param object $config Databse connection config
         * @param boolean $debug Should the connection ignore errors or throw exceptions
         *
         * @return void
         *
         * @todo Create site config option that is passed in to the debug param
         */
        protected function connectDB($config, $debug=true) {
            // Connect to database
            try {
                $dsn = "{$config['driver']}:host={$config['host']}";
                $dsn .= (!empty($config['port'])) ? ';port=' . $config['port'] : '';
                $dsn .= ";dbname={$config['database']}";
                $this->db = new PDO($dsn, $config['username'], $config['password']);

                if ($debug) {
                    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }

                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $this->db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
            } catch(PDOException $e) {
                die($e->getMessage());
            }
        }

        /**
         * Initaite BBCode parser
         *
         * @param none
         *
         * @return void
         */
        private function initBBC(){
            require('vendor/nbbc.php');
            $this->bbcode = new BBCode;
            $this->bbcode->SetDetectURLs(true);
        }


        /**
         * Initaite Twig parser
         *
         * @param none
         *
         * @return void
         */
        private function initTwig() {
            // Load Twig
            require_once($this->config['path'] . '/files/vendor/Twig/Autoloader.php');
            Twig_Autoloader::register();

            $loader = new Twig_Loader_Filesystem($this->config['path'] . "/files/templates/");
            $this->twig = new Twig_Environment($loader, array(
                // 'cache' => $this->config['path'] . "/files/cache/twig/",
                'cache' => false,
                'autoescape' => false
            ));

            $wysiwyg = new Twig_SimpleFunction('wysiwyg', function ($name="", $placeholder="", $text="") {
                $wysiwyg_name = $name;
                $wysiwyg_placeholder = $placeholder;
                $wysiwyg_text = $text;
                include('elements/wysiwyg.php');
            });
            $this->twig->addFunction($wysiwyg);

            $csrf = new Twig_SimpleFunction('CSRFKey', function ($name) {
                echo $this->generateCSRFKey($name);
            });
            $this->twig->addFunction($csrf);

            $msg = new Twig_SimpleFunction('msg', function ($text, $type="error") { 
                $this->utils->message($text, $type);
            });
            $this->twig->addFunction($msg);

            $this->twig->addFilter('floor', new Twig_Filter_Function('floor'));
            $this->twig->addFilter('ceil', new Twig_Filter_Function('ceil'));

            $since = new Twig_Filter_Function(function ($time) {
                return $this->utils->timeSince($time);
            });
            $this->twig->addFilter('since', $since);

            $sinceShort = new Twig_Filter_Function(function ($time) {
                return $this->utils->timeSince($time, false, true);
            });
            $this->twig->addFilter('sinceShort', $sinceShort);

            $getImg = new Twig_SimpleFunction('getImg', function ($img, $size=48, $gravatar=false) { 
                echo profile::getImg($img, $size, $gravatar);
            });
            $this->twig->addFunction($getImg);

            $include = new Twig_SimpleFunction('include', function ($file) {
                $app = $this;
                include($file);
            });
            $this->twig->addFunction($include);

            $printForumSection = new Twig_SimpleFunction('printForumSection', function ($section) {
                $this->forum->printSectionsList($section, true);
            });
            $this->twig->addFunction($printForumSection);
        }


        /**
         * Get the maximum score any user can obtain from solving levels.
         *
         * @return int Maximum score
         */
        public function getMaxLevelsScore() {
            $sql = 'SELECT SUM(`value`) AS `score`
                    FROM levels
                    INNER JOIN levels_data
                    ON levels_data.level_id = levels.level_id AND levels_data.key = "reward"';
            $st = $this->db->prepare($sql);
            $st->execute();
            $row = $st->fetch();

            return $row->score;
        }

        /**
         * Get the maximum score any user can obtain. This value is cached and recalculated every 1 hour
         *
         * @return int Maximum score
         */
        private function getMaxScore() {
            $score = $this->cache->get('maxscore', 60);

            if (!$score) {
                // Level total
                $levels_score = $this->getMaxLevelsScore();

                // Medal total
                $sql = 'SELECT SUM(`reward`) AS `score`
                        FROM medals
                        INNER JOIN medals_colours
                        ON medals_colours.colour_id = medals.colour_id';
                $st = $this->db->prepare($sql);
                $st->execute();
                $medals = $st->fetch();

                $score = $levels_score + $medals->score;

                $this->cache->set('maxscore', $score);
            }

            return $score;
        }

        public function getTheme() {
            if (isset($_COOKIE["theme"]) && ($_COOKIE["theme"] == 'light' || $_COOKIE["theme"] == 'dark')) {
                $this->theme = $_COOKIE["theme"];
            } else {
                $this->theme = 'dark';
            }
        }

        public function setTheme($theme) {
            if ($theme != 'light' && $theme != 'dark') {
                return;
            }

            $this->theme = $theme;
            setcookie("theme", $theme);
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
