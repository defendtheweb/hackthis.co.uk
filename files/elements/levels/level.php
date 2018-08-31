<?php
    if (isset($level->attempt) && $level->attempt === true) :
        if (isset($level->data['solution'])):
?>
        <div class='info solution'>
            <?=$app->utils->parse($level->data['solution']);?>
        </div>
        <br/>
<?php
        endif;
?>
        <div class='info solution'>
            Want to share your solution? <a href='/forum/level-discussion/<?=strtolower($currentLevel->group);?>-levels/<?=strtolower($currentLevel->group);?>-level-<?=$currentLevel->name;?>/solutions'>Visit the solutions section</a> in the forum.
        </div>
<?php
    elseif (isset($level->data['form'])):
?>

            <div class='level-form'>
<?php
        if (isset($currentLevel) && isset($currentLevel->data['code']->pos3)) {
            echo '                '.$currentLevel->data['code']->pos3 . "\n";
        }
        if ($form = json_decode($level->data['form'])):
?>
                <form <?=isset($form->method)?'method="'.strtoupper($form->method).'"':'';?>>
                    <fieldset>
<?php       foreach($form->fields AS $field): ?>
                        <label <?=isset($field->name)?"for='{$field->name}'":""?>><?=$field->label;?>:</label>
                        <input type='<?=isset($field->type)?"{$field->type}":'text';?>' autocomplete="off" <?=isset($field->name)?"id='{$field->name}' name='{$field->name}'":'';?>><br>
<?php       endforeach; ?>
                        <input type="submit" class="button" value="Submit">
                    </fieldset>
                </form>            
<?php
        elseif ($page = realpath($app->config('path') . '/files/elements/levels/'.basename($level->data['form']).'.php')):
            include($page);
        else:
            echo $level->data['form'];
        endif;
?>
            </div>
<?php
        if (isset($currentLevel) && isset($currentLevel->data['code']->pos4)) {
            echo '            '.$currentLevel->data['code']->pos4 . "\n";
        }
    endif;
?>
