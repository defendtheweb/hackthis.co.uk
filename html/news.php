<?php
    if(!defined('PAGE_PUBLIC'))
        define('PAGE_PUBLIC', false);

    require_once('header.php');

    $articles = new articles();

    $single = false;
    if (isset($_GET['slug'])) {
        $single = true;
        $news_article = $articles->get_article($_GET['slug']);

        $news_articles = array($news_article);
    } else {
        $news_articles = $articles->get_articles(0);
    }
?>
                    <section class='news'>
<?php
    foreach ($news_articles as $article):
        $article->title = $app->parse($article->title, false);
        $article->body = $app->parse($article->body);
?>
                        <article>
                            <header class='title clearfix'>
                                <?php if ($user->admin_pub_priv): ?>
                                    <a href='/admin/news.php?action=delete&slug=<?=$article->slug;?>' class='button right'><i class='icon-trash'></i></a>
                                    <a href='/admin/news.php?action=edit&slug=<?=$article->slug;?>' class='button right'><i class='icon-pencil'></i></a>
                                <?php endif; ?>
                                <h1><a href='/news/<?=$article->slug;?>'><?=$article->title;?></a></h1>
                                <time pubdate datetime="<?=date('c', strtotime($article->submitted));?>"><?=date('d/m/Y', strtotime($article->submitted));?></time>
                                <?php if ($article->updated > 0): ?>&#183; updated <time pubdate datetime="<?=date('c', strtotime($article->updated));?>"><?=date('d/m/Y', strtotime($article->updated));?></time><?php endif; ?>
                                <?php if (isset($article->username)) { echo "&#183; {$app->utils->username_link($article->username)}"; }?>

                                <?php
                                    $share = new stdClass();
                                    $share->item = $article->id;
                                    $share->right = true;
                                    $share->comments = $article->comments;
                                    $share->title = $article->title;
                                    $share->link = "/news/{$article->slug}";
                                    $share->favourites = $article->favourites;
                                    $share->favourited = $article->favourited;
                                    include("elements/share.php");
                                ?>
                            </header>
                            <?php
                                echo $article->body;
                            ?>
                        </article>
<?php
        if ($single) {
            $comments = array("id"=>$article->id);
            include('elements/comments.php');
        }


    endforeach;
?>
                    </section>

<?php
    require_once('footer.php');
?>           