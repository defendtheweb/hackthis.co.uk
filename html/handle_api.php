<?php
    define("PAGE_PUBLIC", true);
    require_once('init.php');

    $key = null;
    if (isset($_GET['key'])) {
        $key = $_GET['key'];
    }

    $api = new api($app, $key);

    if (isset($_GET['method'])) {
        $api->handleRequest($_GET['method'], $_GET);
    } else {
        $api->respond(400);
    }
?>