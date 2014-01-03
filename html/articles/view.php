<?php
    $custom_css = array('articles.scss', 'highlight.css');
    $custom_js = array('articles.js', 'highlight.js');
    define("_SIDEBAR", false);
    define('PAGE_PUBLIC', true);

    require_once('init.php');

    $review = false;
    if (isset($_GET['slug'])) {
        $myArticle = false;
        // check if it is a category
        $category = $app->articles->getCategory($_GET['slug']);
        if ($category) {
            include('index.php');
            die();
        }

        $article = $app->articles->getArticle($_GET['slug']);
    } else if (isset($_GET['id'])) {
        $myArticle = true;
        $id = preg_replace('/[^0-9]*/','',$_GET['id']);
        if (!$id) {
            header('Location: /articles');
            die();
        }
        $article = $app->articles->getMyArticle($id);

        if ($article->user_id != $app->user->uid || $app->user->admin_site_priv)
            $review = true;
    }

    if ($article && !$myArticle) {
        $app->page->title = $app->parse($article->title, false);

        $app->page->desc = "Learn about " . $app->page->title . " with our range of security and hacking tutorials and articles. Join our security community and test your hacking skills.";

        $app->page->canonical = 'https://www.hackthis.co.uk/articles/'.$article->slug;

        $app->page->meta['twitter:card'] = "summary";
        $app->page->meta['twitter:title'] = $app->page->title;
        $app->page->meta['twitter:description'] = substr($app->parse($article->body, false), 0, 300);
        $app->page->meta['og:type'] = "article";
        $app->page->meta['og:url'] = $app->page->canonical;
        $app->page->meta['og:title'] = $app->page->title;
        $app->page->meta['og:description'] = substr($app->parse($article->body, false), 0, 300);

        if (isset($article->thumbnail) && $article->thumbnail) {
            $app->page->meta['og:image'] = "https://www.hackthis.co.uk/images/200/4:3/{$article->thumbnail}";
            $app->page->meta['twitter:image'] = $app->page->meta['og:image'];
        } else if (isset($article->video)) {
            $app->page->meta['og:image'] = "https://img.youtube.com/vi/{$article->video}/0.jpg";
            $app->page->meta['twitter:image'] = $app->page->meta['og:image'];
        }
    }
    else
        $app->page->title = 'Articles';

    require_once('header.php');
?>
                    <section class="row">
<?php
    if (!isset($_GET['view']) || $_GET['view'] != 'app')
        include('elements/sidebar_article.php');
?>    
                        <div class="col span_<?=(!isset($_GET['view']) || $_GET['view'] != 'app')?'18':'24';?> article-main">
<?php
    if (!isset($article) || !$article):
?>
                            <div class='msg msg-error'>
                                <i class='icon-error'></i>
                                Article not found
                            </div>
<?php
    else:

        if ($myArticle):
            if ($article->note):
?>
                            <div class='msg msg-error'>
                                <i class='icon-error'></i>
                                Article declined: <?=$app->parse($article->note, false);?>
                            </div>
<?php
            else:
?>            
                            <div class='msg msg-info'>
                                <i class='icon-info'></i>
                                This article is awaiting review
                            </div>
<?php
            endif;
        endif;

        $article->title = $app->parse($article->title, false);
        $article->body = $app->parse($article->body);
?>
                            <article class='bbcode body' itemscope itemtype="http://schema.org/Article">
<?php
    if (!isset($_GET['view']) || $_GET['view'] != 'app'):
?>
                                <header class='clearfix'>
<?php if ($myArticle): ?>
                                        <a href='/articles/me/submit.php?action=edit&id=<?=$id;?>' class='button icon right'><i class='icon-edit'></i></a>
<?php elseif ($app->user->admin_pub_priv): ?>
                                        <a href='/admin/articles.php?action=edit&slug=<?=$article->slug;?>' class='button icon right'><i class='icon-edit'></i></a>
<?php endif; ?>
<?php if ($review): ?>
                                        <a href='#' class='right button'><i class='icon-cross'></i> Decline</a>
                                        <a href='/admin/articles.php?accept=<?=$article->id;?>' class='right button'><i class='icon-tick'></i> Accept</a>
<?php endif; ?>
                                    <h1 itemprop="name"><?=$article->title;?></h1>
                                    <div class='meta'>
                                        <i class="icon-clock"></i> <time itemprop='datePublished' pubdate datetime="<?=date('c', strtotime($article->submitted));?>"><?=$app->utils->timeSince($article->submitted);?></time>
<?php if (isset($article->updated) && $article->updated > 0): ?>
                                        &#183; updated <time itemprop='dateModified' datetime="<?=date('c', strtotime($article->updated));?>"><?=$app->utils->timeSince($article->updated);?></time>
<?php endif; ?>
<?php if (isset($article->cat_title)): ?>
                                        <i class="icon-books"></i> <a href='<?=$article->cat_slug;?>'><?=$article->cat_title;?></a>
<?php endif; ?>
<?php if (isset($article->username)): ?>
                                        <i class="icon-user"></i> <a rel='author' itemprop='author' href='/user/<?=$article->username;?>'><?=$article->username;?></a>
<?php endif; ?>
                                    </div>
                                    <?php
                                        if (!$myArticle && (!isset($_GET['view']) || $_GET['view'] != 'app')) {
                                            $share = new stdClass();
                                            $share->item = $article->id;
                                            $share->right = true;
                                            $share->comments = $article->comments;
                                            $share->title = $article->title;
                                            $share->link = "/articles/{$article->slug}";
                                            $share->favourites = $article->favourites;
                                            $share->favourited = $article->favourited;
                                            include("elements/share.php");
                                            echo '<meta itemprop="interactionCount" content="UserComments:'.$share->comments.'"/>';
                                        }
                                    ?>
                                </header>

<?php
    endif;

    $matches = $app->articles->getTOC($article->body);
    if (count($matches[0])):
?>
                                <div class="right contents">
<?php
    if ($article->cat_id == 6):
?>
        <a href='http://www.walkerlocksmiths.co.uk'><img src='/files/images/lock_picking_sponsor.png'/></a><br/>
<?php
    endif;
?>
                                    <h2>Contents</h2>
                                    <ul>
<?php
    $i = 0;
    foreach($matches[2] as $match) {
        $c = '>';
        if ($matches[1][$i] == '2')
        $c = " class='sub'>- ";

        $slug = $app->utils->generateSlug($match);

        echo "<li{$c}<a href='#{$slug}'>{$match}</a></li>";
        $i++;
    }

    echo "<li><a href='#comments'>Comments</a></li>";
?>
                                    </ul>
                                </div>
<?php
    endif; 
    
    $article->body = $app->articles->setupTOC($article->body);
?>
                                <meta itemprop="wordCount" content="<?=str_word_count($article->body);?>"/>
                                <div itemprop='articleBody'>
<?php
    echo $article->body;
?>
                                </div>
                            </article>
<?php
        if (!$myArticle && (!isset($_GET['view']) || $_GET['view'] != 'app')) {
            $comments = array("id"=>$article->id,"title"=>$article->title,"count"=>$article->comments);
            include('elements/comments.php');
        }
    endif;
?>
                        </div>
                    </section>

<?php
    if (isset($article->next) && $article->next):
?>
                    <a href='<?=$article->next->uri;?>' class='article-suggest'>
                        Next article<Br/>
                        <span><?=$article->next->title;?></span>
                    </a>
<?php
    endif;

    require_once('footer.php');
?>           