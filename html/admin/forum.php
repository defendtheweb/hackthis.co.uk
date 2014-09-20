<?php
    $custom_css = array('highlight.css', 'forum.scss', 'admin.scss');
    $custom_js = array('highlight.js', 'admin.js', 'admin_forum.js');
    $page_title = 'Admin - Forum';
    define("PAGE_PRIV", "admin_forum");

    require_once('header.php');

    if (!isset($_GET['id'])) {
        // Forum flags
        $flags = $app->admin->getLatestForumFlags(false);
        foreach ($flags AS $post):
            $post->title = $app->parse($post->title, false);
            $post->datetime = date('c', strtotime($post->latest));
            $post->latest = $app->utils->timeSince($post->latest);
        endforeach;

        echo $app->twig->render('admin_forum_flags.html', array('flags' => $flags));
    } else {
        $post_id = $_GET['id'];

        $post = $app->forum->getPost($post_id);
        if (!$post) {
            $app->utils->message('Post not found');
            require_once('footer.php');
            die();
        }

        $thread = $app->forum->getThread($post->thread_id, 1, 50, true);
        if (!$thread) {
            $app->utils->message('Thread not found');
            require_once('footer.php');
            die();
        }

        $section = $thread->section;

        $breadcrumb = $app->forum->getBreadcrumb($section, true) . "<a href='/forum/{$thread->slug}'>{$thread->title}</a>";
?>


<div class="forum-main" data-thread-id="<?=$thread->id;?>" itemscope itemtype="http://schema.org/Article">
    <h1 class='no-margin' itemprop="name"><?=$thread->title;?></h1>
    <?=$breadcrumb;?><br/><br/>
    <ul class='post-list'>
<?php
        if ($thread->question->post_id == $post_id) {
            $tmp = clone $thread->question;
            $thread->question->highlight = true;
            $app->forum->printThreadPost($tmp, true, false, true);
        } else {
            foreach($thread->posts AS $post) {
                if ($post->post_id == $post_id) {
                    $tmp = clone $post;
                    $post->highlight = true;
                    $app->forum->printThreadPost($tmp, false, false, true);
                    break;
                }
            }
        }
?>
    </ul>
</div>

<br/><br/>
<h2>Full thread</h2>
<div class="forum-main" data-thread-id="<?=$thread->id;?>" itemscope itemtype="http://schema.org/Article">
    <ul class='post-list'>
<?php
        $post = $thread->question;
        $app->forum->printThreadPost($post, true, false, true);

        foreach($thread->posts AS $post):
            $app->forum->printThreadPost($post, false, false, true);
        endforeach;
?>
    </ul>
</div>

<?php
    }

    require_once('footer.php');
?>
