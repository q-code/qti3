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

$oVIP->selfurl = 'qti_change.php';
$oVIP->selfname = 'QuickTicket command';
$oVIP->exitname = L('Exit');

// --------
// EXECUTE COMMAND
// --------

switch($a)
{

// --------------
case 'dropattach':
// --------------

  if ( !sUser::CanView('V6') ) { $oHtml->PageMsg(11); return; }

  if ( $p>=0 )
  {
    $oVIP->exiturl = "qti_item.php?t=$t#$p";
    $oVIP->exitname = '&laquo; '.$L['Message'];
    $oPost = new cPost($p);
    $oPost->Dropattach();
  }

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'pwdreset':
// --------------

  if ( !sUser::IsStaff() ) die(Error(12));

  if ( $s<0 ) die('Wrong id '.$s);
  if ( $s==1 && sUser::Id()!=1 ) die('First Admin password can be changed by himself only...');
  include 'bin/class/qt_class_smtp.php';
  include Translate(APP.'_reg.php');

  $oVIP->selfname = L('Reset_pwd');
  $oVIP->exiturl = 'qti_user.php?id='.$s;
  $oVIP->exitname = L('Exit');

  $oDB->Query('SELECT name,mail,children,parentmail,photo FROM '.TABUSER.' WHERE id='.$s);
  $row = $oDB->Getrow();
  $strUserImage = AsUserImg( (empty($row['photo']) ? '' : QTI_DIR_PIC.$row['photo']) ); // title is in caption

  // ask delay
  if ( empty($ok) )
  {
    $oHtml->PageMsgAdm
    (
    NULL,
    '<form method="get" action="'.Href().'">
    <table class="hidden">
    <tr>
    <td>'.AsImgBox($strUserImage,QTtrunc($row['name'],20)).'</td>
    <td class="right">
    <p>'.$L['Reset_pwd_help'].'</p>
    <p>
    <input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="s" value="'.$s.'" />
    <input type="submit" name="ok" value="'.QTstrh($oVIP->selfname).' !" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
    </p>
    <p><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>
    </td>
    </tr>
    </table>
    </form>',
    0,
    '550px'
    );
    exit;
  }

  // reset user
  $strNewpwd = 'qt'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
  $oDB->Exec('UPDATE '.TABUSER.' SET pwd="'.sha1($strNewpwd).'" WHERE id='.$s);

  // send email
  $strSubject = $_SESSION[QT]['site_name'].' - New password';
  $strMessage = "Here are your login and password\nLogin: %s\nPassword: %s";
  $strFile = GetLang().'mail_pwd.php';
  if ( file_exists($strFile) ) include $strFile;
  $strMessage = sprintf($strMessage,$row['name'],$strNewpwd);
  QTmail($row['mail'],$strSubject,$strMessage,QT_HTML_CHAR);
  $strEndmessage = str_replace("\n",'<br />',$strMessage);

  // exit
  $oHtml->PageMsgAdm(NULL,'<p>'.$L['S_update'].'</p><p>'.$strEndmessage.'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',0,'500px');
  exit;
  break;

// --------------
case 'topicstatus':
// --------------

  if ( !sUser::IsStaff() ) die(Error(12));
  if ( !sUser::CanView('V6') ) die(Error(11));

  $oVIP->selfname = $L['Change'].' '.$L['Status'];
  $oVIP->exiturl = "qti_item.php?t=$t";

  // ASK STATUS IF MISSING: When value "*" repost with method GET

  if ( $v==='*' )
  {
    $oVIP->selfname = $L['Change'].' '.$L['Status'];
    $arrS = sMem::Get('sys_statuses');
    $oHtml->PageMsg
    (
      NULL,
      '<form method="get" action="'.$oVIP->selfurl.'">
      <p><input type="hidden" name="a" value="'.$a.'" />
      <input type="hidden" name="s" value="'.$s.'" />
      <input type="hidden" name="t" value="'.$t.'" />
      <select name="v" size="1" style="width:200px">'.QTasTag($arrS).'</select></p>
      <p><input type="submit" name="ok" value="'.$L['Ok'].'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.$L['Cancel'].'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
      </form>
      <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>'
    );
    exit;
  }

  // CHANGE STATUS

  $oTopic = new cTopic($t);
  $oTopic->SetStatus($v,true,$oTopic->firstpostid); // this also updates the section stats in case of closed topics
  if ( $v=='Z' )
  {
  $oVIP->exitname = '&laquo; '.$L['Section'];
  $oVIP->exiturl = "qti_items.php?s=$s";
  $voidSEC = new cSection(); $voidSEC->uid=$s; $voidSEC->UpdateStats();
  }

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topictype':
// --------------

  if ( !sUser::IsStaff() ) die(Error(12));

  $oVIP->selfname = $L['Change'].' '.$L['Type'];
  $oVIP->exiturl  = 'qti_item.php?t='.$t;

  // ASK TYPE IF MISSING: When value "*" repost with method GET
  if ( $v==='*' )
  {
    $oVIP->selfname = $L['Change'].' '.$L['Type'];
    $oHtml->PageMsg
    (
      NULL,
      '<form method="get" action="'.$oVIP->selfurl.'">
      <p><input type="hidden" name="a" value="'.$a.'" />
      <input type="hidden" name="s" value="'.$s.'" />
      <input type="hidden" name="t" value="'.$t.'" />
      <select name="v" size="1">'.QTasTag(cTopic::Types()).'</select></p>
      <p><input type="submit" name="ok" value="'.$L['Ok'].'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.$L['Cancel'].'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
      </form>
      <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>'
    );
    exit;
  }

  // CHANGE TYPE

  cTopic::SetType($s,$t,$v);
  if ( $v=='I' ) $oHtml->Redirect('qti_change.php?a=topicparam&amp;s='.$s.'&amp;t='.$t);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicactor':
// --------------

  if ( !sUser::IsStaff() ) die(Error(12));

  $oVIP->selfname = L('Change').' '.L('Actor');
  $oVIP->exiturl  = 'qti_item.php?t='.$t;
  $intOldactor    = -1; if ( isset($_GET['old']) ) $intOldactor=$_GET['old'];

  $oTopic = new cTopic($t);

  // ASK ACTOR IF MISSING: When value "*" repost with method GET
  if ( $v==='*' )
  {
    $arrAdmUsers = GetUsers('A');
    asort($arrAdmUsers);
    $strAdmUsers = QTasTag($arrAdmUsers,$intOldactor,array('current'=>$intOldactor,'classC'=>'bold'));
    $arrModUsers = GetUsers('M-');
    asort($arrModUsers);
    $strModUsers = QTasTag($arrModUsers,$intOldactor,array('current'=>$intOldactor,'classC'=>'bold'));
    $oVIP->selfname = L('Change').' '.L('Actor');
    $oHtml->PageMsg
    (
      NULL,
      '<table class="hidden">
      <tr>
      <td>
      <p>'.$L['Role_A'].'</p>
      <form method="get" action="'.$oVIP->selfurl.'">
      <p><input type="hidden" name="a" value="'.$a.'" />
      <input type="hidden" name="s" value="'.$s.'" />
      <input type="hidden" name="t" value="'.$t.'" />
      <select name="v" size="12" style="width:150px">'.$strAdmUsers.'</select><br /><br />
      <input type="submit" name="ok" value="'.$L['Ok'].'" /></p>
      </form>
      </td>
      <td>
      <p>'.$L['Role_M'].'</p>
      <form method="get" action="'.$oVIP->selfurl.'">
      <p><input type="hidden" name="a" value="'.$a.'" />
      <input type="hidden" name="s" value="'.$s.'" />
      <input type="hidden" name="t" value="'.$t.'" />
      <select name="v" size="12" style="width:150px">'.$strModUsers.'</select><br /><br />
      <input type="submit" name="ok" value="'.$L['Ok'].'" /></p>
      </form>
      </td>
      </tr>
      </table>
      <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
      0,
      '500px'
    );
    exit;
  }
  $v = intval($v);

  // CHANGE ACTOR
  $oTopic->SetActor($v);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicdelete':
// --------------

  if ( !sUser::IsStaff() ) die(Error(12));
  if ( $t<0 ) die('Wrong parameters: missing topic id');

  $oVIP->selfname = L('Delete').' '.L('item');
  $oVIP->exiturl = 'qti_items.php?s='.$s;

  // ask confirmation
  if ( empty($ok) )
  {
    $oVIP->exiturl = 'qti_item.php?t='.$t;
    $oTopic = new cTopic($t);
    if ( $oTopic->items==0 ) { $str=$L['None']; } else { $str=$oTopic->items.' <span class="small">('.$L['Last_message'].' '.QTdatestr($oTopic->lastpostdate).')</span>'; }

    $oHtml->PageMsg
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th>'.$L['Title'].'</th>
    <td>'.$oTopic->GetTopicTitle().'</td>
    </tr>
    <tr>
    <th>'.$L['Author'].'</th>
    <td>'.$oTopic->firstpostname.' <span class="small">('.QTdatestr($oTopic->firstpostdate).')</span></td>
    </tr>
    <tr>
    <th>'.$L['Replys'].'</th>
    <td>'.$str.'</td>
    </tr>
    </table>
    <p class="submit"><input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="s" value="'.$s.'"/><input type="hidden" name="t" value="'.$t.'"/><input type="submit" name="ok" value="'.L('Delete').'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
    <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>
    </form>',
    0,
    '500px'
    );
    exit;
  }

  // delete topic
  cTopic::Drop($t);

  // update section stats
  $voidSEC = new cSection(); $voidSEC->uid=$s; $voidSEC->UpdateStats(array(),true,false);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicmove':
