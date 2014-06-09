<?php
    define("PAGE_PUBLIC", true);
    require_once('init.php');

    $key = $app->config['wechall'];

    if (!isset($_GET['username']) ||
        !isset($_GET['email']) ||
        is_array($_GET['username']) ||
        is_array($_GET['email']) ||
        $key != $_GET['authkey']) { 
        die('0'); 
    }

    $username = $_GET['username'];
    $email = $_GET['email'];

    $st = $app->db->prepare('SELECT username FROM users WHERE username = :username AND email = :email LIMIT 1');
    $st->bindValue(':username', $username);
    $st->bindValue(':email', $email);
    $st->execute();
    $row = $st->fetch();

    // Entry exists
    if ($row) {
        die('1');
    }

    // Entry doesn't exist
    die('0');
?>
