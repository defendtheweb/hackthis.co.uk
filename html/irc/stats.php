<?php
    define("PAGE_PUBLIC", true);

    $custom_css = array('irc.scss');
    $custom_js = array('irc_stats.js');
    require_once('init.php');
    $app->page->title = 'IRC';
    require_once('header.php');


    // Check for cache
    $data = $app->cache->get('irc_stats', 15);

    if (!$data) {
        $data = new stdClass();

        // Generate weekly stats
        $sql = "SELECT DATE(`time`) AS `date`, COUNT(*) AS `lines`
                FROM `irc_logs`
                WHERE DATE(`time`) > DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY `date`
                ORDER BY DATE(`time`)";

        $st = $app->db->prepare($sql);
        $st->execute();
        $data->latest = $st->fetchAll();


        // Generate hour stats
        $sql = "SELECT COUNT(*) AS `lines`
                FROM `irc_logs`
                GROUP BY HOUR(`time`)
                ORDER BY HOUR(`time`)";

        $st = $app->db->prepare($sql);
        $st->execute();
        $data->hours = $st->fetchAll();


        // Generate user stats
        $sql = "SELECT `irc_logs`.`nick`, MAX(`time`) AS `last`, COUNT(*) AS `lines`, SUM(LENGTH(`irc_logs`.`log`) - LENGTH(REPLACE(`irc_logs`.`log`, ' ', ''))+1) AS `words`,
                COUNT(IF(HOUR(`time`) <= 6,1,NULL)) AS `night`, COUNT(IF(HOUR(`time`) > 6 AND HOUR(`time`) <= 12,1,NULL)) AS `morning`,
                COUNT(IF(HOUR(`time`) > 12 AND HOUR(`time`) <= 18,1,NULL)) AS `daytime`, COUNT(IF(HOUR(`time`) > 18 AND HOUR(`time`) <= 23,1,NULL)) AS `evening`
                FROM `irc_logs`
                GROUP BY `nick`
                ORDER BY `lines` DESC, `words` DESC";

        $st = $app->db->prepare($sql);
        $st->execute();
        $data->users = $st->fetchAll();

        $data->totalUsers = count($data->users);

        $data->users = array_slice($data->users, 0, 25);

        // Calcualate hour percentages
        foreach($data->users AS &$user) {
            $user->night = $user->night / $user->lines * 100;
            $user->morning = $user->morning / $user->lines * 100;
            $user->daytime = $user->daytime / $user->lines * 100;
            $user->evening = $user->evening / $user->lines * 100;
        }

        $data->generated = date('F jS, Y H:i:s');

        // Store data
        $app->cache->set('irc_stats', json_encode($data));
    } else {
        $data = json_decode($data);
    }
?>

    <h1><a href='/irc'>IRC</a> - Stats</h1>

<?php
    echo $app->twig->render('irc_stats.html', array('data' => $data));

    require_once('footer.php');
?>