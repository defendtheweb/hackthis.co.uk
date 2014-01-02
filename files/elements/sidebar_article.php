<div class="col span_6 article-sidebar">
	<div class='sticky'>
<?php if (isset($myArticle) && $myArticle): ?>
        <a class='button' href='/articles/me?submissions'><i class='icon-caret-left'></i> Submitted Articles</a>
        <br/><br/>
<?php elseif ((isset($category) && $category) || isset($article)): ?>
		<a class='button' href='/articles/'><i class='icon-caret-left'></i> Article Index</a>
        <br/><br/>
<?php elseif ($app->user->loggedIn): ?>
        <a class='button' href='/articles/submit'><i class='icon-books'></i> Submit Article</a>
        <a class='button' href='/articles/me'>My Articles</a>
        <br/><br/>
<?php endif; ?>
		<h2>Categories</h2>
        <ul class='categories'>
<?php
            $parent = null;
            $cat_id = null;
            if (isset($category) && $category) {
                if (isset($category->parent))
                    $parent = $category->parent;
                $cat_id = $category->id;
            } else if (isset($article) && $article) {
                $parent = $article->parent;
                $cat_id = $article->cat_id;
            }

            $categories = $app->articles->getCategories(null, false);
            foreach($categories as $cat) {
                $app->articles->printCategoryList($cat, true, '', $parent, $cat_id);
            }
?>
        </ul>
<?php
    // if ((isset($category) && $category) || isset($article)):
    if (false):
?>
        <h2>Top Articles</h2>
        <ul class='hot'>
<?php
        $hot = $app->articles->getHotArticles();
        foreach($hot AS $hotArticle) {
?>
            <li><a href='<?=$hotArticle->slug;?>'><?=$hotArticle->title;?></a></li>
<?php
        }
    endif;
?>
        </ul>

<?php
    include('widgets/adverts.php');
?>
	</div>
</div>