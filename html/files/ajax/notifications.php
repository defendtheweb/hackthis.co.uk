<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $userId = $user->uid;
    $result = array('status'=>true);

    if (isset($_GET['events'])) {
        $result['items'] = $app->notifications->getEvents();
    } else if (isset($_GET['pm'])) {
        if (isset($_GET['id']))
            $result['items'] = $app->notifications->getPm($_GET['id']);
        else if (isset($_POST['to']) && isset($_POST['body'])) {
            $messages = new messages();
            $result['status'] = $messages->newMessage($_POST['to'], $_POST['body']);
        } else if (isset($_POST['pm_id']) && isset($_POST['body'])) {
            $messages = new messages();
            $result['status'] = $messages->newMessage(null, $_POST['body'], $_POST['pm_id']);
        } else
            $result['items'] = $app->notifications->getPms();
    } else {
        $last = isset($_POST['last'])?$_POST['last']:0;
        $result['feed'] = $app->feed->get($last);
        $result['counts'] = $app->notifications->getCounts();
    }

    $json = json_encode($result);
    // Remove null entries
    echo preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $json);
?>