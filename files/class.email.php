<?php
    class email {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function queue($recipient, $subject, $body, $uid=false) {
            if ($this->app->user->loggedIn) {
                if ($uid === false)
                    $uid = $this->app->user->uid;

                $st = $this->app->db->prepare('INSERT INTO email_queue (`recipient`, `user_id`, `subject`, `body`) VALUES (:rec, :uid, :sub, :body)');
                return $st->execute(array(':rec' => $recipient, ':uid' => $uid, ':sub' => $subject, ':body' => $body));
            } else {
                $st = $this->app->db->prepare('INSERT INTO email_queue (`recipient`, `subject`, `body`) VALUES (:rec, :sub, :body)');
                return $st->execute(array(':rec' => $recipient, ':sub' => $subject, ':body' => $body));
            }
        }

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