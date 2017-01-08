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
if ( !sUser::CanView('V4') ) { $oHtml->PageMsg(11); return; }
include Translate(APP.'_stat.php');

// INITIALISE

if ( !isset($_GET['y']) ) die('Missing parameter m');
$intYear = (int)$_GET['y'];
$intMonth = 0;
$s = '*';
if ( isset($_GET['s']) ) $s = $_GET['s'];
if ( $s==='' || $s=='-1' ) $s='*';
if ( isset($_GET['m']) ) $intMonth = (int)$_GET['m'];
$datesize=6; if ( $intMonth==0 ) $datesize=4;

$strWhere = ' WHERE forum>=0';
$strSectionTitle = '';
if ( $s!=='*' )
{
$strWhere = ' WHERE forum='.(int)$s;
$arrSectionTitle = QTarrget(GetSections('A'));
$strSectionTitle = '<br/>'.$L['Section'].' '.$arrSectionTitle[$s].'';
}

$oVIP->selfurl = 'qti_stat.php';
$oVIP->selfname = $L['Statistics'];
$oVIP->exiturl = 'qti_stats.php';
$oVIP->exitname = $L['Statistics'];

// --------
// HTML START
// --------

$oHtml->scripts_end[] = '<script type="text/javascript">
var roles = "'.L('Role_A').';'.L('Role_M').';'.L('Role_U').'";
$(function() {
  $(".jmouseover").mouseover(function() {
    $.post("bin/qti_j_user.php",
      {id:this.id,dir:"'.QTI_DIR_PIC.'",roles:roles,ph:"'.$_SESSION[QT]['skin_dir'].'/user.gif"},
      function(data) { if ( data.length>0 ) document.getElementById("popupinfo").innerHTML=data; });
  });
});
</script>
';

include 'qti_inc_hd.php';

switch($oDB->type)
{
case 'pdo.mysql': 
case 'mysql4':
case 'mysql': $oDB->Query('SELECT DISTINCT userid, username, count(id) as countid FROM '.TABPOST.$strWhere.' AND LEFT(issuedate,'.$datesize.')="'.($datesize==4 ? $intYear : $intYear*100+$intMonth).'" GROUP BY userid,username' ); break;
case 'pdo.sqlsrv': 
case 'sqlsrv':$oDB->Query('SELECT DISTINCT userid, username FROM '.TABPOST.$strWhere.' AND LEFT(issuedate,'.$datesize.')="'.($datesize==4 ? $intYear : $intYear*100+$intMonth).'"' ); break;
case 'pdo.pg': 
case 'pg':    $oDB->Query('SELECT DISTINCT userid, username FROM '.TABPOST.$strWhere.' AND SUBSTRING(issuedate,1,'.$datesize.')="'.($datesize==4 ? $intYear : $intYear*100+$intMonth).'"' ); break;
case 'pdo.firebird': 
case 'ibase': $oDB->Query('SELECT DISTINCT userid, username FROM '.TABPOST.$strWhere.' AND SUBSTRING(issuedate FROM 1 FOR '.$datesize.')="'.($datesize==4 ? $intYear : $intYear*100+$intMonth).'"' ); break;
case 'pdo.sqlite': 
case 'sqlite':
case 'pdo.oci': 
case 'oci': 
case 'db2':  $oDB->Query('SELECT DISTINCT userid, username FROM '.TABPOST.$strWhere.' AND SUBSTR(issuedate,1,'.$datesize.')="'.($datesize==4 ? $intYear : $intYear*100+$intMonth).'"' ); break;
default: die('Unknown db type '.$oDB->type);
}
$arrUsers = array();
while($row=$oDB->Getrow())
{
  $arrUsers[$row['userid']]=$row['username'].(isset($row['countid']) ? ' ('.$row['countid'].')' : '');
}
$intUsers = count($arrUsers);
asort($arrUsers);

echo '<h1>',$L['Statistics'],'</h1>',PHP_EOL;

echo '<h2>',$L['Distinct_users'],( $intMonth!=0 ? ' '.$L['dateMM'][$intMonth] : ''),' ',$intYear,$strSectionTitle,'</h2>',PHP_EOL;
echo L('User',$intUsers).'<br/><br/>';

echo '<table class="t-data">
<tr>
<th style="width:50%">',$L['Username'],'</th>
<th style="width:50%">',$L['Information'],'</th>
</tr>
<tr>
<td style="vertical-align:top;height:175px">
';

if ( $intUsers>0 )
{
  $str = '<br/>'; if ($intUsers>50) $str = ', ';
  foreach($arrUsers as $intId=>$strName)
  {
  echo '<a class="jmouseover" id="u',$intId,'" href="qti_user.php?id=',$intId,'">',$strName,'</a>',$str;
  }
}
else
{
  echo $L['None'].'<br/>';
}

echo '</td>',PHP_EOL;
echo '<td style="vertical-align:top;height:175px">',PHP_EOL;

  // DISPLAY Preview
  echo '<script type="text/javascript"></script><noscript>Your browser does not support JavaScript</noscript>';
  echo '<div id="popupinfo"></div>',PHP_EOL;

// preview
echo '</td>',PHP_EOL;
echo '</tr>',PHP_EOL,'</table>',PHP_EOL;

// --------
// HTML END
// --------

echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>',PHP_EOL;

include 'qti_inc_ft.php';