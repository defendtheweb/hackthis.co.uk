<?php
    $custom_css = array('articles.scss');
    $page_title = 'Admin - Articles';
    define("PAGE_PRIV", "admin_pub");

    require_once('header.php');

    if (isset($_GET['accept'])) {
        $status = $app->articles->acceptArticle($_GET['accept']);
        if ($status === true)
            $app->utils->message('Article accepted, <a href="#">view here</a>', 'good');
        else if ($status === false)
            $app->utils->message('Article not found', 'error');
        else
            $app->utils->message($status, 'error');
    }

    if (!isset($_GET['action'])) {

        $limit = 25;
        $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;

        $articleList = $app->articles->getMyArticles(false, $limit, $page, true);

        if (!$articleList['total']):
            $app->utils->message('No new articles to review', 'info');
        else:
?>
                            <table class='striped'>
                                <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th width='25px'></th>
                                    </tr>
                                </thead>
                                <tbody>
<?php
            foreach ($articleList['articles'] as $article):
                $article->title = $app->parse($article->title, false);
?>
                                    <tr class='<?=($article->note?'declined':'awaiting');?>'>
                                        <td><a href='/articles/view.php?id=<?=$article->id;?>'><?=$article->title;?></a> <span class='dark'>&middot; <?=$article->cat_title;?></a></td>
<?php           if ($article->note): ?>
                                        <td class='center'><span class='hint--left' data-hint='Declined: <?=str_replace("'", '`', str_replace("<br />", "", $app->parse($article->note, false, false)));?>'><i class='icon-cross'></i></span></td>
<?php           else: ?>
                                        <td class='center'><span class='hint--left' data-hint='Awaiting review'><i class='icon-eye'></i></span></td>
<?php           endif; ?>
                                    </tr>
<?php
            endforeach;
?>
                                </tbody>
                            </table>
<?php

            if (ceil($articleList['total']/$limit) > 1) {
                $pagination = new stdClass();
                $pagination->current = $articleList['page'];
                $pagination->count = ceil($articleList['total']/$limit);
                $pagination->root = '?page=';
                include('elements/pagination.php');
            }
        endif;

    } else {
        if ($_GET['action'] === 'edit'):
            $article = $app->articles->getArticle($_GET['slug'], 'all');

            if (!$article):
?>
        <div class='msg msg-error'>
            <i class='icon-error'></i>
            Article not found
        </div>
<?php
            else:
                // Check for submission
                if (isset($_POST['body'])) {
                    $changes = array('title'=>$_POST['title'], 'body'=>$_POST['body'], 'category_id'=>$_POST['category']);

                    $updated = $app->articles->updateArticle($article->id, $changes, isset($_POST['update']));
                    if ($updated) {
                        $app->utils->message('Article updated, <a href="'.$article->uri.'">view here</a>', 'good');
                    } else {
?>
        <div class='msg msg-error'>
            <i class='icon-error'></i>
            Error updating post
        </div>
<?php
                        die();
                    }
                }

                // Check for update
                if (isset($_GET['update'])):
?>
        <div class='msg msg-good'>
            <i class='icon-good'></i>
            Post updated, <a href='<?=$article->uri;?>'>view post</a>
        </div>
<?php
                endif;
?>

        <form method='POST'>
            <label>Title:</label><br/>
            <input type="text" value="<?=htmlentities($article->title);?>" id='title' name='title' class='medium'/>
            <div class='select-menu right' data-id="category" data-value="<?=$article->cat_id;?>">
                <label><?=$article->cat_title;?></label>
            
                <ul>
<?php
                $categories = $app->articles->getCategories();
                foreach($categories as $cat) {
                    $app->articles->printCategoryList($cat);
                }
?>
                </ul>
            </div>
<?php
                $wysiwyg_text = $article->body;
                include('elements/wysiwyg.php');
?>
            <input type='submit' class='button' value='Save'/>

            <input type="checkbox" id="update" name="update" checked/>
            <label class='right' for="update">Mark as update</label>
        </form>
<?php
            endif;
        endif; //$action = 'edit'
    } //isset $_GET['action']

    require_once('footer.php');
?>