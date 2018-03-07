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
    $thread_page_count = $thread_page_count == 0?1:$thread_page_count;



    if (isset($_GET['post'])) {
        // redirect user to correct page with #
        if ($_GET['post'] == 'latest') {
            header('Location: /forum/' . $thread->slug . '?page=' . $thread_page_count . '#latest');
        } else {
            // find page of post
            $p = $forum->findPost($thread->id, $_GET['post']);
            if ($p) {
                header('Location: /forum/' . $thread->slug . '?page=' . ($p>0?$p:1) . '#post-' . $_GET['post']);
            }
        }
    }


    if (isset($_GET['submitted']) || isset($_GET['latest'])) {
        if ($thread_page != $thread_page_count) {
            $thread_page = $thread_page_count;
            $thread = $forum->getThread($thread->id, $thread_page);
        }
    }

    if (!$thread->closed) {
        if (isset($_GET['submit']) && isset($_POST['body'])) {
            if ($app->checkCSRFKey("forumPost", $_POST['token'])) {
                $submitted = $forum->newPost($thread->id, $_POST['body']);
            } else {
                $submitted = false;
            }

            if ($submitted) {
                header('Location: '. strtok($_SERVER["REQUEST_URI"], '?') . '?submitted#latest');
                die();
            }
        }

        if (isset($_GET['close'])) {
            if ($app->checkCSRFKey("closeThread", $_GET['close'])) {
                $forum->closeThread($thread->id);
            }

            header('Location: /forum/'.$thread->slug);
        }
    }

    $section = $thread->section;

    $breadcrumb = $forum->getBreadcrumb($section, true) . "<a href='/forum/{$thread->slug}'>{$thread->title}</a>";

    $app->page->title = $thread->title;
    if ($thread_page == 1) {
        $app->page->canonical = 'https://www.hackthis.co.uk/forum/'.$thread->slug;
    } else {
        $app->page->canonical = 'https://www.hackthis.co.uk/forum/'.$thread->slug.'?page='.$thread_page;
    }

    if ($thread_page < $thread_page_count) {
        $app->page->next = 'https://www.hackthis.co.uk/forum/'.$thread->slug.'?page='.($thread_page + 1);
    }

    if ($thread_page > 1) {
        $app->page->prev = 'https://www.hackthis.co.uk/forum/'.$thread->slug.'?page='.($thread_page - 1);
    }

    require_once('header.php');
?>
                    <section class="row">
<?php
        include('elements/sidebar_forum.php');
?>    
                        <div class="col span_18 forum-main" data-thread-id="<?=$thread->id;?>" itemscope itemtype="http://schema.org/Article">

<?php if ($app->user->loggedIn): ?>
    <a href='?page=<?=$thread_page_count;?>#submit' class='post-reply button right mobile-hide'><i class='icon-chat'></i> Post reply</a>
<?php   if ($thread->watching): ?>
    <a href='#' class='post-watch post-unwatch button right mobile-hide'><i class='icon-eye-blocked'></i> Unwatch</a>
<?php   else: ?>
    <a href='#' class='post-watch button right mobile-hide'><i class='icon-eye'></i> Watch</a>
<?php
        endif;
        if ($app->user->admin_forum_priv):
?>
    <div class='post-admin-container right'>
        <a href='#' class='post-admin button icon mobile-hide'><i class='icon-lock'></i></a>
        <ul class='post-admin-list plain'>
            <li><a href='/admin/forum.php?thread=<?=$thread->id;?>&close<?=$thread->closed?'=false':'';?>'><?=$thread->closed?'Reopen':'Close';?></a></li>
            <li><a href='/admin/forum.php?thread=<?=$thread->id;?>&sticky<?=$thread->sticky?'=false':'';?>'><?=$thread->sticky?'Unstick':'Make sticky';?></a></li>
            <li><a href='/admin/forum.php?thread=<?=$thread->id;?>&delete'>Delete</a></li>
        </ul>
    </div>
<?php
        elseif (!$thread->closed && $thread->question->user_id === $app->user->uid):
?>
            <a href='?close=<?= $app->generateCSRFKey("closeThread"); ?>' class='post-close button right mobile-hide'><i class='icon-lock'></i> Close</a>
<?php
        endif;
      endif;
?>

                            <h1 class='no-margin' itemprop="name"><?=$thread->title;?></h1>
                            <?=$breadcrumb;?><br/><br/>

<?php
    if (isset($_GET['submit']) && isset($_POST['body'])) {
        $app->utils->message($forum->getError(), 'error');
        $wysiwyg_text = $_POST['body'];
    } else if (isset($_GET['submitted'])) {
        $app->utils->message('Post submitted', 'good');
    }
?>

                            <ul class='post-list'>
<?php
    $post = $thread->question;
    echo '<meta itemprop="interactionCount" content="UserComments:'.count($thread->posts).'"/>';
    echo '<meta itemprop="wordCount" content="'.str_word_count($post->body).'"/>';
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
<?php
        if (!$thread->closed && $thread->question->user_id === $app->user->uid) {
            $app->utils->message("Is one of these posts the answer to your question? If so <a href='?close=".$app->generateCSRFKey("closeThread")."'>click here to close thread</a>.<br/>After closing a thread no more posts will be accepted.", 'info');
        }
?>
                            <ul class='post-list reply-list'>
<?php 
        $n = 0;
        $l = count($thread->posts);
        foreach($thread->posts AS $post):
            $n++;
            $forum->printThreadPost($post, false, $thread_page == $thread_page_count && $l == $n);
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

    if ($thread_page == $thread_page_count):
        if ($app->user->loggedIn && $app->user->forum_priv > 0 && !$thread->closed):
?>

                            <form id="submit" class='forum-thread-reply' method="POST" action="?page=<?=$thread_page_count;?>&submit#submit">
<?php
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (isset($_GET['submit']) && isset($_POST['body'])) {
            $app->utils->message($forum->getError(), 'error');
            $wysiwyg_text = $_POST['body'];
        } else if (isset($_GET['submitted'])) {
            $app->utils->message('Post submitted', 'good');
        } else if ($lang && $lang != 'en') {
            $app->utils->message('This is an English only forum, please refrain from posting in other languages. We are working on adapting the site for your language.', 'info');
        }
        include('elements/wysiwyg.php');
?>
                                <input type="hidden" value="<?=$app->generateCSRFKey("forumPost", true);?>" name="token">
                                <input type='submit' class='button' value='Submit'/>
                            </form>

<?php
        elseif ($thread->closed):
            $app->utils->message('This thread has been closed, you can not add new posts', 'error');
        elseif ($app->user->loggedIn):
            $app->utils->message('You have been banned from posting content in the forum', 'error');
        else:
            $app->utils->message('You must be logged in to reply to this topic', 'info');
        endif;
    endif;
?>

                        </div>
                    </section>

<?php
   require_once('footer.php');
?>
