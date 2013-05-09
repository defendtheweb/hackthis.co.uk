<?php
    require_once('header.php');

    $articles = new articles();

    if (isset($_GET['slug'])) {
        $news_article = $articles->get_article($_GET['slug']);

        $news_articles = array($news_article);
    } else {
        $news_articles = $articles->get_articles(0);
    }
?>
                    <section class='news'>
<?php
    foreach ($news_articles as $article):
?>
                        <article>
                            <header class='title'>
                                <?php if ($user->news_priv): ?>
                                    <a href='/admin/news.php?action=delete&slug=<?=$article->slug;?>' class='button right'><i class='icon-trash'></i></a>
                                    <a href='/admin/news.php?action=edit&slug=<?=$article->slug;?>' class='button right'><i class='icon-pencil'></i></a>
                                <?php endif; ?>
                                <h1><a href='/news/<?=$article->slug;?>'><?=$article->title;?></a></h1>
                                <span class='small'>
                                    <time pubdate datetime="<?=date('c', strtotime($article->submitted));?>"><?=date('d/m/Y', strtotime($article->submitted));?></time> - <?=$app->utils->username_link($article->username);?>
                                </span>
                                <a href='/news/<?=$article->slug;?>#comments' class='small right'><?=$article->comments;?> comment<?=($article->comments == 1)?'':'s';?></a>
                            </header>
                            <?php
                                echo $app->bbcode->Parse($article->body);
                            ?>
                        </article>
<?php
    endforeach;
?>
                    </section>

<?php
    require_once('footer.php');
?>           