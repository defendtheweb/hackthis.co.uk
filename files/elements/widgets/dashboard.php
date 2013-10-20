<?php
    // To do list
    $medal_id = 20;
    $todo_position = 0;
    $todo = array("Complete <a href='/levels/main/1'>Main 1</a>",
                  "Upload a <a href='/settings/image'>profile image</a>");
    
    // Does user have todo medal?
    $st = $app->db->prepare('SELECT medal_id FROM users_medals
                             WHERE user_id = :uid AND medal_id = :medal_id');
    $st->execute(array(':uid' => $app->user->uid, ':medal_id' => $medal_id));
    $result = $st->fetch();

    if (!$result):
        $levels = $app->levels->getList();
        if ($levels[0]->completed) {
            $todo_position = 1;

            // Does user have cheese medal?
            $st = $app->db->prepare('SELECT medal_id FROM users_medals
                                     WHERE user_id = :uid AND medal_id = :medal_id');
            $st->execute(array(':uid' => $app->user->uid, ':medal_id' => 11));
            $result = $st->fetch();
            if ($result) {
                $todo_position = 2;
            }
        }

        if ($todo_position == count($todo)):
            $app->awardMedal($medal_id, $app->user->uid);
        else:
?>

                    <article class="widget dashboard-tasks">
                        <section class="fluid clr">
                            <span class='strong'>To-do:</span> <?=$todo[$todo_position];?><br/>
                            <div class='tasks-progress-container'>
                                <div style='width: <?=($todo_position/count($todo)) * 100;?>%'></div>
                            </div>
                        </section>
                    </article>


<?php
        endif;
    endif;

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