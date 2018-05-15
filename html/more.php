<?php
    define("PAGE_PUBLIC", true);

    $custom_css = array('faq.scss');
    require_once('init.php');
    $app->page->title = 'More';
    require_once('header.php');
?>
<h1>More</h1>
<ul>
    <li><a href="/irc">IRC</a><br>
     -&nbsp;&nbsp;&nbsp;&nbsp; <a href="/irc/stats.php">Stats</a>
    </li>
<?php
if ($app->user->loggedIn):
?>
    <li><a href='/medals.php'>Medals</a></li>
<?php
endif;
?>
    <li><a href='/contact'>Contact us</a></li>
<?php
    if ($app->user->loggedIn):
?>
    <li><a href='/donator.php'>Donate</a></li>
    <li><a href='/git.php'>Contribute</a></li>
<?php
endif;
?>
</ul>
<?php  
    require_once('footer.php');
?>
