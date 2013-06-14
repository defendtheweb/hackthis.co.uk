                    <article class="widget dashboard">
                        <h1><a href='/user/<?=$user->username;?>'><?=$user->username;?></a></h1>
<?php
    if ($user->connect_msg):
?>
                        <div class='msg msg-info'>
                            <i class='icon-info'></i>
                            <?=$user->connect_msg;?>
                        </div>
<?php
    endif;
    if (!$user->connected):
?>
                        <a class='stop-external facebook-connect' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo $fb['public'];?>&redirect_uri=http://dev.hackthis/?facebook&scope=email'>
                            Connect to Facebook <i class='remove icon-remove right'></i>
                        </a>
<?php
    endif;
?>
                        <section class="fluid clr">
                            <div class='col span_7'>
                                <img class='profile-pic' src='https://www.hackthis.co.uk/users/images/60/1:1/<?=md5($user->username);?>.jpg' width='100%'/>
                            </div>
                            <div class='col span_17 user-profile'>
                                Score: <?=$user->score;?>
                            </div>
                        </section>
                        <div class='progress-group-container'>
                            <div class='progress-container'><div class='progress' style='width: 90%'></div></div>
                            <div class='progress-container'><div class='progress progress-blue' style='width: 65%'></div></div>
                        </div>
                    </article>