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
* @package    QTI
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_profile.css" />';
include Translate(APP.'_reg.php');

// INITIALISE

$oVIP->selfurl = 'qti_login.php';
$oVIP->selfname = L('Login');

$strName = '';
if ( isset($_GET['dfltname']) )
{
  $strName=$_GET['dfltname'];
  $strName=QTconv($strName,'U');
}

// --------
// SUBMITTED for login
// --------

if ( isset($_POST['ok']) )
{
  // CHECK FORM VALUE

  $strName = trim($_POST['title']);
  $strName = QTconv($strName,'U',false,false);
  if ( !QTislogin($strName) ) $error = $L['Username'].' '.Error(1);

  $strPwd = trim($_POST['pwd']);
  $strPwd = QTconv($strPwd,'U',false,false);
  if ( !QTispassword($strPwd) ) $error = $L['Password'].' '.Error(1);

  // EXECUTE Login

  if ( empty($error) )
  {
    sUser::Login($strName,$strPwd,isset($_POST['remember']));

    // Post processing login (check login,ban,profile)

    if ( sUser::Auth() ) $error = sUser::LoginPostProc(); // this can exit to an other page, return an error message or return null
  }

  // Exit (or error window)

  if ( sUser::Auth() && empty($error) )
  {
      // end message
      $_SESSION['pagedialog']='L|'.$L['Welcome'].' '.sUser::Name();
      $oHtml->Redirect('qti_index.php');
  }
  $_SESSION['pagedialog']='E|'.Error(10);
}

// --------
// SUBMITTED for loggout
// --------

if ( isset($_GET['a']) && $_GET['a']==='out' )
{

  // LOGGING OUT

  $oVIP->Logout();

  // REBOOT

  GetSettings('',true);

  // check major parameters
  if ( !isset($_SESSION[QT]['skin_dir']) ) $_SESSION[QT]['skin_dir']='skin/default';
  if ( !isset($_SESSION[QT]['language']) ) $_SESSION[QT]['language']='english';
  if ( empty($_SESSION[QT]['skin_dir']) ) $_SESSION[QT]['skin_dir']='skin/default';
  if ( empty($_SESSION[QT]['language']) ) $_SESSION[QT]['language']='english';
  if ( substr($_SESSION[QT]['skin_dir'],0,5)!='skin/' ) $_SESSION[QT]['skin_dir'] = 'skin/'.$_SESSION[QT]['skin_dir'];

  session_start();
  $_SESSION['pagedialog']='U|'.L('Goodbye');
  $oHtml->Redirect('qti_index.php');

}

// --------
// HTML START
// --------

$oHtml->scripts_end[] = '<script type="text/javascript">
var doc = document.getElementById("title");
doc.focus();
if ( doc.value.length>1 ) { document.getElementById("pwd").focus(); }
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.L('Missing').': '.$L['Username'].'")); return false; }
  if (theForm.pwd.value.length==0) { alert(qtHtmldecode("'.L('Missing').': '.$L['Password'].'")); return false; }
  return null;
}
</script>
';

include 'qti_inc_hd.php';

$oHtml->MsgBox($oVIP->selfname,'msgbox login,msgboxtitle login,msgboxbody login');

if ( !empty($error) ) echo '<span class="error">',$error,'</span>&nbsp;';
echo '<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
<p><label><i class="fa fa-user fa-lg" title="',QTstrh(L('Username')),'"/></i></label><input type="text" id="title" name="title" pattern=".{2}.*" size="25" maxlength="64" value="',QTstrh($strName),'" placeholder="',QTstrh(L('Username')),'"/></p>
<p><label><i class="fa fa-lock fa-lg" title="',QTstrh(L('Password')),'"/></i></label><input type="password" id="pwd" name="pwd" pattern=".{4}.*" size="25" maxlength="40" placeholder="',QTstrh(L('Password')),'"/></p>
<p><input type="checkbox" id="remember" name="remember"/>&nbsp;<label for="remember">',$L['Remember'],'</label>&nbsp;&nbsp;
<input type="submit" name="ok" value="',$L['Ok'],'"/></p>
<p><a href="',Href('qti_register.php'),'">',L('Register'),'</a> &middot; <a href="',Href('qti_reset_pwd.php'),'?a=id">',L('Forgotten_pwd'),'</a></p>
</form>
';

$oHtml->MsgBox(END);

// HTML END

include 'qti_inc_ft.php';