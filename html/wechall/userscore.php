<?php
    # userscore.php
    # return username:rank:score:maxscore:challssolved:challcount:usercount

    define("PAGE_PUBLIC", true);
    require_once('init.php');

    $key = $app->config['wechall'];

    if (!isset($_GET['username']) ||
        is_array($_GET['username']) ||
        $key !== $_GET['authkey']) { 
        die('0'); 
    }

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
    $profile->total_score = $row->score;

    // Rank data based on HackThis!! score
    $st = $app->db->prepare('SELECT COUNT(*) AS `count`
                             FROM users
                             WHERE score > :score
                             LIMIT 1');
    $st->bindValue(':score', $profile->total_score);
    $st->execute();
    $row = $st->fetch();

    $profile->rank = $row->count + 1;

    // Level data
    $st = $app->db->prepare('SELECT COUNT(*) AS `count`, SUM(ld.value) AS `score`
                             FROM users_levels ul
                             LEFT JOIN levels_data ld
                             ON ul.level_id = ld.level_id
                             WHERE user_id = :uid AND completed > 0 AND ld.key = "reward"
                             LIMIT 1');
    $st->bindValue(':uid', $profile->user_id);
    $st->execute();
    $row = $st->fetch();

    $profile->challs_solved = $row->count;
    $profile->levels_score = $row->score;

    // Level count
    $st = $app->db->prepare('SELECT COUNT(*) AS `count` FROM levels');
    $st->execute();
    $row = $st->fetch();

    $profile->chall_count = $row->count;

    // User count
    $st = $app->db->prepare('SELECT COUNT(*) AS `count` FROM users');
    $st->execute();
    $row = $st->fetch();

    $profile->user_count = $row->count;

    // Maximum scores
    $profile->max_levels_score = $app->getMaxLevelsScore();
    $profile->max_total_score = $app->max_score;

    
    if ( $_GET['format'] === 'json' )
    {
        echo json_encode($profile);
    } else { // default: wechall format
        echo sprintf('%s:%d:%d:%d:%d:%d:%d',
                     $profile->username,
                     $profile->rank,
                     $profile->levels_score,
                     $profile->max_levels_score,
                     $profile->challs_solved,
                     $profile->chall_count,
                     $profile->user_count);
    }
?>
