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
            if (isset($_GET['request'])):
                include('elements/widgets/request.php');
            else:
                include('widgets/welcome.php');
            endif;

            // include('widgets/login.php');
            // include('widgets/register.php');
        }
        include('widgets/adverts.php');
?>
                </sidebar>
<?php
    endif;
?>