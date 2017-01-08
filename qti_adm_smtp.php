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
if ( sUser::Role()!='A' ) die('Access is restricted to administrators only');

// INITIALISE

include 'bin/class/qt_class_smtp.php';

$oVIP->selfurl = 'qti_adm_smtp.php';
$oVIP->selfname = 'SMTP test';

if ( isset($_GET['h']) ) $_SESSION[QT]['smtp_host'] = $_GET['h'];
if ( isset($_GET['u']) ) $_SESSION[QT]['smtp_username'] = $_GET['u'];
if ( isset($_GET['p']) ) $_SESSION[QT]['smtp_password'] = $_GET['p'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // register value used
  $_SESSION[QT]['smtp_host'] = $_POST['smtphost'];
  $_SESSION[QT]['smtp_username'] = $_POST['smtpusr'];
  $_SESSION[QT]['smtp_password'] = $_POST['smtppwd'];
  if ( !QTismail($_POST['mailto']) ) die($L['Email'].' '.L('invalid'));

  // send mail
  smtpmail($_POST['mailto'],$_POST['subject'],$_POST['message'],'From:'.$_SESSION[QT]['admin_email']);

  // exit
  $oVIP->exiturl = 'qti_adm_smtp.php';
  $oVIP->exitname = 'SMTP test';
  $oVIP->EndMessage(NULL,'Process completed...<br/><br/>If you have changed the smtp settings during the test, go to the Administration page and SAVE your new settings!','admin',0);
}

// --------
// HTML START
// --------

$oVIP->arrJava=null;
include 'qti_adm_inc_hd.php';
include 'qti_adm_p_title.php';

// CONTENT

echo '<br/>
<form method="post" action="',$oVIP->selfurl,'">
<table class="t-data horiz">
<tr>
<td class="colhd colgroup" style="width:200px;">SMTP Settings</td>
<td  style="width:200px;">&nbsp;</td>
<td >&nbsp;</td>
</tr>
<tr>
<th><label for="smtphost">Smtp host</label></th>
<td ><input type="text" id="smtphost" name="smtphost" size="30" maxlength="64" value="',$_SESSION[QT]['smtp_host'],'"/></td>
<td >&nbsp;</td>
</tr>
<tr>
<th><label for="smtpusr">Smtp username</label></th>
<td ><input type="text" id="smtpusr" name="smtpusr" size="30" maxlength="64" value="',$_SESSION[QT]['smtp_username'],'"/></td>
<td >&nbsp;</td>
</tr>
<tr>
<th><label for="smtppwd">Smtp password</label></th>
<td ><input type="text" id="smtppwd" name="smtppwd" size="30" maxlength="64" value="',$_SESSION[QT]['smtp_password'],'"/></td>
<td >&nbsp;</td>
</tr>
<tr>
<td class="colhd colgroup">',$L['Email'],'</td>
<td >&nbsp;</td>
<td >&nbsp;</td>
</tr>
<tr>
<th><label for="mailto">SEND TO</label></th>
<td ><input type="text" id="mailto" name="mailto" size="30" maxlength="64" value=""/></td>
<td >&nbsp;</td>
</tr>
<tr>
<th><label for="subject">Subject</label></th>
<td ><input type="text" id="subject" name="subject" size="30" maxlength="64" value="Test smtp"/></td>
<td >&nbsp;</td>
</tr>
<tr>
<th><label for="message">Message</label></th>
<td ><input type="text" id="message" name="message" size="30" maxlength="64" value="Test mail send by smtp server"/></td>
<td >&nbsp;</td>
</tr>
<tr>
<th>&nbsp;</th>
<td ><input type="submit" name="ok" value="',$L['Send'],'"/></td>
<td >&nbsp;</td>
</tr>
</table>
</form>
';

// HTML END

include 'qti_adm_inc_ft.php';