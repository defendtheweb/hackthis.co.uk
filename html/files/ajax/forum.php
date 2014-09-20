<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $forum = new forum($app);
    $result = array("status"=>false);

    // Check csrf token
    if(!isset($_GET['ajax_csrf_token']) || $_GET['ajax_csrf_token'] != $app->user->csrf_basic) {
        die();
    }

    if (isset($_GET['action'])) {
        if ($_GET['action'] == "watch" && isset($_GET['watch'])) {
            $result['status'] = $forum->watchThread($_GET['thread_id'], $_GET['watch'] === 'true');
        } else if (($_GET['action'] == "karma.positive" || $_GET['action'] == "karma.negative") && isset($_GET['id'])) {
            $cancel = isset($_GET['cancel']);
            if ($_GET['action'] == "karma.positive")
                $result['status'] = $forum->giveKarma(true, $_GET['id'], $cancel);
            else
                $result['status'] = $forum->giveKarma(false, $_GET['id'], $cancel);
        } else if ($_GET['action'] == "post.flag" && isset($_GET['id'])) {
            $result['status'] = $forum->flagPost($_GET['id'], $_GET['reason'], $_GET['extra']);
        } else if ($_GET['action'] == "post.remove" && isset($_GET['id'])) {
            $result['status'] = $forum->deletePost($_GET['id']);
        }
    }

    echo json_encode($result);
?>