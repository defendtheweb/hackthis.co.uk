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
            graphData = [<?php foreach($result AS $res) { echo '{ "date" : "' . $res->date . '", "count" : ' . $res->count . ' }, '; } ?>];
        </script>

        <div class='graph'></div>
        <script type="text/javascript" src="/files/js/d3.js"></script>

<?php
    require_once('footer.php');
?>