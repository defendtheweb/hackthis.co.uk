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
    $forum->printThreadPost($post, true);
?>
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
            $forum->printThreadPost($post);
        endforeach;
?>

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