<?php
    require_once('header.php');

    $articles = new articles();
    $news_articles = $articles->get_articles(0);
?>
                    <section class='news'>
<?php
    foreach ($news_articles as $article) {
?>
                        <article>
                            <header class='title'>
                                <h1><a href='/news/<?=$article->slug;?>'><?=$article->title;?></a></h1>
                                <time pubdate datetime="<?=date('c', strtotime($article->submitted));?>"><?=date('d/m/Y', strtotime($article->submitted));?></time> - <?=$app->utils->username_link($article->username);?>
                                <a href='/news/<?=$article->slug;?>#comments' class='right'><?=$article->comments;?> comment<?=($article->comments == 1)?'':'s';?></a>
                            </header>
                            <?php
                                echo $app->bbcode->Parse($article->body);
                            ?>
                        </article>
<?php
    }
?>
                    </section>

<?php
    require_once('footer.php');
?>           