<?php
class files {

    public static function upload($file) {
        global $app;

        $folder = $app->config('path') . '/files/uploads/images/';

        list($width,$height,$type,$attr) = getimagesize($file);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        // mime checks add a layer of security that keeps out less sophisticated attackers 
        if(($mime != "image/jpeg") && ($mime != "image/pjpeg") && ($mime != "image/png")) {
            return false;
        } else {
            // If the file has no width its not a valid image
            if(!$width) {
                $fileType = exif_imagetype($file);
                $allowed = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
                if(!in_array($fileType, $allowed)) {
                    // don't overwrite an existing file
                    $name = 'up_'.md5(mt_rand());
                    while(file_exists($folder . $name)) {
                        $name = 'up_'.md5(mt_rand());
                    }

                    if(move_uploaded_file($file['tmp_name'], $folder.$name)) {
                        chmod($folder.$name, 0644);
                        return $name;
                    } else {
                        return false;
                    }
                }
            }
        }
    }

}
?>