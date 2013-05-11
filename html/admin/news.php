<?php
    require_once('header.php');

    $articles = new articles();

    if (isset($_GET['action'])):
        $article = $articles->get_article($_GET['slug']);

        if (!$article):
?>
        <div class='msg msg-error'>
            <i class='icon-error'></i>
            News post not found
        </div>
<?php
        else:
            // Check for submission
            if (isset($_POST['body'])) {
                $changes = array('title'=>$_POST['title'], 'body'=>$_POST['body']);

                $updated = $articles->update_article($article->id, $changes, isset($_POST['update']));
                if ($updated):
?>
        <div class='msg msg-good'>
            <i class='icon-good'></i>
            Post updated
        </div>
<?php
                else:
?>
        <div class='msg msg-error'>
            <i class='icon-error'></i>
            Error updating post
        </div>
<?php
                endif;
            }
?>

        <form method='POST'>
            <label>Title:</label>
            <input type="text" value="<?=htmlentities($article->title);?>" id='title' name='title'/>
<?php
            $wysiwyg_text = $article->body;
            include('elements/wysiwyg.php');
?>
            <input type="checkbox" id="update" name="update" checked/>
            <label for="update">Mark as update</label>
            <input type='submit' class='button' value='Submit'/>
        </form>
<?php
        endif;
    endif;

    require_once('footer.php');
?>           