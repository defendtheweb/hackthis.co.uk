<?php
    $latest = $app->forum->getLatest();
?>

<table class='forum-latest dark'>
    <tbody>
<?php
    foreach($latest AS $post):
?>
        <tr class='<?=(!$post->viewed)?($post->watching)?'highlight':'new':'';?> <?=($post->closed)?'closed':'';?>'>
            <td><a href="/forum/<?=$post->slug;?>"><?=$post->title;?></a> <span>2 hours ago</span></td>
            <td><a href="/user/<?=$post->author;?>"><?=$post->author;?></a></td>
            <td><a href="/user/<?=$post->latest_author;?>"><?=$post->latest_author;?></a></td>
            <td class='text-right'><span class="medal medal-green <?=($post->count==0)?'medal-green-dark':'';?>"><?=$post->count;?></span></td>
        </tr>
<?php
    endforeach;
?>
    </tbody>
</table>