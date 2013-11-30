<?php
    $custom_css = array('settings.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Notifications';

    require_once('header.php');

    $tab = 'notifications';
    include('elements/tabs_settings.php');

    if (isset($_GET['save'])) {
        $saved = $app->user->changeNotificationSettings();
    }

    $st = $app->db->prepare('SELECT email_pm, email_forum_reply, email_forum_mention, email_friend, email_news FROM users_settings WHERE user_id = :uid');
    $st->execute(array(':uid' => $app->user->uid));
    $settings = $st->fetch();
?>

    <h1>Email Notifications</h1>
    <p>Currently emails can be configured to notify you instantly or never about each notification type. Soon there will be the option to recieve a daily summary.</p>
    <form method='POST' action='?save'>
<?php
    if (isset($saved)) {
        if ($saved)
            $app->utils->message('Notification settings updated', 'good');
        else
            $app->utils->message('Error saving updates', 'error');
    }
?>
        <table class='striped'>
            <thead>
                <tr>
                    <td>&nbsp;</td>
                    <td class='center'>Instantly</td>
                    <td class='center'>Never</td>
                </tr>
            <thead>
            <tbody>
                <tr>
                    <td>Private message</td>
                    <td class='center'><input type="radio" name="pm" id="pm1" value="1" <?=$settings->email_pm?'checked':'';?>/><label for="pm1"></label></td>
                    <td class='center'><input type="radio" name="pm" id="pm2" value="0" <?=!$settings->email_pm?'checked':'';?>/><label for="pm2"></label></td>
                </tr>
                <tr>
                    <td>Friend request</td>
                    <td class='center'><input type="radio" name="friend" id="friend1" value="1" <?=$settings->email_friend?'checked':'';?>/><label for="friend1"></label></td>
                    <td class='center'><input type="radio" name="friend" id="friend2" value="0" <?=!$settings->email_friend?'checked':'';?>/><label for="friend2"></label></td>
                </tr>
                <tr>
                    <td>Forum reply</td>
                    <td class='center'><input type="radio" name="forum_reply" id="forum_reply1" value="1" <?=$settings->email_forum_reply?'checked':'';?>/><label for="forum_reply1"></label></td>
                    <td class='center'><input type="radio" name="forum_reply" id="forum_reply2" value="0" <?=!$settings->email_forum_reply?'checked':'';?>/><label for="forum_reply2"></label></td>
                </tr>
                <tr>
                    <td>Forum mention</td>
                    <td class='center'><input type="radio" name="forum_mention" id="forum_mention1" value="1" <?=$settings->email_forum_mention?'checked':'';?>/><label for="forum_mention1"></label></td>
                    <td class='center'><input type="radio" name="forum_mention" id="forum_mention2" value="0" <?=!$settings->email_forum_mention?'checked':'';?>/><label for="forum_mention2"></label></td>
                </tr>
                <tr>
                    <td>News / Updates</td>
                    <td class='center'><input type="radio" name="news" id="news1" value="1" <?=$settings->email_news?'checked':'';?>/><label for="news1"></label></td>
                    <td class='center'><input type="radio" name="news" id="news2" value="0" <?=!$settings->email_news?'checked':'';?>/><label for="news2"></label></td>
                </tr>
            </tbody>
        </table>
        <br/>
        <input type="submit" class='button right' value="Save settings"/>
    </form>
<?php
    require_once('footer.php');
?>