<?php
    $hot = $app->articles->getHotArticles();
?>

<div class='home-articles clr'>
    <div class='home-module home-module-light col span_18'>
        <h3 class='no-margin'>Featured articles</h3>
        <div class='clr'>
<?php
    $n = 0;
    foreach($hot AS $article):
        if ($n++ == 2)
            break;
?>
                <a href='<?=$article->slug;?>' class="col span_12 <?=isset($article->thumbnail) || isset($article->video)?'img':'';?> thumbnail" data-overlay="<?=$article->category;?>">
<?php   if (isset($article->thumbnail)): ?>
                    <img src="/users/images/200/4:3/<?=$article->thumbnail;?>">
<?php   elseif (isset($article->video)): ?>
                    <img src="http://img.youtube.com/vi/<?=$article->video;?>/0.jpg">
<?php   endif; ?>
                    <div class="caption">
                        <h3><?=$article->title;?></h3>
                    <p><?=$app->parse($article->body, false);?></p>
                    </div>
                </a>
<?php
    endforeach;
?>
        </div>
    </div>
    <div class='home-module col span_6'>
        <h3 class='no-margin'>Around the internet</h3>
    </div>
</div>