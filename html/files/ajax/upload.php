<?php
require_once('init.php');

$error = false;
$id = uniqid('', true);

$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);
if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/jpg")
|| ($_FILES["file"]["type"] == "image/png"))
&& ($_FILES["file"]["size"] < 2000000)
&& in_array($extension, $allowedExts))
  {
  if ($_FILES["file"]["error"] > 0)
    {
   $error = true;
    }
  else
    {
      $filename = $id . '.' . $extension;
      $filepath = $app->config('path') . "/files/uploads/images/" . $filename;
      echo $filepath;
    if (file_exists($filepath))
      {

   $error = true;
      }
    else
      {
      move_uploaded_file($_FILES["file"]["tmp_name"], $filepath);
      }
    }
  }
else
  {

   $error = true;
  }

//update users image
$user->setImagePath($filename);

if ($error) {
  if ($redirect)
    header('Location: ?error');
  else
    echo "error";
} else {
  if ($redirect)
    header('Location: ?done');
  else
    echo "done";
}
?>