<?php
    $custom_css = array('articles.scss', 'highlight.css');
    $custom_js = array('articles.js', 'highlight.js');
    if (!defined("_SIDEBAR")) define("_SIDEBAR", false);
    if (!defined("PAGE_PUBLIC")) define('PAGE_PUBLIC', true);

    require_once('header.php');

    $limit = 5;
    $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;

    $articles = new articles();
    if (isset($_GET['slug'])) {
        $category = $articles->getCategory($_GET['slug']);
        if (!$category) {
            header('Location: /articles');
            die();
        }
        $articleList = $articles->getArticles($category->id, $limit, $page);
    } else {
        $category = null;
        $articleList = $articles->getArticles(null, $limit, $page);
    }
?>
                    <section class="row">
<?php
        include('elements/sidebar_article.php');
?>    
                        <div class="col span_18 article-main">
<?php
    if ($category):
?>
                            <h1><?=$category->title;?> [<?=$articleList['total'];?>]</h1>
<?php
    endif;
    if (!isset($articleList) || !$articleList || $articleList['total'] == 0):
?>
                            <div class='msg msg-error'>
                                <i class='icon-error'></i>
                                No articles found
                            </div>
<?php
    else:

        if (!$category):
            $n = 0;
            $hot = $articles->getHotArticles();
            foreach($hot AS $article):
                if ($n++ == 3)
                    break;

                if ($n == 1):
?>
                            <div class="row fluid">
<?php           endif; ?>
                                <a href='<?=$article->slug;?>' class="col span_8 <?=isset($article->thumbnail)?'img':'';?> thumbnail">
<?php               if (isset($article->thumbnail)): ?>
                                    <img src="/users/images/200/4:3/<?=$article->thumbnail;?>">
<?php               endif; ?>
                                    <div class="caption">
                                        <h3><?=$article->title;?></h3>
<?php               if (!isset($article->thumbnail)): ?>
                                    <p><?=$article->body;?></p>
<?php               endif; ?>
                                    </div>
                                </a>
<?php           if ($n == 3): ?>
                            </div>
<?php
               endif;
            endforeach;
        endif;

        $n = 0;
        foreach ($articleList['articles'] as $article):
            $article->title = $app->parse($article->title, false);
            $article->body = substr($app->parse($article->body, false), 0, 300) . '...';
?>
                            <article class='bbcode body index'>
                                <header class='title clearfix'>
                                    <h1><a href='<?=$article->uri;?>'><?=$article->title;?></a></h1>
                                </header>
                                <?php
                                    echo $article->body;
                                ?>
                                <a href='<?=$article->uri;?>'>continue reading</a>
                            </article>
<?php
        endforeach;

        if (ceil($articleList['total']/$limit) > 1) {
            $pagination = new stdClass();
            $pagination->current = $articleList['page'];
            $pagination->count = ceil($articleList['total']/$limit);
            $pagination->root = '?page=';
            include('elements/pagination.php');
        }
    endif;
?>
                        </div>
                    </section>
<?php
   require_once('footer.php');
?>