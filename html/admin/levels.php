<?php
    $custom_css = array('levels.scss', 'admin.scss');
    $page_title = 'Admin - Levels';
    define("PAGE_PRIV", "admin_site");

    require_once('header.php');
?>

<a class='button right' href='?edit=new'>Add level</a>
<a class='button right' href='?edit-categories'>Edit categories</a>
<a href='levels.php'><h1>Level editor</h1></a>

<?php
    if (isset($_GET['edit-categories'])) {
        include('elements/level_editor/edit_categories.php');
    }

    if (isset($_GET['edit'])) {
        include('elements/level_editor/edit_level.php');
    } else {
        include('elements/level_editor/level_list.php');
    }
?>


<?php
    require_once('footer.php');
?>