<?php
    $custom_css = array('settings.scss');
    $custom_js = array('highlight.js');
    require_once('init.php');

    $app->page->title = 'Settings - 2 Step Authentication';

    require_once('header.php');

    $tab = '2-step';
    include('elements/tabs_settings.php');

    require('vendor/gauth.php'); 
    $ga = new gauth();

    $st = $app->db->prepare('SELECT g_auth, g_secret FROM users WHERE user_id = :uid');
    $st->execute(array(':uid' => $app->user->uid));
    $step = $st->fetch();
?>

    <h1>2 Step Authentication</h1>
    <p>2-Step Authentication adds an extra layer of security to your HackThis Account, drastically reducing the chances of having your account stolen. To break into an account with 2-Step Authentication, bad guys would not only have to know your username and password, they'd also have to get a hold of your phone.</p>
    
    <h2>Google Authenticator</h2>
    <p>Google Authenticator is a product developed by Google which allows the user to make use of <a href="http://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm">TOTP</a>.<br />When enabled you will be asked for a code from your Google Authenicator app on your mobile device when logging into HackThis. It is available for <a href="https://itunes.apple.com/gb/app/google-authenticator/id388497605?mt=8">Apple</a> and <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en_GB">Android</a> devices</p>
    
<?php
    if(($step->g_auth != 1) && !isset($_GET['google'])) {
?>
        <p><a href="?google=1">Enable Google Authenticator</a></p>
<?php
    } else if (!isset($_GET['google'])) {
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('HackThis - '.$app->user->username, $step->g_secret);
?>
        <p>Your Google Authenticator QR Code</p>
        <p><img src="<?php echo $qrCodeUrl; ?>" /></p>
        <p><a href="?google=0" class="button">Disable Google Authenticator</a></p>
<?php   
    }

    if(isset($_GET['google']) && ($_GET['google'] == 1) && ($step->g_auth != 1)) {
        $secret = $ga->createSecret();
        $st = $app->db->prepare('UPDATE users SET g_auth = 1, g_secret = :secret WHERE user_id = :uid LIMIT 1');
        $status = $st->execute(array(':secret' => $secret, ':uid' => $app->user->uid));
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('HackThis - '.$app->user->username, $secret);
?>
    
        <p>Please scan the below QR code using your Google Authenticator App to add the account</p>
        <p><img src="<?php echo $qrCodeUrl; ?>" /></p>
        <p><a href="?google=0" class="button">Disable Google Authenticator</a></p>

<?php
    } else if (isset($_GET['google']) && ($_GET['google'] == 0) && ($step->g_auth == 1)) {
        $st = $app->db->prepare('UPDATE users SET g_auth = 0, g_secret = NULL WHERE user_id = :uid LIMIT 1');
        $status = $st->execute(array(':uid' => $app->user->uid));
?>

    <p>Google Authenticator Disabled<br />It is now ok for you to remove your HackThis account from your Google Authenticator app.</p>
    <p><a href="?google=1">Enable Google Authenticator</a></p>

<?php
    }
    require_once('footer.php');
?>
