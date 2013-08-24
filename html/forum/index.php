<?php
    $custom_css = array('forum.scss', 'highlight.css');
    $custom_js = array('forum.js', 'highlight.js');
    if (!defined("_SIDEBAR")) define("_SIDEBAR", false);
    if (!defined("PAGE_PUBLIC")) define('PAGE_PUBLIC', true);

    require_once('init.php');

    $forum = new forum($app);

    $breadcrumb = '';
    if (isset($_GET['slug'])) {
        $section = $forum->getSection($_GET['slug']);
        if (!$section) {
            header('Location: /forum');
            die();
        }

        $breadcrumb = $forum->getBreadcrumb($section);

        if (!$section->child && isset($_GET['submit'])) {
            if (isset($_POST['title']) && isset($_POST['body'])) {
                $submitted = $forum->newThread($section, $_POST['title'], $_POST['body']);

                if ($submitted)
                    header('Location: '. strtok($_SERVER["REQUEST_URI"], '?'));
            }
        }
    } else {
        $section = null;
    }

    $threads = $forum->getThreads($section);

    require_once('header.php');
?>

                    <section class="row">
<?php
        include('elements/sidebar_forum.php');
?>    
                        <div class="col span_18 article-main">

<?php if ($app->user->loggedIn && $section && !$section->child): ?>
    <a href='#' class='new-thread button right'><i class='icon-chat'></i> New thread</a>
<?php endif; ?>

                            <h1 class='no-margin'>Forum</h1>
                            <?=$breadcrumb;?><br/>
                            <div class='forum-container clearfix'>
                                <ul class='forum-topics fluid'>
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
                                            <li class='row <?=($thread->closed)?'closed':'';?> <?=($thread->sticky)?'sticky':'';?>'>
                                                <div class="section_info col span_16">
                                                    <a class='strong' href="/forum/<?=$thread->slug;?>"><?=$thread->title;?></a><br>
                                                    Started by <a href="/user/<?=$thread->author;?>"><?=$thread->author;?></a>, <time itemprop='datePublished' pubdate datetime="<?=date('c', strtotime($thread->created));?>"><?=$app->utils->timeSince($thread->created);?></time>
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
                                <form class='forum-new-thread' method="POST" action="?submit">
                                    <label>Title:</label><br/>
                                    <input type="text" id='title' name='title' class='short'/>
<?php include('elements/wysiwyg.php'); ?>
                                    <input type='submit' class='button' value='Submit'/>
                                </form>
                            </div>

                        </div>
                    </section>

<?php
   require_once('footer.php');
?>