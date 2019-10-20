<?php

    class ldap {
        private $app;
        private $connected = false;
        private $authenticated = false;
        
        public function __construct($app) {
            $this->app = $app;
            $this->config = $config = $this->app->config['ldap'];
        }
        
        public function isEnabled() {
            return $this->config['enabled'];
        }

        private function bind($authenticated=false) {
            if ($this->connected) {
                if ($authenticated && !$this->authenticated) {
                    ldap_bind($this->connection, $this->config['username'] . ',' . $this->config['dn'], $this->config['password']);
                }
            
                return;
            }
            
            ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
             
            try {
                $connection = ldap_connect($this->config['host']);
                
                // Change protocol
                ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

                if ($authenticated) {
                    ldap_bind($connection, $this->config['username'] . ',' . $this->config['dn'], $this->config['password']);
                }

                // Start TLS
                // ldap_start_tls($connection);
            } catch (Exception $e) {
                print_r($e);
                return;
            }

            $this->connected = true;
            $this->connection = $connection;
        }
        
        public function checkLogin($username, $password) {
            if (!$this->isEnabled()) return false;
            if (!$username || !$password) return false;

            $username = $this->escapeUsername($username);
            if (!$username) return false;
       
            $this->bind();

            $dn = 'cn=' . $username . ',' . $this->config['dn'];
            
            $authenticated = ldap_bind($this->connection, $dn, $password);
            if (!$authenticated) {
                return false; // User details where invalid
            }
            
            $result = ldap_search($this->connection, $this->config['dn'], 'cn= ' . $username);
            if (!$result) {
                return false; // Couldn't find user
            }

            $info = ldap_get_entries($this->connection, $result);
            $user_id = intval($info[0]['uid'][0]);
            if (!$user_id) {
                return false; // No user_id defined, or invalid
            }
            
            return $user_id; // Login successful
        }
        
        public function createUser($user_id, $username, $password) {
            if (!$this->isEnabled()) return false;
            $username = $this->escapeUsername($username);
            if (!$username) return false;
            
            $this->bind(true);
        
            $info = array();
        
            // prepare data
            $info['cn'] = $username;
            $info['sn'] = $username;
            $info['objectClass'][0] = "top";
            $info['objectClass'][1] = "person";
            $info['objectClass'][2] = "inetOrgPerson";
            $info['uid'] = $user_id;
            $info['userPassword'] = $encodedPassword = "{SHA}" . base64_encode(pack("H*", sha1($password)));

            $dn = 'cn=' . $username . ',' . $this->config['dn'];

            // add data to directory
            return ldap_add($this->connection, $dn, $info);
        }
        
        public function changePassword($username, $newPassword) {
            if (!$this->isEnabled()) return false;
            if (!$username || !$newPassword) return false;
        
            $username = $this->escapeUsername($username);
            if (!$username) return false;
        
	    $this->bind(true);

            // Check old password was correct
//            if ($this->checkLogin($username, $oldPassword)) {
//                return "Old password is incorrect";
//            }
        
            // Set password  
            $encodedPassword = "{SHA}" . base64_encode(pack("H*", sha1($newPassword)));
            $entry = array();
            $entry["userPassword"] = $encodedPassword;
            
            $dn = 'cn=' . $username . ',' . $this->config['dn'];
    
            return ldap_modify($this->connection, $dn, $entry);
        }
        
        private function escapeUsername($username) {
            if ($this->app->utils->check_user($username)) {
                return $username;
            } else {
                return false;
            }
        }

    }
    
?>
