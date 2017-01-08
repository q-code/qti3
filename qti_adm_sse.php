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
include 'bin/qti_lang.php';
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qti_adm_sse.php';
$oVIP->selfname = '<span class="upper">'.L('Settings').'</span><br/>SSE (server-sent events)';
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;

if ( !defined('SSE_CONNECT') ) define('SSE_CONNECT',10000);
if ( !defined('SSE_ORIGIN') ) define('SSE_ORIGIN','http://localhost');
if ( !defined('SSE_MAX_ROWS') ) define('SSE_MAX_ROWS',2);
if ( !defined('SSE_TIMEOUT') ) define('SSE_TIMEOUT',30);
if ( !defined('SSE_LATENCY') ) define('SSE_LATENCY',10000);

// Restore config file if missing

$strFilename = 'bin/config_sse.php';
if ( !file_exists($strFilename) )
{
  $oVIP->exiturl = 'qti_adm_index.php';
  $oVIP->exitname = L('Exit');
  $restore = ''; // submitted
  QThttpvar('restore','str');

  // ask to restore missing file
  if ( empty($restore) )
  {
    // form
    $oHtml->PageMsgAdm
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <p>'.L('Warning').'</p>
    <p class="error">Config file ['.$strFilename.'] is missing</p>
    <p>Create this file now?</p>
    <p><input type="submit" name="restore" value="'.L('Ok').'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/></p>
    <p><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>
    </form>',
    0,
    'msgbox'
    );
    exit;
  }
  // restore file
  $myfile = fopen($strFilename, 'w') or die('Unable to create the file '.$strFilename.'! Contact administrator.');
  fwrite($myfile, '<?php'.PHP_EOL.'define("SSE_CONNECT",10000);'.PHP_EOL.'define("SSE_ORIGIN","localhost");'.PHP_EOL.'define("SSE_MAX_ROWS",2);'.PHP_EOL.'define("SSE_TIEMOUT",30);'.PHP_EOL.'define("SSE_LATENCY",10000);');
  fclose($myfile);
}

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  if ( !QTisbetween($_POST['sse_connect'],0,SSE_TIMEOUT) ) { $error = L('sse_connect').' '.L('Invalid').' (0-'.SSE_TIMEOUT.' seconds)'; $_POST['sse_connect']='0'; }
  if ( empty($_POST['sse_origin']) ) { $error = L('origin').' '.L('Invalid'); $_POST['sse_origin']='http://localhost'; }
  if ( !QTisbetween($_POST['sse_max_rows'],1,5) ) { $error = L('rows').' '.L('Invalid').' (0-5)'; $_POST['sse_max_rows']='2'; }
  if ( !QTisbetween($_POST['sse_timeout'],10,120) ) { $error = 'SSE_TIMEOUT invalid (10-120 seconds)'; $_POST['sse_timeout']='30'; }
  if ( !QTisbetween($_POST['sse_latency'],1000,60000) ) { $error = 'SSE_LATENCY invalid (1000-60000 milliseconds)'; $_POST['sse_latency']='10000'; }

  // save
  if ( empty($error) )
  {

$content = '<?php
define("SSE_CONNECT",'.(empty($_POST['sse_connect']) ? '0' : $_POST['sse_connect'].'000').');
define("SSE_ORIGIN","'.$_POST['sse_origin'].'");
define("SSE_MAX_ROWS",'.$_POST['sse_max_rows'].');
define("SSE_TIEMOUT",'.$_POST['sse_timeout'].');
define("SSE_LATENCY",'.$_POST['sse_latency'].');
// -----------------
// SSE (server-sent events) allows automatic update of the page content. To disable this set SSE_CONNECT to 0
// -----------------

// SSE_CONNECT: To enable SSE set a value in milliseconds (recommended 10000). This is the delay before the client page re-connect server
// SSE_ORIGIN: Domain of the script sending the SSE events.
// SSE_MAX_ROWS: Number of recent topics that can be added in the list of topics. When more topics arrive, the oldest is replaced. Recommended 2, maximum 5.
// SSE_TIMEOUT: Server message duration in seconds (recommended 30).
// SSE_LATENCY: This is the delay in miliseconds (recommended 10000) before starting the sse process and updating the page content.

// About SSE polyfill: For legacy browser that does not support SSE, an auto-refresh is used (120 seconds). Setting SSE_CONNECT to 0 will also disable this auto-resh.
// When SSE is enabled following settings can be defined.

// Note on SSE_ORIGIN
// SSE_ORIGIN is used as security control to reject messages coming from other servers. It is possible to enter here several origins (with semicolumn)
// If the qti_sse.php script (i.e. the server script) is on the same server as the pages, it must be http://localhost.
// To identify the correct origin, put temporary http://x here, then check the javascript consol log on the index page. The origin will be reported after 10 secondes.';

		if (!is_writable($strFilename)) $error="Impossible to write into the file [$strFilename].";
    if ( empty($error) )
		{
		if (!$handle = fopen($strFilename, 'w')) $error="Impossible to open the file [$strFilename].";
		}
    if ( empty($error) )
		{
		if ( fwrite($handle, $content)===FALSE ) $error="Impossible to write into the file [$strFilename].";
		fclose($handle);
		}
  }

  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

