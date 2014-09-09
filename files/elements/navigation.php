<?php
    $levelSections = $app->levels->getList();
    $articleCategories = $app->articles->getCategories(null, false);
    $forumSections = $app->forum->getSections(null, false);

    echo $app->twig->render('navigation.html', array('user' => $app->user, 'articleCategories' => $articleCategories, 'levelSections' => $levelSections, 'forumSections' => $forumSections));
?>