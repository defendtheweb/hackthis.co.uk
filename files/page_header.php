<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
        <meta charset="utf-8">
<?php if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)): ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<?php endif; ?>
        <title>HackThis!!<?=isset($app->page->title) && $app->page->title?' - '.$app->page->title:' - The Hackers Playground';?></title>
        <meta name="description" content="<?=isset($app->page->desc) && $app->page->desc?$app->page->desc:'Want to learn about hacking, hackers and network security. Try our hacking challenges or join our community to discuss the latest software and cracking tools.';?>">
        <meta name="keywords" content="hack this, hackers playground, hacking challenges, hacking forums, hackers, website security, how to secure a website, how to hack, articles, network security">
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
        <meta property='og:site_name' content='HackThis!!'>

        <link href='https://fonts.googleapis.com/css?family=Orbitron|Lato:400,700' rel='stylesheet' type='text/css'>

        <?= $minifier->load("css"); ?>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/0.9.6/socket.io.min.js"></script>
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

<?php
    // Mark a user as a returning user only when logged in
    if ($app->user->loggedIn):
?>
        <script>
            if (Modernizr.localstorage) {
                window.localStorage.setItem('ht_returning_user', 'true');
            }
        </script>
<?php
    endif;
?>
    </head>
    <body class='theme-<?php echo $app->theme; ?>' <?php if ($app->user) echo "data-username='{$app->user->username}' data-key='".$app->user->csrf_basic."'";?>>
<?php
    if (!isset($_GET['view']) || $_GET['view'] != 'app'):
        if (!isset($_COOKIE["member"]) || !$_COOKIE["member"]):
?>

    <div class='cookies container'>
        <h3>Privacy &amp; Cookies</h3>
        This website uses cookies. By continuing to use this site you are agreeing to our use of cookies.
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
                $ads = array(
                    array('nullsec', 'http://www.nullsecurity.net'),
                    array('walker', 'http://www.walkerlocksmiths.co.uk/')
                );

                $id = array_rand($ads);
                $image = $ads[$id][0];
                $link = $ads[$id][1];
?>
                <div class="col span_13 advert">
                    <a href='<?=$link;?>' taget='_blank' class='hide-external'>
                        <img src='/files/images/ad_banner_<?=$image;?>.png'/>
                    </a>
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
    endif;

    //Calculate document width
/*    if (!$app->user->loggedIn && defined('LANDING_PAGE') && LANDING_PAGE)
        $span = '16';
    else */ if (!defined('_SIDEBAR') || _SIDEBAR)
        $span = '18';
    else
        $span = '24';
?>
        <div id="body-wrap" class="container row">
            <section id="content-wrap" class="row">
                <article id="main-content" class="col span_<?=$span;?> clr">
