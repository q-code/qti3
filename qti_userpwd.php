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
if ( !sUser::CanView('U') ) die(Error(11));

// INITIALISE

include 'bin/class/qt_class_smtp.php';
include Translate(APP.'_reg.php');

$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Missing parameters');

$oVIP->selfurl = 'qti_userpwd.php';
$oVIP->selfuri = 'qti_userpwd.php?id='.$id;
$oVIP->selfname = L('Change_password');
$oVIP->exiturl = 'qti_user.php?id='.$id;
$oVIP->exitname = L('Exit');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // CHECK VALUE and protection against injection

  if ( !QTispassword($_POST['title']) ) $error=$L['Old_password'].' '.L('invalid');
  if ( !QTispassword($_POST['newpwd']) ) $error=$L['New_password'].' '.L('invalid');
  if ( !QTispassword($_POST['conpwd']) ) $error=$L['Confirm_password'].' '.L('invalid');
  if ( $_POST['title']==$_POST['newpwd'] ) $error=$L['New_password'].' '.L('invalid');
  if ( $_POST['conpwd']!=$_POST['newpwd'] ) $error=$L['Confirm_password'].' '.L('invalid');

  // CHECK OLD PWD

  if ( empty($error) )
  {
    $oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE id='.$id.' AND pwd="'.sha1($_POST['title']).'"');
    $row = $oDB->Getrow();
    if ( $row===false || $row['countid']==0 ) $error = L('Old_password').' '.L('invalid');
  }

  // EXECUTE

  if ( empty($error) )
  {
    // save new password
    $oDB->Exec('UPDATE '.TABUSER.' SET pwd="'.sha1($_POST['newpwd']).'" WHERE id='.$id);

    // exit
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
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

// CHECK ACCESS RIGHT

if ($id < 0) die('Missing parameters');
if ( ( sUser::Role()!='A' ) && (sUser::Id()!=$id) ) die(Error(11));

// QUERY

$oDB->Query('SELECT name,mail,children,parentmail,photo FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

$strUserImage = AsUserImg((empty($row['photo']) ?  '' : QTI_DIR_PIC.$row['photo']));

include 'qti_inc_hd.php';

// DISPLAY

echo '
<table>
<tr>
<td style="width:120px"><br/>',AsImgBox($strUserImage,QTtrunc($row['name'],24)),'</td>
<td>
';

$oHtml->Msgbox($oVIP->selfname);

if ( !empty($error) ) echo '<p id="infomessage" class="error">',$error,'</p>';

echo '<form method="post" action="',Href($oVIP->selfuri),'">
<p>',L('Old_password'),'&nbsp;<input required type="password" id="title" name="title" pattern="^.{3}.*" title="'.sprintf(L('E_char_min'),3).'" size="24" maxlength="24"/></p>
<p>',L('New_password'),'&nbsp;<input required type="password" id="newpwd" name="newpwd" pattern="^.{3}.*" title="'.sprintf(L('E_char_min'),3).'" size="24" maxlength="24"/></p>
<p>',L('Confirm_password'),'&nbsp;<input required type="password" id="conpwd" name="conpwd" pattern="^.{3}.*" title="'.sprintf(L('E_char_min'),3).'" size="24" maxlength="24"/></p>
<p><input type="hidden" name="id" value="',$id,'"/>
<input type="hidden" name="name" value="',$row['name'],'"/>
<input type="hidden" name="mail" value="',$row['mail'],'"/>
<input type="hidden" name="child" value="',$row['children'],'"/>
<input type="hidden" name="parentmail" value="',$row['parentmail'],'"/>
<input type="submit" id="ok" name="ok" value="',L('Save'),'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
</form>
<p><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>
';

$oHtml->Msgbox(END);

echo '
</td>
</tr>
</table>
';

// HTML END

include 'qti_inc_ft.php';