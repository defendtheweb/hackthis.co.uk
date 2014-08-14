<?php
    require_once('init.php');

    $api = new api($app);

    if (isset($_GET['method'])) {
        $api->handleRequest($_GET['method'], $_GET);
    }
?>