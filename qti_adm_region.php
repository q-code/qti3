<?php

/**
* PHP version 5
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
include 'bin/qti_lang.php'; $arrLangDir = QTarrget($arrLang,2); // this creates an array with only the [iso]directories
include Translate(APP.'_adm.php');
include Translate(APP.'_zone.php');

if ( sUser::Role()!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qti_adm_region.php';
$oVIP->selfname = '<span class="upper">'.L('Settings').'</span><br/>'.$L['Adm_region'];
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $_SESSION[QT]['time_zone'] = substr($_POST['timezone'],3);
  $_SESSION[QT]['show_time_zone'] = $_POST['showtimezone'];
  $_SESSION[QT]['userlang'] = $_POST['userlang'];
  $_SESSION[QT]['language'] = (isset($arrLangDir[$_POST['dfltlang']]) ? $arrLangDir[$_POST['dfltlang']] : 'english');

  // change language
  include Translate(APP.'_main.php');
  include Translate(APP.'_adm.php');
  include Translate(APP.'_zone.php');
  $oVIP->selfname = $L['Adm_region'];
  $oVIP->exitname = $oVIP->selfname;

  // save
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['time_zone'].'" WHERE param="time_zone"');
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_time_zone'].'" WHERE param="show_time_zone"');
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['userlang'].'",loaded="1" WHERE param="userlang"');
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.(isset($arrLangDir[$_POST['dfltlang']]) ? $arrLangDir[$_POST['dfltlang']] : 'english').'" WHERE param="language"');

  // formatdate
  $str = trim($_POST['formatdate']); if ( get_magic_quotes_gpc() ) $str = stripslashes($_POST['formatdate']);
  if ( $str=='' ) $error = L('invalid').' '.$L['Date_format'];
  if ( empty($error) )
  {
  $_SESSION[QT]['formatdate'] = $str;
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['formatdate'].'" WHERE param="formatdate"');
  }

  // formattime
  $str = trim($_POST['formattime']); if ( get_magic_quotes_gpc() ) $str = stripslashes($_POST['formattime']);
  if ( $str=='' ) $error = L('invalid').' '.$L['Time_format'];
  if ( empty($error) )
  {
  $_SESSION[QT]['formattime'] = $str;
  $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['formattime'].'" WHERE param="formattime"');
  }

  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';

// Current language

$strCurrent = 'en';
  $arr = GetSettings('param="language"');
  $str = $arr['language'];
  $arr = array_flip($arrLangDir);
  if ( isset($arr[$str]) ) $strCurrent = $arr[$str];

// Check language subdirectories

$arrFiles = array();
foreach($arrLang as $strIso=>$arr)
{
  if ( is_dir('language/'.$arr[2]) ) $arrFiles[$strIso] = ucfirst($arr[1]);
}
asort($arrFiles);

// FORM

echo '
<form method="post" action="',$oVIP->selfurl,'">
<h2 class="subtitle">',$L['Language'],'</h2>
<table class="t-data horiz">
';
echo '<tr>
<th><label for="dfltlang">',$L['Dflt_language'],'</label></th>
<td style="width:175px;"><select id="dfltlang" name="dfltlang" onchange="bEdited=true;">',QTasTag($arrFiles,$strCurrent),'</select></td>
<td><span class="help">&nbsp;</span></td>
</tr>
';
echo '<tr>
<th><label for="userlang">',$L['User_language'],'</label></th>
<td style="width:175px;"><select id="userlang" name="userlang" onchange="bEdited=true;">',QTasTag(array(L('N'),L('Y')),(int)$_SESSION[QT]['userlang']),'</select></td>
<td><span class="help">',$L['H_User_language'],'</span></td>
</tr>
</table>
';

echo '<h2 class="subtitle">',$L['Date_time'],'</h2>
<table class="t-data horiz">
';
if ( PHP_VERSION_ID>=50200 )
{
echo '<tr>
<th>',$L['Time'],' (system)</th>
<td style="width:175px;">',date('H:i'),' <span class="small">(gmt ',gmdate('H:i'),')</span></td>
<td><span class="help"><a href="qti_adm_time.php">',$L['Change_time'],'...</a></span></td>
</tr>
';
}
echo '<tr>
<th><label for="formatdate">',$L['Date_format'],'</label></th>
<td style="width:175px;"><input required type="text" id="formatdate" name="formatdate" size="10" maxlength="24" value="',QTstrh($_SESSION[QT]['formatdate']),'" onchange="bEdited=true;"/></td>
<td><span class="help">',$L['H_Date_format'],'</span></td>
</tr>
';
echo '<tr>
<th><label for="formattime">',$L['Time_format'],'</label></th>
<td><input required type="text" id="formattime" name="formattime" size="10" maxlength="24" value="',QTstrh($_SESSION[QT]['formattime']),'" onchange="bEdited=true;"/></td>
<td><span class="help">',$L['H_Time_format'],'</span></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',$L['Clock'],'</h2>
<table class="t-data horiz">
<tr>
<th><label for="timezone">',$L['Clock_setting'],'</label></th>
<td><select id="timezone" name="timezone" onchange="bEdited=true;">',QTasTag($L['tz'],'gmt'.$_SESSION[QT]['time_zone']),'</select></td>
<td><span class="help">&nbsp;</span></td>
</tr>
<tr>
<th><label for="showtimezone">',$L['Show_time_zone'],'</label></th>
<td style="width:175px;"><select id="showtimezone" name="showtimezone" onchange="bEdited=true;">',QTasTag(array(L('N'),L('Y')),(int)$_SESSION[QT]['show_time_zone']),'</select></td>
<td><span class="help">',$L['H_Show_time_zone'],'</span></td>
</tr>
</table>
';
echo '<p style="margin:0 0 5px 0;text-align:center"><input type="submit" name="ok" value="',L('Save'),'"/></p>
</form>
';

echo '<h1>',$L['Format_preview'],'</h1>';
echo '<p><i class="fa fa-calendar fa-lg" title="',QTstrh(L('Date')),'"></i> ',QTdatestr('now','$',''),'</p>';
echo '<p><i class="fa fa-clock-o fa-lg" title="',QTstrh(L('Clock')),'"></i> ';
echo gmdate($_SESSION[QT]['formattime'],time()+(3600*$_SESSION[QT]['time_zone']));
if ( $_SESSION[QT]['show_time_zone']=='1' )
{
echo ' (gmt',($_SESSION[QT]['time_zone']>0 ? '+' : ''),($_SESSION[QT]['time_zone']==0 ? '' : $_SESSION[QT]['time_zone']),')';
}
echo '</p>';

// HTML END

include APP.'_adm_inc_ft.php';