<?php
    require_once('init.php');

    $result = array("status"=>false);

    if (isset($_GET['user'])) {
        $users = $app->utils->search_users($_GET['user'], 5);

        if ($users) {
            $result['status'] = true;
            $result['users'] = $users;
        }
    }

    echo json_encode($result);
?>