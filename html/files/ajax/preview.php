<?php
    header('Content-Type: application/json');
    (!isset($_POST['data'])) && die('Error processing response');

    require_once('init.php');
    echo $app->parse($_POST['data']);
?>