<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    // Session security flags
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);

    //Set timezone
    date_default_timezone_set("Europe/London");
    putenv("TZ=Europe/London");

    function __autoload($class) {
        require_once 'class.'.$class.'.php';
    }

    // Setup app
    try {
        $app = new app();
    } catch (Exception $e) {
        die($e->getMessage());
    }

    do {
        $email = $app->email->getNext();
        if ($email) {
            // build body
            $template = file_get_contents('elements/email_template.html', true);

            $vars = array(
                '{username}' => $email->username,
                '{message}' => $email->body
            );

            $email->body = str_replace(array_keys($vars), $vars, $template);

            $app->email->send($email);
        }
    } while ($email);
?>