// --------------

  if ( !sUser::IsStaff() ) die(Error(12));
  if ( $t<0 ) die('Wrong parameters: missing topic id');

  $oVIP->selfname = L('Move').' '.L('item');
  $oVIP->exiturl = 'qti_items.php?s='.$s;

  // ask confirmation
  if ( empty($ok) || $p<0 )
  {
    $oVIP->exiturl = 'qti_item.php?t='.$t;
    $oTopic = new cTopic($t);
    if ( $oTopic->items==0 ) { $str=$L['None']; } else { $str=$oTopic->items.' <span class="small">('.$L['Last_message'].' '.QTdatestr($oTopic->lastpostdate).')</span>'; }

    $oHtml->PageMsg
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th>'.$L['Title'].'</th>
    <td>'.$oTopic->GetTopicTitle().'</td>
    </tr>
    <tr>
    <th>'.$L['Author'].'</th>
    <td>'.$oTopic->firstpostname.' <span class="small">('.QTdatestr($oTopic->firstpostdate).')</span></td>
    </tr>
    <tr>
    <th>'.$L['Replys'].'</th>
    <td>'.$str.'</td>
    </tr>
    <tr>
    <th>'.$L['Move_to'].'</th>
    <td><select name="p" size="1">'.Sectionlist(-1,$s).'</select></td>
    </tr>
    <tr class="tr">
    <th>'.$L['Ref'].'</th>
    <td><select name="v" size="1">
    <option value="1">'.$L['Move_keep'].'</option>
    <option value="0">'.$L['Move_reset'].'</option>
    <option value="2">'.$L['Move_follow'].'</option>
    </select></td>
    </tr>
    </table>
    <p class="submit"><input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="s" value="'.$s.'"/><input type="hidden" name="t" value="'.$t.'"/><input type="submit" name="ok" value="'.$L['Ok'].'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
    <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>
    </form>',
    0,
    '500px'
    );
    exit;
  }

  // move topic
  if ( $s<0 ) die('Wrong parameters forum id');
  if ( $t<0 ) die('Wrong parameters id');
  if ( $p<0 ) die('Wrong parameters dest');
  if ( $v<0 ) die('Wrong parameters ref');
  $oSEC = new cSection($s);
  $oSEC->MoveTopics($p,$v,$t);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicparam':
