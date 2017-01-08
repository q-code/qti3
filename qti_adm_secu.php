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
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die($L['E_admin']);

// INITIALISE

$_SESSION[QT]['visitor_right'] = (int)$_SESSION[QT]['visitor_right'];

$oVIP->selfurl = 'qti_adm_secu.php';
$oVIP->selfname = '<span class="upper">'.L('Settings').'</span><br/>'.L('Security');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check form

  $_SESSION[QT]['visitor_right'] = (int)$_POST['pal'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['visitor_right'].'" WHERE param="visitor_right"');
/*
  $_SESSION[QT]['login_qte']=trim($_POST['login_qte']);
  if ( empty($_SESSION[QT]['login_qte']) || strlen($_SESSION[QT]['login_qte'])<3 ) $_SESSION[QT]['login_team']='0';
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['login_qte'].'" WHERE param="login_qte"');

  $_SESSION[QT]['login_qte_web']=$_POST['login_qte_web'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['login_qte_web'].'" WHERE param="login_qte_web"');
*/
  $_SESSION[QT]['register_mode']=$_POST['regmode'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_POST['regmode'].'" WHERE param="register_mode"');

  $_SESSION[QT]['register_safe']=$_POST['regsafe'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_POST['regsafe'].'" WHERE param="register_safe"');

  $_SESSION[QT]['avatar']=$_POST['avatar'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_POST['avatar'].'" WHERE param="avatar"');

  $_SESSION[QT]['upload']=$_POST['upload'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_POST['upload'].'" WHERE param="upload"');

  $_SESSION[QT]['show_calendar'] = $_POST['show_calendar'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_calendar'].'" WHERE param="show_calendar"');

  $_SESSION[QT]['show_stats'] = $_POST['show_stats'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_stats'].'" WHERE param="show_stats"');

  $_SESSION[QT]['tags']=$_POST['tags'];
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_POST['tags'].'" WHERE param="tags"');

  if ( $_SESSION[QT]['avatar']!='0' )
  {
    if ( isset($_POST['avatarwidth']) )
    {
      $str = strip_tags(trim($_POST['avatarwidth']));
      if ( !QTisbetween($str,20,200) ) { $error = $L['Max_picture_size'].' '.L('invalid').' (20-200 pixels)'; }
      if ( empty($error) )
      {
      $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="avatar_width"');
      $_SESSION[QT]['avatar_width']=$str;
      }
    }
    if ( isset($_POST['avatarheight']) )
    {
      $str = strip_tags(trim($_POST['avatarheight']));
      if ( !QTisbetween($str,20,200) ) { $error = $L['Max_picture_size'].' '.L('invalid').' (20-200 pixels)'; }
      if ( empty($error) )
      {
      $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="avatar_height"');
      $_SESSION[QT]['avatar_height']=$str;
      }
    }
    if ( isset($_POST['avatarsize']) )
    {
      $str = strip_tags(trim($_POST['avatarsize']));
      if ( !QTisbetween($str,10,100) ) $error = $L['Max_picture_size'].' '.L('invalid').' (10-100 kb)';
      if ( empty($error) )
      {
      $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="avatar_size"');
      $_SESSION[QT]['avatar_size']=$str;
      }
    }
  }
  if ( $_SESSION[QT]['upload']!='0' )
  {
    if ( isset($_POST['uploadsize']) )
    {
      $str = strip_tags(trim($_POST['uploadsize']));
      if ( !QTisbetween($str,100,99000) ) { $error = $L['Allow_upload'].' '.L('invalid').' (100-99000 Kb)'; }
      if ( empty($error) )
      {
      $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="upload_size"');
      $_SESSION[QT]['upload_size']=$str;
      }
    }
  }

  $str = strip_tags(trim($_POST['ppt']));
  if ( !QTisbetween($str,10,999) ) $error = $L['Max_replies_per_items'].' '.L('invalid').' (10-999)';
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="posts_per_item"');
    $_SESSION[QT]['posts_per_item']=$str;
  }
  $str = strip_tags(trim($_POST['cpp']));
  if ( !QTisbetween($str,1,32) ) $error = $L['Max_char_per_post'].' '.L('invalid').' (1-32)';
  if ( $oDB->type=='oci' && !QTisbetween($str,1,4) ) $error = $L['Max_char_per_post'].' '.L('invalid').' (1-4)';
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'000" WHERE param="chars_per_post"');
    $_SESSION[QT]['chars_per_post']=intval($str)*1000;
  }
  $str = strip_tags(trim($_POST['lpp']));
  if ( !QTisbetween($str,10,999) ) $error = $L['Max_line_per_post'].' '.L('invalid').' (10-999)';
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="lines_per_post"');
    $_SESSION[QT]['lines_per_post']=$str;
  }
  $str = strip_tags(trim($_POST['delay']));
  if ( !QTisbetween($str,1,99) ) $error = $L['Posts_delay'].' '.L('invalid').' (1-99)';
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="posts_delay"');
    $_SESSION[QT]['posts_delay']=$str;
  }
  $str = strip_tags(trim($_POST['ppd']));
  if ( !QTisbetween($str,1,999) ) $error = $L['Max_post_per_user'].' '.L('invalid').' (1-999)';
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$str.'" WHERE param="posts_per_day"');
    $_SESSION[QT]['posts_per_day']=$str;
  }

  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';

