<?php
    class api {

        public function __construct($app, $key) {
            if ($app->config('api') != $key)
                throw new Exception('Invalid API key');

            $this->app = $app;
        }

        public function process() {
            if (!isset($_GET['action']))
                throw new Exception('Invalid request');

            switch ($_GET['action']) {
                case 'irc.log': $this->logIrc(); break;
                default: throw new Exception('Invalid request');
            }
        }



        /* IRC */
        public function logIrc() {
            if (!isset($_POST['data']))
                throw new Exception('Missing data field');

            $data = json_decode($_POST['data']);

            if (!$data || !isset($data->nick) || !isset($data->chan) || !isset($data->msg))
                throw new Exception('Invalid data field');

            $st = $this->app->db->prepare('INSERT INTO irc_logs (`nick`, `channel`, `log`)
                    VALUES (:nick, :chan, :msg)');
            $result = $st->execute(array(':nick' => $data->nick, ':chan' => $data->chan, ':msg' => $data->msg));


            // Calculate stats
            $st = $this->app->db->prepare('INSERT INTO irc_stats (`nick`, `lines`, `words`, `chars`)
                    VALUES (:nick, :lines, :words, :chars)
                    ON DUPLICATE KEY UPDATE `lines`=`lines`+:lines, `words`=`words`+:words, `chars`=`chars`+:chars, `time`=NOW()');

            $st->bindValue(':nick', $data->nick, PDO::PARAM_INT);
            $st->bindValue(':lines', 1, PDO::PARAM_INT);
            $st->bindValue(':words', str_word_count($data->msg), PDO::PARAM_INT);
            $st->bindValue(':chars', strlen($data->msg), PDO::PARAM_INT);
            $result = $st->execute();
        }

    }
?>