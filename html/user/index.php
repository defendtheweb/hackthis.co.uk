<?php
    $custom_css = array('profile.scss');
    require_once('header.php');
    $profile = new profile($_GET['user']);
    //print_r($profile);
?>
    <article class='profile'>
<?php if ($profile->friends):?>
        <a href='#' class='button right'><i class='icon-user'></i> Friends</a>
<?php else: ?>
        <a href='#' class='button right'><i class='icon-addfriend'></i> Add friend</a>
<?php endif; ?>

        <a href='#' class='button right'><i class='icon-envelope-alt'></i> PM user</a>

        <h1><?=$profile->username;?></h1>
        <ul class='profile-details clr'>
<?php
    echo $profile->printItem("Name", $profile->name);
    if ($profile->show_email) { echo $profile->printItem("Email", $profile->email); }
    echo $profile->printItem("Gender", ucfirst($profile->gender));
    echo $profile->printItem("DOB", $profile->getDob());
    echo $profile->printItem("Joined", $profile->joined, true);
    echo $profile->printItem("Last seen", $profile->last_active, true);

    if (count($profile->social)):
?>
            <li><ul class='social'>

<?php
        foreach($profile->social as $social):
?>
            <li><a class='hide-external' href='<?=$social['uri'];?>'><i class='icon-<?=$social['icon'];?>'></i></a></li>
<?php
        endforeach;
?>
            </ul></li>
<?php
    endif;
?>
        </ul>

        <section class='row fluid'>
            <div class='col span_7 clr'>
                <img src='https://www.hackthis.co.uk/users/images/198/1:1/<?=md5($profile->username);?>.jpg' width='100%' alt='<?=$profile->username;?> profile picture'/><br/>
                <div class='progress-container'><div class='progress' style='width: 90%'>90%</div></div>

                <ul class='medals'>
<?php
    foreach ($profile->medals as $medal):
?>
                <li class="medal medal-<?=$medal->colour;?>"><?=$medal->label;?></li>
<?php
    endforeach;
?>
                </ul>
            </div>
            <div class='col span_17 clr profile-feed nano'>
                <ul class='content'>
<?php
    foreach($profile->feed as $item):
?>
                    <li>
                        <i class='icon-<?=$item['icon'];?>'></i> <?=$item['string'];?>
                        <span class='dark'>· <time datetime="<?=date('c', $item['time']);?>"><?=date('d/m/Y', $item['time']);?></time></span>
                    </li>
<?php
    endforeach;
?>
                </ul>
            </div>
        </section>
    </article>
<?php
    require_once('footer.php');
?>