<?php
    require_once('init.php');
    
    function upload() {
        global $app, $user;
        $allowedExtensions = array(".jpg", ".jpeg", ".png", ".gif", ".bmp");

        if (empty($_FILES) || empty($_FILES['file']['tmp_name']))
            return false;

        $ext = strtolower(strrchr($_FILES['file']['name'], "."));
        $e = 0;
        foreach($allowedExtensions as $extension) {
            if ($ext == $extension)
                $e++;
        }
        if ($e <= 0)
            return false;
        
        $file_info = getimagesize($_FILES['file']['tmp_name']);
        
        if (empty($file_info))
            return false;
            
        if ($file_info[3] == IMAGETYPE_GIF || $file_info[3] == IMAGETYPE_JPEG || $file_info[3] == IMAGETYPE_PNG)
            return false;
            
        //Check file size
        if ($_FILES['file']['size'] > 5000000)
            return false;

        $id = uniqid('', true);
        $filename = $id . $ext;
        $filepath = $app->config('path') . "/files/uploads/users/" . $filename;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $filepath)) {
            $app->user->setImagePath($filename);

            return true;
        }

        return false;
    }

    if (upload()) {
        if (isset($redirect) && $redirect)
            header('Location: ?done');
        else
            echo "done";
    } else {
        if (isset($redirect) && $redirect)
            header('Location: ?error');
        else
            echo "error";
    }
    die();
?>
