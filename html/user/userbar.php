<?php
// Create objects
$image = new Imagick('../files/images/userbar_old.png');
 
// Watermark text
$text = 'FlabbyRabbit // Admin';
 
// Create a new drawing palette
$draw = new ImagickDraw();
 
// Set font properties
$draw->setFont('../files/fonts/visitor.ttf');
$draw->setFontSize(10);
$draw->setFillColor('white');
 
// Position text at the bottom-right of the image
$draw->setGravity(Imagick::GRAVITY_EAST);
$image->annotateImage($draw, 8, 0, 0, $text);
 
// Set output image format
$image->setImageFormat('png');
 
// Output the new image
header('Content-type: image/png');
echo $image;

die();

header("Content-type: image/png");

// switch ($display) {
//     case 1:
//         $text = $user . " - " . $rank;
//         break;
//     case 2:
//         $text = $user . " - " . $score;
//         break;
//     case 3:
//         $text = $user . " - " . $rank . " [" . $score . "]";
//         break;
//     case 4:
//         $text = $rank . " // " . $user;
//         break;
//     case 5:
//         $text = $score . " // " . $user;
//         break;
//     case 6:
//         $text = $rank . " [" . $score . "] // " . $user;
//         break;
//     case 7:
//         $text = $rank . " // " . $user . " - " . $score;
//         break;
//     case 8:
//         $text = $user;
//         break;
//     case 0:
//         $text = "";
//         break;
//     default:
//        $text = $rank . " // " . $user;
// }
     
$text = 'FlabbyRabbit';   
        
$image = imagecreatefrompng("../files/images/userbar_old.png");
$white = imagecolorallocate($image, 255, 255, 255);
$font = "../files/fonts/visitor.ttf";

$tb = imagettfbbox(9, 0, $font, $text);
$x = 350 - 10 - $tb[2];
imagettftext($image, 9, 0, $x, 12, $white, $font, $text);  

imagepng($image);
imagedestroy($image);

?>