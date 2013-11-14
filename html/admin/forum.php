<?php
    $custom_css = array('admin.scss');
    $custom_js = array('admin.js', 'admin_forum.js');
    $page_title = 'Admin - Forum';
    define("PAGE_PRIV", "admin_forum");

    require_once('init.php');

    if (isset($_GET['action'])) {
        $return = new stdClass();
        if ($_GET['action'] == 'flag.remove') {
            $return->status = $app->forum->removeFlags($_GET['id']);
        }
        echo json_encode($return);
        die();
    }


    if (isset($_GET['post'])) {
        $post = $app->forum->getPost($_GET['post']);
    }

    require_once('header.php');

    if (!isset($_GET['post']) && !isset($_GET['thread'])):
        $sql = "SELECT COUNT(author) AS `count`, DATE_FORMAT(posted, '%d-%m-%Y') AS `date` FROM forum_posts WHERE posted > date_sub(now(), INTERVAL 1 WEEK)  GROUP BY YEAR(posted), MONTH(posted), DAY(posted) ORDER BY posted DESC";
        $st = $app->db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll();
?>
    <p>
        <script type="text/javascript">
            graphData = [<?php foreach($result AS $res) { echo '{ "date" : "' . $res->date . '", "count" : ' . $res->count . ' }, '; } ?>];
        </script>

        <div class='graph'></div>
        <script type="text/javascript" src="/files/js/d3.js"></script>
<?php
    $sql = "SELECT MAX(users_forum.flag) AS `latest`, COUNT(users_forum.post_id) AS `flags`, users.username, forum_threads.thread_id, forum_threads.slug, forum_threads.title, forum_posts.post_id, forum_posts.body
            FROM users_forum
            INNER JOIN forum_posts
            ON users_forum.post_id = forum_posts.post_id
            INNER JOIN forum_threads
            ON forum_posts.thread_id = forum_threads.thread_id
            INNER JOIN users
            ON users.user_id = forum_posts.author
            WHERE flag > 0 AND forum_posts.deleted = 0 AND forum_threads.deleted = 0
            GROUP BY users_forum.post_id
            ORDER BY `flags` DESC, `latest` DESC";
    $st = $app->db->prepare($sql);
    $st->execute();
    $result = $st->fetchAll();
?>
    </p>
<?php
        if ($result):
?>
    <p>
        <table class='striped flags'>
            <thead>
                <tr>
                    <th>Thread</th>
                    <th>Author</th>
                    <th>Flags</th>
                    <th>Latest</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
<?php
    foreach ($result AS $post):
        $post->title = $app->parse($post->title, false);
?>
                <tr data-pid="<?=$post->post_id;?>">
                    <td><a target='_blank' href='/forum/<?=$post->slug;?>?post=<?=$post->post_id;?>'><?=$post->title;?></a></td>
                    <td><a target='_blank' href='/user/<?=$post->username;?>'><?=$post->username;?></a></td>
                    <td><?=$post->flags;?></td>
                    <td><time datetime="<?=date('c', strtotime($post->latest));?>"><?=$app->utils->timeSince($post->latest);?></time></td>
                    <td class='text-right'>
                        <a href='#' class='remove hint--top' data-hint="Delete flag"><i class='icon-remove'></i></a>
                    </td>
                </tr>
<?php
    endforeach;
?>
            </tbody>
        </table>
    </p>


