<?php
    $levels = $app->levels->getList();
    $lastGroup = '';
    foreach($levels as $level):
        if ($level->group !== $lastGroup):
            if ($lastGroup !== ''):
?>
            </ul>
<?php
            endif;
            $lastGroup = $level->group;
?>
        <h3 class='white'><?=$level->group;?></h3>
        <ul class='levels-list plain clr'>
<?php
        endif;
?>
            <li>
                <a href="levels.php?edit=<?=$level->id;?>">
                    <span class="thumb_title"><?=$level->title;?></span>
                </a>
            </li>
<?php
    endforeach;
?>
            </ul>