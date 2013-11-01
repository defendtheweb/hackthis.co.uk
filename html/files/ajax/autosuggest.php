<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $result = array("status"=>false);

    if (isset($_GET['user'])) {
        if (isset($_GET['max']))
            $max = $_GET['max'];
        else
            $max = 5;

        $users = $app->utils->search_users($_GET['user'], $max);

        if ($users) {
            $result['status'] = true;
            $result['users'] = $users;
        }
    } else if (isset($_GET['search'])) {
        $search = new search($app);
        $q = preg_replace('/[^a-zA-Z0-9"@._-\s]/', '', strip_tags(html_entity_decode($_GET['search'])));
        $result['data'] = $search->go($q);

        if ($result['data']['articles'] || $result['data']['users'] || $result['data']['forum']) {
            $result['status'] = true;
        }
    }

    $json = json_encode($result);
    echo htmlspecialchars($json, ENT_NOQUOTES);
?>