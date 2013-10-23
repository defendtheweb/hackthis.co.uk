<?php
    class email {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function queue($recipient, $subject, $body) {
            $st = $this->app->db->prepare('INSERT INTO email_queue (`recipient`, `subject`, `body`) VALUES (:rec, :sub, :body)');
            return $st->execute(array(':rec' => $recipient, ':sub' => $subject, ':body' => $body));
        }

        public function getNext() {
            $st = $this->app->db->prepare("SELECT *
                     FROM email_queue
                     WHERE (`status` = 0 OR (`status` > 2 AND `status`< 9)) AND date_add(sent, INTERVAL (status-2)*5 MINUTE) < NOW()
                     ORDER BY `sent` ASC
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

            $this->emailer->AddAddress("flabbyrabbit@gmail.com");
            //$this->emailer->AddAddress($email->recipient);
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