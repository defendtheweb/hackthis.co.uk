<?php
    define("PAGE_PUBLIC", true);

    $custom_css = array('ticker.scss');
    $custom_js = array('ticker.js');
    $page_title = 'Ticker';

    require_once('init.php');

    if (isset($_POST['title']) && isset($_POST['url'])) {
        $response = new stdClass();

        $response->status = $app->ticker->add($_POST['title'], $_POST['url']);

        echo json_encode($response);
        die();
    }

    if (isset($_POST['tid'])) {
        $response = new stdClass();

        if (isset($_POST['action']) && $_POST['action'] == 'vote') {
            $response->status = $app->ticker->vote($_POST['tid']);
        } else if (isset($_POST['action']) && ($_POST['action'] == 'accept' || $_POST['action'] == 'decline')) {
            $response->status = $app->ticker->changeStatus($_POST['tid'], $_POST['action']);
        }

        echo json_encode($response);
        die();
    }

    if (isset($_POST['get'])) {


        die();
    }

    require_once('header.php');

    if (isset($_GET['latest'])) {
        $tab = 'latest';
        $items = $app->ticker->get(25, false);
    } else if (isset($_GET['favourites'])) {
        $tab = 'favourites';
        $items = $app->ticker->getFavourites();
    } else if (isset($_GET['submissions'])) {
        $tab = 'submissions';
        $items = $app->ticker->getSubmissions();
    } else if (isset($_GET['admin']) && $app->user->admin_pub_priv) {
        $tab = 'admin';
        $items = $app->ticker->getAdmin();
    } else {
        $tab = 'top';
        $items = $app->ticker->get(25, true);
    }
?>

    <h1>Ticker</h1>

    <p>The HackThis!! ticker is a list of the most popular user submitted links.
    Anyone can submit a link to the list but currently all submissions need to be approved by a moderator.</p>

    <?php include('elements/tabs_ticker.php'); ?>

    <ul class='plain ticker'>
<?php
    if (count($items)):
        foreach($items AS $item):
?>
    <li data-id="<?=$item->id;?>" class="<?=$item->voted?'voted':'';?> <?=($tab == 'admin')?'admin':'';?>">
<?php
            if ($tab == 'admin'):
?>
        <i class='icon-remove'></i>
        <i class='icon-ok'></i>
        <a href='<?=$item->url;?>' class='hide-external' target='_blank'><?=$item->text;?></a> [<?=$item->source;?>]
        &middot;
        <time itemprop='datePublished' pubdate datetime="<?=date('c', strtotime($item->time));?>"><?=$app->utils->timeSince($item->time);?></time>
        &middot;
        <?php echo $app->utils->userLink($item->username); ?>
<?php
            else:
?>
        <a class='ticker-up' href='#'><i class='icon-heart-2'></i></a>
        <div class='ticker-up-voted' href='#'><i class='icon-heart'></i></div>
        <div>
            <a href='<?=$item->url;?>' class='hide-external' target='_blank'><?=$item->text;?></a> [<?=$item->source;?>]
<?php
                if ($tab == 'submissions'):
                    switch ($item->status) {
                        case '0':
                            echo "<span class='status-0'>[awaiting approval]</span>";
                            break;
                        case '1':
                            echo "<span class='status-1'>[approved]</span>";
                            break;
                        case '2':
                            echo "<span class='status-2'>[declined]</span>";
                            break;
                    }
                endif;
?>
            <br/>
            <span class='small dark'>
                <i class="icon-clock"></i> <time itemprop='datePublished' pubdate datetime="<?=date('c', strtotime($item->time));?>"><?=$app->utils->timeSince($item->time);?></time>
                &middot;
                <?php echo $app->utils->userLink($item->username); ?>
                &middot;
                <span class='points'><?=$item->count;?></span> point<?=$item->count==1?'':'s';?>
            </span>
        </div>
<?php
            endif;
?>
    </li>

<?php
        endforeach;
    else:
        $app->utils->message('No links found');
    endif;
?>
    </ul>
<?php
    require_once('footer.php');
?>           
