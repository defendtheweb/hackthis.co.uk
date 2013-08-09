<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $userId = $app->user->uid;
    $result = array('status'=>false);

    if (isset($_GET['list'])) {
        $result['status'] = true;
        $result['items'] = $app->notifications->getPms();
    } else if (isset($_GET['view']) && isset($_GET['id'])) {
        $result['status'] = true;
        $result['items'] = $app->notifications->getPm($_GET['id']);
    } else if (isset($_GET['send'])) {
        if (isset($_POST['to']) && isset($_POST['body'])) {
            $messages = new messages($app);
            $result['status'] = $messages->newMessage($_POST['to'], $_POST['body']);
        } else if (isset($_POST['pm_id']) && isset($_POST['body'])) {
            $messages = new messages($app);
            $result['status'] = $messages->newMessage(null, $_POST['body'], $_POST['pm_id']);
            $result['message'] = $app->parse($_POST['body']);
        }
    }

    $json = json_encode($result);
    // Remove null entries
    echo preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $json);
?>