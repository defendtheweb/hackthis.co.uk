<?php
if (!isset($profile) || !isset($profile->uid)) {
    header('HTTP/1.0 404 Not Found');
    die();
}

// Create objects
$image = new Imagick('../files/images/userbar_old.png');
 
// Watermark text
 
// Create a new drawing palette
$draw = new ImagickDraw();
 
// Set font properties
$draw->setFont('../files/fonts/visitor.ttf');
$draw->setFontSize(10);
$draw->setFillColor('white');



$user = $profile->username;
$rank = "Admin";
$score = $profile->score;

if (isset($_GET['display'])) {
    switch ($_GET['display']) {
        case 1:
            $text = $user . " - " . $rank;
            break;
        case 2:
            $text = $user . " - " . $score;
            break;
        case 3:
            $text = $user . " - " . $rank . " [" . $score . "]";
            break;
        case 4:
            $text = $rank . " // " . $user;
            break;
        case 5:
            $text = $score . " // " . $user;
            break;
        case 6:
            $text = $rank . " [" . $score . "] // " . $user;
            break;
        case 7:
            $text = $rank . " // " . $user . " - " . $score;
            break;
        case 8:
            $text = $user;
            break;
        case 0:
            $text = "";
            break;
        default:
           $text = $rank . " // " . $user;
    }
} else {
    $text = $rank . " // " . $user;
}
        
// Position text at the bottom-right of the image
$draw->setGravity(Imagick::GRAVITY_EAST);
$image->annotateImage($draw, 8, 0, 0, $text);
 
// Set output image format
$image->setImageFormat('png');
 
// Output the new image
header('Content-type: image/png');
echo $image;

?>