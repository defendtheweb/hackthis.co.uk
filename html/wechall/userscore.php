<?php
    # userscore.php
    # return username:rank:score:maxscore:challssolved:challcount:usercount

    define("PAGE_PUBLIC", true);
    require_once('init.php');

    $key = $app->config['wechall'];

    if (!isset($_GET['username']) ||
        is_array($_GET['username']) ||
        $key != $_GET['authkey']) { 
        die('0'); 
    }

    $maxscore = $app->max_score;

    $username = $_GET['username'];

    $profile = new stdClass();

    // Basic user data
    $st = $app->db->prepare('SELECT users.username, users.score, users.user_id
                             FROM users
                             WHERE users.username = :username
                             LIMIT 1');
    $st->bindValue(':username', $username);
    $st->execute();
    $row = $st->fetch();

    if (!$row) {
        die('0');
    }

    $profile->user_id = $row->user_id;
    $profile->username = $row->username;
    $profile->score = $row->score;

    // Rank data
    $st = $app->db->prepare('SELECT COUNT(*) AS `count`
                             FROM users
                             WHERE score > :score
                             LIMIT 1');
    $st->bindValue(':score', $profile->score);
    $st->execute();
    $row = $st->fetch();

    $profile->rank = $row->count + 1;


    // Level data
    $st = $app->db->prepare('SELECT COUNT(*) AS `count`
                             FROM users_levels
                             WHERE user_id = :uid AND completed > 0
                             LIMIT 1');
    $st->bindValue(':uid', $profile->user_id);
    $st->execute();
    $row = $st->fetch();

    $profile->challssolved = $row->count;

    // Level count
    $st = $app->db->prepare('SELECT COUNT(*) AS `count` FROM levels');
    $st->execute();
    $row = $st->fetch();

    $profile->challcount = $row->count;

    // User count
    $st = $app->db->prepare('SELECT COUNT(*) AS `count` FROM users');
    $st->execute();
    $row = $st->fetch();

    $profile->usercount = $row->count;


    echo sprintf('%s:%d:%d:%d:%d:%d:%d', $profile->username, $profile->rank, $profile->score, $maxscore, $profile->challssolved, $profile->challcount, $profile->usercount);
?>
