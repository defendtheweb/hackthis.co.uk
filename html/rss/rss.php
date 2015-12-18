<?php
	include_once 'class.rss.php';

	$feedClass = new rss();
	$rssFeed = $feedClass->generateRSS(feedType::RSS);

	header('Content-Type: application/xml');
	echo $rssFeed;
?>
