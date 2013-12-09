<?php
    $custom_css = array('admin.scss');
    $custom_js = array('admin.js', 'admin_forum.js');
    $page_title = 'Admin - Users';
    define("PAGE_PRIV", "admin_site");

    require_once('init.php');

    require_once('header.php');

    $sql = "SELECT COUNT(user_id) AS `count`, DATE_FORMAT(joined, '%d-%m-%Y') AS `date` FROM users_activity WHERE joined > date_sub(now(), INTERVAL 1 WEEK) GROUP BY YEAR(joined), MONTH(joined), DAY(joined) ORDER BY joined DESC";
    $st = $app->db->prepare($sql);
    $st->execute();
    $result = $st->fetchAll();
?>

        <script type="text/javascript">
            graphTitle = "New members over the last 7 days";
            graphData = [<?php foreach($result AS $res) { echo '{ "date" : "' . $res->date . '", "count" : ' . $res->count . ' }, '; } ?>];
        </script>

        <div class='graph'></div>
        <script type="text/javascript" src="/files/js/d3.js"></script>
        <br/><br/>
<?php
    $sql = "SELECT COUNT(user_id) AS `count` FROM users";
    $st = $app->db->prepare($sql);
    $st->execute();
    $result = $st->fetch();

    echo "<strong class='white'>Total:</strong> ". number_format($result->count);


    if (isset($_GET['calculate'])) {
        $l = 0;

        while ($l < $_GET['calculate']) {

            $sql = "SELECT user_id, score FROM users ORDER BY user_id ASC LIMIT :l, 1";
            $st = $app->db->prepare($sql);
            $st->bindValue(':l', $l, PDO::PARAM_INT);
            $st->execute();
            $result = $st->fetch();
            if (!$result)
                break;


            $sql2 = 'SELECT users.user_id, SUM(medal_total + level_total) AS `total` FROM users

                    LEFT JOIN (SELECT users_medals.user_id, SUM(medals_colours.reward) AS medal_total FROM users_medals
                    INNER JOIN medals
                    ON medals.medal_id = users_medals.medal_id
                    INNER JOIN medals_colours
                    ON medals.colour_id = medals_colours.colour_id
                    WHERE users_medals.user_id = :uid
                    GROUP BY users_medals.user_id) medals
                    ON medals.user_id = users.user_id

                    LEFT JOIN (SELECT users_levels.user_id, SUM(levels_data.value) AS level_total FROM users_levels
                    INNER JOIN levels_data
                    ON levels_data.level_id = users_levels.level_id
                    WHERE completed > 0 AND users_levels.user_id = :uid
                    GROUP BY users_levels.user_id) levels
                    ON levels.user_id = users.user_id

                    WHERE users.user_id = :uid;';
            $st2 = $app->db->prepare($sql);
            $st2->execute(array(':uid' => $result->user_id));

            if ($st2 && $st2->total != $result->score) {
                echo "<br/>".$result->user_id . " - " . $result->score . ":" . $st2->total;
            }

            $l++;
        }

    }


    require_once('footer.php');
?>