<?php
    $latest = $app->forum->getLatest();
?>

<table class='home-module home-module-light forum-latest dark'>
    <tbody>
<?php
    foreach($latest AS $post):
?>
        <tr class='<?=(!$post->viewed)?($post->watching)?'highlight':'new':'';?> <?=($post->closed)?'closed':'';?>'>
            <td><a href="/forum/<?=$post->slug;?>"><?=$post->title;?></a> <time datetime="<?=date('c', strtotime($post->latest));?>"><?=$app->utils->timeSince($post->latest);?></time></td>
            <td class='mobile-hide'><a href="/user/<?=$post->author;?>"><?=$post->author;?></a></td>
            <td class='mobile-hide'><a href="/user/<?=$post->latest_author;?>"><?=$post->latest_author;?></a></td>
            <td class='text-right'><span class="medal medal-green <?=($post->count==0)?'medal-green-dark':'';?>"><?=$post->count;?></span></td>
        </tr>
<?php
    endforeach;
?>
    </tbody>
</table>