<?php
    $scoreboard = $app->stats->getLeaderboard();
?>
                   <article class="widget scoreboard">
                        <h1>Scoreboard</h1>
                        <ul class='plain'>
<?php
    $n = 0;
    foreach($scoreboard AS $position):
        $n++;
        if ($n <= 3):
?>
                            <li class='<?=$n==1?'first':($n==2?'second':'third');?> clr'>
                                <img class='left' src='https://www.hackthis.co.uk/users/images/<?=$n==1?'30':'18';?>/1:1/51cd46e41364870c15e058a80849118e.jpg'/>
                                <span class='score'><?=number_format($position->score);?> pts</span>
                                <span class='position'><?=$n==1?'1st':($n==2?'2nd':'3rd');?></span><?=$n==1?'<br/>':'';?>
                                <a href='/user/<?=$position->username;?>'><?=$position->username;?></a>
                            </li>
<?php
        else:
?>
                            <li><span class='position'><?=$n;?></span> <a href='/user/<?=$position->username;?>'><?=$position->username;?></a><span class='score'><?=number_format($position->score);?> pts</span></li>
<?php
        endif;
    endforeach;
?>
                       </ul>
                    </article>