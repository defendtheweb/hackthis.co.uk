<?php
    define('PAGE_PUBLIC', true);
    $custom_css = array('levels.scss');
    $custom_js = array('levels.js');
    $page_title = 'Levels';
    require_once('header.php');

    $filter = null;
    if (isset($_GET['category'])) {
        $filter = strtolower($_GET['category']);
    }

    $sections = $app->levels->getList(null, $filter);

    echo $app->twig->render('levels_list.html', array('sections' => $sections, 'filter' => $filter));

    require_once('footer.php');
?>