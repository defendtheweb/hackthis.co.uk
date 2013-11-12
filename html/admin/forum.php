<?php
    $custom_css = array('admin.scss');
    $custom_js = array('admin.js', 'admin_forum.js');
    $page_title = 'Admin - Forum';
    define("PAGE_PRIV", "admin_forum");

    require_once('init.php');


    if (isset($_GET['post'])) {
        $post = $app->forum->getPost($_GET['post']);
    }

    require_once('header.php');

    if (!isset($_GET['post'])):
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
    <p>
        <table class='striped'>
            <thead>
                <tr>
                    <th>Thread</th>
                    <th>Author</th>
                    <th>Flags</th>
                </tr>
            </thead>
            <tbody>
<?php
    foreach ($result AS $post):
        $post->title = $app->parse($post->title, false);
?>
                <tr>
                    <td><a href='/forum/<?=$post->slug;?>?post=<?=$post->post_id;?>'><?=$post->title;?></a></td>
                    <td><a href='/users/<?=$post->username;?>'><?=$post->username;?></a></td>
                    <td><?=$post->flags;?></td>
                </tr>
<?php
    endforeach;
?>
            </tbody>
        </table>
    </p>


<?php
    else: // POST SPECIFIED
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
                $st = $app->db->prepare("INSERT INTO mod_reports (`user_id`, `type`, `about`, `subject`, `body`)
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
    endif;

    require_once('footer.php');
?>