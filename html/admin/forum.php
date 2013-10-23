<?php
    $custom_css = array('admin.scss');
    $custom_js = array('admin.js');
    $page_title = 'Admin - Forum';
    define("PAGE_PRIV", "admin_forum");

    require_once('init.php');


    if (isset($_GET['post'])) {
        $post = $app->forum->getPost($_GET['post']);
    }

    require_once('header.php');

    if (isset($_GET['post'])): // POST SPECIFIED
        if (!$post) {
            $app->utils->message('Post not found');
            require_once('footer.php');
            die();
        }

        if (isset($_GET['edit'])):
            if (isset($_POST['body']) && isset($_POST['reason'])) {
                if ($_POST['body'] && $_POST['reason']) {
                    $updated = $app->forum->editPost($post->post_id, null, $_POST['body']);
                } else {
                    $updated = false;
                }
            }

            if (isset($updated) && $updated === true):
                // Add to reports
                $st = $app->db->prepare("INSERT INTO reports (`user_id`, `type`, `about`, `subject`, `body`)
                        VALUES (:uid, 'forum', :post_id, 'Edited post', :body)");
                $st->execute(array(':post_id'=>$post->post_id, ':uid'=>$app->user->uid, ':body'=>$_POST['reason']));

                $app->utils->message('Post updated', 'good');
?>
                


<?php
            else:
                $wysiwyg_text = $post->body;

                $app->utils->message('Users will be notified of the edit along with the reason you give, so please make it constructive', 'info');

                if (isset($updated) && $updated === false) {
                    $app->utils->message('Error editing post, missing field?');
                }
?>

        <form id="submit" class='forum-thread-reply' method="POST">
            <label for="reason">Reason for edit:</label><br/>
            <input type="text" name="reason"/><br/>

            <?php include('elements/wysiwyg.php'); ?>
            <input type='submit' class='button' value='Submit'/>
        </form>

<?php
            endif;
        endif;

    else:
        //
    endif;

    require_once('footer.php');
?>