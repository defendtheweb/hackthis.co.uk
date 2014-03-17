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
            // build template
            $template = file_get_contents('elements/emails/template.html', true);

            // build content
            if ($email->type == "password") {
                $email->subject = "Password request";
                $content = file_get_contents('elements/emails/plain_message.html', true); 

                $body = "We received a request for your HackThis!! account details.<br/><br/>Username: {$email->data->username}<br/>To reset your password, click on this link: <a href='http://www.hackthis.co.uk/?request={$email->data->token}'>http://www.hackthis.co.uk/?request={$email->data->token}</a><br/><br/>If you feel you have received this message in error, delete this email. Your password can only be reset via this email.";

                $vars = array(
                    '{message}' => $body
                );
            } else if ($email->type == "email_confirmation") {
                $email->subject = "Confirm your email address";
                $content = file_get_contents('elements/emails/plain_message.html', true); 

                $body = "Click on the following link to verify your e-mail address:<br/><a style='color:#ffffff; text-decoration: none;' href='https://www.hackthis.co.uk/settings/account.php?verify={$email->data->token}'>https://www.hackthis.co.uk/settings/account.php?verify={$email->data->token}</a>";

                if ($email->data->new) {
                    $body = "Thank you for signing up for a <a style='color:#ffffff; text-decoration: none;' href='https://www.hackthis.co.uk/'>HackThis!!</a> account.<br/><br/>" . $body;
                }

                $vars = array(
                    '{message}' => $body
                );
            } else if ($email->type == "ticket_reply") {
                $email->subject = "Ticket reply";
                $content = file_get_contents('elements/emails/plain_message.html', true); 

                $body = "A reply has been added to a ticket you opened. To view the message please click the following link:<br/><a style='color:#ffffff; text-decoration: none;' href='https://www.hackthis.co.uk/contact?view={$email->data->id}";

                if ($email->data->email)
                    $body .= "&email={$email->data->email}";

                $body .= "'>https://www.hackthis.co.uk/contact?view={$email->data->id}";

                if ($email->data->email)
                    $body .= "&email={$email->data->email}";

                $body .= "</a>";

                $vars = array(
                    '{message}' => $body
                );
            } else if ($email->type == "friend") {
                $email->subject = "Friend request";
                $content = file_get_contents('elements/emails/friend_request.html', true); 

                $vars = array(
                    '{username}' => $email->data->username,
                    '{score}' => $email->data->score,
                    '{posts}' => $email->data->posts,
                    '{image}' => $email->data->image
                );
            } else if ($email->type == "forum_reply" || $email->type == "forum_mention") {
                $email->subject = "Forum reply";
                $content = file_get_contents('elements/emails/forum.html', true); 

                $vars = array(
                    '{username}' => $email->data->username,
                    '{post}' => $app->parse($email->data->post, false),
                    '{title}' => $app->parse($email->data->title, false),
                    '{uri}' => $email->data->uri
                );
            } else {
                return false;
            }

            if (isset($email->unsubscribe)) {
                $vars['{email}'] = $email->recipient;
                $vars['{unsubscribe}'] = $email->unsubscribe;
            }
            
            $content = str_replace(array_keys($vars), $vars, $content);

            $email->body = str_replace('{content}', $content, $template);

            $app->email->send($email);
        }
    } while ($email);
?>