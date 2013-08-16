<?php
    if ($app->user->connect_msg):
?>
                        <div class='msg msg-info'>
                            <i class='icon-info'></i>
                            <?=$app->user->connect_msg;?>
                        </div>
<?php
    endif;
    if (!$app->user->connected):
?>
                        <a class='stop-external facebook-connect' href='https://www.facebook.com/dialog/oauth?client_id=<?php $fb = $app->config('facebook'); echo $fb['public'];?>&redirect_uri=http://dev.hackthis/?facebook&scope=email'>
                            Connect to Facebook <i class='remove icon-remove right'></i>
                        </a>
<?php
    endif;
?>
                    <article class="widget dashboard">
                        <section class="fluid clr center">
                            <h1 class='lower'><a href='/user/<?=$app->user->username;?>'><?=$app->user->username;?></a></h1>
                            <div class='profile-pic'>
                                <img src='<?=$app->user->image;?>'/>
                                <a href='/settings/image' class='upload'>
                                    <i class="icon-image"></i><br/>
                                    Change Picture
                                </a>
                            </div>
                            <ul class='user-profile'>
                                <li class='progress progress-score'><span style='width: <?=$app->user->score_perc;?>%'><?=$app->user->score_perc;?>%</span></li>
                                <li>Score <span class='right'><?=$app->user->score;?></span></li>
                                <li class='progress progress-login'><span style='width: <?=$app->user->consecutive_perc;?>%'><?=$app->user->consecutive_perc;?>%</span></li>
                                <li>
                                    Activity <span class='hint--bottom' data-hint="Consecutive number of active days.&#10;Consecutive days are calculated using UTC.&#10;Your personal best is highlighted."><i class='icon-info'></i></span>
                                    <span class='right'><?=$app->user->consecutive;?> <span><?=$app->user->consecutive_most;?></span></span>
                                </li>
                            </ul>
                        </section>
                    </article>