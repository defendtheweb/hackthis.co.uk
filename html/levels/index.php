<?php
    $custom_css = array('levels.scss');
    require_once('header.php');

    $levels = $app->levels->getList();
?>
        <h1>Levels</h1>
<?php
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
                <a class="progress_<?=$level->completed;?>" href="<?=$level->uri;?>">
                    <span class="thumb_title"><?=$level->title;?></span>
                </a>
            </li>
<?php
    endforeach;
?>
            </ul>
<?php
    require_once('footer.php');
?>