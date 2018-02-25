<?php
    $custom_css = array('admin.scss');
    $custom_js = array('admin.js');
    $page_title = 'Admin';
    define("PAGE_PRIV", "admin");
    define("_SIDEBAR", false);

    require_once('header.php');
?>

    <div class="row">
        <div id="admin-left" class="col span_17">

<?php
    // Forum flags
    $flags = $app->admin->getLatestForumFlags();
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

    if ($flags) {
        echo $app->twig->render('admin_forum_flags.html', array('flags' => $flags));
    }

    // Article submissions
    $articles = $app->admin->getLatestArticleSubmissions();
    foreach ($articles AS $article):
        $article->title = $app->parse($article->title, false);
        $article->datetime = date('c', strtotime($article->time));
        $article->time = $app->utils->timeSince($article->time);
    endforeach;

    if ($articles) {
        echo $app->twig->render('admin_article_submissions.html', array('articles' => $articles));
    }
?>


        </div>
        <div id="admin-right" class="col span_7">
            <div class='admin-module admin-module-tickets'>
                <h3>Tickets</h3>
                <div class='open-tickets'>
                    <span><?= $app->admin->getUnreadTickets(); ?></span>
                    open
                </div>
                <a href='/contact' class='right'>View all</a>
            </div>

<?php
    echo $app->twig->render('admin_user_manager.html');
?>

        </div>
    </div>


<?php
    require_once('footer.php');
?>
