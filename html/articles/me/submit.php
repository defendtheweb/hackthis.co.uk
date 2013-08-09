<?php
    $custom_css = array('articles.scss', 'highlight.css');
    $custom_js = array('articles.js', 'highlight.js');
    if (!defined("_SIDEBAR")) define("_SIDEBAR", false);

    require_once('header.php');

    if (isset($_GET['id']) && $_GET['action'] == 'edit') {
        $myArticle = true;
        $id = preg_replace('/[^0-9]*/','',$_GET['id']);
        if (!$id) {
            header('Location: /articles');
            die();
        }

        //Update?
        if (isset($_POST['body'])) {
            $changes = array('title'=>$_POST['title'], 'body'=>$_POST['body'], 'category_id'=>$_POST['category']);
            $status = $app->articles->updateArticle($id, $changes, false, true);
        }

        $article = $app->articles->getMyArticle($id);
    } else {
        if (isset($_POST['body'])) {
            $status = $app->articles->submitArticle($_POST['title'], $_POST['body'], $_POST['category']);
            if ($status) {
                header('Location: /articles/view.php?id='.$status);
                die();
            }
        }
    }
?>
                    <section class="row">
<?php
        include('elements/sidebar_article.php');
?>    
                        <div class="col span_18 article-main">
<?php
    if (isset($_POST['body'])):
        if (!$status) {
            $msg = "Error with request";
            $type = 'error';
        } else {
            $msg = "Post updated, <a href='/articles/view.php?id={$article->id}'>view post</a>";
            $type = 'good';
        }
?>
                            <div class='msg msg-<?=$type;?>'>
                                <i class='icon-<?=$type;?>'></i>
                                <?=$msg;?>
                            </div>
<?php
    endif;
?>
                            <form method='POST'>
                                <label>Title:</label><br/>
                                <input type="text" value="<?php
                                    if (isset($article->title))
                                        echo htmlentities($article->title);
                                    else if (isset($_POST['title']))
                                        echo htmlentities($_POST['title']);
                                ?>" id='title' name='title' class='medium'/>
                                <div class='select-menu right' data-id="category" data-value="<?php if (isset($article->cat_id)) echo htmlentities($article->cat_id);?>">
                                    <label><?=isset($article->cat_title)?htmlentities($article->cat_title):'Category';?></label>
                                
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
                if (isset($article->body))
                    $wysiwyg_text = $article->body;
                else if (isset($_POST['body']))
                    $wysiwyg_text = $_POST['body'];
                include('elements/wysiwyg.php');

                if (isset($article)):
?>
                                <input type='submit' class='button' value='Save'/>
<?php           else: ?>
                                <input type='submit' class='button' value='Submit'/>
<?php           endif; ?>
                            </form>
                        </div>
                    </section>
<?php
   require_once('footer.php');
?>