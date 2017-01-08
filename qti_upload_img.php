<?php

/**
 * This is included in qti_adm_section_img.php and qti_userimage.php
 * In these parent pages you must define:
 * - specific code for DELETE and EXIT request
 * - specific function saveThumbnail()
 * - variables:
 *   $photo = ''; // current photo
 *   $photolabel = ''; // label of the current photo
 *   $max_file = 2;       // Maximum file size in MB
 *   $max_width = 650;    // Display width of large image (large image will be resized)
 *   $thumb_max_width = 150;  // Above this value, the crop tool will start
 *   $thumb_max_height = 150; // Above this value, the crop tool will start
 *   $thumb_width = 100;  // Width of thumbnail image
 *   $thumb_height = 100; // Height of thumbnail image
 *   $strMimetypes = 'image/pjpeg,image/jpeg,image/jpg,image/gif,image/png,image/x-png'; // allowed mimetypes
 *   $large_photo_exists = '';
 */

// --------
// FUNCTION
// --------

function resizeImage($image,$width,$height,$scale)
{
  list($imagewidth, $imageheight, $imageType) = getimagesize($image);
  $imageType = image_type_to_mime_type($imageType);
  $newImageWidth = ceil($width * $scale);
  $newImageHeight = ceil($height * $scale);
  $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
  switch($imageType)
  {
    case 'image/gif': $source=imagecreatefromgif($image); break;
    case 'image/pjpeg':
    case 'image/jpeg':
    case 'image/jpg': $source=imagecreatefromjpeg($image); break;
    case 'image/png':
    case 'image/x-png': $source=imagecreatefrompng($image); break;
  }
  imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
  switch($imageType)
  {
    case 'image/gif': imagegif($newImage,$image); break;
    case 'image/pjpeg':
    case 'image/jpeg':
    case 'image/jpg': imagejpeg($newImage,$image,90); break;
    case 'image/png':
    case 'image/x-png': imagepng($newImage,$image); break;
  }
  chmod($image, 0777);
  return $image;
}

function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale)
{
  list($imagewidth, $imageheight, $imageType) = getimagesize($image);
  $imageType = image_type_to_mime_type($imageType);
  $newImageWidth = ceil($width * $scale);
  $newImageHeight = ceil($height * $scale);
  $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
  switch($imageType)
  {
    case 'image/gif': $source=imagecreatefromgif($image); break;
    case 'image/pjpeg':
    case 'image/jpeg':
    case 'image/jpg': $source=imagecreatefromjpeg($image); break;
    case 'image/png':
    case 'image/x-png': $source=imagecreatefrompng($image); break;
  }
  imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
  switch($imageType)
  {
    case 'image/gif': imagegif($newImage,$thumb_image_name); break;
    case 'image/pjpeg':
    case 'image/jpeg':
    case 'image/jpg': imagejpeg($newImage,$thumb_image_name,90); break;
    case 'image/png':
    case "image/x-png": imagepng($newImage,$thumb_image_name); break;
  }
  chmod($thumb_image_name, 0777);
  return $thumb_image_name;
}

function getHeight($image)
{
  $size = getimagesize($image);
  $height = $size[1];
  return $height;
}

function getWidth($image)
{
  $size = getimagesize($image);
  $width = $size[0];
  return $width;
}

// --------
// SUBMITTED FOR UPLOAD
// --------

if (isset($_POST['upload']))
{
  // delete old large_image
  if ( file_exists($large_image_location) ) unlink($large_image_location);

  //Only process if the file is a JPG, PNG or GIF and below the allowed limit
  $error = InvalidUpload($_FILES['image'],'gif,jpg,jpeg,png',$strMimetypes,$max_file*1048576,0,0);

  // Check destination folder
  if ( empty($error) )
  {
    if ( !is_dir($upload_path) || !is_writable($upload_path) ) $error = 'Destination folder ['.$upload_path.'] is not writable.';
  }

  // Process uploaded image

  if ( empty($error) )
  {
    // Check size
    $width = getWidth($_FILES['image']['tmp_name']);
    $height = getHeight($_FILES['image']['tmp_name']);
    $str = basename($_FILES['image']['name']);
    $_SESSION['temp_ext'] = '.'.strtolower(substr($str,strrpos($str, '.')+1));
    $thumb_image_location = $upload_path.$id.$_SESSION['temp_ext'];

    // Process for small image
    if ( $width<=$thumb_max_width && $height<=$thumb_max_height )
    {
      move_uploaded_file($_FILES['image']['tmp_name'], $thumb_image_location);
      chmod($thumb_image_location, 0777);
      saveThumbnail($id,$thumb_image_location);
    }
    else
    {
      // Process for large image. save large_image with a new name (to be sure that it will be reloaded through the proxys)
      $_SESSION['temp_key'] = strtotime(date('Y-m-d H:i:s'));
      $large_image_location = $upload_path.'src'.$id.'_'.$_SESSION['temp_key'].$_SESSION['temp_ext'];
      move_uploaded_file($_FILES['image']['tmp_name'], $large_image_location);
      chmod($large_image_location, 0777);

      //Scale the image if it is greater than the width set above
      if ($width > $max_width)
      {
        $scale = $max_width/$width;
        $uploaded = resizeImage($large_image_location,$width,$height,$scale);
      }else{
        $scale = 1;
        $uploaded = resizeImage($large_image_location,$width,$height,$scale);
      }
    }
    //Refresh the page to show the new uploaded image
    $oHtml->Redirect($oVIP->selfurl.'?id='.$id);
    exit;
  }
}

