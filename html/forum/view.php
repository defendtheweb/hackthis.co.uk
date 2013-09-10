<?php
    if (!isset($forum))
        header('Location: /forum');

    if (isset($_GET['page']) && is_numeric($_GET['page']))
        $thread_page = $_GET['page'];
    else
        $thread_page = 1;

    $thread = $forum->getThread($thread->id, $thread_page);
    if (!$thread)
        header('Location: /forum');

    $viewing_thread = true;

    $thread_page_count = ceil($thread->replies/10);

    if (isset($_GET['submitted']) || isset($_GET['latest'])) {
        if ($thread_page != $thread_page_count) {
            $thread_page = $thread_page_count;
            $thread = $forum->getThread($thread->id, $thread_page);
        }
    }

    if (isset($_GET['submit']) && isset($_POST['body'])) {
        $submitted = $forum->newPost($thread->id, $_POST['body']);

        if ($submitted) {
            header('Location: '. strtok($_SERVER["REQUEST_URI"], '?') . '?submitted#latest');
            die();
        }
    }

    $section = $thread->section;

    $breadcrumb = $forum->getBreadcrumb($section, true) . "<a href='/forum/{$thread->slug}'>{$thread->title}</a>";

    $app->page->title = $thread->title;

    require_once('header.php');
?>
                    <section class="row">
<?php
        include('elements/sidebar_forum.php');
?>    
                        <div class="col span_18 forum-main" data-thread-id="<?=$thread->id;?>">

<?php if ($app->user->loggedIn): ?>
    <a href='#submit' class='post-reply button right'><i class='icon-chat'></i> Post reply</a>
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

<?php
    if (isset($_GET['submit']) && isset($_POST['body'])) {
        $app->utils->message($forum->getError(), 'error');
        $wysiwyg_text = $_POST['body'];
    } else if (isset($_GET['submitted'])) {
        $app->utils->message('Posted submitted', 'good');
    }
?>

                            <ul class='post-list'>
<?php
    $post = $thread->question;
    $post->body = $app->parse($post->body);
?>
                                <li class='row fluid clr' data-id='<?=$post->post_id;?>'>
                                    <div id="post-<?=$post->post_id;?>" class="col span_5 post_header">
                                        <a href="/user/<?=$post->username;?>" class="user">
                                            <?=$post->username;?><br/>
                                            <img src="<?=$post->image;?>" width="50" height="50" alt="<?=$post->username;?>'s profile picture">
                                        </a><br/>

                                        <i class='icon-trophy'></i> <?=$post->score;?><br/>
                                        <i class='icon-chat'></i> <?=$post->posts;?><br/>
                                        <br/>
<?php   if ($post->user_id === $app->user->uid || $app->user->forum_priv > 1): ?>
                                        <a href='#' class='button'><i class='icon-edit'></i> Edit post</a>
<?php   else: ?>
                                        <a href='#' class='button'><i class='icon-flag'></i> Flag post</a>
<?php   endif; ?>
                                    </div>
                                    <article class="col span_19 post_content">
                                        <div class="karma small">
<?php   if ($post->user_id !== $app->user->uid): ?>
                                            <a href='#' class='karma karma-down <?=$post->user_karma < 0?'karma-cancel':'';?>'><i class='icon-caret-down'></i></a>
                                            <span><?=$post->karma;?></span>
                                            <a href='#' class='karma karma-up <?=$post->user_karma > 0?'karma-cancel':'';?>'><i class='icon-caret-up'></i></a>
<?php   else: ?>
                                            <span><?=$post->karma;?></span>
<?php   endif; ?>
                                            <a class='dark' href='/faq#karma'><i class='icon-help'></i></a>
                                        </div>
                                        <div class="post_body">
                                            <?=$post->body;?>
<?php
        if (isset($post->signature)):
            $post->signature = $app->parse($post->signature);
?>
                                            <div class='post_signature'>
                                                <?=$post->signature;?>
                                            </div>
<?php   endif; ?>
                                        </div>
                                    </article>
                                </li>
                            </ul>

<?php
    if (count($thread->posts)):
?>

                            <div class='forum-pagination'>
<?php
        if ($thread_page_count > 1) {
            $pagination = new stdClass();
            $pagination->current = $thread_page;
            $pagination->count = $thread_page_count;
            $pagination->root = '?page=';
            include('elements/pagination.php');
        }
