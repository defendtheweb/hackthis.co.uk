<?php
    class utils {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function parse($text, $bbcode=true, $mentions=true, $twitterfy=true) {
            if ($bbcode) {
                $text = $this->app->bbcode->Parse($text);
                if ($mentions) {
                    $text = preg_replace_callback("/(?:(?<=\s)|^)@(\w*[0-9A-Za-z_.-]+\w*)/", array($this, 'mentions_callback'), $text);
                }

                if ($twitterfy) {
                    $text = preg_replace_callback("/\[tweet\](.*?)\[\/tweet\]/is", array($this, 'twitterfy_callback'), $text);
                }
            } else {
                //$text = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $text); // Strip bbcode
                $text = trim(preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $text)); // Strip bbcode
                $text = htmlspecialchars($text);
                //$text = nl2br($text);
            }

            return $text;
        }

        private function mentions_callback($matches) {
            $mention = $matches[1];

            $st = $this->app->db->prepare('SELECT username FROM users WHERE username = :username LIMIT 1');
            $st->execute(array(':username' => $mention));
            
            if ($res = $st->fetch())
                return "<a href='/user/{$res->username}'>{$res->username}</a>";

            return $matches[0];
        }

        private function twitterfy_callback($matches) {
            return $this->twitterfy($matches[1]);
        }

        public function twitterfy($id) {
            if (strstr($id, '/')) {
                $id = basename($id);
            }

            $file = 'tweet_'.$id;
            $cache = $this->app->cache->get($file);
            if ($cache)
                return $cache;

            $uri = "https://api.twitter.com/1/statuses/oembed.json?id={$id}";
            $content = @file_get_contents($uri);
            if (!$content) {
                return false;
            }
            $details = json_decode($content);
            $html = $details->html;

            $this->app->cache->set($file, $html);

            return $html;
        }

        public function repairUri($uri) {
            if ($ret = parse_url($uri)) {
                if (!isset($ret["scheme"]))
                   $uri = "http://{$uri}";
            } else {
                $uri = false;
            }
            return $uri;
        }

        function generateSlug($phrase) {
            $result = strtolower($phrase);
            $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
            $result = trim(preg_replace("/[\s-]+/", " ", $result));
            $result = trim(substr($result, 0, 125));
            $result = preg_replace("/\s/", "-", $result);
            return $result;
        }

        public function userLink($username) {
            return "<a href='/user/{$username}'>{$username}</a>";
        }

        public function check_user($str) {
            if (strlen($str) <= 16 && strlen($str) > 3) {
                if (preg_match('/[^0-9A-Za-z_.-]/', $str))
                    return false;

                if ($str[0] == ".")
                    return false;

                if ($str[strlen($str)-1] == ".")
                    return false;
            } else {
                return false;
            }

            return true;
        }

        public function check_email($email) {
            // First, we check that there's one @ symbol, 
            // and that the lengths are right.
            if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
            // Email invalid because wrong number of characters 
            // in one section or wrong number of @ symbols.
                return false;
            }
            // Split it into sections to make life easier
            $email_array = explode("@", $email);
            $local_array = explode(".", $email_array[0]);
            for ($i = 0; $i < sizeof($local_array); $i++) {
                if (!preg_match("/^(([A-Za-z0-9!#$%&'*+=?^_`{|}~-][A-Za-z0-9!#$%&
                     ?'*+=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
                        return false;
                }
            }
            // Check if domain is IP. If not, 
            // it should be valid domain name
            if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) {
                    $domain_array = explode(".", $email_array[1]);
                    if (sizeof($domain_array) < 2) {
                        return false; // Not enough parts to domain
                    }
                    for ($i = 0; $i < sizeof($domain_array); $i++) {
                        if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
                             ?([A-Za-z0-9]+))$/", $domain_array[$i])) {
                                return false;
                        }
                    }
            }
            return true;
        }

        public function search_users($username, $limit=0) {
            $username .= '%';

            $sql = 'SELECT u.username, IFNULL(friends.status, 0) AS friends
                    FROM users u
                    LEFT JOIN users_friends friends
                    ON (friends.user_id = u.user_id AND friends.friend_id = :user) OR (friends.user_id = :user AND friends.friend_id = u.user_id)
                    WHERE u.username LIKE :username AND u.user_id != :user
                    ORDER BY friends DESC, u.username
                    LIMIT :limit';
            $st = $this->app->db->prepare($sql);
            $st->bindValue(':username', $username);
            $st->bindValue(':user', $this->app->user->uid);
            $st->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $st->execute();

            return $st->fetchAll();
        }

        public function fdate($datetimestring = '1970-01-01 00:00:00', $format = 'c') {
            $dt = new DateTime($datetimestring);
            return $dt->format($format);
        }

        public function timeSince($date, $short=false, $forceSince=false) {
            $date = strtotime($date);

            $diff = time() - $date;
            
            if (!$diff)
                return "secs" . (!$short?' ago':'');

            $isSameDay = (date('d-m-Y', $date) === date('d-m-Y'));

            if ($isSameDay || $forceSince) {
                if ($diff < 60)
                    return "secs" . (!$short?' ago':'');
                else if ($diff < 3600) {
                    $n = floor($diff/60);
                    return "{$n} min" . ($n==1?'':'s') . (!$short?' ago':''); 
                } else if ($diff < 86400) {
                    $n = floor($diff/3600);
                    return "{$n} hour" . ($n==1?'':'s') . (!$short?' ago':''); 
                } else if ($diff < 604800) {
                    $n = floor($diff/86400);
                    return "{$n} day" . ($n==1?'':'s') . (!$short?' ago':''); 
                } else if ($diff < 2630000) {
                    $n = floor($diff/604800);
                    return "{$n} week" . ($n==1?'':'s') . (!$short?' ago':''); 
                } else if ($diff < 31560000) {
                    $n = floor($diff/2630000);
                    return "{$n} month" . ($n==1?'':'s') . (!$short?' ago':''); 
                } else {
                    $n = floor($diff/31560000);
                    return "{$n} year" . ($n==1?'':'s') . (!$short?' ago':''); 
                }
            } else if ($short) {
                return date('d/m', $date);
            } else {
                $yesterday = (date('d-m-Y', $date) === date('d-m-Y', strtotime("yesterday")));

                if ($yesterday)
                    return "Yesterday";
                else {
                    $thisWeek = ($date > strtotime("-6 days"));

                    if ($thisWeek)
                        return date('l', $date);
                    else
                        return date('F j, Y', $date);
                }
            }
        }

        public function timeBetween($date, $date2) {
            $date = strtotime($date);
            $diff = strtotime($date2) - $date;

            if (!$diff || $diff < 60)
                return "secs";
            else if ($diff < 3600) {
                $n = floor($diff/60);
                return "{$n} min" . ($n==1?'':'s'); 
            } else if ($diff < 86400) {
                $n = floor($diff/3600);
                return "{$n} hour" . ($n==1?'':'s'); 
            } else {
                $n = floor($diff/86400);
                return "{$n} day" . ($n==1?'':'s'); 
            }
        }


        public function html_cut($text, $max_length) {
            $tags   = array();
            $result = "";

            $is_open   = false;
            $grab_open = false;
            $is_close  = false;
            $in_double_quotes = false;
            $in_single_quotes = false;
            $tag = "";

            $i = 0;
            $stripped = 0;

            $stripped_text = strip_tags($text);

            while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
            {
                $symbol  = $text{$i};
                $result .= $symbol;

                switch ($symbol)
                {
                   case '<':
                        $is_open   = true;
                        $grab_open = true;
                        break;

                   case '"':
                       if ($in_double_quotes)
                           $in_double_quotes = false;
                       else
                           $in_double_quotes = true;

                    break;

                    case "'":
                      if ($in_single_quotes)
                          $in_single_quotes = false;
                      else
                          $in_single_quotes = true;

                    break;

                    case '/':
                        if ($is_open && !$in_double_quotes && !$in_single_quotes)
                        {
                            $is_close  = true;
                            $is_open   = false;
                            $grab_open = false;
                        }

                        break;

                    case ' ':
                        if ($is_open)
                            $grab_open = false;
                        else
                            $stripped++;

                        break;

                    case '>':
                        if ($is_open)
                        {
                            $is_open   = false;
                            $grab_open = false;
                            array_push($tags, $tag);
                            $tag = "";
                        }
                        else if ($is_close)
                        {
                            $is_close = false;
                            array_pop($tags);
                            $tag = "";
                        }

                        break;

                    default:
                        if ($grab_open || $is_close)
                            $tag .= $symbol;

                        if (!$is_open && !$is_close)
                            $stripped++;
                }

                $i++;
            }

            while ($tags)
                $result .= "</".array_pop($tags).">";

            return $result;
        }

        function message($msg, $type='error') {
            echo "                        <div class='msg msg-{$type}'>
                            <i class='icon-{$type}'></i>
                            {$msg}
                        </div>";
        }

        function get_browser() {
            $visitor_user_agent = $_SERVER["HTTP_USER_AGENT"];
            if (stristr($visitor_user_agent, 'MSIE') && !stristr($visitor_user_agent, 'Opera')) {
                $bname = 'IE';
            } elseif (stristr($visitor_user_agent, 'Firefox')) {
                $bname = "Firefox";
            } elseif (stristr($visitor_user_agent, 'Chrome')) {
                $bname = 'Chrome';
            } elseif (stristr($visitor_user_agent, 'Safari')) {
                $bname = 'Safari';
            } elseif (stristr($visitor_user_agent, 'Opera')) {
                $bname = 'Opera';
            } else {
                $bname = "Unknown";
            }

            return $bname;
        }
    }
?>