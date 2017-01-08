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

$id = -1;
if ( isset($_GET['id']) ) $id = intval(strip_tags($_GET['id']));
if ( isset($_POST['id']) ) $id = intval(strip_tags($_POST['id']));
if ( $id<=0 ) die('Missing parameter');

$oVIP->selfurl = 'qti_userquestion.php';
$oVIP->selfuri = 'qti_userquestion.php?id='.$id;
$oVIP->selfname = $L['Secret_question'];
$oVIP->exiturl = 'qti_user.php?id='.$id;
$oVIP->exitname = L('Exit');

// QUERY

$oDB->Query('SELECT name,mail,children,parentmail,photo,secret_q,secret_a FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // CHECK VALUE and protection against injection

  $strQ = trim($_POST['secret_q']);
  $strA = trim($_POST['secret_a']);

  if ( empty($error) )
  {
    // save new password
    $oDB->Exec('UPDATE '.TABUSER.' SET secret_q="'.QTstrd($strQ,255).'",secret_a="'.QTstrd($strA,255).'" WHERE id='.$id);

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

include 'qti_inc_hd.php';

$strUserImage = AsUserImg((empty($row['photo']) ?  '' : QTI_DIR_PIC.$row['photo']));

echo '
<table>
<tr>
<td style="width:150px"><br/>',AsImgBox($strUserImage,QTtrunc($row['name'],20)),'</td>
<td>
';

$oHtml->Msgbox($oVIP->selfname);

if ( !empty($error) ) echo '<p id="infomessage" class="error">',$error,'</p>';

echo '<form method="post" action="',Href($oVIP->selfuri),'">
<p>',$L['H_Secret_question'],'</p>
<p><select id="secret_q" name="secret_q">',QTasTag($L['Secret_q'],$row['secret_q']),'</select></p>
<p><input required type="text" id="secret_a" name="secret_a" size="32" maxlength="255" value="',$row['secret_a'],'"/></p>
<p>';
echo '<input type="hidden" name="id" value="',$id,'"/>
<input type="hidden" name="name" value="',$row['name'],'"/>
<input type="hidden" name="mail" value="',$row['mail'],'"/>
<input type="hidden" name="child" value="',$row['children'],'"/>
<input type="hidden" name="parentmail" value="',$row['parentmail'],'"/>
<input type="submit" id="ok" name="ok" value="',L('Save'),'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
</p>
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