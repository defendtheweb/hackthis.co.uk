<?php
    $custom_css = array('profile.scss', 'highlight.css', 'confirm.css');
    $custom_js = array('jquery.confirm.js', 'highlight.js', 'profile.js');
    require_once('init.php');
    
    $profile = new profile($_GET['user']);
    if (isset($profile->uid))
        $app->page->title = $profile->username;

    if (isset($_GET['image'])) {
        require('userbar.php');
        die();
    }

    require_once('header.php');
    

    if (!isset($profile->uid)):
        $app->utils->message("User not found");
        require_once('footer.php');
        die();
    endif;

    // FRIENDS LIST
    if (isset($_GET['friends']) && count($profile->friendsList)):
?>
        <div id="friends-search" class="mobile-hide">
            <input placeholder="Search friends">
            <i class="icon-search"></i>
        </div>
        <a href='/user/<?=$profile->username;?>'><i class='icon-caret-left'></i> <?=$profile->username;?>'s profile</a>
        <br/><br/>
        <ul class='users-list'>
<?php
        foreach($profile->friendsList as $friend):
            if (isset($friend->image)) {
                $gravatar = isset($friend->gravatar) && $friend->gravatar == 1;
                $friend->image = profile::getImg($friend->image, 48, $gravatar);
            } else
                $friend->image = profile::getImg(null, 48);
?>
            <li>
                <a href='/user/<?=$friend->username;?>'>
                    <img src='<?=$friend->image;?>' width='100%' alt='<?=$friend->username;?> profile picture'/>
                    <div>
                        <span><?=$friend->username;?></span><br/>
                        Score: <?=$friend->score;?><br/>
                        <?=($friend->status)?'Friends':'';?>
                    </div>
                </a>
            </li>
<?php   endforeach; ?>
        </ul>

<?php
        require_once('footer.php');
        die();
    endif; // End friends list

    /* USERS PROFILE STARTS */
?>
    <article class='profile' data-uid='<?=$profile->uid;?>'>
<?php
    if (!$profile->blockedMe):
        if ($profile->friends):
?>
        <a href='#' class='button button-blank right button-friend removefriend'><i class='icon-user'></i> Friends</a>
<?php   elseif ($profile->friends !== NULL && $profile->friend != $profile->uid): ?>
        <a href='#' class='button right button-disabled button-friend pendingfriend'>Pending</a>
<?php   elseif ($profile->friends !== NULL && $profile->friend == $profile->uid): ?>
        <a href='#' class='button right button-friend acceptfriend'><i class='icon-addfriend'></i> Accept</a>
<?php   else if (!$profile->owner): ?>
        <a href='#' class='button right button-friend addfriend'><i class='icon-addfriend'></i> Add friend</a>
<?php
        endif;
    endif;

    if ($profile->owner):
?>
        <a href='/settings/' class='button right'><i class='icon-edit'></i> Edit profile</a>
<?php
    else:
        if (!$profile->blockedMe):
?>
        <a href='/inbox/compose?to=<?=$profile->username;?>' class='messages-new button right' data-to="<?=$profile->username;?>"><i class='icon-envelope-alt'></i> PM user</a>
<?php
        endif;
        if (!$profile->friends):
?>
        <a href='#' class='block <?=$profile->blocked?'blocked':'';?> button button-blank right'><i class='icon-blocked'></i> <?=$profile->blocked?'Blocked':'Block';?></a>
<?php
        endif;
    endif;
?>

        <h1 class='lower'><?=$profile->username;?></h1> 
<?php if ($app->user->admin_site_priv):
        echo '#'.$profile->uid;
      endif;
      if ($profile->admin): ?>
        <strong>Administrator</strong>
<?php elseif ($profile->moderator): ?>
        <strong>Moderator</strong>
<?php endif; ?>

        <section class='clr'>
            <div class='col span_7 clr'>
                <div class="image">
<?php if ($profile->donator): ?>
                    <div class="label corner">
                        <i class="icon-heart"></i>
                    </div>
<?php endif; ?>
                    <img src='<?=$profile->image;?>' width='100%' alt='<?=$profile->username;?> profile picture'/>
                </div>
                <div class='progress-container'><div class='progress' style='width: <?=$profile->score_perc;?>%'><span><?=ceil($profile->score_perc);?>%</span></div></div>            
            </div>

            <div class='col span_17 clr'>
                <div class='profile-feed scroll'>
                    <ul class='content'>
<?php
    foreach($profile->feed as $item):
?>
                        <li>
                            <i class='icon-<?=$item['icon'];?>'></i> <?=$item['string'];?>
                            <span class='dark'>· <time datetime="<?=date('c', strtotime($item['time']));?>"><?=$app->utils->timeSince($item['time']);?></time></span>
