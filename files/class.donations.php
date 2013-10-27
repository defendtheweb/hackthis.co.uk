<?php
    class donations {
        private $app;
        private $sizes = array('s', 'm', 'l', 'xl', 'xxl');

        public function __construct($app) {
            $this->app = $app;
        }

        public function getAll() {
            $st = $this->app->db->prepare("SELECT users.username, donations.amount, donations.time
                FROM users_donations donations
                LEFT JOIN users
                ON users.user_id = donations.user_id
                ORDER BY `time` DESC");

            $st->execute();
            $result = $st->fetchAll();

            return $result;
        }



        public function makeTransaction($amount, $size) {
            $items = '{
                        "quantity": 1,
                        "name": "Donation",
                        "price": "'.number_format($amount,2).'",
                        "currency": "GBP"
                    }';

            if ($amount >= 20 && in_array($size, $this->sizes)) {
                $items .= ', {
                        "quantity": 1,
                        "name": "Free T-Shirt, size '.strtoupper($size).'",
                        "price": "0.00",
                        "currency": "GBP"
                    }';
            }

            $config = $this->app->config('paypal');

            // Get bearer token
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/oauth2/token');
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $config['client'] . ":" . $config['secret']);
            curl_setopt($ch, CURLOPT_SSLVERSION, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $options = array(
                CURLOPT_HEADER => true,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HTTPHEADER => array('Accept: application/json', 'Accept-Language: en_US'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_TIMEOUT => 10
            );

            $options[CURLOPT_POSTFIELDS] = "grant_type=client_credentials";
            $options[CURLOPT_CUSTOMREQUEST] = 'POST';

            curl_setopt_array($ch, $options);

            $response = curl_exec($ch);
            $header = substr($response, 0, curl_getinfo($ch,CURLINFO_HEADER_SIZE));
            $body = json_decode(substr($response, curl_getinfo($ch,CURLINFO_HEADER_SIZE)));

            curl_close($ch);

            $token = $body->access_token;


            // Make actually request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/payments/payment');

            $options = array(
                CURLOPT_HEADER => true,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HTTPHEADER => array('Content-Type:application/json', 'Authorization:Bearer '.$token),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_TIMEOUT => 10
            );

            $options[CURLOPT_POSTFIELDS] = '{
              "intent":"sale",
              "redirect_urls":{
                "return_url":"http://dev.hackthis/donator.php",
                "cancel_url":"http://dev.hackthis/donator.php"
              },
              "payer":{
                "payment_method":"paypal"
              },
              "transactions":[
                {
                  "amount":{
                    "total":"'.number_format($amount,2).'",
                    "currency":"GBP"
                  },
                  "description":"HackThis!! donation",
                  "item_list": {
                    "items": ['.$items.']
                  }
                }
              ]
            }';
            $options[CURLOPT_CUSTOMREQUEST] = 'POST';

            curl_setopt_array($ch, $options);


            $response = curl_exec($ch);
            print_r($response);

            $header = substr($response, 0, curl_getinfo($ch,CURLINFO_HEADER_SIZE));
            $body = json_decode(substr($response, curl_getinfo($ch,CURLINFO_HEADER_SIZE)));

            curl_close($ch);

            if ($body->links[1]) {
                // Set session variables
                $_SESSION['paypal_bearer'] = $token;
                $_SESSION['paypal_id'] = $body->id;

                header('Location: '.$body->links[1]->href);
            }
        }

        public function confirmPayment($token, $id) {
            // Get bearer and id from session
            if (!isset($_SESSION['paypal_bearer']) || !isset($_SESSION['paypal_id']))
                return false;

            // confirm
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/payments/payment/'.$_SESSION['paypal_id'].'/execute');

            $options = array(
                CURLOPT_HEADER => true,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_HTTPHEADER => array('Content-Type:application/json', 'Authorization:Bearer '.$_SESSION['paypal_bearer']),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_TIMEOUT => 10
            );

            $options[CURLOPT_POSTFIELDS] = '{ "payer_id" : "'.$id.'" }';
            $options[CURLOPT_CUSTOMREQUEST] = 'POST';

            curl_setopt_array($ch, $options);

            $response = curl_exec($ch);
            $header = substr($response, 0, curl_getinfo($ch,CURLINFO_HEADER_SIZE));
            $body = json_decode(substr($response, curl_getinfo($ch,CURLINFO_HEADER_SIZE)));

            curl_close($ch);

            if (isset($body->id)) {
                $this->storeDonation(floatval($body->transactions[0]->amount->total), $body->id);
                return true;
            }

            return false;
        }

        public function storeDonation($amount, $id) {
            if (!$_SESSION['donate_anon']) {
                $st = $this->app->db->prepare('INSERT INTO users_donations (`user_id`, `amount`, `id`)
                    VALUES (:uid, :amount, :id)');
                $result = $st->execute(array(':uid' => $this->app->user->uid, ':amount' => $amount, ':id' => $id));
            } else {
                $st = $this->app->db->prepare('INSERT INTO users_donations (`user_id`, `amount`, `id`)
                    VALUES (:uid, :amount, :id)');
                $result = $st->execute(array(':uid' => null, ':amount' => $amount, ':id' => $id));
            }

            if ($amount >= 5) {
                $this->app->user->awardMedal('donator', '4');
            }
        }

    }
?>    