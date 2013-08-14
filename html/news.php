<?php
    if(!defined('PAGE_PUBLIC'))
        define('PAGE_PUBLIC', false);
    
    require_once('init.php');
    
    $minifier->add_file('highlight.js', 'js');
    $minifier->add_file('articles.js', 'js');

    $minifier->add_file('highlight.css', 'css');
    $minifier->add_file('articles.css', 'css');

    require_once('header.php');


    $limit = 5;
    $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;

    $single = false;
    if (isset($_GET['slug'])) {
        $single = true;
        $newsArticle = $app->articles->getArticle($_GET['slug'], true);
        if ($newsArticle)
            $newsArticles['articles'] = array($newsArticle);
    } else {
        $newsArticles = $app->articles->getArticles(0, $limit, $page);
    }
?>
                    <section class='news'>
<?php
    if (!isset($newsArticles) || !count($newsArticles)):
?>
                        <div class='msg msg-error'>
                            <i class='icon-error'></i>
                            Article not found
                        </div>
<?php
    else:
        if (!$single)
            include('elements/forum_latest.php');

        foreach ($newsArticles['articles'] as $article):
            $article->title = $app->parse($article->title, false);
            $article->body = $app->parse($article->body);
?>
                        <article class='bbcode body'>
                            <header class='title clearfix'>
                                <?php if ($single && $app->user->admin_pub_priv): ?>
                                    <a href='/admin/articles.php?action=edit&slug=<?=$article->slug;?>' class='button right'><i class='icon-pencil'></i></a>
                                <?php endif; ?>
                                <h1><a href='<?=$article->uri;?>'><?=$article->title;?></a></h1>
                                <time pubdate datetime="<?=date('c', strtotime($article->submitted));?>"><?=$app->utils->timeSince($article->submitted);?></time>
                                <?php if ($article->updated > 0): ?>&#183; updated <time pubdate datetime="<?=date('c', strtotime($article->updated));?>"><?=$app->utils->timeSince($article->updated);?></time><?php endif; ?>
                                <?php if (isset($article->username)) { echo "&#183; by {$app->utils->username_link($article->username)}"; }?>

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
        endforeach;

        if ($single) {
            $comments = array("id"=>$article->id,"title"=>$article->title,"count"=>$article->comments);
            include('elements/comments.php');
        } else {
            if (ceil($newsArticles['total']/$limit) > 1) {
                $pagination = new stdClass();
                $pagination->current = $newsArticles['page'];
                $pagination->count = ceil($newsArticles['total']/$limit);
                $pagination->root = '?page=';
                include('elements/pagination.php');
            }
        }

    endif;
?>
                    </section>

<?php
    require_once('footer.php');
?>           