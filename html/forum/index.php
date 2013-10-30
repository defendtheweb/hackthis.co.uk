<?php
    $custom_css = array('forum.scss', 'highlight.css', 'confirm.css');
    $custom_js = array('forum.js', 'highlight.js', 'jquery.confirm.js');
    if (!defined("_SIDEBAR")) define("_SIDEBAR", false);
    if (!defined("PAGE_PUBLIC")) define('PAGE_PUBLIC', true);

    require_once('init.php');

    if (isset($_GET['page']) && is_numeric($_GET['page']))
        $page = $_GET['page'];
    else
        $page = 1;

    $forum = $app->forum;

    $breadcrumb = '';
    if (isset($_GET['slug'])) {
        // Section or thread?
        $thread = $forum->isThread($_GET['slug']);
        if ($thread) {
            if (isset($_GET['edit']))
                include('edit.php');
            else
                include('view.php');
            die();
        }

        $section = $forum->getSection($_GET['slug']);
        if (!$section) {
            header('Location: /forum');
            die();
        }

        if (!$section->child && isset($_GET['submit'])) {
            if (isset($_POST['title']) && isset($_POST['body'])) {
                $newThreadResult = $forum->newThread($section, $_POST['title'], $_POST['body']);

                if (strpos($newThreadResult, '/') === 0) {
                    header('Location: '. $newThreadResult);
                }
            }
        }

        $app->page->title = 'Forum - ' . $section->title;
    } else {
        $section = null;
        $app->page->title = 'Forum';
    }

    $breadcrumb = $forum->getBreadcrumb($section);

    if (isset($_GET['watching'])) {
        $breadcrumb .= '<span class="white">Watched threads</span>';
        if (isset($_GET['popular']) || isset($_GET['no-replies'])) {
            $breadcrumb .= ' & ';
        }
    }
    if (isset($_GET['popular'])) {
        $breadcrumb .= '<span class="white">Most popular threads</span>';
        if (isset($_GET['no-replies'])) {
            $breadcrumb .= ' & ';
        }
    }
    if (isset($_GET['no-replies'])) {
        $breadcrumb .= '<span class="white">Threads with no replies</span>';
    }
    if (!isset($_GET['no-replies']) && !isset($_GET['popular']) && !isset($_GET['watching'])) {
        $breadcrumb .= '<span class="white">Latest threads</span>';
    }

    $threads = $forum->getThreads($section, $page, isset($_GET['no-replies']), isset($_GET['popular']), isset($_GET['watching']));

    $threads_count = $threads->count;
    $threads = $threads->threads;

    require_once('header.php');
?>

                    <section class="row">
<?php
        include('elements/sidebar_forum.php');
?>    
                        <div class="col span_18 article-main">

<?php
    if ($app->user->loggedIn && $app->user->forum_priv > 0 && $section && !$section->child):
        if (isset($newThreadResult)):
?>
    <a class="new-thread button right" href="#"><i class="icon-caret-left"></i> Thread list</a>
<?php
        else:
?>
    <a href='#' class='new-thread button right'><i class='icon-chat'></i> New thread</a>
<?php
        endif;
    else:
?>
    <a class='button button-disabled right hint--left' data-hint="New threads can only be started in sub-sections"><i class='icon-chat'></i> New thread</a>
<?php
    endif;
?>

                            <h1 class='no-margin'>Forum</h1>
                            <?=$breadcrumb;?><br/><br/>
<?php
    if ($app->user->loggedIn && $app->user->forum_priv < 1) {
        $app->utils->message('You have been banned from posting content in the forum', 'error');
    }
?>
                            <div class='forum-container clearfix <?=isset($newThreadResult)?'new-thread':'';?>'>
                                <div class='forum-topics'>
<?php
    if (count($threads)):
?>
                                    <ul class='fluid'>
                                        <li class='forum-topic-header row'>
                                            <div class="section_info col span_16">Thread</div>
                                            <div class="section_replies col span_2">Replies</div>
                                            <div class="section_voices col span_2">Voices</div>
                                            <div class="section_latest col span_4">Latest</div>        
                                        </li>
                                        <li class='forum-section'>
                                            <ul>

<?php
        foreach($threads AS $thread):
?>
                                                <li class='row <?=($app->user->loggedIn)?(!$thread->viewed)?($thread->watching)?'highlight':'new':'':'';?> <?=($thread->closed)?'closed':'';?> <?=($thread->sticky)?'sticky':'';?>'>
                                                    <div class="section_info col span_16">
                                                        <a class='strong' href="/forum/<?=$thread->slug;?>"><?=$thread->title;?></a>
<?php
            if (ceil($thread->count/10) > 1) {
                $pagination = new stdClass();
                $pagination->count = ceil($thread->count/10);
                $pagination->root = '/forum/' . $thread->slug . '?page=';
                include('elements/lite_pagination.php');
            }

            $threadBreadcrumb = $forum->getThreadBreadcrumb($section, $thread);
            if ($threadBreadcrumb):
?>
                                                        <div class='small thread-sections dark'><?=$threadBreadcrumb;?></div>
<?php
            else:
?>
                                                        <div class='small thread-blurb dark'>
                                                            <?=$thread->blurb;?>
                                                        </div>
<?php
            endif;
?>
                                                    </div>
                                                    <div class="section_replies col span_2"><?=$thread->count;?></div>
                                                    <div class="section_voices col span_2"><?=$thread->voices;?></div>
                                                    <div class="section_latest col span_4">
                                                        <time itemprop='datePublished' pubdate datetime="<?=date('c', strtotime($thread->latest));?>"><?=$app->utils->timeSince($thread->latest);?></time><br/>
                                                        <a class='strong' href="/user/<?=$thread->latest_author;?>"><?=$thread->latest_author;?></a>
                                                    </div>
                                                </li>
<?php
        endforeach;
?>
                                            </ul>
                                        </li>
                                    </ul>
<?php
        else:
            $app->utils->message('No threads found, consider starting your own', 'info');

        endif;

        if ($threads_count > 10) {
            $pagination = new stdClass();
            $pagination->current = $page;
            $pagination->count = ceil($threads_count/10);
            $pagination->root = '?page=';
            include('elements/pagination.php');
        }
?>
                                    <ul class='key plain clr'>
                                        <li class='new'>New post</li>
                                        <li class='highlight'>New post in watched thread</li>
                                        <li class='closed'>Thread closed</li>
                                        <li class='sticky'>Sticky thread</li>
                                    </ul>
                                </div>
                                <div class='forum-new-thread'>
<?php
    if (isset($newThreadResult)) {
        $app->utils->message($newThreadResult);
    }
?>
                                    <form method="POST" action="?submit">
                                        <label>Title:</label><br/>
                                        <input type="text" id='title' name='title' class='short'/>
    <?php include('elements/wysiwyg.php'); ?>
                                        <input type='submit' class='button' value='Submit'/>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>

<?php
   require_once('footer.php');
?>