<div class="col span_6 article-sidebar">
	<div class='sticky'>
<?php if ($category || isset($article)): ?>
		<a class='button' href='/articles/'><i class='icon-caret-left'></i> Article Index</a>
<?php else: ?>
        <a class='button' href='/articles/submit'><i class='icon-books'></i> Submit Article</a>
<?php endif; ?>
        <br/><br/>
		<h2>Categories</h2>
        <ul class='categories'>
<?php
            $parent = null;
            if (isset($category) && $category)
                $parent = $category->parent;
            else if (isset($article))
                $parent = $article->parent;

            $categories = articles::getCategories(null, false);
            foreach($categories as $cat) {
                articles::printCategoryList($cat, true, '', $parent);
            }
?>
        </ul>

        <h2>Top Articles</h2>
        <ul class='hot'>
<?php
        $hot = $articles->getHotArticles();
        foreach($hot AS $hotArticle) {
?>
            <li><a href='<?=$hotArticle->slug;?>'><?=$hotArticle->title;?></a></li>
<?php
        }
?>
        </ul>
	</div>
</div>