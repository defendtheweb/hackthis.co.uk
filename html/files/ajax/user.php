<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $result = array("status"=>false);

    if (isset($_GET['action']) && isset($_GET['uid'])) {
        $profile = new profile($_GET['uid'], true);
        if (isset($profile->uid)) {
            if ($_GET['action'] == 'add')
                $res = $profile->addFriend();
            else if ($_GET['action'] == 'remove')
                $res = $profile->removeFriend();
            else
                $res = false;

            $result['status'] = (bool) $res;
        }
    }

    $json = json_encode($result);
    echo htmlspecialchars($json, ENT_NOQUOTES);
?>