<?php
    $custom_css = array('search.scss', 'forum.scss');
    $page_title = 'Search';
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
        if (!$results['articles'] && !$results['users'] && !$results['forum']):
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

        if ($results['forum']):
?>

                    <h1>Forum</h1>
                        <div class='forum-container clearfix'>
                            <div class='forum-topics'>
                                <ul class='fluid'>
                                    <li class='forum-topic-header row'>
                                        <div class="section_info col span_16">Thread</div>
                                        <div class="section_replies col span_2">Replies</div>
                                        <div class="section_voices col span_2">Voices</div>
                                        <div class="section_latest col span_4">Latest</div>        
                                    </li>
                                    <li class='forum-section'>
                                        <ul>
<?php
            foreach ($results['forum'] as $thread):
                $thread->title = $app->parse($thread->title, false);
?>
                                            <li class='row'>
                                                <div class="section_info col span_16">
                                                    <a class='strong' href="/forum/<?=$thread->slug;?>"><?=$thread->title;?></a>
                                                </div>
                                                <div class="section_replies col span_2"><?=$thread->count;?></div>
                                                <div class="section_voices col span_2"><?=$thread->voices;?></div>
                                                <div class="section_latest col span_4">
                                                    <time itemprop='datePublished' pubdate datetime="<?=date('c', strtotime($thread->latest));?>"><?=$app->utils->timeSince($thread->latest);?></time><br/>
                                                    <a class='strong' href="/user/<?=$thread->latest_author;?>"><?=$thread->latest_author;?></a>
                                                </div>
                                            </li>
<?php
        endforeach;
?>
                                        </ul>
                                    </li>
                                </ul>
<?php
        endif; // End check for forum

    endif; // isset($_GET['q'])
    require_once('footer.php');
?>