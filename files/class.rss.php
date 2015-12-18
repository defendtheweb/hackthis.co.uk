<?php
    abstract class enumBase {
        final public function __construct($value) {
            $refClass = new ReflectionClass($this);

            if (!in_array($value, $refClass->getConstants()))
                throw IllegalArgumentException();

            $this->value = $value;
        }

        final public function __toString() {
            return $this->value;
        }
    }

    class feedType extends enumBase {
        const ATOM = "atom";
        const RSS = "rss";
    }

    class feedCategory extends enumBase {
        const ARTICLE = "Article";
        const FORUM = "Forum";
        const NEWS = "News";
    }

    class rss {
        public function __construct() {
            //load configuration file
            require('config.php');

            if (!isset($config) || !is_array($config))
                throw new Exception('Config error');

            $this->config = $config;
            $this->connectDB($this->config['db'], false);
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

                if ($debug)
                    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $this->db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
            } catch(PDOException $e) {
                die($e->getMessage());
            }
        }

        /**
         * Store a new feed item in the DB
         *
         * @param string $title Feed title
         * @param string $link Link to the thread/news item/article
         * @param string $description A short descriptive text
         * @param feedCategory $category To allow RSS readers to cathegorize the feeds
         *
         * @return Boolean
         *
         * @todo Create everything, pubDate will be done using SQL Now()
         */
        public function storeRSS($title, $link, $description, $category) {
            // TO-DO
            return True;
        }

        /**
         * Create and parse RSS/ATOM feed
         *
         * @param feedType $type To determine output method
         *
         * @return array
         */
        public function generateRSS($type) {
            $sql = 'SELECT * FROM rss_feed ORDER BY id DESC';
            $st = $this->db->prepare($sql);
            $st->execute();
            $result = $st->fetchAll();

            if ($type == feedType::RSS) {
                $data = '<?xml version="1.0" encoding="UTF-8" ?>';
                $data .= '<?xml-stylesheet type="text/css" href="../files/css/rss.css" ?>';
                $data .= '<rss version="2.0">';
                $data .= '<channel>';
                $data .= '<title>HackThis!! RSS</title>';
                $data .= '<link>https://www.hackthis.co.uk/</link>';
                $data .= '<description><![CDATA[Want to learn about hacking, hackers and network security. Try our hacking challenges or join our community to discuss the latest software and cracking tools.]]></description>';
                $data .= '<language>en-gb</language>';

                foreach ($result as $row) {
                    $data .= '<item>';
                    $data .= '<title>'.$row->title.'</title>';
                    $data .= '<link>'.$row->link.'</link>';
                    $data .= '<description>'.$row->description.'</description>';
                    $data .= '<category>'.$row->category.'</category>';
                    $data .= '<pubDate>'.$row->pubDate.'</pubDate>';
                    $data .= '</item>';
                }

                $data .= '</channel>';
                $data .= '</rss> ';
            } elseif ($type == feedType::ATOM) {
                $data = '<?xml version="1.0" encoding="utf-8" ?>';
                $data .= '<?xml-stylesheet type="text/css" href="../files/css/rss.css" ?>';
                $data .= '<feed xmlns="http://www.w3.org/2005/Atom">';
                $data .= '<title>HackThis!! ATOM</title>';
                $data .= '<link href="https://www.hackthis.co.uk/" />';
                $data .= '<subtitle>Want to learn about hacking, hackers and network security. Try our hacking challenges or join our community to discuss the latest software and cracking tools.</subtitle>';
                $data .= '<language>en-gb</language>';

                foreach ($result as $row) {
                    $data .= '<entry>';
                    $data .= '<title>'.$row->title.'</title>';
                    $data .= '<link href="'.$row->link.'" />';
                    $data .= '<updated>'.$row->pubDate.'</updated>';
                    $data .= '<summary>'.$row->description.'</summary>';
                    $data .= '<category term=">'.$row->category.'" />';
                    $data .= '</entry>';
                }

                $data .= '</feed> ';
            } else {
                $data = null;
                throw IllegalArgumentException();
            }

            return $data;
        }
    }
?>
