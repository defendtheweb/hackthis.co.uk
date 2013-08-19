<?php
    $custom_css = array('search.scss');
    require_once('header.php');

    $search = new search($app);

    if (isset($_GET['q'])) {
        $q = preg_replace('/[^a-zA-Z0-9"@._-\s]/', '', strip_tags(html_entity_decode($_GET['q'])));
        $results = $search->go($q);
    }

?>
            <h1>Search</h1>
            <form action="/search.php" method="GET">
                <input type="text" placeholder="Search term" value="<?=isset($_GET['q'])?htmlspecialchars($_GET['q']):'';?>" name="q" class='short'>
                <input type="submit" value="Search" class="button left">
            </form>

<?php
    if (isset($_GET['q'])):
?>
            <strong class='white'>Search results for:</strong> <?=$search->getLastSearchTerm();?>
            <br/><br/>

<?php
        //Are there any results
        if (!$results['articles'] && !$results['users']):
?>
                    <div class='msg msg-error'>
                        <i class='icon-error'></i>
                        No results found
                    </div>
<?php
        endif;

       if ($results['users']):
?>
                    <ul class='users-list clr'>
<?php
            foreach($results['users'] as $result):
?>
                        <li>
                            <a href='/user/<?=$result->username;?>'>
                                <img src='<?=$result->image;?>' width='100%' alt='<?=$result->username;?> profile picture'/>
                                <div>
                                    <span><?=$result->username;?></span><br/>
                                    Score: <?=$result->score;?><br/>
                                    <?=($result->status)?'Friends':'';?>
                                </div>
                            </a>
                        </li>
<?php 
            endforeach;
?>
                    </ul>
<?php
        endif; // End check for users

        if ($results['articles']):
?>

                    <h1>Articles</h1>
<?php
            foreach ($results['articles'] as $result):
                $result->title = $app->parse($result->title, false);
                $result->body = substr($app->parse($result->body, false), 0, 200) . '...';
?>
                    <article class='article'>
                        <header class='title clearfix'>
                            <h2><a href='<?=$result->uri;?>'><?=$result->title;?></a></h2>
                        </header>
                        <?php
                            echo $result->body;
                        ?>
                        <a href='<?=$result->uri;?>'>continue reading</a>
                    </article>
<?php
            endforeach; // End loop of article results
        endif; // End check for articles
    endif; // isset($_GET['q'])
    require_once('footer.php');
?>