if ( !isset($_POST['sse_connect']) ) $_POST['sse_connect']=SSE_CONNECT/1000;
if ( !isset($_POST['sse_origin']) ) $_POST['sse_origin']=SSE_ORIGIN;
if ( !isset($_POST['sse_max_rows']) ) $_POST['sse_max_rows']=SSE_MAX_ROWS;
if ( !isset($_POST['sse_timeout']) ) $_POST['sse_timeout']=SSE_TIMEOUT;
if ( !isset($_POST['sse_latency']) ) $_POST['sse_latency']=SSE_LATENCY;

include APP.'_adm_inc_hd.php';

// FORM

echo '
<form method="post" action="',$oVIP->selfurl,'">
<h2 class="subtitle">SSE</h2>
<table class="t-data horiz">
';
echo '<tr>
<th>',L('Status'),'</th>
';
if ( empty($_POST['sse_connect']) )
{
  echo '<td style="width:100px;background-color:#FFAAAA;text-align:center">',$L['Off_line'],'</td>';
}
else
{
  echo '<td style="width:100px;background-color:#AAFFAA;text-align:center">',$L['On_line'],'</td>';
}
echo '
<td><input required id="sse_connect" type="text" name="sse_connect" size="2" maxlength="2" value="',$_POST['sse_connect'],'" pattern="[0-9]{1,2}" onchange="bEdited=true;"/> ',L('seconds'),'</td>
</tr>
';
echo '<tr>
<th>&nbsp;</th>
<td colspan="2">';
if ( isset($L['SSE_1']) ) { echo $L['SSE_1']; } else { echo 'To enable SSE set a requery delay value (recommended 10 seconds).<br/>To disable SSE, use 0.'; }
echo '</td>
</tr>
</table>
';

echo '<h2 class="subtitle">',L('Security'),'</h2>
<table class="t-data horiz">
';
echo '<tr>
<th>',L('Origin'),'</th>
<td colspan="2"><input type="text" id="sse_origin" name="sse_origin" size="50" maxlength="500" value="',QTstrh($_POST['sse_origin']),'" onchange="bEdited=true;"/></td>
</tr>
';
echo '<tr>
<th>&nbsp;</th>
<td colspan="2">';
if ( isset($L['SSE_2']) ) { echo $L['SSE_2']; } else { echo 'Origin is a security control required to reject messages coming from other servers. It\'s possible to enter here several origins (with semicolumn).<br/>If the qti_sse.php script (the server script) is on the same server as the other pages, it must be http://localhost.<br/><br/>To identify the correct origin, put temporary http://x here, then check the javascript consol log on the index page. The origin will be reported after 10 seconds.'; }
echo '</td>
</tr>
</table>
';
echo '<h2 class="subtitle">',L('Display'),'</h2>
<table class="t-data horiz">
<tr>
<th>',L('Recent_messages'),'</th>
<td colspan="2"><select id="sse_max_rows" name="sse_max_rows" onchange="bEdited=true;">',QTasTag(array(1=>1,2,3,4,5),(int)$_POST['sse_max_rows']),'</select></td>
</tr>
';
echo '<tr>
<th>&nbsp;</th>
<td colspan="2">';
if ( isset($L['SSE_3']) ) { echo $L['SSE_3']; } else { echo 'Number of recent tickets that can be added on top of the section list.<br/>When more topics arrive, the oldest is replaced.<br/>Recommended 2.'; }
echo '</td>
</tr>
</table>
';
echo '<p style="margin:0 0 5px 0;text-align:center"><input type="submit" name="ok" value="',L('Save'),'"/></p>
<input type="hidden" name="sse_timeout" value="',$_POST['sse_timeout'],'"/>
<input type="hidden" name="sse_latency" value="',$_POST['sse_latency'],'"/>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';