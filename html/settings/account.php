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
    }
?>
    <form action="?delete" method="POST" class="span_12">
        <label for="delete">Delete account:</label><br/>
        Enter your password to delete account<br/>
        <input name="delete" type="password" autocomplete="off" /><br/>

        <input type="hidden" value="<?=$app->generateCSRFKey("deleteAccount");?>" name="token">
        <input type="submit" class="button" value="Delete account" />
    </form>

<?php
    require_once('footer.php');
?>