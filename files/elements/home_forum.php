<?php
    $latest = $app->forum->getLatest(5);
?>

<table class='home-module forum-latest home-module-light'>
    <thead>
        <th >Latest forum posts</th>
        <th class='mobile-hide'>Started by</th>
        <th class='mobile-hide'>Latest</th>
        <th class='text-right'>Replies</th>
    </thead>
    <tbody>
<?php
    foreach($latest AS $post):
?>
        <tr class='<?=(!$post->viewed)?($post->watching)?'highlight':'new':'';?> <?=($post->closed)?'closed':'';?>'>
            <td>
                <a href="/forum/<?=$post->slug;?>?post=latest"><?=$post->title;?></a>
                <div class="small thread-sections mobile-hide">
                    <a href="/forum/<?=$post->section_slug;?>" class="dark"><?=$post->section;?></a>
                </div>
            </td>
            <td class='mobile-hide'>
                <time datetime="<?=date('c', strtotime($post->started));?>"><?=$app->utils->timeSince($post->started);?></time><br/>
                <a class="small dark" href="/user/<?=$post->author;?>"><?=$post->author;?></a>
            </td>
            <td class='mobile-hide'>
                <time datetime="<?=date('c', strtotime($post->latest));?>"><?=$app->utils->timeSince($post->latest);?></time><br/>
                <a class="small dark" href="/user/<?=$post->latest_author;?>"><?=$post->latest_author;?></a>
            </td>
            <td class='text-right'><span class="medal medal-green <?=($post->count==0)?'medal-green-dark':'';?>"><?=$post->count;?></span></td>
        </tr>
<?php
    endforeach;
?>
    </tbody>
</table>