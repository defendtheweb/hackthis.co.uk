<?php
    define("_SIDEBAR", false);

    $custom_css = array('inbox.scss');
    $custom_js = array('inbox.js');
    require_once('header.php');

    $messages = new messages();

    if (isset($_POST['body']) && isset($_GET['view']))
        $result = $messages->newMessage(null, $_POST['body'], $_GET['view']);

    $inbox = $messages->getAll(42, false);

    if (isset($_GET['view'])) {
        $convo = new stdClass();
        $convo->id = $_GET['view'];
        if ($convo->id) {
            $convo->messages = $messages->getConvo($_GET['view'], false);

            if (!$convo->messages)
                unset($convo);
        }
    }
?>
    <h1><a href='/inbox'>Inbox</a></h1>

    <section class="inbox row">
        <div class="col span_6 inbox-list scroll">
            <ul class='plain'>
<?php    
    foreach($inbox as $message):
?>
                <li <?=(isset($convo->id) && $message->pm_id == $convo->id)?'class="active"':'';?>>
                    <a href='<?=$message->pm_id;?>' <?=($message->seen || (isset($convo->id) && $message->pm_id == $convo->id))?'':'class="new"';?>>
                        <img width='42px' height='42px' class='left' src='<?=$message->users[0]->img;?>'/>
                        <div>
                            <time class="short dark right" datetime="<?=$message->timestamp;?>"><?=$app->utils->timeSince($message->timestamp, true);?></time>
                            <span class='strong'>
<?php
        $numItems = count($message->users);
        $i = 0;
        foreach($message->users as $u) {
            echo $u->username;
            if(++$i !== $numItems)
                echo ', ';
        }
?>
                            </span>
                            <?=$message->message;?>
                        </div>
                    </a>
                </li>
<?php
    endforeach;
?>
            </ul>
        </div>
        <div class="col span_18 inbox-main scroll">
<?php
    if (isset($convo) && $convo):
?>
            <ul class='plain conversation'>
<?php
        foreach($convo->messages as $message):
?>
                <li class='clr <?=$message->seen?'':'new';?>'>
                    <a href='/user/<?=$message->username;?>'>
                        <img width='28px' height='28px' class='left' src='<?=$message->img;?>'/>
                        <?=$message->username;?>
                    </a>
                    <time class="right dark" datetime="<?=$message->timestamp;?>"><?=$app->utils->timeSince($message->timestamp);?></time><br/>
                    <div class='body'><?=$message->message;?></div>
                </li>
<?php
        endforeach;
?>
            </ul>
            <form method="POST">
                <?php $wysiwyg_placeholder = 'Add your comment here...'; include('elements/wysiwyg.php'); ?>
                <input id="comment_submit" type="submit" value="Post Comment" class="submit button right"/>
            </form>
<?php
    else:
?>
            <div class="center empty"><i class="icon-envelope-alt icon-4x"></i><?=count($inbox)?'No message selected':'No messages available';?></div>
<?php
    endif;
?>
        </div>
    </section>
<?php
    require_once('footer.php');
?>