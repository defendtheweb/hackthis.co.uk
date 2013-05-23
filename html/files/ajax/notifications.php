<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $userId = $user->uid;
    $result = array('status'=>true);

    if (isset($_GET['events'])) {
        $result['items'] = $app->notifications->getEvents();
    } else if (isset($_GET['pm'])) {
        $result['items'] = $app->notifications->getPms();
    } else {
        $feed = new feed();
        $last = isset($_POST['last'])?$_POST['last']:0;
        $result['feed'] = $feed->get($last);
        $result['counts'] = $app->notifications->getCounts();
    }

    $json = json_encode($result);
    // Remove null entries
    echo preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $json);
?>