// --------
// SUBMITTED FOR SAVE THUMBNAIL
// --------

if (isset($_POST['upload_thumbnail']) && strlen($large_photo_exists)>0)
{
  $x1 = $_POST['x1'];
  $y1 = $_POST['y1'];
  $x2 = $_POST['x2'];
  $y2 = $_POST['y2'];
  $w = $_POST['w'];
  $h = $_POST['h'];
  $scale = $thumb_width/$w;
  $cropped = resizeThumbnailImage($thumb_image_location, $large_image_location,$w,$h,$x1,$y1,$scale);

  // Save
  saveThumbnail($id,$upload_path.$id.$_SESSION['temp_ext']); // The function saveThumbnail() must be defined in the parent page.

  // Delete temporary image and Exit
  if (file_exists($large_image_location)) unlink($large_image_location);
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// HTML START
// --------

if(strlen($large_photo_exists)>0)
{
$current_large_image_width = getWidth($large_image_location);
$current_large_image_height = getHeight($large_image_location);

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="bin/js/imgareaselect.css" />';
$oHtml->scripts[] = '
<script type="text/javascript" src="bin/js/jquery.min.js"></script>
<script type="text/javascript" src="bin/js/imgareaselect.js"></script>
<script type="text/javascript">

function preview(img, selection)
{
  var scaleX = '.$thumb_width.' / selection.width;
  var scaleY = '.$thumb_height.' / selection.height;
  $("#preview").css({
    width: Math.round(scaleX * '.$current_large_image_width.') + "px",
    height: Math.round(scaleY * '.$current_large_image_height.') + "px",
    marginLeft: "-" + Math.round(scaleX * selection.x1) + "px",
    marginTop: "-" + Math.round(scaleY * selection.y1) + "px"
  });
  $("#x1").val(selection.x1);
  $("#y1").val(selection.y1);
  $("#x2").val(selection.x2);
  $("#y2").val(selection.y2);
  $("#w").val(selection.width);
  $("#h").val(selection.height);
}

jQuery(function($){

$("#save_thumb").click(function() {
	var x1 = $("#x1").val();
	var y1 = $("#y1").val();
	var x2 = $("#x2").val();
	var y2 = $("#y2").val();
	var w = $("#w").val();
	var h = $("#h").val();
	if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h=="") { alert("You must make a selection first"); return false; } else { return true; }
});

$("#thumbnail").imgAreaSelect({ aspectRatio: "1:'.$thumb_height/$thumb_width.'", onSelectChange: preview });


});

</script>
';
}

echo $oHtml->Head();
echo $oHtml->Body();


$oHtml->Msgbox($oVIP->selfname,array('style'=>'width:680px'));

if ( !empty($error) ) echo '<p class="error">',$error,'</p>',PHP_EOL;

// PLACEHOLDER image (if empty image)

if ( empty($strUserPlaceholder) ) $strUserPlaceholder='';
$strUserImage = AsUserImg((empty($photo) ? $strUserPlaceholder : $photo));

echo '
<table class="hidden">
<tr>
<td style="width:200px;">',AsImgBox($strUserImage,$photolabel,'picbox','float:left;min-height:80px'),'</td>
<td style="text-align:right;">
<form action="'.$oVIP->selfuri.'" method="post" enctype="multipart/form-data">
<p><input type="hidden" name="id" value="'.$id.'"/><input type="file" name="image" size="30" /> <input type="submit" name="upload" value="Upload" style="width:75px" /></p>
</form>
',(empty($photo) ? '<p>&nbsp;</p>' : '<form method="post" action="'.$oVIP->selfuri.'"><p>'.L('Delete_picture').' <input type="hidden" name="id" value="'.$id.'" /><input type="submit" name="del" value="'.L('Delete').'" style="width:75px" /></p></form>'),'
<form method="post" action="',$oVIP->selfuri,'"><p><input type="hidden" name="id" value="',$id,'" /><input type="submit" name="exit" value="',L('Exit'),'" style="width:75px" /></p></form>
</td>
</tr>
</table>
';

if(strlen($large_photo_exists)>0)
{
echo'<hr style="margin:20px 0;border:1px solid #dddddd" />
<noscript><p class="error">Your browser must support javascript to be able to resize image.</p></noscript>
<table>
<tr>
<td style="width:200px"><div style="border:1px #e5e5e5 solid; overflow:hidden; width:'.$thumb_width.'px; height:'.$thumb_height.'px;"><img id="preview" src="'.$large_image_location.'" style="position: relative;" alt="Thumbnail Preview" /></div></td>
<td>
<p class="small">',$L['Picture_thumbnail'],'</p>
<form action="',$_SERVER['PHP_SELF'],'" method="post">
<p><input type="hidden" name="id" value="'.$id.'" id="id" />
<input type="hidden" name="x1" value="" id="x1" />
<input type="hidden" name="y1" value="" id="y1" />
<input type="hidden" name="x2" value="" id="x2" />
<input type="hidden" name="y2" value="" id="y2" />
<input type="hidden" name="w" value="" id="w" />
<input type="hidden" name="h" value="" id="h" />
<input type="submit" name="upload_thumbnail" value="',L('Save'),'" id="save_thumb" /></p>
</form>
</td>
</tr>
</table>
<img style="border:1px #e5e5e5 solid;margin-top:10px" src="'.$large_image_location.'" id="thumbnail" alt="Create Thumbnail" />',PHP_EOL;
}

$oHtml->Msgbox(END);

echo $oHtml->End();