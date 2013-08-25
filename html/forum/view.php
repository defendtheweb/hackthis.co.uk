<?php
    if (!isset($forum))
        header('Location: /forum');

    if (isset($_GET['page']) && is_numeric($_GET['page']))
        $page = $_GET['page'];
    else
        $page = 1;

    $thread = $forum->getThread($thread->id, $page);
    if (!$thread)
        header('Location: /forum');


    if (isset($_GET['submit']) && isset($_POST['body'])) {
        $submitted = $forum->newPost($thread->id, $_POST['body']);

        if ($submitted)
            header('Location: '. strtok($_SERVER["REQUEST_URI"], '?') . '?submitted');
    }

    $section = $thread->section;

    $breadcrumb = $forum->getBreadcrumb($section, true) . "<a href='/forum/{$thread->slug}'>{$thread->title}</a>";

    require_once('header.php');
?>
                    <section class="row">
<?php
        include('elements/sidebar_forum.php');
?>    
                        <div class="col span_18 forum-main" data-thread-id="<?=$thread->id;?>">

<?php if ($app->user->loggedIn): ?>
    <a href='#' class='post-reply button right'><i class='icon-chat'></i> Post reply</a>
<?php   if ($thread->watching): ?>
    <a href='#' class='post-watch post-unwatch button right'><i class='icon-eye-blocked'></i> Unwatch</a>
<?php   else: ?>
    <a href='#' class='post-watch button right'><i class='icon-eye'></i> Watch</a>
<?php
        endif;
      endif;
?>

                            <h1 class='no-margin'><?=$thread->title;?></h1>
                            <?=$breadcrumb;?><br/><br/>

                            <ul class='post-list'>
<?php
    $post = $thread->question;
?>
                                <li>
                                    <div class="post_header clr">
                                        <a href="/user/<?=$post->username;?>"><img src="/users/images/29/1:1/bfb4871a2dd1e1f372a6784458b6dce9.jpg" class="user_img"> <?=$post->username;?></a>
                                        <div class="karma small">
                                            Karma: <span class="karma"><a href="#" data-id="18241" class="rep rep_down">&lt;</a> 0 <a href="#" data-id="18241" class="rep rep_up">&gt;</a></span>
                                        </div>
                                    </div>
                                    <div class="post_body"><?=$post->body;?></div>
<?php   if ($post->edited > 0): ?>
                                    <div class="post_footer small">
                                        <i>Edited 3 hours ago by</i>
                                    </div>
<?php   endif; ?>
                                </li>
                            </ul>
                            <div class='forum-pagination'>
                                Viewing <?=count($thread->posts);?> repl<?=(count($thread->posts) == 1)?'y':'ies';?> - <?=$thread->p_start;?> through <?=$thread->p_end;?> (of <?=$thread->replies;?> total)
                            </div>
                            <ul class='post-list reply-list'>
<?php 
    foreach($thread->posts AS $post):
        $post->body = $app->parse($post->body);
?>
                                <li>
                                    <div class="post_header clr">
                                        <a href="/user/<?=$post->username;?>"><img src="/users/images/29/1:1/bfb4871a2dd1e1f372a6784458b6dce9.jpg" class="user_img"> <?=$post->username;?></a>
                                        <div class="karma small">
                                            Karma: <span class="karma"><a href="#" data-id="18241" class="rep rep_down">&lt;</a> 0 <a href="#" data-id="18241" class="rep rep_up">&gt;</a></span>
                                        </div>
                                    </div>
                                    <div class="post_body"><?=$post->body;?></div>
<?php   if ($post->edited > 0): ?>
                                    <div class="post_footer small">
                                        <i>Edited 3 hours ago by</i>
                                    </div>
<?php   endif; ?>
                                </li>
<?php endforeach; ?>

                            </ul>
                            <div class='forum-pagination'>
                                Viewing <?=count($thread->posts);?> repl<?=(count($thread->posts) == 1)?'y':'ies';?> - <?=$thread->p_start;?> through <?=$thread->p_end;?> (of <?=$thread->replies;?> total)
                            </div>


                            <form class='forum-thread-reply' method="POST" action="?submit">
<?php include('elements/wysiwyg.php'); ?>
                                <input type='submit' class='button' value='Submit'/>
                            </form>

                        </div>
                    </section>

<?php
   require_once('footer.php');
?>