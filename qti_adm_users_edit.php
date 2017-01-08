<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
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
if ( !sUser::IsStaff() ) die(Error(12));

// INITIALISE

$bCmdok = false;
$strMails = '';
$a = ''; // mandatory action
$s = -1; // section id (or user id)
$t = -1; // topic
$p = -1; // post
$v = ''; // value
$v1 = ''; // value
$v2 = ''; // value
$src = ''; // value
$ids = ''; // list of comma separated id (to be converted to array)
$ok = ''; // submitted
QThttpvar('a s t p v v1 v2 src ids ok','str int int int str str str str str str');

$oVIP->selfurl = 'qti_adm_users_edit.php';
$oVIP->selfname = 'QuickTicket command';
$oVIP->exitname = L('Exit');

// --------
// EXECUTE COMMAND
// --------

switch($a)
{

// --------------
case 'usersdel':
// --------------

$oVIP->selfname = L('Delete').' '.strtolower($L['User']);
$oVIP->exiturl = 'qti_adm_users.php';
$oVIP->exitname = L('Exit');

// Ask confirmation

if ( empty($ok) )
{
  $arrId = GetCheckedIds('t1-cb',($ids==='' ? false : $ids));
  if ( $oVIP->exiturl==='qti_adm_users.php' ) $oVIP->exiturl .= '?cb='.implode(',',$arrId);
  if ( in_array(0,$arrId) || in_array(1,$arrId) ) die('User 0 and 1 cannot be deleted');

  $arrUsers = GetUsersInfos(array_slice($arrId,0,5),array('name','role','photo'));
  if ( count($arrUsers)==5 ) { array_pop($arrUsers); $last='...'; } else { $last=''; }
  $strUsers = '';
  foreach($arrUsers as $id=>$row)
  {
  $photo = empty($row['photo']) ? AsImgPopup('usr_'.$row['id'],$_SESSION[QT]['skin_dir'].'/user.gif','&rect;') : AsImgPopup('usr_'.$row['id'],$qti_root.QTI_DIR_PIC.$row['photo'],'&rect;');
  $strUsers .= $photo.' '.$row['name'].' ('.L('Role_'.$row['role']).')<br/>';
  }
  $strUsers .= $last; 

  $oHtml->PageMsgAdm
  (
  NULL,
  '<form method="post" action="'.Href().'">
  <table class="t-data horiz">
  <tr>
  <th>'.$L['Users'].'</th>
  <td>'.$strUsers.'</td>
  </tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input type="submit" name="ok" value="'.L('Delete').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;
  <input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '500px'
  );
  exit;
}

// Process

if ( !isset($ids) || $ids==='' ) die('Nothing selected');
$arrId = explode(',',$ids);
$arrId = array_map('intval', $arrId);
if ( in_array(0,$arrId) || in_array(1,$arrId) ) die('User 0 and 1 cannot be deleted');
// Search photo
$arr = GetUsersInfo($arrId,'photo');
// Delete (photo and user)
foreach($arr as $id=>$photo) cVIP::Unregister( array('id'=>$id,'photo'=>$photo) );

// End message

$_SESSION['pagedialog'] = 'O|'.L('S_delete').'|'.count($arrId);
$oHtml->Redirect($oVIP->exiturl);

break;

// --------------
case 'usersrole':
// --------------

$oVIP->selfname = L('Change_role');
$oVIP->exiturl = $src==='adm' ? 'qti_adm_users.php' : 'qti_user.php?id='.$ids;
$oVIP->exitname = L('Exit');

if ( $v2!=='A' && $v2!=='M' ) $v2='U'; // current status (can come from profile

// Ask confirmation

if ( empty($ok) )
{
  $arrId = GetCheckedIds('t1-cb',$ids);
  if ( $oVIP->exiturl==='qti_adm_users.php' ) $oVIP->exiturl .= '?cb='.implode(',',$arrId);
  if ( in_array(0,$arrId) || in_array(1,$arrId) ) die('User 0 and 1 cannot be deleted');

  $arrUsers = GetUsersInfos(array_slice($arrId,0,5),array('name','role','photo'));
  if ( count($arrUsers)==5 ) { array_pop($arrUsers); $last='...'; } else { $last=''; }

  $strUsers = '';
  foreach($arrUsers as $id=>$row)
  {
  if ( isset($row['name'][24]) ) $row['name']=QTtrunc($row['name'],24);
  $photo = empty($row['photo']) ? AsImgPopup('usr_'.$row['id'],$_SESSION[QT]['skin_dir'].'/user.gif','&rect;') : AsImgPopup('usr_'.$row['id'],$qti_root.QTI_DIR_PIC.$row['photo'],'&rect;');
  $strUsers .= $photo.' '.$row['name'].' ('.L('Role_'.$row['role']).')<br/>';
  }
  $strUsers .= $last; 
  
  $oHtml->PageMsgAdm
  (
  NULL,
  '<form method="post" action="'.Href().'">
  <table class="t-data horiz">
  <tr><th>'.$L['Role'].'</th><td><select name="v" size="1"><option value="U"'.($v2==='U' ? QSEL : '').'>'.$L['Role_U'].'</option><option value="M"'.($v2==='M' ? QSEL : '').'>'.$L['Role_M'].'</option><option value="A"'.($v2==='A' ? QSEL : '').(sUser::Role()!=='A' ? QDIS :'').'>'.$L['Role_A'].'</option></select></td>
  <tr><th>'.$L['Users'].'</th><td>'.$strUsers.'</td></tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input type="submit" name="ok" value="'.L('Save').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;
  <input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '500px'
  );
  exit;
}

if ( !isset($ids) || $ids==='' ) die('Nothing selected');
$arrId = explode(',',$ids);
$arrId = array_map('intval', $arrId);
if ( in_array(0,$arrId) || in_array(1,$arrId) ) die('User 0 and 1 cannot be deleted');
// status (except admin and visitor)
$oDB->Exec('UPDATE '.TABUSER.' SET role="'.strtoupper(substr($v,0,1)).'" WHERE id IN ('.implode(',',$arrId).')' );
// change section coordinator if required
if ( $v=='U' ) $oDB->Exec('UPDATE '.TABSECTION.' SET moderator=1,moderatorname="Admin" WHERE moderator IN ('.implode(',',$arrId).')');

// End message
$_SESSION['pagedialog'] = 'O|'.L('S_update').'|'.count($arrId);
$oHtml->Redirect($oVIP->exiturl);

break;

// --------------
case 'usersban':
// --------------

$oVIP->selfname = $L['Ban_user'];
$oVIP->exiturl = 'qti_adm_users.php';
$oVIP->exitname = L('Exit');

// ask confirmation
if ( empty($ok) )
{
  $arrId = GetCheckedIds();
  if ( $oVIP->exiturl==='qti_adm_users.php' ) $oVIP->exiturl .= '?cb='.implode(',',$arrId);
  if ( in_array(0,$arrId) || in_array(1,$arrId) ) die('User 0 and 1 cannot be deleted');

  $arrUsers = GetUsersInfos(array_slice($arrId,0,5),array('name','role','photo'));
  if ( count($arrUsers)==5 ) { array_pop($arrUsers); $last='...'; } else { $last=''; }

  $strUsers = '';
  foreach($arrUsers as $id=>$row)
  {
  $photo = empty($row['photo']) ? AsImgPopup('usr_'.$row['id'],$_SESSION[QT]['skin_dir'].'/user.gif','&rect;') : AsImgPopup('usr_'.$row['id'],$qti_root.QTI_DIR_PIC.$row['photo'],'&rect;');
  $strUsers .= $photo.' '.$row['name'].' ('.L('Role_'.$row['role']).')<br/>';
  }
  $strUsers .= $last; 

  $oHtml->PageMsgAdm
  (
  NULL,
  '<form method="post" action="'.Href().'">
  <table class="t-data horiz">
  <tr><th>'.L('Ban').'</th><td><select name="t" size="1">
  <option value="0"'.($v2==='0' ? QSEL : '').'>'.L('N').'</option>
  <option value="1"'.($v2==='1' ? QSEL : '').'>1 '.$L['Day'].'</option>
  <option value="2"'.($v2==='2' ? QSEL : '').'>10 '.$L['Days'].'</option>
  <option value="3"'.($v2==='3' ? QSEL : '').'>20 '.$L['Days'].'</option>
  <option value="4"'.($v2==='4' ? QSEL : '').'>30 '.$L['Days'].'</option>
  </select></td></tr>
  <tr><th>'.L('Users').'</th><td>'.$strUsers.'</td></tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input type="submit" name="ok" value="'.L('Change').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;
  <input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>  
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '500px'
  );
  exit;
}

if ( empty($ids) ) die('Nothing selected');
$arr = explode(',',$ids);
$arr = array_map('intval', $arr);
if ( empty($arr) ) die('Nothing selected');

if ( in_array(0,$arr) || in_array(1,$ids) ) die('User 0 and 1 cannot be updated');
// ban user
if ( $t<0 ) die('Wrong parameters: delay');
$oDB->Exec('UPDATE '.TABUSER.' SET closed="'.$t.'" WHERE id IN ('.implode(',',$arr).')' );

// End message
$_SESSION['pagedialog'] = 'O|'.L('S_update').'|'.count($arr);
$oHtml->Redirect($oVIP->exiturl);
break;

// --------------
default:
// --------------

echo 'Unknown action';
break;

// --------------
}