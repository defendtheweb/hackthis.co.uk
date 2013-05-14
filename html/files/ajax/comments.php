<?php
    require_once('init.php');

    $articles = new articles();

    if (isset($_GET['action'])) {
        $comments = $articles->get_comments($_GET['id']);
        $result = array("status"=>true, "comments"=>$comments);
        echo json_encode($result);
    }
?>