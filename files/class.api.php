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
            if (!isset($_POST['nick']) || !isset($_POST['chan']) || !isset($_POST['msg']))
                throw new Exception('Missing data fields');

            $_POST['msg'] = preg_replace('/\x01/', '', $_POST['msg']);

            $st = $this->app->db->prepare('INSERT INTO irc_logs (`nick`, `channel`, `log`)
                    VALUES (:nick, :chan, :msg)');
            $result = $st->execute(array(':nick' => $_POST['nick'], ':chan' => $_POST['chan'], ':msg' => $_POST['msg']));


            // Calculate stats
            $st = $this->app->db->prepare('INSERT INTO irc_stats (`nick`, `lines`, `words`, `chars`)
                    VALUES (:nick, :lines, :words, :chars)
                    ON DUPLICATE KEY UPDATE `lines`=`lines`+:lines, `words`=`words`+:words, `chars`=`chars`+:chars, `time`=NOW()');

            $st->bindValue(':nick', $_POST['nick'], PDO::PARAM_INT);
            $st->bindValue(':lines', 1, PDO::PARAM_INT);
            $st->bindValue(':words', str_word_count($_POST['msg']), PDO::PARAM_INT);
            $st->bindValue(':chars', strlen($_POST['msg']), PDO::PARAM_INT);
            $result = $st->execute();
        }

    }
?>