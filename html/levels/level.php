<?php
	if (!isset($_GET['group']) || !isset($_GET['level']))
		header('Location: /levels/');

    $custom_css = array('levels.scss');
    require_once('init.php');

    //Load level
    $currentLevel = $app->levels->getLevel(urlencode($_GET['group']), $_GET['level']);

    if (!$currentLevel) {
		require_once('header.php');
		$app->utils->message('Level not found, <a href="/levels">return to index</a>');
		require_once('footer.php');
		die();
    }

	require_once('header.php');

	$level = $currentLevel;
	print_r($level);
?>
	<div class='row'>
		<div class='col span_6 level-sidebar'>
<?php
    require_once('elements/levels/stats.php');
?>
		</div>

		<div class='col span_18'>
<?php
    require_once('elements/levels/header.php');
    require_once('elements/levels/level.php');
?>
		</div>
	</div>
<?php
    require_once('footer.php');
?>