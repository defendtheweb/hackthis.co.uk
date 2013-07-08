<?php
    define("PAGE_PRIV", "admin_pub");

    require_once('header.php');

    $articles = new articles();

    if (!isset($_GET['action'])):
?>


<?php
    else:
        if ($_GET['action'] === 'edit'):
            $article = $articles->getArticle($_GET['slug'], 'all');

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

                    $updated = $articles->updateArticle($article->id, $changes, isset($_POST['update']));
                    if ($updated) {
                        $uri = "http://{$_SERVER[HTTP_HOST]}{$_SERVER[REQUEST_URI]}";
                        if (!isset($_GET['update'])) $uri .= '&update';
                        header('Location: '.$uri);
                        die();
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
                $categories = $articles->getCategories();
                foreach($categories as $cat) {
                    printCategoryList($cat);
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
    endif; //isset $_GET['action']

    require_once('footer.php');



    function printCategoryList($cat) {
        echo "<li data-value='{$cat->id}'>{$cat->title}\n";
        if (isset($cat->children) && count($cat->children)) {
            echo "<ul>\n";
            foreach($cat->children AS $child) {
                printCategoryList($child);
            }
            echo "</ul>\n";
        }
        echo "</li>\n";
    }
?>