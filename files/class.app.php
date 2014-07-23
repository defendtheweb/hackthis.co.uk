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
            $this->config['log'] = $this->config['path'] . "/files/log/";

            // Connect to database
            $this->connectDB($this->config['db']);

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

                // Load Twig
                require_once($this->config['path'] . '/files/vendor/Twig/Autoloader.php');
                Twig_Autoloader::register();

                $loader = new Twig_Loader_Filesystem($this->config['path'] . "/files/templates/");
                $this->twig = new Twig_Environment($loader, array(
                    // 'cache' => $this->config['path'] . "/files/cache/twig/",
                    'cache' => false,
                    'autoescape' => false
                ));


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
			function fixnewlines($bbcode, $action, $name, $default, $params, $content){
				if ($action !== BBCODE_OUTPUT) return true;
				return preg_replace('/<br(?: \/)?>'."\n".'/',"\n",$content);
			}
			$this->bbcode->AddRule('code',  Array(
				'mode' => BBCODE_MODE_CALLBACK,
				'method' => 'fixnewlines',
                'template' => "<br/>\n<div class=\"bbcode_code\">\n<div class=\"bbcode_code_head\">Code:</div>\n<pre class=\"bbcode_code_body prettyprint\" style=\"overflow: hidden\">{\$_content/v}</pre>\n</div>\n",
                'class' => 'code',
                'allow_in' => Array('listitem', 'block', 'columns'),
                'before_tag' => "sns",
                'after_tag' => "sn",
                'before_endtag' => "sn",
                'after_endtag' => "sns",
                'plain_start' => "\n<b>Code:</b>\n",
                'plain_end' => "\n",

			));
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
        private function connectDB($config, $debug=false) {
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
