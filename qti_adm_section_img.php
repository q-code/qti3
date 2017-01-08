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
 * @version    3.0 build:20160703
 */

session_start();
require 'bin/init.php';
if ( sUser::Role()!='A' ) die($L['E_admin']);
$id = -1;
QThttpvar('id','int'); if ( $id<0 ) die('Missing parameter id...');

// --------
// INITIALISE
// --------

include Translate(APP.'_reg.php');

if ( !isset($_SESSION['temp_key']) ) $_SESSION['temp_key']='';
if ( !isset($_SESSION['temp_ext']) ) $_SESSION['temp_ext']='';

$oVIP->selfurl = 'qti_adm_section_img.php';
$oVIP->selfname = $L['Change_picture'];
$oVIP->exiturl = 'qti_adm_section.php?tt=1&amp;s='.$id;

$upload_path = QTI_DIR_DOC.'section/'; // The path to where the image will be saved
$large_image_location = $upload_path.'src'.$id.'_'.$_SESSION['temp_key'].$_SESSION['temp_ext'];
$thumb_image_location = $upload_path.$id.$_SESSION['temp_ext'];

if ( isset($_POST['exit']) )
{
  // Exit request code is placed before as following object initialisation is not required in case of exiting
  if ( file_exists($large_image_location) ) unlink($large_image_location);
  unset($_SESSION['temp_key']);
  $oHtml->Redirect($oVIP->exiturl);
}

$oSEC = new cSection($id);
$logo = $oSEC->ReadOption('logo');

if ( empty($logo) )
{
if ( file_exists('upload/section/'.$id.'.gif') ) $logo = $id.'.gif'; // section-logo directory is 'upload/section' (even if DIR_DOC is changed)
if ( file_exists('upload/section/'.$id.'.jpg') ) $logo = $id.'.jpg';
if ( file_exists('upload/section/'.$id.'.png') ) $logo = $id.'.png';
if ( file_exists('upload/section/'.$id.'.jpeg') ) $logo = $id.'.jpeg';
}

$max_file = 2;       // Maximum file size in MB
$max_width = 650;    // Display width for the large image (image can be larger)
$thumb_max_width  = (defined('QTI_SECTIONLOGO_WIDTH') ? QTI_SECTIONLOGO_WIDTH+25 : 75); // Above this value, the crop tool will start
$thumb_max_height = (defined('QTI_SECTIONLOGO_HEIGHT') ? QTI_SECTIONLOGO_HEIGHT+25 : 75); // Above this value, the crop tool will start
$thumb_width      = (defined('QTI_SECTIONLOGO_WIDTH') ? QTI_SECTIONLOGO_WIDTH : 50);  // Width of thumbnail image (75px)
$thumb_height     = (defined('QTI_SECTIONLOGO_HEIGHT') ? QTI_SECTIONLOGO_HEIGHT : 50); // Height of thumbnail image (75px)
$strMimetypes = 'image/pjpeg,image/jpeg,image/jpg,image/gif,image/png,image/x-png';
$photo = 'upload/section/'.$logo; // Current photo (Can be empty)
if ( !file_exists($photo) ) $photo='';
$photolabel = '';

//Check to see if any images with the same name already exist
$large_photo_exists = ''; if ( file_exists($large_image_location) ) $large_photo_exists = '<img src="'.$large_image_location.'" alt="Large image"/>';

function saveThumbnail($id,$str)
{
  global $oSEC;
  $oSEC->ChangeOption('logo',$id.$_SESSION['temp_ext']);
}

// --------
// SUBMITTED FOR DELETE
// --------

if ( isset($_POST['del']) )
{
  if ( !empty($logo) && file_exists('upload/section/'.$logo) ) unlink('upload/section/'.$logo);
  if ( file_exists($large_image_location) ) unlink($large_image_location);
  $oSEC->ChangeOption('logo','0');
  unset($_SESSION['temp_key']);
  $oVIP->selfname = $L['Picture'];
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// PAGE
// --------

$oHtml->links = array();
$oHtml->links[] = '<link rel="shortcut icon" href="admin/qti_icon.ico" />';
$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="admin/qt_base.css" />';
$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="admin/qti_main.css" />';

include 'qti_upload_img.php';