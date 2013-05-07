                <sidebar class="col span_3 clr">
<?php
    if ($user->loggedIn) {
        include('widgets/feed.php');
    } else {
        include('widgets/login.php');
        include('widgets/register.php');
    }
    include('widgets/adverts.php');
?>
                </sidebar>
