<?php
    $sections = $app->levels->getList();
    $lastGroup = '';
    foreach($sections as $key => $section):
?>
        <h3 class='white'><?=$key;?></h3>
        <ul class='levels-list plain clr'>
<?php
        foreach($section->levels as $level):
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
<?php
    endforeach;
?>