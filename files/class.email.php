<?php
    /**
     * Handles adding, queueing and sending emails.
     *
     * However it does not send emails by itself, a seperate cron based PHP file 'send_emails.php' does this.
     */
    class email {
        private $app;
        private $types = array('password', 'ticket_reply', 'forum_mention', 'forum_reply', 'friend', 'pm', 'email_confirmation', 'digest');

        public function __construct($app) {
            $this->app = $app;
        }

        /**
         * Add an email to the queue
         * @param  string  $recipient Email address of recipient
         * @param  string  $type      Type of email as defined by the DB schema. Each type relates to a different template/content
         * @param  JSON    $data      Object containing information to be used to fill in email template
         * @param  boolean $uid       User id of recipient, if applicable. Used for notification settings
         * @return boolean            Success of queue
         */
        public function queue($recipient, $type, $data, $uid=false) {
            // Check type and data
            if (!in_array($type, $this->types)) {
                return false;
            }

            if (json_decode($data) == null) {
                return false;
            }

            if ($this->app->user->loggedIn) {
                if ($uid === false)
                    $uid = $this->app->user->uid;

                $st = $this->app->db->prepare('INSERT INTO email_queue (`recipient`, `user_id`, `type`, `data`) VALUES (:rec, :uid, :type, :data)');
                return $st->execute(array(':rec' => $recipient, ':uid' => $uid, ':type' => $type, ':data' => $data));
            } else {
                $st = $this->app->db->prepare('INSERT INTO email_queue (`recipient`, `type`, `data`) VALUES (:rec, :type, :data)');
                return $st->execute(array(':rec' => $recipient, ':type' => $type, ':data' => $data));
            }
        }

        /**
         * Fetch the next email from the queue
         * @return object Email data
         */
        public function getNext() {
            $st = $this->app->db->prepare("SELECT email_queue.*, users.username
                     FROM email_queue
                     LEFT JOIN users
                     ON users.user_id = email_queue.user_id
                     WHERE (email_queue.`status` = 0 OR (email_queue.`status` > 2 AND email_queue.`status`< 9)) AND date_add(email_queue.sent, INTERVAL (status-2)*5 MINUTE) < NOW()
                     ORDER BY email_queue.`sent` ASC
                     LIMIT 1");
            $st->execute();
            $email = $st->fetch();

            if ($email) {
                // Mark email as being processed
                $this->updateStatus($email->email_id, 1);

                // Check type and get extra details
                $email->data = json_decode($email->data);

                if ($email->user_id) {
                    // Check if user wants email
                    $st = $this->app->db->prepare("SELECT * FROM users_settings WHERE user_id = :uid");
                    $st->execute(array(':uid' => $email->user_id));
                    $u = $st->fetch();

                    if ($u) {
                        if (($email->type == "pm" && $u->email_pm != '1') OR
                            ($email->type == "forum_reply" && $u->email_forum_reply != '1') OR
                            ($email->type == "forum_mention" && $u->email_forum_mention != '1') OR
                            ($email->type == "friend" && $u->email_friend != '1')) {
                                // Mark email as sent and get next available
                                $this->updateStatus($email->email_id, 2);
                                return $this->getNext();
                        }
                    }

                    // Load the users unsubscribe token
                    $email->unsubscribe = $this->app->getData('unsubscribe', $email->user_id);

                    if (!$email->unsubscribe) {
                        // Create new token
                        $email->unsubscribe = md5(openssl_random_pseudo_bytes(32));
                        $this->setData('unsubscribe', $email->unsubscribe, $email->user_id, true);
                    }
                }

                return $email;
            } else {
                return false;
            }
        }

        public function updateStatus($email_id, $status) {
            $st = $this->app->db->prepare("UPDATE email_queue
                                       SET status = :status
                                       WHERE email_id = :id");
            $st->execute(array(':id'=>$email_id, ':status'=>$status));  
        }

        public function send($email) {
            if (!isset($this->emailer)) {
                $config = $this->app->config('smtp');

                $this->emailer = new PHPMailer();
                $this->emailer->IsSMTP();
                $this->emailer->SMTPDebug = 0;
                $this->emailer->Host = $config['host'];
                $this->emailer->Port = $config['port'];
                $this->emailer->SMTPAuth = true;
                $this->emailer->Username = $config['username'];
                $this->emailer->Password = $config['password'];
                
                $this->emailer->SetFrom("no-reply@mail.hackthis.co.uk", "HackThis");
            }


            $sent = false;

            $this->emailer->ClearAllRecipients();
            $this->emailer->AddAddress($email->recipient);
            $this->emailer->Subject = $email->subject;
            $this->emailer->MsgHTML($email->body);

            $sent = $this->emailer->Send();

            if ($sent) {
                $this->updateStatus($email->email_id, 2);
            } else {
                $this->updateStatus($email->email_id, $email->status==0?3:++$email->status);
            }
        }
    }
?>