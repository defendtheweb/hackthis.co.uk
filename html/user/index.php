<?php
    require_once('header.php');

    print_r($app->utils->get_profile($_GET['user']));

    require_once('footer.php');
?>