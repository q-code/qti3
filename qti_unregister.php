<?php

/**
 * PHP versions 5
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
if ( !sUser::CanView('U') ) die(Error(11));
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_profile.css" />';

// --------
// INITIALISE
// --------

$id = -1;
if (isset($_GET['id'])) $id = intval(strip_tags($_GET['id']));
if (isset($_POST['id'])) $id = intval(strip_tags($_POST['id']));
if ($id<0) die('Missing parameters');
if (sUser::Id()!=$id ) die(Error(11));

include 'bin/class/qt_class_smtp.php';
include Translate(APP.'_reg.php');

$oVIP->selfurl = 'qti_unregister.php';
$oVIP->selfuri = 'qti_unregister.php?id='.$id;
$oVIP->selfname = L('Unregister');
$oVIP->exiturl = 'qti_user.php?id='.$id;
$oVIP->exitname = L('Exit');


// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check password
  $oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE id='.$id.' AND pwd="'.sha1($_POST['title']).'"');
  $row = $oDB->Getrow();
  if ($row['countid']==0) $error=L('Password').' '.L('invalid');

  // execute and exit
  if ( empty($error) )
  {
    $oDB->Query('SELECT * FROM '.TABUSER.' WHERE id='.$id);
    $row = $oDB->Getrow();
    cVIP::Unregister($row);
    $oVIP->exiturl='qti_login.php?a=out';
    
    // exit
    $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
    $oHtml->Redirect($oVIP->exiturl);
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// --------
// HTML START
// --------

$oDB->Query('SELECT * FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

$oHtml->scripts[] = '<script type="text/javascript">
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(html_entity_decode("'.L('Missing').': '.$L['Password'].'")); return false; }
  return null;
}
</script>
';

include 'qti_inc_hd.php';

$strUserImage = AsUserImg((empty($row['photo']) ?  '' : QTI_DIR_PIC.$row['photo']));

echo '
<table>
<tr>
<td style="width:150px;"><br />',AsImgBox($strUserImage,$row['name']),'</td>
<td>
';

$oHtml->Msgbox($oVIP->selfname.' '.$row['name']);

$str = $L['H_Unregister'].'
<form method="post" action="'.Href($oVIP->selfuri).'" onsubmit="return ValidateForm(this);">
<input type="hidden" name="id" value="'.$id.'" />
<p><label><i class="fa fa-lock fa-lg" title="'.QTstrh(L('Password')).'"/></label></i><input required type="password" id="title" name="title" pattern=".{3}.*" size="20" maxlength="24" placeholder="'.QTstrh(L('Password')).'"/> <input type="submit" name="ok" value="'.QTstrh(L('Unregister')).'&nbsp;!" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
</form>
';
if ( $row['role']!='U' ) $str = '<p>'.$row['name'].' is a Staff member.<br />To unregister a staff member, an administrator must first change role to User, or use the delete function.</p>';
if ( $id<2 ) $str = '<p>Admin and Visitor cannot be removed...</p>';

if ( !empty($error) ) echo '<p id="infomessage" class="error">',$error,'</p>';

echo $str,'
<p><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>
';

$oHtml->Msgbox(-1);

echo '
</td>
</tr>
</table>
';

// --------
// HTML END
// --------

include 'qti_inc_ft.php';