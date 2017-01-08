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
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_profile.css" />';
if ( !sUser::CanView('U',true) ) die(Error(11));

// INITIALISE

include Translate(APP.'_reg.php');

$id = -1;
if ( isset($_GET['id']) ) $id = intval(strip_tags($_GET['id']));
if ( isset($_POST['id']) ) $id = intval(strip_tags($_POST['id']));
if ( $id<0 ) die('Missing parameters');
if ( sUser::Role()!='A' && sUser::Id()!=$id ) die(Error(11));

$oVIP->selfurl = 'qti_usersign.php';
$oVIP->selfuri = 'qti_usersign.php?id='.$id;
$oVIP->selfname = L('Change_signature');
$oVIP->exiturl = 'qti_user.php?id='.$id;
$oVIP->exitname = L('Exit');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check values and protect against injection
  $str = trim($_POST['text']);

  // update user
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABUSER.' SET signature="'.QTstrd($str,255).'" WHERE id='.$id);
    // exit
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
    $oHtml->Redirect($oVIP->exiturl);
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// DEFAULT

$oDB->Query('SELECT signature,name,photo FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow(); if ( !is_string($row['signature']) ) $row['signature']='';

if ( empty($row['photo']) )
{
$strUserImage = '<img src="'.$_SESSION[QT]['skin_dir'].'/user.gif" title="'.QTstrh($row['name']).'" alt="(user)">';
}
else
{
$strUserImage = AsImg(QTI_DIR_PIC.$row['photo'],'',QTstrh($row['name']));
}

// --------
// HTML START
// --------

$intBbc=3;
if ( QTI_BBC )
{
$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qti_bbc.js"></script>';
}

include 'qti_inc_hd.php';

if ( sUser::Role()!='A' && sUser::Id()!=$id ) die(Error(11));

$strSign = QTbbc($row['signature']);
if ( empty($strSign) ) $strSign=S;

$strUserImage = AsUserImg((empty($row['photo']) ?  '' : QTI_DIR_PIC.$row['photo']));

echo '<table>
<tr>
<td style="width:150px">',AsImgBox($strUserImage,QTtrunc($row['name'],20)),'</td>
<td>
';

// SIGNATURE

echo '<h2>',$L['Signature'],'</h2>
<table class="t-data"><tr><td>',$strSign,'</td></tr></table>
<p>',$L['H_no_signature'],'</p>
';

// NEW SIGNATURE

echo '
<h2>',$oVIP->selfname,'</h2>
<form method="post" action="',Href($oVIP->selfuri),'">
<table class="t-data">
<tr>
<td>
';
if ( QTI_BBC ) include 'qti_form_button.php';
echo '<div><a id="textarea"></a><textarea id="text" name="text" rows="5" cols="75">',$row['signature'],'</textarea></div>',PHP_EOL;
echo '<br/>
<input type="hidden" name="id" value="',$id,'"/>
<input type="submit" id="ok" name="ok" value="',L('Save'),'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>',( !empty($error) ? ' <span class="error">'.$error.'</span>' : ''),'
</td>
</tr>
</table>
</form>
<p><a href="',Href($oVIP->exiturl),'">',$oVIP->exitname,'</a></p>
';

echo '
</td>
</tr>
</table>
';

// HTML END

include 'qti_inc_ft.php';