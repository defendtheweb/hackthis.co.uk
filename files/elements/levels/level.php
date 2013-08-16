<?php
    if (isset($level->data['form'])):
?>
    <div class='level-area'>
<?php
        if ($form = json_decode($level->data['form'])):
?>
        <form <?=isset($form->method)?'method="'.strtoupper($form->method).'"':'';?>>
            <fieldset>
<?php       foreach($form->fields AS $field): ?>
                <label for="user"><?=$field->label;?>:</label>
                <input <?=isset($field->type)?"type='{$field->type}'":'';?> autocomplete="off" <?=isset($field->name)?"id='{$field->name}' name='{$field->name}'":'';?>><br>
<?php       endforeach; ?>
                <input type="submit" class="button" value="Submit">
            </fieldset>
        </form>            
<?php
        else:
            echo $level->data['form'];
        endif;
?>
    </div>
<?php
    endif;
?>