// FORM

echo '
<script type="text/javascript">
function avatardisabled(str) {
  ctrl1 = document.getElementById("avatarwidth");
  ctrl2 = document.getElementById("avatarheight");
  ctrl3 = document.getElementById("avatarsize");
  if (str=="0")
  {
  ctrl1.disabled=true;
  ctrl2.disabled=true;
  ctrl3.disabled=true;
  }
  else
  {
  ctrl1.disabled=false; if (ctrl1.value.length==0) { ctrl1.value="100"; }
  ctrl2.disabled=false; if (ctrl2.value.length==0) { ctrl2.value="100"; }
  ctrl3.disabled=false; if (ctrl3.value.length==0) { ctrl3.value="12"; }
  }
  return null;
}
function uploaddisabled(str) {
  ctrl1 = document.getElementById("uploadsize");
  if (str=="0")
  {
  ctrl1.disabled=true;
  }
  else
  {
  ctrl1.disabled=false; if (ctrl1.value.length==0) { ctrl1.value="500"; }
  }
  return null;
}
</script>
';

echo '
<form method="post" action="',$oVIP->selfurl,'">
<h2 class="subtitle">',$L['Public_access_level'],'</h2>
<table class="t-data horiz">
<tr title="',$L['H_Visitors_can'],'">
<th><label for="pal">',$L['Visitors_can'],'</label></th>
<td>
<select id="pal" name="pal" onchange="bEdited=true;">',QTasTag($L['Pal'],$_SESSION[QT]['visitor_right']),'</select></td>
</tr>
</table>
';

if ( !isset($_SESSION[QT]['login_addon']) ) $_SESSION[QT]['login_addon']='0';
$str = 'Internal authority (default)';
$arrLoginAddOn=array('0'=>$str);
$arr = GetSettings('param LIKE "m_%:login"');
foreach($arr as $param=>$name)
{
  $sPrefix = str_replace(':login','',$param);
  if ( isset($_SESSION[QT][$sPrefix]) && $_SESSION[QT][$sPrefix]!=='0' ) $arrLoginAddOn[$sPrefix] = 'Module '.$name;
}
if ( count($arrLoginAddOn)>1 ) $str = '<select id="login_addon" name="login_addon" onchange="bEdited=true;">'.QTasTag($arrLoginAddOn,$_SESSION[QT]['login_addon']).'</select>';

echo '<h2 class="subtitle">',$L['Registration'],'</h2>
<table class="t-data horiz">
<tr>
<th>',L('Authority'),'</th>
<td>',$str,'</td>
</tr>
<tr title="',$L['Reg_mode'],'">
<th><label for="regmode">',$L['Reg_mode'],'</label></th>
<td>
<select id="regmode" name="regmode" onchange="bEdited=true;">
',QTasTag(array('direct'=>L('Reg_direct'),'email'=>L('Reg_email'),'backoffice'=>L('Reg_backoffice')),$_SESSION[QT]['register_mode']),'
</select>
</tr>
';
echo '<tr title="',$L['H_Reg_security'],'">
<th><label for="regsafe">',$L['Reg_security'],'</label></th>
<td>
<select id="regsafe" name="regsafe" onchange="bEdited=true;">
<option value="none"',($_SESSION[QT]['register_safe']=='none' ? QSEL : ''),'>',$L['None'],'</option>
<option value="text"',($_SESSION[QT]['register_safe']=='text' ? QSEL : ''),'>',$L['Text_code'],'</option>
<option value="image"',($_SESSION[QT]['register_safe']=='image' ? QSEL : ''),'>',$L['Image_code'],'</option>
</select>
</tr>
</table>
';

