<?php
    $custom_css = array('settings.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Privacy';

    require_once('header.php');

    $tab = 'privacy';
    include('elements/tabs_settings.php');

    if (isset($_GET['save'])) {
        $saved = $app->user->changePrivacySettings();
    }

    $st = $app->db->prepare('SELECT show_online, show_leaderboard FROM users_profile WHERE user_id = :uid');
    $st->execute(array(':uid' => $app->user->uid));
    $settings = $st->fetch();
?>

    <h1>Privacy</h1>
    <form method='POST' action='?save'>
<?php
    if (isset($saved)) {
        if ($saved)
            $app->utils->message('Privacy settings updated', 'good');
        else
            $app->utils->message('Error saving updates', 'error');
    }
?>
        <table class='striped'>
            <thead>
                <tr>
                    <td>&nbsp;</td>
                    <td class='center'>Show</td>
                    <td class='center'>Hide</td>
                </tr>
            <thead>
            <tbody>
                <tr>
                    <td class='white strong'>Online status</td>
                    <td class='center'><input type="radio" name="online" id="online1" value="1" <?=!$settings || $settings->show_online?'checked':'';?>/><label for="online1"></label></td>
                    <td class='center'><input type="radio" name="online" id="online2" value="0" <?=$settings && !$settings->show_online?'checked':'';?>/><label for="online2"></label></td>
                </tr>
                <tr>
                    <td class='white strong'>Scoreboard position</td>
                    <td class='center'><input type="radio" name="score" id="score1" value="1" <?=!$settings || $settings->show_leaderboard?'checked':'';?>/><label for="score1"></label></td>
                    <td class='center'><input type="radio" name="score" id="score2" value="0" <?=$settings && !$settings->show_leaderboard?'checked':'';?>/><label for="score2"></label></td>
                </tr>
            </tbody>
        </table>
        <br/>
        Settings may take a few minutes to take effect.
        <input type="submit" class='button right' value="Save settings"/>
    </form>
<?php
    require_once('footer.php');
?>
