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
        } else if ($action == 'connect.hide') {
            $result['status'] = (bool) $user->hideConnect();
        } else if ($subject == 'friend' && isset($_GET['uid'])) {
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
        } else if ($action == 'music' && isset($_GET['id'])) {
            $res = profile::getMusic($_GET['id']);

            $result['status'] = (bool) $res;

            if ($res)
                $result['music'] = $res;
        }
    }

    $json = json_encode($result);
    echo htmlspecialchars($json, ENT_NOQUOTES);
?>