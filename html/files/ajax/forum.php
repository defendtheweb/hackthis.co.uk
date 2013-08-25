<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $forum = new forum($app);
    $result = array("status"=>false);

    if (isset($_GET['action'])) {
        if ($_GET['action'] == "watch" && isset($_GET['watch'])) {
            $result['status'] = $forum->watchThread($_GET['thread_id'], $_GET['watch'] === 'true');
        }
    }

    echo json_encode($result);
?>