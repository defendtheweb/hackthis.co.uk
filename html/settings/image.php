<?php
    if (isset($_GET['upload'])) {
        $redirect = true;
        require('../files/ajax/upload.php');
    }

    $custom_js = array('jquery.filedrop.js', 'upload.js');
    $custom_css = array('settings.scss');
    require_once('init.php');

    if (isset($_GET['gravatar']) && !isset($_GET['done'])) {
        $user->setImagePath('gravatar');
        header('Location: ?gravatar&done');
    }

    require_once('header.php');

    if (isset($_GET['done'])):
?>
    <div class='msg msg-good'>
        <i class='icon-good'></i>
        Image updated
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

    <section class="row">
        <div class="col span_15">
            <h1>Upload Image</h1>
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
            <br/>
            By uploading a file you certify that you have the right to distribute this picture and does not violate the <a href='/terms.php'>Terms of Service</a>.
            <br/><br/>
        </div>
        <div class="col span_1">
            &nbsp;
        </div>
        <div class="col span_8">
            <h1>Use Gravatar</h1>
            <div class='gravatar-pic'>
                <img width='200px' src='https://www.gravatar.com/avatar/<?=md5(strtolower(trim($user->email)));?>?d=http://www.hackthis.co.uk/users/images/no_pic.jpg&s=200'/>
                <a href='?gravatar'>
                    <i class="icon-image"></i><br/>
                    Use Gravatar
                </a>
            </div>
            <br/><br/>
            Gravatar is a <strong>G</strong>lobally <strong>R</strong>ecognized <strong>Avatar</strong>. You upload it and create your profile just once, and then when you participate in any Gravatar-enabled site, your Gravatar image will automatically follow you there. To create your account or update your image <a href='https://en.gravatar.com/site/signup'>click here</a>.
        </div>
    </section>
<?php
    require_once('footer.php');
?>           