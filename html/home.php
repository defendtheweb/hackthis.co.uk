<?php
    if(!defined('PAGE_PUBLIC'))
        define('PAGE_PUBLIC', false);
    
    require_once('init.php');
    
    $minifier->add_file('highlight.js', 'js');
    $minifier->add_file('articles.js', 'js');

    $minifier->add_file('home.scss', 'css');
    $minifier->add_file('highlight.css', 'css');
    $minifier->add_file('articles.css', 'css');

    // Set canonical link
    $app->page->canonical = "https://www.hackthis.co.uk";

    require_once('header.php');
?>
                    <section class='home'>
<?php
    include('elements/home_latest_articles.php');
    if (!$app->user->loggedIn) {
        include('elements/home_intro.php');
    } else {
        include('elements/home_news.php');
    }
    include('elements/home_forum.php');
    include('elements/home_articles.php');
?>
                    </section>
<?php
    require_once('footer.php');
?>           