<?php
        if ($profile->owner):
?>
                            <a class='right hide remove' data-fid='<?=$item['id'];?>' href='#'><i class='icon-remove'></i></a>
<?php
        endif;
?>
                        </li>
<?php
    endforeach;
?>
                    </ul>
                </div>
            </div>
        </section>

        <section class='profile-extra row'>
            <div class='col span_7 clr'>
<?php
    /* MEDALS */

    $medalCount = count($profile->medals);
    if ($medalCount):
?>
                <section class='row'>
                    <ul class='medals clr'>
<?php
        foreach ($profile->medals as $medal):
?>
                        <li class="medal medal-<?=$medal->colour;?>"><a href='/medals.php#<?=strtolower($medal->label);?>'><?=$medal->label;?></a></li>
<?php
        endforeach;
?>
                    </ul>
                </section>
<?php
    endif;


    /* FRIENDS */

    $friendCount = count($profile->friendsList);
    if ($friendCount):
?>
                <section class='row'>
                    <h2><a href='/user/<?=$profile->username;?>/friends'><?=$friendCount;?> Friend<?=($friendCount==1?'':'s');?></a></h2>

                    <ul class='friends-list'>
<?php     
        $i = 0;
        foreach($profile->friendsList as $friend):
            $i++;
            if ($i > 8)
                break;

            if (isset($friend->image)) {
                $gravatar = isset($friend->gravatar) && $friend->gravatar == 1;
                $friend->image = profile::getImg($friend->image, 48, $gravatar);
            } else
                $friend->image = profile::getImg(null, 48);
?>
                        <li>
                            <figure>
                                <a href='/user/<?=$friend->username;?>'>
                                    <img src='<?=$friend->image;?>' width='100%' alt='<?=$friend->username;?> profile picture'/>
                                </a>
                                <figcaption>
                                    <a href='/user/<?=$friend->username;?>'><?=$friend->username;?></a><br/>
                                    Score: <?=$friend->score;?><br/>
                                    <?=($friend->status)?'Friends':'';?>
                                </figcaption>
                            </figure>
                        </li>
<?php   endforeach; ?>
                    </ul>
                </section>
<?php
    endif;

    if ($profile->lastfm):
?>
                <section class='row music mobile-hide'>
                    <a class='right hide-external icon-hover' href='http://www.last.fm/'><i class='icon-lastfm'></i></a>
                    <h2><a href='http://www.last.fm/user/<?=$profile->lastfm;?>'>Music</a></h2>
                    <div data-user="<?=$profile->lastfm;?>" class="profile-music loading">
                        <img src='/files/images/icons/loading.gif' class='icon'/>
                    </div>
                </section>
<?php
    endif;
?>
                &nbsp;
            </div>

            <div class='col span_17 clr'>
                <section class='profile-details row fluid' data-graph-start='<?=date('d/m/Y', strtotime($profile->joined));?>' data-graph-end='<?=date('d/m/Y');?>'>
                    <ul class='clr line1'>
<?php
    $profile->printItem("<a href='#' class='show-levels' title='Click to view more detail'>Score</a>", $profile->score);
    $profile->printItem("<a href='#' class='show-posts' title='Click to view more detail'>Posts</a>", $profile->posts);
    $profile->printItem("<a href='#' class='show-articles' title='Click to view more detail'>Articles</a>", $profile->articles);
    $profile->printItem("Karma", $profile->karma > 0 ? $profile->karma : $profile->karma);
?>
                    </ul>
                    <ul class='clr'>
<?php
    if ($profile->show_name) { $profile->printItem("Name", $profile->name); }
    if ($profile->show_email) { $profile->printItem("Email", $profile->email); }
    if ($profile->show_gender) { $profile->printItem("Gender", $profile->gender, false, true); }
    if ($profile->getDob()) { $profile->printItem("DOB", $profile->getDob()); }
    $profile->printItem("Joined", $profile->joined, true);
    $profile->printItem("Last seen", $profile->last_active, true);
?>
                    </ul>
<?php                    
    if (count($profile->social)):
?>               
                    <ul class='social clr'>

<?php
        foreach($profile->social as $social):
?>
                        <li><a class='hide-external' href='<?=$social['uri'];?>'><i class='icon-<?=$social['icon'];?>'></i></a></li>
<?php
        endforeach;
?>
                    </ul>
<?php
    endif;
?>
                </section>
<?php
    if (isset($profile->about))
        echo $profile->about;
?>
            </div>
        </section>
    </article>

    <script type="text/javascript" src="/files/js/d3.js"></script>
<?php
    require_once('footer.php');
?>