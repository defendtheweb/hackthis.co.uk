<?php
    $custom_css = array('articles.scss', 'highlight.css');
    $custom_js = array('articles.js', 'highlight.js');
    if (!defined("_SIDEBAR")) define("_SIDEBAR", false);
    if (!defined("PAGE_PUBLIC")) define('PAGE_PUBLIC', true);

    require_once('init.php');

    $app->page->title = 'Articles - Contributors';
    $app->page->canonical = 'https://www.hackthis.co.uk/articles/contributors';

    if (!isset($_GET['author'])) {
        $contributors = $app->articles->getContributors();
    }

    require_once('header.php');
?>
                    <section class="row">
<?php
        include('elements/sidebar_article.php');
?>    
                        <div class="col span_18">
                            <h1>Contributors</h1>
                            <ul class='contributors plain'>
<?php foreach($contributors AS $contributor): ?>
                                <li>
                                    <div class='header'>
                                        <?=$contributor->username;?>
                                        <span class='right article-count'><i class="icon-books"></i> <?=$contributor->count;?> articles</span>
                                    </div>
                                    <ul class='articles plain'>
<?php
        foreach ($contributor->articles as $article):
            $article->title = $app->parse($article->title, false);
?>
                                        <li>
                                            <a href='<?=$article->uri;?>'><?=$article->title;?></a>
                                        </li>
<?php
        endforeach;
?>
                                    </ul>
                                </li>
<?php endforeach; ?>
                            </ul>
                        </div>
                    </section>
<?php
   require_once('footer.php');
?>