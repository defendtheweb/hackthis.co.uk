<?php
    $hot = $app->articles->getHotArticles();
?>

<div class='home-articles clr'>
    <div class='home-module home-module-light col span_24'>
        <h3 class='no-margin'>Featured articles</h3>
        <div class='clr'>
<?php
    $n = 0;
    foreach($hot AS $article):
        if ($n++ == 3)
            break;
?>
                <a href='<?=$article->slug;?>' class="col span_8 <?=isset($article->thumbnail) || isset($article->video)?'img':'';?> thumbnail" data-overlay="<?=$article->category;?>">
<?php   if (isset($article->thumbnail) && $article->thumbnail): ?>
                    <img src="https://www.hackthis.co.uk/images/200/4:3/<?=$article->thumbnail;?>" alt="<?=$article->title;?> thumbnail">
<?php   elseif (isset($article->video)): ?>
                    <img src="https://img.youtube.com/vi/<?=$article->video;?>/0.jpg" alt="<?=$article->title;?> thumbnail">
<?php   endif; ?>
                    <div class="caption">
                        <h3><?=$article->title;?></h3>
<?php       if (!(isset($article->thumbnail) && $article->thumbnail) && !isset($article->video)): ?>
                        <p><?=$app->parse($article->body, false);?></p>
<?php       endif; ?>
                    </div>
                </a>
<?php
    endforeach;
?>
        </div>
    </div>
</div>
