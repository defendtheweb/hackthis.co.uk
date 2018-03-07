<?php
    ini_set('session.cookie_httponly', true);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', '0');

    // Session security flags
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);

    // Content Security Policy
    $csp_rules = "
        default-src 'self' https://hackthis.co.uk:8080 wss://hackthis.co.uk:8080 https://themes.googleusercontent.com https://*.facebook.com  https://fonts.gstatic.com;
        script-src 'self' 'unsafe-inline' 'unsafe-eval' https://*.googleapis.com https://*.google-analytics.com https://hackthis.co.uk:8080 https://cdnjs.cloudflare.com https://*.twitter.com https://*.api.twitter.com https://pagead2.googlesyndication.com *.newrelic.com https://www.google.com https://ssl.gstatic.com https://members.internetdefenseleague.org;
        style-src 'self' 'unsafe-inline' https://*.googleapis.com;
        img-src *;
        object-src 'self' https://*.youtube.com  https://*.ytimg.com;
        frame-src 'self' https://googleads.g.doubleclick.net https://*.youtube-nocookie.com https://*.vimeo.com https://kiwiirc.com https://www.google.com";
    header("Content-Security-Policy: " . $csp_rules);

    //Set timezone
    date_default_timezone_set("Europe/London");
    putenv("TZ=Europe/London");


    spl_autoload_register(function ($class) {
        @include_once 'class.'.$class.'.php';
    });

    // Setup app
    try {
        $app = new app();
    } catch (Exception $e) {
        die($e->getMessage());
    }

    // check if theme has changed
    if (isset($_GET['theme'])) {
        $app->setTheme($_GET['theme']);
    }

    $minifier = new loader($app, $custom_css, $custom_js, $app->theme);

    if ($app->user->loggedIn) {
        if (defined('PAGE_PRIV') && !$app->user->{PAGE_PRIV.'_priv'}) {
            require_once('error.php');
        }   

        array_push($minifier->custom_js, 'ajax_csrf_token.js');
        array_push($minifier->custom_js, 'notifications.js');
        array_push($minifier->custom_js, 'chat.js');
        array_push($minifier->custom_js, 'autosuggest.js');
    } else {
        array_push($minifier->custom_js, 'guest.js');
        array_push($minifier->custom_js, 'mailcheck.min.js');
        array_push($minifier->custom_js, 'jquery.transit.min.js');
        array_push($minifier->custom_css, 'guest.scss');

        if (defined('LANDING_PAGE') && LANDING_PAGE) {
            array_push($minifier->custom_css, 'guest_landing.scss');
        }

        if (!defined('PAGE_PUBLIC') || !PAGE_PUBLIC) {
            require_once('error.php');
        }
    }

    if (isset($_GET['view']) && $_GET['view'] == 'app') {
        array_push($minifier->custom_css, 'app.css');
    }
?>
