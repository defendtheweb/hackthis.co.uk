<?php
	$list = $app->utils->getOnlineList();
	$list_count = count($list);
	echo $app->twig->render('widget_online.html', array('count' => $list_count, 'users' => $list));
?>