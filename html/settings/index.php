<?php
    $custom_js = array('bootstrap-datepicker.js');
    $custom_css = array('settings.scss', 'datepicker.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Profile';

    if (isset($_GET['save'])) {
        $changes = array_map('trim', $_POST);
        $updated = $app->user->update($changes);
        if ($updated === true) {
            header('location: ?done');
            die();
        }
    }

    $profile = new profile($app->user->username);

    $alien = false;
    foreach($profile->medals AS $medal) {
        if ($medal->medal_id == 10) {
            $alien = true;
            break;
        }
    }

    require_once('header.php');
?>


<?php
    if (isset($updated)) {
        $app->utils->message($updated);
    } else if (isset($_GET['done'])) {
        $app->utils->message("Profile updated, <a href='/user/".$app->user->username."'>view here</a>", "good");
    }
?>

    <form action="?save" method="POST">
        <fieldset>
            <label for="name">Real Name:</label><br>
            <input name="name" value="<?=htmlspecialchars($profile->name);?>" class='short'>
            <input type="checkbox" id="display_name" name="display_name" <?=$profile->show_name?'checked':'';?>/><label class='right' for="display_name">Display name</label><br>

            <label>Email Address:</label><br>
            <input name="email" value="<?=htmlspecialchars($profile->email);?>" class='short'>
            <input type="checkbox" id="display_email" name="display_email" <?=$profile->show_email?'checked':'';?>/><label class='right' for="display_email">Display email</label><br>

            <label>Gender:</label><br>
            <select class='tiny' name="gender">
                <option value="m" <?php if($profile->gender == 'male') echo 'selected="true"'; ?>>Male</option>
                <option value="f" <?php if($profile->gender == 'female') echo 'selected="true"'; ?>>Female</option>
<?php if ($alien || $profile->gender == 'alien'): ?>
                <option value="a" <?php if($profile->gender == 'alien') echo 'selected="true"'; ?>>Alien</option>
<?php endif; ?>
            </select>
            <input type="checkbox" id="display_gender" name="display_gender" <?=$profile->show_gender?'checked':'';?>/><label class='right' for="display_gender">Display gender</label><br>

            <label for="dob">Date of Birth:</label><br/>
            <input class="tiny" size="16" type="text" placeholder="dd/mm/yyyy" id="datepicker" data-date="29/05/1990" data-date-format="dd/mm/yyyy" data-date-viewmode="years" readonly/>
            <select name="show_dob" class="tiny right">
                <option value="2">Show date of birth</option>
                <option selected="true" value="1">Hide year of birth</option>
                <option value="0">Hide date of birth</option>
            </select><br/>

            <label for="name">About:</label><br>
<?php
    $wysiwyg_name = 'about';
    $wysiwyg_placeholder = 'Write something about yourself...';
    $wysiwyg_text = $profile->about_plain;
    include('elements/wysiwyg.php');
?>

            <input type="hidden" value="<?=$app->generateCSRFKey("settings");?>" name="token">
            <input type="submit" value="Save Changes" class="button">
        </fieldset>
    </form>

<?php
    require_once('footer.php');
?>