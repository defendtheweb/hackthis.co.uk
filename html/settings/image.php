<?php
    if (isset($_GET['upload'])) {
        $redirect = true;
        require('../files/ajax/upload.php');
    }

    $custom_js = array('jquery.filedrop.js', 'upload.js');
    $custom_css = array('settings.scss');
    require_once('header.php');

    if (isset($_GET['done'])):
?>
    <div class='msg msg-good'>
        <i class='icon-good'></i>
        Image uploaded
    </div>
<?php
    elseif (isset($_GET['error'])):
?>
    <div class='msg msg-error'>
        <i class='icon-error'></i>
        There was an error uploading your image
    </div>
<?php
    else:
?>
    <div class='msg msg-error hide'>
        <i class='icon-error'></i>
        <span></span>
    </div>
<?php
    endif;
?>

    <form id="upload-form" action="image.php?upload" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file"><br/>
        <input type="submit" class='button left' name="submit" value="Submit">
    </form>
    <div id="upload-drop">
        <div class='action'>
            <i class='icon-upload'></i>
            Drop file here<br/>or click to browse
        </div>
    </div>

<?php
    require_once('footer.php');
?>           