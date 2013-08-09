<?php
    header('Content-Type: application/json');
    define("PAGE_PUBLIC", true);
    require_once('init.php');

    $result = array("status"=>false);

    if (isset($_GET['action'])) {
        if ($_GET['action'] == "get") {
            $comments = $app->articles->getComments($_GET['id']);

            $result['status'] = true;
            $result['comments'] = $comments;
        } else if ($app->user->loggedIn) {
            if ($_GET['action'] == "add") {
                $comment = $app->articles->addComment($_POST['body'], $_POST['id'], $_POST['parent']);

                if ($comment) {
                    $result['status'] = true;
                    $result['comment'] = $comment;
                } else
                    $result['status'] = false;
            } else if ($_GET['action'] == "delete") {
                $result['status'] = $comment = $app->articles->deleteComment($_POST['id']);
            } else if ($_GET['action'] == "favourite") {
                $result['status'] = $comment = $app->articles->favourite($_POST['id']);
            } else if ($_GET['action'] == "unfavourite") {
                $result['status'] = $comment = $app->articles->unfavourite($_POST['id']);
            }
        }
    }

    echo json_encode($result);
?>