// --------------

  if ( !sUser::IsStaff() ) die(Error(12));
  if ( $t<0 ) die('Wrong parameters: missing topic id');
  $v1 = substr($v1,1); // because value must be coded a1,... in the array of the form

  $oVIP->selfname = L('Inspection').' '.L('Parameters');
  $oVIP->exiturl = 'qti_item.php?t='.$t;

  $oTopic = new cTopic($t);
  if ( $oTopic->type!='I' ) { $oHtml->PageMsg( NULL, '<p>Specific parameters cannot be confirgured.</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>'); exit; }

  $arr = $oTopic->ReadOptions();

  // ask confirmation

  if ( empty($ok) )
  {
    if ( !isset($arr['Itype']) ) $arr['Itype'] = 'A';
    if ( !isset($arr['Ilevel']) ) $arr['Ilevel'] = '3' ;
    if ( !isset($arr['Istat']) ) $arr['Istat'] = 'mean';
    if ( $arr['Itype']=='0' ) $arr['Itype']='Z'; // for backwark compatibility
    if ( $arr['Itype']=='1' ) $arr['Itype']='A'; // for backwark compatibility
    if ( $arr['Itype']!='A' && $arr['Itype']!='Z' ) $arr['Itype']='Z'; // for backwark compatibility
    $oHtml->PageMsg
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th>'.$L['Status'].'</th>
    <td><select name="v" size="1">'.QTasTag(cTopic::Statuses('I'),$arr['Itype']).'</select></td>
    </tr>
    <tr>
    <th>'.L('I_level').'</th>
    <td><select name="v1" size="1">'.QTasTag(
    array('a2'=>'2) '.L('I_r_yes').' / '.L('I_r_no'),
          'a3'=>'3) '.L('I_r_good').' / '.L('I_r_medium').' / '.L('I_r_bad'),
          'a5'=>'5) '.L('I_r_veryhigh').' / '.L('I_r_high').' / '.L('I_r_medium').' / '.L('I_r_low').' / '.L('I_r_verylow'),
          'a100'=>'100) '.L('Percent')),
    'a'.$arr['Ilevel']).'</select></td>
    </tr>
    <tr>
    <th>'.L('I_aggregation').'</th>
    <td><select name="v2" size="1">'.QTasTag(array('mean'=>L('I_v_mean'),'min'=>L('I_v_min'),'max'=>L('I_v_max'),'first'=>L('I_v_first'),'last'=>L('I_v_last')),$arr['Istat']).'</select></td>
    </tr>
    </table>
    <p><input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="s" value="'.$s.'"/><input type="hidden" name="t" value="'.$t.'" /><input type="submit" name="ok" value="'.$L['Ok'].'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
    <p><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>
    </form>',
    0,
    '500px'
    );
    exit;
  }

  // save

  $arr = QTarradd($arr,'Itype',$v);
  $arr = QTarradd($arr,'Ilevel',$v1);
  $arr = QTarradd($arr,'Istat',$v2);
  $oTopic->options = QTimplodeIni($arr);
  $oTopic->WriteOptions();

  // activate inspection and recompute aggregation

  $oTopic->SetStatus($arr['Itype'],false);
  if ( $oTopic->items>0 )
  {
    $oTopic->z = $oTopic->InspectionAggregate();
    $oDB->Exec('UPDATE '.TABTOPIC.' SET z='.$oTopic->z.' WHERE id='.$oTopic->id );
  }

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'post':
// --------------

  $oVIP->selfname = $L['Message'];
  $oVIP->exiturl  = 'qti_item.php?t='.$t;
  $oVIP->exitname = '&laquo; '.L('Item');

  $oSEC = new cSection($s);
  $oTopic = new cTopic($t);
  $oPost = new cPost($p);

  echo $oHtml->Head();
  echo $oHtml->Body();

  echo cHtml::Page(START);

  echo '
  <div class="msgboxpreview">

  <h2>',$oVIP->selfname,'</h2>
  ';

  $oAvatar = true;
  if ( $oTopic->type==='I' && $oPost->type==='R' )
  {
  $optionstat = $oTopic->ReadOptions('Istat'); if ( empty($optionstat) ) $optionstat='mean'; // default if empty options
  $optionlevel= $oTopic->ReadOptions('Ilevel'); if ( empty($optionlevel) ) $optionlevel='3'; // default if empty options
  $oAvatar = AsImgBox( '<span class="small">('.L('I_v_'.$optionstat).')</span><br />'.ValueScalebar($oTopic->z,$optionlevel), ValueName($oTopic->z,$optionlevel), 'picboxmsg' );
  //$bAvatar = $oPost->GetScoreImage($oTopic);
  }
  $oPost->Show($oSEC,$oTopic,$oAvatar,'',$_SESSION[QT]['skin_dir']);

  echo '
  </div>
  ';
  echo '<p><a id="exiturl" href="',Href($oVIP->exiturl),'">',$oVIP->exitname,'</a></p>';

  echo cHtml::Page(END);

  echo $oHtml->End();

  exit;
  break;

// --------------
default:
// --------------

  echo 'Unknown action';
  break;

// --------------
}