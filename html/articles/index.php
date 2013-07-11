<?php
    $custom_css = array('articles.scss', 'highlight.css');
    $custom_js = array('articles.js', 'highlight.js');
    if (!defined("_SIDEBAR")) define("_SIDEBAR", false);
    if (!defined("PAGE_PUBLIC")) define('PAGE_PUBLIC', true);

    require_once('header.php');

	$articles = new articles();
    if (isset($_GET['slug'])) {
    	$category = $articles->getCategory($_GET['slug']);
		if (!$category)
			header('Location: /articles');
		$articleList = $articles->getArticles($category->id);
   	} else {
   		$category = null;
   		$articleList = $articles->getArticles();
   	}
?>
                    <section class="row">
<?php
        include('elements/sidebar_article.php');
?>    
                        <div class="col span_18 article-main">
<?php
    if (!isset($articleList) || !$articleList):
?>
                            <div class='msg msg-error'>
                                <i class='icon-error'></i>
                                No articles found
                            </div>
<?php
    else:
	    foreach ($articleList as $article):
	        $article->title = $app->parse($article->title, false);
	        $article->body = substr($app->parse($article->body, false), 0, 300) . '...';
?>
	                        <article class='bbcode body index'>
	                            <header class='title clearfix'>
	                                <?php if ($user->admin_pub_priv): ?>
	                                    <a href='/admin/articles.php?action=edit&slug=<?=$article->slug;?>' class='button right'><i class='icon-pencil'></i></a>
	                                <?php endif; ?>
	                                <h1><a href='<?=$article->uri;?>'><?=$article->title;?></a></h1>
	                                <time pubdate datetime="<?=date('c', strtotime($article->submitted));?>"><?=$app->utils->timeSince($article->submitted);?></time>
	                                <?php if ($article->updated > 0): ?>&#183; updated <time pubdate datetime="<?=date('c', strtotime($article->updated));?>"><?=$app->utils->timeSince($article->updated);?></time><?php endif; ?>
	                                <?php if (isset($article->cat_title)) { echo "&#183; <a href='{$article->cat_slug}'>{$article->cat_title}</a>"; }?>
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
	                            <a href='<?=$article->uri;?>'>continue reading</a>
	                        </article>
<?php
		endforeach;
	endif;
?>
	                    </div>
                    </section>
<?php
   require_once('footer.php');
?>