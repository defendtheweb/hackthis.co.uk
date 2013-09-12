<?php
    if (isset($level->attempt) && $level->attempt === true) :
        if (isset($level->data['solution'])):
?>
        <div class='info solution'>
            <?=$app->utils->parse($level->data['solution']);?>
        </div>
<?php
        endif;
    elseif (isset($level->data['form'])):
?>
    <div class='level-form'>
<?php
        if ($form = json_decode($level->data['form'])):
?>
        <form <?=isset($form->method)?'method="'.strtoupper($form->method).'"':'';?>>
            <fieldset>
<?php       foreach($form->fields AS $field): ?>
                <label for="user"><?=$field->label;?>:</label>
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
    endif;
?>