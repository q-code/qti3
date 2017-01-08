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

// --------
// INITIALISE
// --------

$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Missing parameters');

include 'bin/class/qt_class_smtp.php';
include Translate(APP.'_reg.php');

$oDB->Query('SELECT * FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

$oVIP->selfurl = 'qti_username.php';
$oVIP->selfuri = 'qti_username.php?id='.$id;
$oVIP->selfname = $L['Change_name'];
$oVIP->exiturl = 'qti_user.php?id='.$id;
$oVIP->exitname = L('Exit');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check name
  if ( empty($error) )
  {
    $_POST['title'] = trim($_POST['title']);
    if ( !QTislogin($_POST['title']) ) $error = L('Username').' '.L('invalid');
    if ( empty($error) )
    {
    $oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE name="'.QTstrd($_POST['title']).'"');
    $row = $oDB->Getrow();
    if ( $row['countid']!=0 ) $error = $error = L('Username').' '.L('e_already_used');
    }
  }

  // execute and exit
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABUSER.' SET name="'.QTstrd($_POST['title']).'" WHERE id='.$id);
    // Post
    $oDB->query('UPDATE '.TABPOST.' SET username="'.QTstrd($_POST['title']).'" WHERE userid='.$id);
    $oDB->query('UPDATE '.TABPOST.' SET modifname="'.QTstrd($_POST['title']).'" WHERE modifuser='.$id);
    $oDB->query('UPDATE '.TABTOPIC.' SET firstpostname="'.QTstrd($_POST['title']).'" WHERE firstpostuser='.$id);
    $oDB->query('UPDATE '.TABTOPIC.' SET lastpostname="'.QTstrd($_POST['title']).'" WHERE lastpostuser='.$id);
    $oDB->query('UPDATE '.TABSECTION.' SET moderatorname="'.QTstrd($_POST['title']).'" WHERE moderator='.$id);

    // exit
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
		$oHtml->redirect($oVIP->exiturl);

  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// DEFAULT

$oDB->Query('SELECT name,photo FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

$strUserImage = AsUserImg((empty($row['photo']) ?  '' : QTI_DIR_PIC.$row['photo']));

// --------
// HTML START
// --------


$oHtml->scripts_jq[] = '
$(function() {
  $("#title").keyup(function() {
    document.getElementById("title_err").innerHTML="&nbsp;";
    if ( $("#title").val().length<4 ) return;
    $.post("bin/qti_j_exists.php",
      {f:"name",v:$("#title").val(),e2:"'.$L['Already_used'].'"},
      function(data) { if ( data.length>0 ) document.getElementById("title_err").innerHTML=data; });
  });
});
';

include 'qti_inc_hd.php';

echo '
<table>
<tr>
<td style="width:150px;">',AsImgBox($strUserImage,QTtrunc($row['name'],20)),'</td>
<td>
';

$oHtml->Msgbox($oVIP->selfname);

if ( !empty($error) ) echo '<p id="infomessage" class="error">',$error,'</p>';

echo '<form method="post" action="',$oVIP->selfuri,'">
<input type="hidden" name="id" value="',$id,'"/>
<h2>',$row['name'],'</h2>
<p>',$L['Choose_name'],'</p>
<p><label><i class="fa fa-user fa-lg" title="',QTstrh(L('Username')),'"/></i></label><input required type="text" id="title" name="title" pattern="^.{2}.*" title="'.sprintf(L('E_char_min'),2).'" size="20" maxlength="64"/></p>
<p><span id="title_err" class="error">&nbsp;</span> <input type="submit" id="DoRename" name="ok" value="',L('Save'),'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
</form>
<p><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>
';

$oHtml->Msgbox(END);

echo '
</td>
</tr>
</table>
';

// --------
// HTML END
// --------

include 'qti_inc_ft.php';