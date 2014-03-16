<?php
    define("PAGE_PUBLIC", true);
    $page_title = 'Unsubscribe';
    require 'init.php';
    
    if ($app->user->loggedIn) {
        die(header('Location: /'));
    }
    
    $minifier->add_file('home.scss', 'css');

    require('header.php');

    /*
     * Status
     * 0: invalid hash
     * 1: correct hash
     * 2: unsubscribed
     * 3: error
     */
    $status = 0;

    /*
        $token = md5(openssl_random_pseudo_bytes(32));
        $this->setData('reset', $token, $row->user_id, true);
     */


    if (isset($_GET['hash']) && isset($_GET['email'])) {
        // get user id from hash
        $tmpuser = $app->user->checkData("unsubscribe", $_GET['hash'], false, null);

        // check if user id matches 
        if ($tmpuser->email === $_GET['email']) {
            $status = 1;
            if (isset($_POST['confirm'])) {
                // remove
                if ($app->user->changeNotificationSettings($tmpuser->user_id, true)) {
                    $status = 2;

                    // unset data
                    $app->user->removeData("unsubscribe", $tmpuser->user_id);
                } else {
                    $status = 3;
                }
            }
        }
    }
?>
    <section class='row clr home'>
        <div class='col span_20 home-module center'>
<?php
    if ($status === 1):
?>
            <h4>Are you sure you want to unsubscribe from <strong>all</strong> future email notifications?</h4><br/>
            <form method="POST">
                <input type="hidden" name="confirm"/>
                <input type="submit" class='button left' style='font-size: 1em' value='Unsubscribe'/>
            </form><br/>
<?php
    elseif ($status === 2):
?>
            <h4>You have been successfully unsubscribed from receiving further e-mail messages from HackThis!!</h4>
            You can switch emails back on and control which emails you receive by logging in and navigating to settings.<br/><br/><br/>
            <a href='/auth.php' class='button'>Login</a>
            <br/><br/>
<?php
    elseif ($status === 3):
?>
            <h4>Something went terribly wrong, <a href='/contact'>please let us know</a></h4>
<?php
    else:
?>
            <h4>Invalid request, <a href='/contact'>please let us know</a></h4>
<?php
    endif;
?>
        </div>
    </section>
<?php
    require 'footer.php';
?>
