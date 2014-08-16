<?php
    $custom_css = array('profile.scss', 'highlight.css', 'confirm.css');
    $custom_js = array('jquery.confirm.js', 'highlight.js', 'profile.js');
    require_once('init.php');
    
    $profile = new profile($_GET['user']);
    if (isset($profile->uid))
        $app->page->title = $profile->username;

    if (isset($_GET['image'])) {
        require('userbar.php');
        die();
    }

    require_once('header.php');

    if (!isset($profile->uid)):
        $app->utils->message("User not found");
        require_once('footer.php');
        die();
    endif;

    if (isset($_GET['friends']) && count($profile->friendsList)):

        /* FRIENDS LIST STARTS */
        $profile->getDob = $profile->getDob();
        echo $app->twig->render('profile_friends.html', array('profile' => $profile));

    else:

        /* USERS PROFILE STARTS */
        $profile->getDob = $profile->getDob();
        echo $app->twig->render('profile.html', array('profile' => $profile));

    endif;

    require_once('footer.php');
?>