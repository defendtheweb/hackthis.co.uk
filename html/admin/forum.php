<?php
    $custom_css = array('highlight.css', 'forum.scss', 'admin.scss');
    $custom_js = array('highlight.js', 'admin.js', 'admin_forum.js');
    $page_title = 'Admin - Forum';
    define("PAGE_PRIV", "admin_forum");
    
    require_once('init.php');

    // Remove post flag
    if (isset($_GET['remove'])) {
        $app->forum->removeFlags(false, true, $_GET['remove']);
        die();
    }

    require_once('header.php');

    if (!isset($_GET['id'])) {
        // Forum flags
        $flags = $app->admin->getLatestForumFlags(false);
        foreach ($flags AS $post):
            $post->title = $app->parse($post->title, false);
            $post->datetime = date('c', strtotime($post->latest));
            $post->latest = $app->utils->timeSince($post->latest);

            switch ($post->reason) {
                case '1': $post->reason = "Off-topic"; break;
                case '2': $post->reason = "Spoiler"; break;
                case '3': $post->reason = "Spam"; break;
                case '4': $post->reason = "Low quality"; break;
                case '5': $post->reason = "Non-English"; break;
                case '6': $post->reason = "Other"; break;
            }
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

        $thread = $app->forum->getThread($post->thread_id, 1, 250, true);
        if (!$thread) {
            $app->utils->message('Thread not found');
            require_once('footer.php');
            die();
        }

        $section = $thread->section;

        $breadcrumb = $app->forum->getBreadcrumb($section, true) . "<a href='/forum/{$thread->slug}'>{$thread->title}</a>";

        $flags = $app->forum->getPostFlags($post_id);
        foreach ($flags AS $flag):
            switch ($flag->reason) {
                case '1': $flag->reason = "Off-topic"; break;
                case '2': $flag->reason = "Spoiler"; break;
                case '3': $flag->reason = "Spam"; break;
                case '4': $flag->reason = "Low quality"; break;
                case '5': $flag->reason = "Non-English"; break;
                case '6': $flag->reason = "Other - " . $flag->details; break;
            }
        endforeach;
?>

    <div class="forum-main admin-forum" data-thread-id="<?=$thread->id;?>" itemscope itemtype="http://schema.org/Article">
        <h1 class='no-margin' style="display: inline-block" itemprop="name"><?=$thread->title;?></h1>
        <a href='#' class='button icon thread-edit'><i class='icon-edit'></i></a>
        <a href='#' class='button icon thread-delete'><i class='icon-trash'></i></a><br/>
        <?=$breadcrumb;?><br/><br/>
        <?= $app->twig->render('admin_forum_post_flags.html', array('post' => $post->post_id, 'flags' => $flags)); ?>
        <h2>Post</h2>
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

    <br/>
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
