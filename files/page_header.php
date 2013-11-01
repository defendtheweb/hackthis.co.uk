<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>HackThis!!<?=$app->page->title?' - '.$app->page->title:' - The Hackers Playground';?></title>
        <meta name="description" content="Want to learn about hacking, hackers and network security. Try our hacking challenges or join our community to discuss the latest software and cracking tools.">
        <meta name="keywords" content="hack this, hackers playground, hacking challenges, hacking forums, hackers, website security, how to secure a website, how to hack, articles, cracking, phreaking, network security">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">

        <link href="/favicon.png" rel="icon" id="basic-favicon" type="images/png" />
        <link rel="shortcut icon" href="/favicon.ico" type="image/vnd.microsoft.icon" /> 
        <link rel="icon" href="/favicon.ico" type="image/vnd.microsoft.icon" />

<?php
    echo isset($app->page->canonical)?"        <link rel='canonical' href='{$app->page->canonical}'/>\n":'';
    echo isset($app->page->prev)?"        <link rel='prev' href='{$app->page->prev}'/>\n":'';
    echo isset($app->page->next)?"        <link rel='next' href='{$app->page->next}'/>\n":'';

    if (count($app->page->meta)) {
        foreach($app->page->meta AS $id=>$content) {
            echo "        <meta name='{$id}' content='{$content}'>\n";
        }
    }
?>
        <meta property="fb:app_id" content="163820353667417" />
        <meta name='twitter:site' content='@hackthisuk'>
        <meta name='og:site_name' content='HackThis!!'>

        <link href='//fonts.googleapis.com/css?family=Orbitron|Lato:400,700' rel='stylesheet' type='text/css'>

        <?= $minifier->load("css"); ?>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/files/js/jquery-1.9.1.min.js"><\/script>')</script>
        <script src="<?php $s = $app->config['socket']; echo $s['address']; ?>/socket.io/socket.io.js"></script>
<?php
    if (isset($currentLevel) && isset($currentLevel->data['code']->pos1)) {
        echo '        '.$currentLevel->data['code']->pos1."\n";
    }
?>
        <script src="/files/js/modernizr-2.6.2.min.js"></script>
        <!--[if lt IE 9]>
            <script src="/files/js/respond.min.js"></script>
            <script src="/files/js/html5shiv.js"></script>
        <![endif]-->
    </head>
    <body <?php if ($app->user) echo "data-username='{$app->user->username}' data-key='".md5($app->user->username)."'";?>>
<?php
    if (!isset($_COOKIE["member"]) || !$_COOKIE["member"]):
?>

    <div class='cookies container'>
        <h3>Privacy &amp; Cookies</h3>
        This website users cookies. By continuing to use this site you are agreeing to our use of cookies.
    </div>

<?php
    endif;    

    if ($app->user->loggedIn || !(defined('LANDING_PAGE') && LANDING_PAGE)):
?>
    <div class="page-wrap">
<?php
    if (isset($currentLevel) && isset($currentLevel->data['code']->pos2)) {
        echo '        '.$currentLevel->data['code']->pos2 . "\n";
    }
?>
        <div id="header-wrap" class="container clr">
            <header>
                <div class="col span_11 banner">
                    <a href='/'>&nbsp;</a>
                </div>
<?php
    if (!$app->user->loggedIn || !$app->user->donator):
?>
                <div class="col span_13 advert">
                    <script type="text/javascript"><!--
                        google_ad_client = "ca-pub-1120564121036240";
                        /* Header */
                        google_ad_slot = "5947620469";
                        google_ad_width = 468;
                        google_ad_height = 60;
                        //-->
                    </script>
                    <script type="text/javascript" src="//pagead2.googlesyndication.com/pagead/show_ads.js"></script>
                </div>
<?php
    endif;
?>
            </header>
        </div>
<?php
        include('elements/navigation.php');
    else:
?>
      <div class="page-wrap">
<?php
    endif;

    //Calculate document width
    if (!defined('_SIDEBAR') || _SIDEBAR)
        $span = '18';
    else
        $span = '24';
?>
        <div id="body-wrap" class="container row">
            <section id="content-wrap" class="row">
                <article id="main-content" class="col span_<?=$span;?> clr">
