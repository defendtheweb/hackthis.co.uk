<?php
    require_once('init.php');
    $app->page->title = 'Leaderboards';
    $minifier->add_file('faq.scss', 'css');

    $scoreboard = $app->stats->getLeaderboard(25);

    require_once('header.php');
?>
    <div class='row'>
        <div class='col span_12'>
            <h2>Top scorers</h2>
            <ul class='plain main-scoreboard'>
<?php
    for ($i = 0; $i < count($scoreboard); $i++):
        $position = $scoreboard[$i];
        $n = $i + 1;

        $joint = ($n > 1 && $position->score == $scoreboard[$i-1]->score);
?>
                <li class='<?=$n==1?'first':($n==2?'second':($n==3?'third':''));?> clr row fluid <?=isset($position->highlight)?'highlight':'';?> <?=isset($position->extra)?'extra':'';?>'>
                    <span class='position col span_2'><?=isset($position->extra)?$position->position+1:($joint?'~':$n);?></span>
                    <span class='col span_17'>
                        <img src='<?=$position->image;?>'/>
                        <a href='/user/<?=$position->username;?>'><?=$position->username;?></a>
                        <?php if ($position->donator) echo "<i class='icon-heart'></i>"; ?>
                    </span>
                    <span class='score text-right col span_5'><?=number_format($position->score);?> pts</span>
                </li>
<?php
    endfor;
?>
            </ul>
        </div>
        <div class='col span_12'>
            <h2>Top posters</h2>
            
        </div>
    </div>
<?php  
    require_once('footer.php');
?>