echo '<h2 class="subtitle">',L('Security_rules'),'</h2>
<table class="t-data horiz">
<tr title="',L('H_Posts_delay'),'">
<th><label for="delay">',L('Posts_delay'),'</label></th>
<td><input required type="text" id="delay" name="delay" size="2" maxlength="2" pattern="[1-9][0-9]{0,1}" value="',$_SESSION[QT]['posts_delay'],'" onchange="bEdited=true;"/> '.L('seconds').'</td>
</tr>
';
echo '<tr title="',L('H_Max_replies_per_items'),'">
<th><label for="ppt">',L('Max_replies_per_items'),'</label></th>
<td><input required type="text" id="ppt" name="ppt" size="3" maxlength="3" pattern="[1-9][0-9]{1,2}" value="',$_SESSION[QT]['posts_per_item'],'" onchange="bEdited=true;"/> / ',L('Item'),'</td>
</tr>
';
echo '<tr title="',L('H_hacking_day'),'">
<th><label for="ppd">',L('Max_post_per_user'),'</label></th>
<td><input required type="text" id="ppd" name="ppd" size="3" maxlength="3" pattern="[1-9][0-9]{1,2}" value="',$_SESSION[QT]['posts_per_day'],'" onchange="bEdited=true;"/> / '.L('day').'</td>
</tr>
';
echo '<tr title="',L('H_Max_char_per_post'),'">
<th><label for="cpp">',L('Max_char_per_post'),'</label></th>
<td><input required type="text" id="cpp" name="cpp" size="2" maxlength="2" pattern="[1-9][0-9]{0,1}" value="',($_SESSION[QT]['chars_per_post']/1000),'" onchange="bEdited=true;"/> x 1000</td>
</tr>
';
echo '<tr title="',L('H_Max_line_per_post'),'">
<th><label for="lpp">',L('Max_line_per_post'),'</label></th>
<td><input required type="text" id="lpp" name="lpp" size="3" maxlength="3" pattern="[1-9][0-9]{1,2}" value="',$_SESSION[QT]['lines_per_post'],'" onchange="bEdited=true;"/></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',$L['User_interface'],'</h2>
<table class="t-data horiz">
<tr title="',$L['H_Allow_picture'],'">
<th><label for="avatar">',$L['Allow_picture'],'</label></th>
<td><select id="avatar" name="avatar" onchange="avatardisabled(this.value); bEdited=true;">
<option value="0"',($_SESSION[QT]['avatar']=='0' ? QSEL : ''),'>',L('N'),'</option>
<option value="jpg,jpeg"',($_SESSION[QT]['avatar']=='jpg,jpeg' ? QSEL : ''),'>',L('Y'),' (',$L['Jpg_only'],')</option>
<option value="gif,jpg,jpeg,png"'.($_SESSION[QT]['avatar']=='gif,jpg,jpeg,png' ? QSEL : '').'>',L('Y'),' (',$L['Gif_jpg_png'],')</option>
</select> ',$L['Maximum'],' <input required type="text" id="avatarwidth" name="avatarwidth" size="3" maxlength="3" pattern="[1-9][0-9]{1,2}" value="',$_SESSION[QT]['avatar_width'],'"'.($_SESSION[QT]['avatar']=='0' ? QDIS : '').' onchange="bEdited=true;"/> x <input required type="text" id="avatarheight" name="avatarheight" size="3" maxlength="3" pattern="[1-9][0-9]{1,2}" value="',$_SESSION[QT]['avatar_height'],'"'.($_SESSION[QT]['avatar']=='0' ? QDIS : '').' onchange="bEdited=true;"/> pixels, <input required type="text" id="avatarsize" name="avatarsize" size="3" maxlength="3" pattern="[1-9][0-9]{1,2}" value="',$_SESSION[QT]['avatar_size'],'"'.($_SESSION[QT]['avatar']=='0' ? QDIS : '').' onchange="bEdited=true;"/>Kb</td>
</tr>
';
$arr = array(
  'M'=>L('Y').' ('.$L['Role_M'].')',
  'U'=>L('Y').' ('.$L['Role_U'].')',
  'V'=>L('Y').' ('.$L['Role_V'].')');
echo '<tr title="',$L['H_Allow_upload'],'">
<th><label for="upload">',$L['Allow_upload'],'</label></th>
<td>
<select id="upload" name="upload" onchange="uploaddisabled(this.value); bEdited=true;">
',QTasTag($arr,$_SESSION[QT]['upload']),'
</select> ',$L['Maximum'],' <input type="text" id="uploadsize" name="uploadsize" size="5" maxlength="5" pattern="[1-9][0-9]{1,4}" value="',$_SESSION[QT]['upload_size'],'"',($_SESSION[QT]['upload']=='0' ? QDIS : ''),' onchange="bEdited=true;"/>Kb</td>
</tr>
';
echo '<tr title="',$L['H_Show_calendar'],'">
<th><label for="show_calendar">',$L['Show_calendar'],'</label></th>
<td>
<select id="show_calendar" name="show_calendar" onchange="bEdited=true;">',QTasTag($arr,$_SESSION[QT]['show_calendar']),'</select>
</td>
</tr>
';
echo '<tr title="',$L['H_Show_statistics'],'">
<th><label for="show_stats">',$L['Show_statistics'],'</label></th>
<td>
<select id="show_stats" name="show_stats" onchange="bEdited=true;">',QTasTag($arr,$_SESSION[QT]['show_stats']),'</select>
</td>
</tr>
';
$arr = array(
  '0'=>L('N'),
  'M'=>L('Y').' ('.L('Role_M').')',
  'U'=>L('Y').' ('.L('Member_edit_own_items').')',
  'U+'=>L('Y').' ('.L('Member_edit_any_items').')',
  'V'=>L('Y').' ('.L('Role_V').')' );
echo '<tr>
<th><label for="tags">',$L['Allow_tags'],'</label></th>
<td><select id="tags" name="tags">
',QTasTag($arr,$_SESSION[QT]['tags']),'
</select></td>
</tr>
<tr>
<td class="blanko" colspan="2"><i class="fa fa-exclamation-triangle fa-2x fa-pull-left" style="color:#888888"></i>',$L['H_Allow_tags'],'</td>
</tr>
</table>
';
echo '<p style="margin:0 0 5px 0;text-align:center"><input type="submit" name="ok" value="',L('Save'),'"/></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';