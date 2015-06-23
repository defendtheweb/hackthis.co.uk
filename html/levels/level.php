<?php
	if (!isset($_GET['level']))
		header('Location: /levels/');

    $custom_css = array('levels.scss', 'highlight.css');
    $custom_js = array('levels.js', 'highlight.js');
    require_once('init.php');

    //Load level
    $group = $_GET['group'];
    if ($group === 'basic ') {
        $group = 'basic+';
    }
    $currentLevel = $app->levels->getLevel($group, $_GET['level']);

    if (!$currentLevel) {
		require_once('header.php');
		$app->utils->message('Level not found, <a href="/levels">return to index</a>');
		require_once('footer.php');
		die();
    }

    if (isset($_GET['get-hint'])) {
        if (isset($currentLevel->data['hint']))
            echo json_encode(array('status'=>true, 'hint' => $app->utils->parse($currentLevel->data['hint'])));
        else
            echo json_encode(array('status'=>false));
        die();
    }

    //Check if user completed level
    if (isset($currentLevel->data['form']) && $page = realpath($app->config['path'] . '/files/elements/levels/'.basename($currentLevel->data['form']).'_logic.php')) {
        include($page);
    } else {
        $app->levels->check($currentLevel);
    }

	require_once('header.php');

	$level = $currentLevel;
?>
	<div class='row'>
		<div class='col span_6 level-sidebar'>
<?php
    require_once('elements/levels/stats.php');
?>
		</div>

		<div class='col span_18 level-area'>
<?php
    require_once('elements/levels/header.php');
    require_once('elements/levels/level.php');
?>
		</div>
	</div>
<?php
    require_once('footer.php');
?>