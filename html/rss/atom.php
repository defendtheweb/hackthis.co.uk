<?php
	include_once 'class.rss.php';

	$feedClass = new rss();
	$rssFeed = $feedClass->generateRSS(feedType::ATOM);

	header('Content-Type: application/xml');
	echo $rssFeed;
?>
