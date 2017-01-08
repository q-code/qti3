<?php

// QuickTicket 2.0 build:20160703

session_start();
$strFont = 'qti_icode.ttf';
$intBack = rand(1, 2);
$imgPng = ImageCreateFromPNG('qti_icode_'.$intBack.'.png');

// Generate the random string
$strText = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
$_SESSION['textcolor'] = sha1($strText);

// Create random size, angle, and dark color
$size = 13;
$angle = rand(-5, 5);
$color = ImageColorAllocate($imgPng, rand(0, 100), rand(0, 100), rand(0, 100));

//Determine text size, and use dimensions to generate x & y coordinates
$textsize = imagettfbbox($size, $angle, $strFont, $strText);
$twidth = abs($textsize[2]-$textsize[0]);
$theight = abs($textsize[5]-$textsize[3]);
$x = (imagesx($imgPng)/2)-($twidth/2)+(rand(-15, 15));
$y = (imagesy($imgPng))-($theight/2);

//Add text to image
ImageTTFText($imgPng, $size, $angle, $x, $y, $color, $strFont, $strText);

//Output PNG Image
header('Content-Type: image/png');
ImagePNG($imgPng);

//Destroy the image to free memory
imagedestroy($imgPng);

//End Output
exit;

?>