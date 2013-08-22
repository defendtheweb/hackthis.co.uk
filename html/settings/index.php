<?php
    $custom_js = array('bootstrap-datepicker.js');
    $custom_css = array('settings.scss', 'datepicker.scss');
    require_once('init.php');

    $app->page->title = 'Settings - Profile';

    $profile = new profile($app->user->username);

    $alien = false;
    foreach($profile->medals AS $medal) {
        if ($medal->medal_id == 10) {
            $alien = true;
            break;
        }
    }


    $values = array();
    $values['name'] = htmlspecialchars($profile->name);
    $values['display_name'] = ($profile->show_name || $profile->show_name === null)?'checked':'';
    $values['email'] = htmlspecialchars($profile->email);
    $values['display_email'] = $profile->show_email?'checked':'';
    $values['gender'] = $profile->gender;
    $values['display_gender'] = ($profile->show_gender || $profile->show_gender === null)?'checked':'';
    $values['dob'] = isset($profile->dob)?'value="'.date('d/m/Y', strtotime($profile->dob)).'"':'';
    $values['show_dob'] = $profile->show_dob;
    $values['profile'] = isset($profile->about_plain)?$profile->about_plain:'';

    if (isset($_GET['save'])) {
        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['gender']) && isset($_POST['dob'])) {
            $changes = array_map('trim', $_POST);
            $updated = $app->user->update($changes);
            if ($updated === true) {
                header('location: ?done');
                die();
            }

            $values['name'] = htmlspecialchars($changes['name']);
            $values['display_name'] = isset($changes['display_name'])?'checked':'';
            $values['email'] = htmlspecialchars($changes['email']);
            $values['display_email'] = isset($changes['display_email'])?'checked':'';
            switch($changes['gender']) {
                case 'm': $values['gender'] = 'male'; break;
                case 'f': $values['gender'] = 'female'; break;
                case 'a': $values['gender'] = 'alien';
            }
            $values['display_gender'] = isset($changes['display_gender'])?'checked':'';
            $values['dob'] = "value='{$changes['dob']}'";
            if ($changes['show_dob'] === '0' || $changes['show_dob'] === '1' || $changes['show_dob'] === '2')
                $values['show_dob'] = $changes['show_dob'];
            $values['profile'] = $changes['about'];
        } else {
            $updated = 'Invalid request';
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
            <input name="name" value="<?=$values['name'];?>" class='short'>
            <input type="checkbox" id="display_name" name="display_name" <?=$values['display_name'];?>/><label class='right' for="display_name">Display name</label><br>

            <label>Email Address:</label><br>
            <input name="email" value="<?=$values['email'];?>" class='short'>
            <input type="checkbox" id="display_email" name="display_email" <?=$values['display_email'];?>/><label class='right' for="display_email">Display email</label><br>

            <label>Gender:</label><br>
            <select class='tiny' name="gender">
                <option value="m" <?php if($values['gender'] == 'male') echo 'selected="true"'; ?>>Male</option>
                <option value="f" <?php if($values['gender'] == 'female') echo 'selected="true"'; ?>>Female</option>
<?php if ($alien || $values['gender'] == 'alien'): ?>
                <option value="a" <?php if($values['gender'] == 'alien') echo 'selected="true"'; ?>>Alien</option>
<?php endif; ?>
            </select>
            <input type="checkbox" id="display_gender" name="display_gender" <?=$values['display_gender'];?>/><label class='right' for="display_gender">Display gender</label><br>

            <label for="dob">Date of Birth:</label><br/>
            <input name="dob" class="tiny" size="16" type="text" placeholder="dd/mm/yyyy" <?=$values['dob'];?> id="datepicker" data-date="29/05/1990" data-date-format="dd/mm/yyyy" data-date-viewmode="years" readonly/>
            <select name="show_dob" class="tiny right">
                <option value="2" <?php if($values['show_dob'] == '2') echo 'selected="true"'; ?>>Show date of birth</option>
                <option value="1" <?php if($values['show_dob'] == '1') echo 'selected="true"'; ?>>Hide year of birth</option>
                <option value="0" <?php if($values['show_dob'] == '0') echo 'selected="true"'; ?>>Hide date of birth</option>
            </select><br/>

            <label for="name">About:</label><br>
<?php
    $wysiwyg_name = 'about';
    $wysiwyg_placeholder = 'Write something about yourself...';
    $wysiwyg_text = $values['profile'];
    include('elements/wysiwyg.php');
?>

            <input type="hidden" value="<?=$app->generateCSRFKey("settings");?>" name="token">
            <input type="submit" value="Save Changes" class="button">
        </fieldset>
    </form>

<?php
    require_once('footer.php');
?>