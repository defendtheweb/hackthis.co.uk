<?php
    if (isset($_POST['title'])):
        $status = $app->levels->addCategory($_POST['title']);
        if ($status)
            $app->utils->message('Category added', 'good');
        else
            $app->utils->message('Error and stuff');
    else:
?>

    <form method="POST">
        <label for="title">Category:</label><br/>
        <input class='short' type="text" name="title"/>
        <input type="submit" class='button left' value="Add"/>
    </form>

<?php
    endif;
?>