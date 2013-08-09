<?php
    $custom_css = array('articles.scss', 'highlight.css');
    $custom_js = array('articles.js', 'highlight.js');
    if (!defined("_SIDEBAR")) define("_SIDEBAR", false);
    if (!defined("PAGE_PUBLIC")) define('PAGE_PUBLIC', false);

    require_once('header.php');

    $approved = true;
    if (isset($_GET['submissions']))
    	$approved = false;

    $limit = 25;
    $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;

	$articleList = $app->articles->getMyArticles($approved, $limit, $page);
?>
                    <section class="row">
<?php
        include('elements/sidebar_article.php');
?>    
                        <div class="col span_18 article-main">
                        	<a href="/articles/me<?=($approved?'?submissions':'');?>" class="button right"><?=(!$approved?'Approved':'Submitted');?> Articles</a>
							<h1><?=($approved?'Approved':'Submitted');?> Articles [<?=$articleList['total'];?>]</h1>
<?php
    if (!isset($articleList) || !$articleList || $articleList['total'] == 0):
?>
                            <div class='msg msg-error'>
                                <i class='icon-error'></i>
                                No <?=($approved?'approved':'submitted');?> articles found
                            </div>
<?php
    else:
?>
							<table class='striped'>
							    <thead>
							        <tr>
							        	<th>&nbsp;</th>
<?php 	if ($approved): ?>
							        	<th class='center'><i class='icon-comments'></i></th>
							        	<th class='center'><i class='icon-heart'></i></th>
<?php 	else: ?>
							        	<th width='25px'></th>
<?php 	endif; ?>
							        </tr>
							    </thead>
							    <tbody>
<?php
	    foreach ($articleList['articles'] as $article):
	        $article->title = $app->parse($article->title, false);
?>

<?php 		if ($approved): ?>
									<tr>
							        	<td><a href='<?=$article->uri;?>'><?=$article->title;?></a> <span class='dark'>&middot; <?=$article->cat_title;?></a></td>
							        	<td class='center'><?=$article->comments;?></td>
							        	<td class='center'><?=$article->favourites;?></td>
							        </tr>
<?php 		else: ?>
									<tr class='<?=($article->note?'declined':'awaiting');?>'>
							        	<td><a href='/articles/view.php?id=<?=$article->id;?>'><?=$article->title;?></a> <span class='dark'>&middot; <?=$article->cat_title;?></a></td>
<?php 			if ($article->note): ?>
							        	<td class='center'><span class='hint--left' data-hint='Declined: <?=str_replace("'", '`', str_replace("<br />", "", $app->parse($article->note, false, false)));?>'><i class='icon-cross'></i></span></td>
<?php 			else: ?>
							        	<td class='center'><span class='hint--left' data-hint='Awaiting review'><i class='icon-eye'></i></span></td>
<?php			endif; ?>
									</tr>
<?php
	 		endif;
		endforeach;
?>
							    </tbody>
							</table>
<?php

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