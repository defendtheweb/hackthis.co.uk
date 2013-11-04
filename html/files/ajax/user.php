<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $result = array("status"=>false);

    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $subject = substr($action, 0, strrpos($action,"."));

        if ($action == 'feed.remove' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $result['status'] = (bool) $app->feed->remove($id);
        } else if ($subject == 'friend' && isset($_GET['uid']) && isset($_GET['token'])) {
            // Check request
            if ($_GET['token'] == $app->user->csrf_basic) {
                $profile = new profile($_GET['uid'], true);
                if (isset($profile->uid)) {
                    if ($action == 'friend.add')
                        $res = $profile->addFriend();
                    else if ($action == 'friend.remove')
                        $res = $profile->removeFriend();
                    else
                        $res = false;

                    $result['status'] = (bool) $res;
                }
            }
        } else if ($action == 'music' && isset($_GET['id'])) {
            $res = profile::getMusic($_GET['id']);

            $result['status'] = (bool) $res;

            if ($res)
                $result['music'] = $res;
        } else if ($action == 'graph' && isset($_GET['uid']) && isset($_GET['type'])) {
            $result = profile::getStats($_GET['uid'], $_GET['type']);
        } else if (($action == 'block' || $action == 'unblock') && isset($_GET['uid']) && isset($_GET['token'])) {
            // Check request
            if ($_GET['token'] == $app->user->csrf_basic) {
                $result['status'] = profile::blockUser($_GET['uid'], $action=='block');
            }
        }
    }

    $json = json_encode($result);
    echo $json;
?>