<?php
    $level = null;
    $updated = null;

    // Get level details if not new
    if (is_numeric($_GET['edit'])) {
        $level = $app->levels->getLevelFromID($_GET['edit']);
        if (!$level) {
            $app->utils->message('Level not found');
            die();
        } else {
            if (isset($_POST['save'])) {
                $updated = $app->levels->editLevel($_GET['edit']);

                if ($updated) {
                    if (!isset($_GET['done']))
                        header('Location: '.$_SERVER[REQUEST_URI].'&done');
                    else
                        header('Location: '.$_SERVER[REQUEST_URI]);
                    die();
                }
            }
        }
    } else if ($_GET['edit'] === 'new') {
        if (isset($_POST['save'])) {
            $id = $app->levels->newLevel();

            if ($id !== false)
                header('Location: levels.php?done&edit='.$id);
            else {
                $app->utils->message('Error creating level');
            }
            die();
        }
    } else {
        $app->utils->message('Level not found');
        die();
    }
?>

<h1><?=$level?$level->title:'New level';?></h1>

<?php if (isset($_GET['done'])) $app->utils->message('Level updated', 'good'); ?>

<form class='level-edit' method="POST">
    <div class='clr'>
<?php if (!$level): ?>
        <div class='col span_4'>
            <label for="name">Name:</label><br/>
            <input name="name" value="<?=isset($level->data['reward'])?$level->data['reward']:0;?>"/>
        </div>
<?php endif; ?>
        <div class='col span_6'>
            <label>Category:</label><br/>
            <div class='select-menu' data-id="category" data-value="">
                <label><?=isset($level->group)?htmlentities($level->group):'Category';?></label>            
                <ul>
<?php
    $groups = $app->levels->getGroups();
    foreach($groups AS $group):
?>
                    <li><?=$group->title;?></li>
<?php
    endforeach;
?>
                </ul>
            </div>
        </div>
        <div class='col span_3'>
            <label for="reward">Reward:</label><br/>
            <input name="reward" value="<?=isset($level->data['reward'])?$level->data['reward']:0;?>"/>
        </div>
    </div>

    <div class='clr'>
        <label>Description:</label><br/>
<?php
    $wysiwyg_enter = false;
    $wysiwyg_name = "description";
    $wysiwyg_text = isset($level->data['description'])?$level->data['description']:'';
    include('elements/wysiwyg.php');
?>
    </div>

    <div class='clr'>
        <div class='col span_12'>
            <label>Hint:</label>
<?php
    $wysiwyg_lite = true;
    $wysiwyg_name = "hint";
    $wysiwyg_text = isset($level->data['hint'])?$level->data['hint']:'';
    include('elements/wysiwyg.php');
?>
        </div>
        <div class='col span_12'>
            <label>Solution message:</label>
<?php
    $wysiwyg_name = "solution";
    $wysiwyg_text = isset($level->data['solution'])?$level->data['solution']:'';
    include('elements/wysiwyg.php');
?>
        </div>
    </div>
    <input type="hidden" name="save"/>
    <input type="hidden" value="<?=$app->generateCSRFKey("level-editor");?>" name="token">
    <input type="submit" class="button" value="Save"/>
</form>