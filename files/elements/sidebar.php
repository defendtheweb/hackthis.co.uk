<?php
    if (!defined('_SIDEBAR') || _SIDEBAR):
?>
                <sidebar class="col span_6 clr">
<?php
        if ($app->user->loggedIn) {
            include('widgets/dashboard.php');
            include('widgets/feed.php');
            include('widgets/scoreboard.php');
        } else {
            include('widgets/welcome.php');
        }
        include('widgets/adverts.php');
?>
                </sidebar>
<?php
    endif;
?>