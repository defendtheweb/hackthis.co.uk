<?php
    $custom_js = array('profile.js');
    $custom_css = array('settings.scss', 'profile.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Account';

    if (isset($_GET['password'])) {
        $changed = $app->user->changePassword($_POST['newpassword1'], $_POST['newpassword1']);
    } else if (isset($_GET['delete']) && isset($_POST['delete']) && isset($_POST['token'])) {
        $status = $app->user->delete($_POST['delete'], $_POST['token']);

        if ($status === true) {
            header('Location: /?deleted');
            die();
        }
    } else if (!$app->user->verified && isset($_GET['verify'])) {
        if (empty($_GET['verify'])) {
            $verifySent = $app->user->sendVerficationEmail();
        } else {
            $verifyConfirmed = $app->user->confirmVerification($_GET['verify']);
        }
    }

    require_once('header.php');

    $tab = 'account';
    include('elements/tabs_settings.php');

    if (isset($changed)) {
        $app->utils->message($changed===true?'Password changed':$changed, $changed===true?'good':'error');
    } if (isset($status)) {
        $app->utils->message($status);
    } else if ($app->user->connect_msg) {
        $app->utils->message($app->user->connect_msg, 'info');
    }

    if (isset($verifySent) && $verifySent) {
        $app->utils->message("Verification email sent", 'good');
    } else if (isset($verifySent)) {
        $app->utils->message("Error sending message");
    }

    if (isset($verifyConfirmed) && $verifyConfirmed) {
        $app->utils->message("Email verified", 'good');
    } else if (isset($verifyConfirmed)) {
        $app->utils->message("Incorrect verification code");
    }

    if (isset($app->user->connect_msg)) {
        $app->utils->message($app->user->connect_msg, 'info');
    }
?>
    <h1>Account</h1>
    <div class='row'>
        <h3>Change password</h3>
        <label>If you have connected via facebook this will add a password to your account.</label><br/>
        <form action="?password" method="POST" class="span_12">
            <label>New password:</label><br/>
            <input name="newpassword1" type="password" autocomplete="off" /><br/>
            <label>Repeat password:</label><br/>
            <input name="newpassword2" type="password" autocomplete="off" /><br/>

            <input type="hidden" value="<?=$app->generateCSRFKey("changePassword");?>" name="token">
            <input type="submit" class="button" value="Change password" />
        </form>
    </div>

<?php
    if (!$app->user->verified):
        // when was the last code generated
        $sql = 'SELECT user_id
                FROM users_data
                WHERE `type` = :type AND user_id = :uid
                LIMIT 1';

        $st = $app->db->prepare($sql);
        $st->execute(array(':type' => 'verification', ':uid' => $app->user->uid));
        $row = $st->fetch();
?>
    <div class='row'>
        <h3>Verify email address</h3>
<?php
        if ($row):
?>
        An email has been sent to your account containing details on how to confirm your email address.<br/>
        If you have deleted or not recieved an email please click the button below.<br/>
<?php
        endif;
?>
        <a class='button clean' href='?verify'>
            Send verification email
        </a>
        <br/><br/>
    </div>
<?php
    endif;
   if (!$app->user->connected):
?>
    <div class='row'>
        <h3>Facebook</h3>
        Connect your HackThis!! account to your facebook account. This will allow you to login to your account via the facebook button and releasing you from the burden of remembering another password. We will never post anything to your profile.<br/>
        <a class='stop-external facebook-connect' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo $fb['public'];?>&redirect_uri=<?=$app->config('domain');?>/settings/account.php?facebook&scope=email'>
            Connect to Facebook
        </a>
        <br/><br/>
    </div>
<?php
    endif;
?>

    <div class='warning clr'>
        <h3>Delete account</h3>
        <label>Enter your password to delete your account. <strong>This can NOT be reverted!!</strong></label><br/>
        <form action="?delete" method="POST" class="span_12">
            <input name="delete" type="password" autocomplete="off" /><br/>
            <input type="hidden" value="<?=$app->generateCSRFKey("deleteAccount");?>" name="token">
            <input type="submit" class="button" value="Delete account" />
        </form>
    </div>

<?php
    require_once('footer.php');
?>