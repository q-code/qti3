<?php

/**
* PHP versions 5
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
if ( !sUser::CanView('V0') ) die('Access denied');

// INITIALISE

$a=''; // mandatory action
$p=''; // people's username
$s=''; // secret answer
$ok='';
QThttpvar('a p s ok','str str str str');
if ( empty($a) ) die('Missing argument');

include Translate(APP.'_reg.php');

$oVIP->selfurl = 'qti_reset_pwd.php';
$oVIP->selfname = $L['Forgotten_pwd'];

// 2 PROCESSES: 'id' username, then 'sec' secret question

switch($a)
{

// --------
case 'id': // request username
// --------

  // Submitted

  if ( !empty($ok) )
  {
    if ( !empty($p) ) { if (!QTislogin($p)) $error=$L['Username'].' '.L('invalid'); }
    if ( empty($error) && empty($p) ) $error=L('invalid');
    if ( empty($error) && !empty($p) )
    {
      $oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE name="'.$p.'"');
      $row = $oDB->Getrow();
      if ( intval($row['countid'])!=1 ) $error=$L['Username'].' '.L('invalid');
    }
    if ( empty($error) ) $oHtml->Redirect(Href().'?a=sec&amp;p='.urlencode($p));
  }

  // Form

  include 'qti_inc_hd.php';

  $oHtml->MsgBox($oVIP->selfname);

  echo '
  <form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <input type="hidden" name="a" value="'.$a.'" />
  <p>',$L['Reg_pass'],'</p>
  <p style="text-align:right"><i class="fa fa-user fa-lg" title="',QTstrh(L('Username')),'"/></i><input type="text" id="p" name="p" size="24" maxlength="24" value="',$p,'" placeholder="',$L['Username'],'"/></p>
  <p style="text-align:right">',(!empty($error) ? '<span class="error">'.$error.'</span> ' : ''),'
  <input type="submit" id="ok" name="ok" value="',$L['Next'],'" /></p>
  </form>
  <script type="text/javascript">
  function ValidateForm(theForm)
  {
    if (theForm.p.value.length==0) { alert(qtHtmldecode("',$L['Missing'],'")); return false; }
    return true;
  }
  </script>
  ';
  $oHtml->Msgbox(-1);
  include 'qti_inc_ft.php';

  break;

// --------
case 'sec': // request secret question
// --------

  $oDB->Query('SELECT id,parentmail,children,mail,secret_q,secret_a FROM '.TABUSER.' WHERE name="'.$p.'"');
  $row = $oDB->Getrow();
  $strMail = $row['mail'];
  $strChildren = $row['children'];
  $strParentmail = $row['parentmail'];
  if ( intval($row['id'])<=1 ) die('Admin and Visitor password can not be reset');

  // Submitted

  if ( !empty($ok) && !empty($p) && !empty($s) )
  {
    if ( !isset($_SESSION['try']) ) $_SESSION['try']=0;
    ++$_SESSION['try'];

    if ( strtolower($row['secret_a'])==strtolower($s) )
    {
      include 'bin/class/qt_class_smtp.php';

      // send new password
      $newpwd = 'T'.rand(0,9).rand(0,9).'Q'.rand(0,9).rand(0,9);
      $issuedate = date('Y-m-d H:i:s');
      $oDB->Exec('UPDATE '.TABUSER.' SET pwd="'.sha1($newpwd).'" WHERE name="'.$p.'"');

      // send email
      $strSubject='New password';
      $strMessage="Please find here after a new password to access the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
      $strFile = GetLang().'mail_pwd.php';
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$p,$newpwd);
      QTmail($strMail,$strSubject,$strMessage,QT_HTML_CHAR);

      // exit
      $oHtml->PageMsg( NULL, '<p>'.$L['Password_updated'].'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',0);
    }
    $error = Error(2);
    if ( $_SESSION['try']>4 ) $oHtml->PageMsg( NULL, '<p>Impossible to reset your password. Contact the administrator.</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0 );
  }

  // Form

  if ( empty($row['secret_q']) || empty($row['secret_a']) )
  {
    $oHtml->PageMsg( NULL, '<p>Secret question not defined.<br />Please contact the webmaster ('.$_SESSION[QT]['admin_email'].') to reset your password.</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0 );
  }

  include 'qti_inc_hd.php';
  $oHtml->Msgbox($oVIP->selfname,array(),array('id'=>'login_header'));
  echo '
  <form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <input type="hidden" name="a" value="',$a,'" />
  <input type="hidden" name="p" value="',$p,'" />
  <p>'.$L['Reg_pass_reset'].'</p>
  <br />
  <p style="text-align:right">'.$row['secret_q'].'</p>
  <p style="text-align:right"><input type="text" id="s" name="s" size="24" maxlength="255" value="" /></p>
  <p style="text-align:right">',(!empty($error) ? '<span class="error">'.$error.'</span> ' : ''),'
  <input type="submit" id="ok" name="ok" value="',$L['Ok'],'" /></p>
  </form>
  ';
  $oHtml->Msgbox(END);

  // HTML END

  $oHtml->scripts_end[] = '<script type="text/javascript">
  document.getElementById("secret_a").focus();
  function ValidateForm(theForm)
  {
    if (theForm.s.value.length==0) { alert(qtHtmldecode("'.L('Missing').'")); return false; }
    return null;
  }
  </script>
  ';
  include 'qti_inc_ft.php';

  break;

// --------
default: die('Invalid command');
// --------

}