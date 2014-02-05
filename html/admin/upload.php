<?php
    $custom_css = array('articles.scss');
    $page_title = 'Admin - Articles';
    define("PAGE_PRIV", "admin_pub");

    require_once('init.php');

    if (isset($_FILES["file"])) {
    	require('class.files.php');
    	$upload = files::upload($_FILES["file"]);
    }

    require_once('header.php');

    if ($upload)
    	$app->utils->message('Image uploaded: '.$upload, 'good');
    else
    	$app->utils->message('Error uploading image');
?>

<form enctype="multipart/form-data" method="post">
	<input type="file" name="file" size="40">
	<input type="submit" value="Send" class='left button'>
</form>

<?php
	require_once('footer.php');
?>