<?php
    (!isset($_POST['data'])) && die('Error processing response');

    require_once('init.php');
    echo $app->bbcode->Parse($_POST['data']);
?>