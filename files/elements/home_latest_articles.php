<?php
    $latestNews = $app->articles->getArticles(null, 1);
    $latestNews = $latestNews['articles'][0];

    if (strtotime('-5 days') < strtotime($latestNews->submitted)):

    $latestNews->title = $app->parse($latestNews->title, false);
    $latestNews->body = $app->parse($latestNews->body, false);
?>

<div class='home-module home-module-light home-news clr'>
    <?php
        $share = new stdClass();
        $share->item = $latestNews->id;
        $share->right = true;
        $share->comments = $latestNews->comments;
        $share->title = $latestNews->title;
        $share->link = "/articles/{$latestNews->slug}";
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
</div>

<?php
    endif;
?>