?>
                                Viewing <?=count($thread->posts);?> repl<?=(count($thread->posts) == 1)?'y':'ies';?> - <?=$thread->p_start;?> through <?=$thread->p_end;?> (of <?=$thread->replies;?> total)
                            </div>
                            <ul class='post-list reply-list'>
<?php 
        $n = 0;
        foreach($thread->posts AS $post):
            $n++;
            $post->body = $app->parse($post->body);
?>
                                <li <?=($thread_page == $thread_page_count && $n == count($thread->posts))?'id="latest"':'';?> class='row fluid clr' data-id='<?=$post->post_id;?>'>
                                    <div id="post-<?=$post->post_id;?>" class="col span_5 post_header">
                                        <a href="/user/<?=$post->username;?>" class="user">
                                            <?=$post->username;?><br/>
                                            <img src="<?=$post->image;?>" width="50" height="50" alt="<?=$post->username;?>'s profile picture">
                                        </a><br/>

                                        <i class='icon-trophy'></i> <?=$post->score;?><br/>
                                        <i class='icon-chat'></i> <?=$post->posts;?><br/>
                                        <br/>
<?php   if ($post->user_id === $app->user->uid || $app->user->forum_priv > 1): ?>
                                        <a href='#' class='button icon'><i class='icon-edit'></i></a>
                                        <a href='#' class='button icon remove'><i class='icon-trash'></i></a>
<?php   else: ?>
                                        <a href='#' class='button'><i class='icon-flag'></i> Flag post</a>
<?php   endif; ?>
                                    </div>
                                    <article class="col span_19 post_content">
                                        <div class="karma small">
<?php   if ($post->user_id !== $app->user->uid): ?>
                                            <a href='#' class='karma karma-down <?=$post->user_karma < 0?'karma-cancel':'';?>'><i class='icon-caret-down'></i></a>
                                            <span><?=$post->karma;?></span>
                                            <a href='#' class='karma karma-up <?=$post->user_karma > 0?'karma-cancel':'';?>'><i class='icon-caret-up'></i></a>
<?php   else: ?>
                                            <span><?=$post->karma;?></span>
<?php   endif; ?>
                                        </div>
                                        <div class="post_body">
                                            <?=$post->body;?>
<?php
        if (isset($post->signature)):
            $post->signature = $app->parse($post->signature);
?>
                                            <div class='post_signature'>
                                                <?=$post->signature;?>
                                            </div>
<?php   endif; ?>
                                        </div>
<?php   if ($post->edited > 0): ?>
                                        <div class="post_footer small">
                                            <i>Edited 3 hours ago by</i>
                                        </div>
<?php   endif; ?>
                                    </article>
                                </li>
<?php   endforeach; ?>

                            </ul>
                            <div class='forum-pagination'>
<?php
        if ($thread_page_count > 1) {
            $pagination = new stdClass();
            $pagination->current = $thread_page;
            $pagination->count = $thread_page_count;
            $pagination->root = '?page=';
            include('elements/pagination.php');
        }
?>
                                Viewing <?=count($thread->posts);?> repl<?=(count($thread->posts) == 1)?'y':'ies';?> - <?=$thread->p_start;?> through <?=$thread->p_end;?> (of <?=$thread->replies;?> total)
                            </div>
<?php
    endif; // End reply count check

    if ($app->user->loggedIn && $app->user->forum_priv > 0):
?>

                            <form id="submit" class='forum-thread-reply' method="POST" action="?submit#submit">
<?php
    if (isset($_GET['submit']) && isset($_POST['body'])) {
        $app->utils->message($forum->getError(), 'error');
        $wysiwyg_text = $_POST['body'];
    } else if (isset($_GET['submitted'])) {
        $app->utils->message('Posted submitted', 'good');
    }
    include('elements/wysiwyg.php');
?>
                                <input type='submit' class='button' value='Submit'/>
                            </form>

<?php
    elseif ($app->user->loggedIn):
        $app->utils->message('You have been banned from posting content in the forum', 'error');
    else:
        $app->utils->message('You must be logged in to reply to this topic', 'info');
    endif;
?>

                        </div>
                    </section>

<?php
   require_once('footer.php');
?>