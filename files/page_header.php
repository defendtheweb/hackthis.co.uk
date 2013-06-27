<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>HackThis!!</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link href='//fonts.googleapis.com/css?family=Orbitron|Lato:400,700' rel='stylesheet' type='text/css'>

        <?= $minifier->load("css"); ?>
        <script src="/files/js/modernizr-2.6.2.min.js"></script>
        <!--[if lt IE 9]>
            <script src="/files/js/respond.min.js"></script>
            <script src="/files/js/html5shiv.js"></script>
        <![endif]-->
    </head>
    <body>
        <div id="header-wrap" class="container">
            <header>
                <div class="col span_11 banner">
                    <a href='/'>&nbsp;</a>
                </div>
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
                <br style='clear: both'/>
            </header>
        </div>

<?php
    include('elements/navigation.php');

    //Calculate document width
    if (!defined('_SIDEBAR') || _SIDEBAR)
        $span = '18';
    else
        $span = '24';
?>

        <div id="body-wrap" class="container row">
            <section id="content-wrap" class="row">
                <article id="main-content" class="col span_<?=$span;?> clr">