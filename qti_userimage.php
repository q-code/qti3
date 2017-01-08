<?php

/**
* PHP version 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2015 The PHP Group
* @license    http://www.php.net/license  PHP License 3.0
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
if ( $_SESSION[QT]['avatar']=='0' ) { $oHtml->PageMsg(0); return; }
if ( !sUser::CanView('U') ) die(Error(11));
$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Missing parameter id...');
if ( sUser::Role()!='A' ) { if (sUser::Id()!=$id) die($L['R_user']); }
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_profile.css" />';

include 'bin/class/qt_class_smtp.php';
include Translate(APP.'_reg.php');

// --------
// INITIALISE
// --------

if ( !isset($_SESSION['temp_key']) ) $_SESSION['temp_key']= "";
if ( !isset($_SESSION['temp_ext']) ) $_SESSION['temp_ext']= "";

$oVIP->selfurl = 'qti_userimage.php';
$oVIP->selfuri = 'qti_userimage.php?id='.$id;
$oVIP->selfname = $L['Change_picture'];
$oVIP->exiturl = 'qti_user.php?id='.$id;
$oVIP->exitname = '&laquo; '.$L['Profile'];

$oDB->Query('SELECT name,photo,children,role,parentmail FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();
$arrChild = $row; // children info used in function saveThumbnail

$upload_path = QTI_DIR_PIC.TargetDir(QTI_DIR_PIC,$id); // The path to where the image will be saved
$large_image_location = $upload_path.'src'.$id.'_'.$_SESSION['temp_key'].$_SESSION['temp_ext'];
$thumb_image_location = $upload_path.$id.$_SESSION['temp_ext'];

// Save
function saveThumbnail($id,$str)
{
  global $oDB;
  $oDB->Exec('UPDATE '.TABUSER.' SET photo="'.str_replace(QTI_DIR_PIC,'',$str).'" WHERE id='.$id); //remove the QTI_DIR_PIC
}

// Staff cannot edit other staff
if ( $row['role']=='M' && sUser::Role()==='M' && sUser::Id()!=$id ) die(Error(13));

// check folder

$b=false;
if ( is_dir(QTI_DIR_PIC) ) {
if ( is_readable(QTI_DIR_PIC) ) {
if ( is_writable(QTI_DIR_PIC) ) {
  $b=true;
}}}
if ( !$b ) $oHtml->PageMsg( NULL, '<p>The directory ['.QTI_DIR_PIC.'] is not writable (or missing). Please, contact the webmaster to fix the problem.</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '350px');

// --------
// SUBMITTED for Exit
// --------

if ( isset($_POST['exit']) )
{
  if ( file_exists($large_image_location) ) unlink($large_image_location);
  unset($_SESSION['temp_key']);
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// INITIALISE image and repository object
// --------

$photo = (empty($row['photo']) ? '' : QTI_DIR_PIC.$row['photo']); // Current photo (Can be empty)
$photolabel = QTtrunc($row['name'],20);
$strUserPlaceholder = $_SESSION[QT]['skin_dir'].'/user.gif';

$max_file = 3;       // Maximum file size in MB
$max_width = 650;    // Max width allowed for the large image
$thumb_max_width = (isset($_SESSION[QT]['avatar_width']) ? $_SESSION[QT]['avatar_width'] : 150); // Above this value, the crop tool will start
$thumb_max_height = (isset($_SESSION[QT]['avatar_height']) ? $_SESSION[QT]['avatar_height'] : 150); // Above this value, the crop tool will start
$thumb_width = 100;  // Width of thumbnail image
$thumb_height = 100; // Height of thumbnail image
$strMimetypes = 'image/pjpeg,image/jpeg,image/jpg';
if ( strpos($_SESSION[QT]['avatar'],'gif')!==FALSE) $strMimetypes.=',image/gif';
if ( strpos($_SESSION[QT]['avatar'],'png')!==FALSE) $strMimetypes.=',image/png,image/x-png';

//Check to see if any images with the same name already exist
$large_photo_exists = ''; if ( file_exists($large_image_location) ) $large_photo_exists = "<img src=\"".$large_image_location."\" alt=\"Large Image\"/>";

// --------
// SUBMITTED for Delete
// --------

if ( isset($_POST['del']) )
{
  if ( file_exists($large_image_location) ) unlink($large_image_location);
  if ( file_exists($thumb_image_location) ) unlink($thumb_image_location);
  $oDB->Exec('UPDATE '.TABUSER.' SET photo="0" WHERE id='.$id);
  unset($_SESSION['temp_key']);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// SUBMITTED FOR UPLOAD
// --------

if ( isset($_POST['ok']) )
{
  // Check uploaded document

  if ( !isset($_SESSION[QT]['avatar_width']) ) $_SESSION[QT]['avatar_width']=120;
  if ( !isset($_SESSION[QT]['avatar_height']) ) $_SESSION[QT]['avatar_height']=120;
  if ( !isset($_SESSION[QT]['avatar_size']) ) $_SESSION[QT]['avatar_size']=20;

  $error = InvalidUpload($_FILES['title'],$_SESSION[QT]['avatar'],'',intval($_SESSION[QT]['avatar_size']),intval($_SESSION[QT]['avatar_width']),intval($_SESSION[QT]['avatar_height']));

  // Copy file

  if ( empty($error) )
  {
    $strDir = TargetDir(QTI_DIR_PIC,$id); if ( !is_writable(QTI_DIR_PIC.$strDir) ) $oHtml->PageMsg(NULL,'<p>The directory ['.QTI_DIR_PIC.$strDir.'] is not writable (or missing). Please, contact the webmaster to fix the problem.</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a>' );
    $strExt = strtolower(substr(strrchr($_FILES['title']['name'],'.'),1));
    if ( !copy($_FILES['title']['tmp_name'],QTI_DIR_PIC.$strDir.$id.'.'.$strExt) ) $error = 'Cannot copy the file ['.QTI_DIR_PIC.$strDir.$id.'.'.$strExt.']. Possible cause: this directory is readonly.';
    unlink($_FILES['title']['tmp_name']);
  }

  // Save

  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABUSER.' SET photo="'.$strDir.$id.'.'.$strExt.'" WHERE id='.$id);

    // Exit
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
    $oHtml->Redirect($oVIP->exiturl);
  }
}

// --------
// HTML START
// --------

include 'qti_upload_img.php';