<?php
        endif;
    elseif (isset($_GET['thread'])): // THREAD SPECIFIED
        $thread = $app->forum->getThread($_GET['thread'], 1, 0);

        if (!$thread):
            $app->utils->message('Thread not found');
        else:
            if (isset($_GET['close'])):
                $app->forum->closeThread($thread->id, (boolean) !$_GET['close']);
                $app->utils->message('Thread has been '.($_GET['close']?'opened':'closed').', <a href="/forum/'.$thread->slug.'">return to thread</a>', 'good');
            elseif (isset($_GET['sticky'])):
                $app->forum->stickThread($thread->id, (boolean) !$_GET['sticky']);            
                $app->utils->message('Thread has been '.($_GET['sticky']?'unstuck':'stickied').', <a href="/forum/'.$thread->slug.'">return to thread</a>', 'good');
            elseif (isset($_GET['delete'])):
                if (isset($_POST['reason'])) {
                    if ($_POST['reason']) {
                        $deleted = $app->forum->deleteThread($thread->id);
                    } else {
                        $deleted = false;
                    }
                }

                if (isset($deleted) && $deleted === true):
                    // Add to reports
                    $st = $app->db->prepare("INSERT INTO mod_reports (`user_id`, `type`, `about`, `subject`, `body`)
                            VALUES (:uid, 'forum_thread', :tid, 'Deleted thread', :body)");
                    $st->execute(array(':tid'=>$thread->id, ':uid'=>$app->user->uid, ':body'=>$_POST['reason']));

                    $id = $app->db->lastInsertId();

                    // Notify user
                    $app->notifications->add($thread->owner, 'mod_report', $app->user->uid, $id);

                    // Remove flags and award users who flagged
                    $st = $app->db->prepare("SELECT post_id FROM forum_posts WHERE thread_id = :tid ORDER BY posted ASC LIMIT 1");
                    $st->execute(array(':tid'=>$thread->id));
                    $res = $st->fetch();
                    $app->forum->removeFlags($res->post_id, true);

                    $app->utils->message('Thread deleted, <a href="/admin/forum.php">return to admin page</a>', 'good');
?>
                


<?php
                else:
                    $wysiwyg_text = $post->body;

                    $app->utils->message('Users will be notified of the edit along with the reason you give, so please make it constructive', 'info');

                    if (isset($updated) && $updated === false) {
                        $app->utils->message('Error deleting thread, missing field?');
                    }
?>

        <form id="submit" class='forum-thread-reply' method="POST">
            <label for="reason">Reason for deletion:</label><br/>
            <input type="text" name="reason"/><br/>
            <input type='submit' class='button' value='Submit'/>
        </form>
<?php
                endif;
            endif;
        endif;
    else: // POST SPECIFIED
        if (!$post) {
            $app->utils->message('Post not found');
            require_once('footer.php');
            die();
        }

        if (isset($_GET['edit'])):
            if (isset($_POST['body']) && $_POST['body'] != $post->body && isset($_POST['reason'])) {
                if ($_POST['body'] && $_POST['reason']) {
                    $updated = $app->forum->editPost($post->post_id, null, $_POST['body']);
                } else {
                    $updated = false;
                }
            }

            if (isset($updated) && $updated === true):
                // Add to reports
                $st = $app->db->prepare("INSERT INTO mod_reports (`user_id`, `type`, `about`, `subject`, `body`)
                        VALUES (:uid, 'forum', :post_id, 'Edited post', :body)");
                $st->execute(array(':post_id'=>$post->post_id, ':uid'=>$app->user->uid, ':body'=>$_POST['reason']));

                $id = $app->db->lastInsertId();

                // Notify user
                $app->notifications->add($post->author, 'mod_report', $app->user->uid, $id);

                // Remove flags and award users who flagged
                $app->forum->removeFlags($post->post_id, true);

                $app->utils->message('Post updated, <a href="/admin/forum.php">return to admin page</a>', 'good');
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
        elseif (isset($_GET['remove'])):
            if (isset($_POST['reason'])) {
                if ($_POST['reason']) {
                    $updated = $app->forum->deletePost($post->post_id);
                } else {
                    $updated = false;
                }
            }

            if (isset($updated) && $updated === true):
                // Add to reports
                $st = $app->db->prepare("INSERT INTO mod_reports (`user_id`, `type`, `about`, `subject`, `body`)
                        VALUES (:uid, 'forum', :post_id, 'Deleted post', :body)");
                $st->execute(array(':post_id'=>$post->post_id, ':uid'=>$app->user->uid, ':body'=>$_POST['reason']));

                $id = $app->db->lastInsertId();

                // Notify user
                $app->notifications->add($post->author, 'mod_report', $app->user->uid, $id);

                // Remove flags and award users who flagged
                $app->forum->removeFlags($post->post_id, true);

                $app->utils->message('Post deleted, <a href="/admin/forum.php">return to admin page</a>', 'good');
?>
                


<?php
            else:
                $wysiwyg_text = $post->body;

                $app->utils->message('Users will be notified of the deletion along with the reason you give, so please make it constructive', 'info');

                if (isset($updated) && $updated === false) {
                    $app->utils->message('A reason is required');
                }
?>

        <form id="submit" class='forum-thread-reply' method="POST">
            <label for="reason">Reason for deletion:</label><br/>
            <input type="text" name="reason"/>
            <input type='submit' class='button' value='Submit'/>
        </form>

<?php
            endif;
        endif;
    endif;

    require_once('footer.php');
?>