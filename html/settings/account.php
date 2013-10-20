<?php
    $custom_js = array('profile.js');
    $custom_css = array('settings.scss', 'profile.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Account';

    if (isset($_GET['delete'])) {
        $status = $app->user->delete($_POST['delete'], $_POST['token']);

        if ($status === true) {
            header('Location: /?deleted');
            die();
        }
    }

    require_once('header.php');

    $tab = 'account';
    include('elements/tabs_settings.php');
?>
    <h1>Account</h1>
<?php
    if (isset($status)) {
        $app->utils->message($status);
    } else if ($app->user->connect_msg) {
        $app->utils->message($app->user->connect_msg, 'info');
    }

    if (!$app->user->connected):
?>
    <p>
        <h3>Facebook</h3>
        Connect your HackThis!! account to your facebook account. Enabling you to login to your account via the facebook button and releasing you from the burden of remembering another password.<br/><br/>
        <a class='stop-external facebook-connect' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo $fb['public'];?>&redirect_uri=http://dev.hackthis/?facebook&scope=email'>
            Connect to Facebook
        </a>
        <br/>
    </p>
<?php
    endif;
?>

    <p>
        <h3>Delete account</h3>
        <label>Enter your password to delete your account</label><br/>
        <form action="?delete" method="POST" class="span_12">
            <input name="delete" type="password" autocomplete="off" /><br/>

            <input type="hidden" value="<?=$app->generateCSRFKey("deleteAccount");?>" name="token">
            <input type="submit" class="button" value="Delete account" />
        </form>
    </p>

<?php
    require_once('footer.php');
?>