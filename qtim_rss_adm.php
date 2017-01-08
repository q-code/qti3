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
include Translate(APP.'_adm.php');
include Translate('qtim_rss.php');
if ( sUser::Role()!='A' ) die($L['E_admin']);

// INITIALISE

$oVIP->selfurl = 'qtim_rss_adm.php';
$oVIP->selfname = $L['rss']['Admin'];
$strPageversion = $L['Version'].' 3.0';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check others
  if ( empty($error) )
  {
    $_SESSION[QT]['m_rss'] = $_POST['rss'];
    $_SESSION[QT]['m_rss_conf'] = $_POST['rssuser'].' '.$_POST['rssformat'].' '.$_POST['rsssize'];
  }

  // save value
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['m_rss'].'" WHERE param="m_rss"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['m_rss_conf'].'" WHERE param="m_rss_conf"');
    // exit
    $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
  }
}

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';

// read values
if ( !isset($_SESSION[QT]['m_rss_conf']) )
{
  $arr = GetSettings('param="m_rss_conf"',true);
  if ( empty($arr) ) die('<span class="error">Parameters not found. The module is probably not installed properly.</span><br/><br/><a href="qti_adm_index.php">&laquo;&nbsp;'.L('Exit').'</a>');
}
if ( !isset($_SESSION[QT]['m_rss']) )
{
  $arr = GetSettings('param="m_rss"',true);
  if ( empty($arr) ) die('<span class="error">Parameters not found. The module is probably not installed properly.</span><br/><br/><a href="qti_adm_index.php">&laquo;&nbsp;'.L('Exit').'</a>');
}

$arrConf = explode(' ',$_SESSION[QT]['m_rss_conf']);
$strUser = $arrConf[0];
$strForm = $arrConf[1];
$strSize = $arrConf[2];

// FORM

echo '
<form method="post" action="',$oVIP->selfurl,'">
<h2 class="subtitle">',$L['Status'],'</h2>
<table class="t-data horiz">
<tr>
<th>',$L['Status'],'</th>';
if ( $_SESSION[QT]['m_rss']=='1' )
{
  echo '<td style="width:100px;background-color:#AAFFAA;text-align:center">',L('On_line'),'</td>';
}
else
{
  echo '<td style="width:100px;background-color:#FFAAAA;text-align:center">',L('Off_line'),'</td>';
}
echo '<td style="text-align:right">',L('Change'),'&nbsp;<select id="rss" name="rss">
<option value="1"',($_SESSION[QT]['m_rss']=='1' ? QSEL : ''),'>',L('On_line'),'</option>
<option value="0"',($_SESSION[QT]['m_rss']=='0' ? QSEL : ''),'>',L('Off_line'),'</option>
</select>
</td>
</tr>
</table>
';

echo '<h2 class="subtitle">',L('Settings'),'</h2>
<table class="t-data horiz">
<tr>
<th style="width:200px"><label for="rssuser">',$L['rss']['User'],'</label></td>
<td><select id="rssuser" name="rssuser">
<option value="V"',($strUser=='V' ? QSEL : ''),'>',$L['rss']['All_users'],'</option>
<option value="U"',($strUser=='U' ? QSEL : ''),'>',$L['rss']['Members_only'],'</option>
</select></td>
<td><span class="help">',$L['rss']['H_User'],'</span></td>
</tr>
<tr>
<th><label for="rssformat">',$L['rss']['Format'],'</label></th>
<td><select id="rssformat" name="rssformat">
<option value="2"',($strForm=='2' ? QSEL : ''),'>RSS 2.0</option>
<option value="atom"',($strForm=='atom' ? QSEL : ''),'>Atom</option>
</select></td>
<td ><span class="help">',$L['rss']['H_Format'],'</span></td>
</tr>
<tr>
<th><label for="rsssize">',$L['rss']['Size'],'</label></th>
<td ><select id="rsssize" name="rsssize">
<option value="1"',($strSize=='1' ? QSEL : ''),'>1</option>
<option value="2"',($strSize=='2' ? QSEL : ''),'>2</option>
<option value="3"',($strSize=='3' ? QSEL : ''),'>3</option>
<option value="4"',($strSize=='4' ? QSEL : ''),'>4</option>
<option value="5"',($strSize=='5' ? QSEL : ''),'>5</option>
</select></td>
<td ><span class="help">',$L['rss']['H_Size'],'</span></td>
</tr>
</table>
';

echo '<p style="margin:0 0 5px 0;text-align:center"><input type="submit" name="ok" value="',L('Save'),'"/></p>
</form>
';

$strRssUrl = $_SESSION[QT]['site_url'].'/rss';
$arrSections = GetSections('V');
$arrRss = array();
foreach($arrSections as $s=>$arrSection)
{
if ( file_exists('rss/qti_'.$strForm.'_'.$s.'.xml') ) $arrRss[$s]=$strRssUrl.'/qti_'.$strForm.'_'.$s.'.xml';
}

if ( count($arrRss)>0 )
{
  echo '<h2>',L('Preview'),'</h2>',PHP_EOL;
  echo '<table class="t-item">',PHP_EOL;
  foreach($arrRss as $s=>$strRss)
  {
  echo '<tr class="t-item hover"><td >'.$arrSections[$s]['title'],'</td><td ><a href="',$strRss,'" target="_blank">',$strRss,'</a></td></tr>';
  }
  echo '</table>';
  echo '<p>The feeds remain accessible when the module is off-line.</p>';
}

// HTML END

include APP.'_adm_inc_ft.php';