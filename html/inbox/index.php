<?php
    define("_SIDEBAR", false);

    $custom_css = array('inbox.scss');
    $custom_js = array('inbox.js');
    require_once('header.php');

    $messages = new messages($app);

    if (isset($_POST['body']) && isset($_GET['view'])) {
        $result = $messages->newMessage(null, $_POST['body'], $_GET['view']);
        if ($result) {
            $uri = $_GET['view'] . "?sent";
            header('Location: '.$uri);
            die();
        } else
            $error = $messages->getError();
    }

    if (isset($_POST['body']) && isset($_POST['to']) && isset($_GET['compose'])) {
        $result = $messages->newMessage($_POST['to'], $_POST['body']);
        if ($result) {
            $uri = $messages->getLastInserted();
            header('Location: '.$uri);
            die();
        } else
            $error = $messages->getError();     
    }

    $inbox = $messages->getAll(42, false);

    if (isset($_GET['view'])) {
        $convo = new stdClass();
        $convo->id = $_GET['view'];
        if ($convo->id) {
            $convo->messages = $messages->getConvo($_GET['view'], false);
            $convo->users = $messages->getConvoUsers($_GET['view']);

            if (!$convo->messages)
                unset($convo);
        }
    }


    if (isset($convo)):
?>
    <div class='mobile-hide' id="conversation-search">
        <input placeholder='Search conversation'/>
        <i class='icon-search'></i>
    </div>
<?php
    else:
?>
    <a class='button right' href='/inbox/compose'>
        <i class="icon-envelope-alt"></i> New Message
    </a>
<?php
    endif;
?>

    <h1>
        <a href='/inbox'>Inbox</a>
<?php
    if (isset($convo)) {
        echo " - ";
        $numItems = count($convo->users);
        $i = 0;
        foreach ($convo->users as $u) {
            echo "<a href='/user/{$u['username']}'>{$u['username']}</a>";
            if(++$i !== $numItems)
                echo ', ';
        }
    }
?>
    </h1>

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
        $lastDay = null;
        foreach($convo->messages as $message):
            $today = date('d-m-Y', strtotime($message->timestamp));
            if ($today != $lastDay):
                $lastDay = $today;
?>
                <li class='clean'></li> <!-- Keep zebra stripes -->
                <li class='new-day center'><span><?=date('jS F, Y', strtotime($message->timestamp));?></span></li>
<?php
            endif;
?>
                <li class='clr <?=$message->seen?'':'new';?>'>
                    <a href='/user/<?=$message->username;?>'>
                        <img width='28px' height='28px' class='left' src='<?=$message->img;?>'/>
                        <?=$message->username;?>
                    </a>
                    <time class="right dark" datetime="<?=$message->timestamp;?>" data-timesince="false"><?=date('H:i', strtotime($message->timestamp));?></time><br/>
                    <div class='body'><?=$message->message;?></div>
                </li>
<?php
        endforeach;
?>
            </ul>
<?php
        if (isset($error)):
?>
            <div class='msg msg-error'>
                <i class='icon-error'></i>
                <?=$messages->getError($error);?>
            </div>
<?php
        endif;
?>
            <form method="POST">
                <?php include('elements/wysiwyg.php'); ?>
                <input id="comment_submit" type="submit" value="Send" class="submit button right"/>
            </form>
<?php
    elseif (isset($_GET['compose'])):
        if (isset($error)):
?>
        <div class='msg msg-error'>
            <i class='icon-error'></i>
            <?=$messages->getError($error);?>
        </div>
<?php
        endif;
?>
            <form method="POST">
                <label for="to">To:</label><br/>
                <input autocomplete="off" id="to" data-suggest-max="2" data-suggest-at="false" class="suggest hide-shadow short" name="to" value="<?=(isset($_POST['to']))?htmlentities($_POST['to']):'';?>"><br/>
                <label for="body">Message:</label><br/>
                <?php
                    if (isset($_POST['body']))
                        $wysiwyg_text = htmlentities($_POST['body']);
                    include('elements/wysiwyg.php');
                ?>
                <input id="comment_submit" type="submit" value="Send" class="submit button right"/>
            </form>
<?php
    else:
?>
            <div class="center empty"><i class="icon-envelope-alt icon-4x"></i><?=count($inbox)?'No conversation selected':'No messages available';?></div>
<?php
    endif;
?>
        </div>
    </section>
<?php
    require_once('footer.php');
?>