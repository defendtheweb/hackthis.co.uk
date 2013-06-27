<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $result = array("status"=>false);

    if (isset($_GET['user'])) {
        if (isset($_GET['max']))
            $max = $_GET['max'];
        else
            $max = 5;

        $users = $app->utils->search_users($_GET['user'], $max);

        if ($users) {
            $result['status'] = true;
            $result['users'] = $users;
        }
    }

    $json = json_encode($result);
    echo htmlspecialchars($json, ENT_NOQUOTES);
?>