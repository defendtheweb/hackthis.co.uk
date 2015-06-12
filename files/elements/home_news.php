<?php
    $latestNews = $app->articles->getArticles(0, 1);

    if (isset($latestNews['articles'][0])):

    $latestNews = $latestNews['articles'][0];
    $latestNews->title = $app->parse($latestNews->title, false);
    $latestNews->body = $app->parse($latestNews->body);

	    if (strtotime($latestNews->submitted) > strtotime('14 days ago')):
?>

<div class='home-module home-news clr'>
    <?php
        $share = new stdClass();
        $share->item = $latestNews->id;
        $share->right = true;
        $share->comments = $latestNews->comments;
        $share->title = $latestNews->title;
        $share->link = "/news/{$latestNews->slug}";
        $share->favourites = $latestNews->favourites;
        $share->favourited = $latestNews->favourited;
        include("elements/share.php");
    ?>
    <h2 class='no-margin'><a href='<?=$latestNews->uri;?>'><?=$latestNews->title;?></a></h2>
    <div class='dark'>
        <time pubdate datetime="<?=date('c', strtotime($latestNews->submitted));?>"><?=$app->utils->timeSince($latestNews->submitted);?></time>
        <?php if ($latestNews->updated > 0): ?>&#183; updated <time pubdate datetime="<?=date('c', strtotime($latestNews->updated));?>"><?=$app->utils->timeSince($latestNews->updated);?></time><?php endif; ?>
        <?php if (isset($latestNews->username)) { echo "&#183; by {$app->utils->userLink($latestNews->username)}"; }?>
    </div>

    <p><?=$latestNews->body;?></p>
    <div class='right'><a href='<?=$latestNews->uri;?>'>Read post</a> &middot; <a href='/news'>More news</a></div>
</div>

<?php
	endif;
    endif;
?>
