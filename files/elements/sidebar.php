                <sidebar class="col span_6 clr">
<?php
    if ($user->loggedIn) {
        include('widgets/dashboard.php');
        include('widgets/feed.php');
    } else {
        include('widgets/login.php');
        include('widgets/register.php');
    }
    include('widgets/adverts.php');
?>
                </sidebar>
