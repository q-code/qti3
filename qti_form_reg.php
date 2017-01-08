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
* @copyright  2016 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/'.APP.'_profile.css" />';

// Check validation (regv is created by qtx_registration and extended 15 minutes for this form)

if ( !empty($_SESSION[QT]['regv']) && time()<$_SESSION[QT]['regv'] )
{
  $_SESSION[QT]['regv']=time()+15*60;
}
else
{
  $oHtml->Redirect(APP.'_register.php?timeout',L('Register')); // without valid time, returns to the aggreement page with timeout argument
}

// INITIALISE

include GetLang().'qti_reg.php';

$oVIP->selfurl = 'qti_form_reg.php';
$oVIP->selfname = $L['Register'];
if ( $_SESSION[QT]['register_mode']=='backoffice' ) $oVIP->selfname .= ' ('.L('request').')';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // pre-checks
  if ( empty($_POST['mail']) ) $error=$L['Email'].' '.L('invalid');
  if ( empty($_POST['title']) ) $error=$L['Username'].' '.L('invalid');
  if ( $_SESSION[QT]['register_safe']!='none' )
  {
  if ( trim($_POST['code'])=='' ) $error = $L['Type_code'];
  if ( strlen($_POST['code'])!=6 ) $error = $L['Type_code'];
  }

  // check name
  if ( empty($error) )
  {
    if ( !QTislogin($_POST['title']) ) $error=$L['Username'].' '.L('invalid');
  }

  // check mail
  if ( empty($error) )
  {
    $_POST['mail'] = trim($_POST['mail']);
    if (!QTismail($_POST['mail'])) $error=$L['Email'].' '.L('invalid');
  }

  // check password
  if ( empty($error) && $_SESSION[QT]['register_mode']=='direct' )
  {
    $_POST['pwd'] = QTconv($_POST['pwd'],'U');
    if ( !QTispassword($_POST['pwd']) ) $error = $L['Password'].' '.L('invalid');

    $_POST['conpwd'] = QTconv($_POST['conpwd'],'U');
    if ( !QTispassword($_POST['conpwd']) ) $error = $L['Password'].' '.L('invalid');
  }
  if ( empty($error) && $_SESSION[QT]['register_mode']=='direct' )
  {
    if ( $_POST['conpwd']!=$_POST['pwd'] ) $error = $L['Password'].' '.L('invalid');
  }

  // check role
  if ( empty($error) )
  {
    if ( isset($_POST['role']) ) { $_POST['role']=substr(strtoupper($_POST['role']),0,1); } else { $_POST['role']='U'; }
    if ( !in_array($_POST['role'],array('A','M','U')) ) $_POST['role']='U';
  }

  // check code
  if ( empty($error) )
  {
    if ( $_SESSION[QT]['register_safe']!='none' )
    {
    $strCode = strtoupper(strip_tags(trim($_POST['code'])));
    if ($strCode=='') $error = $L['Type_code'];
    if ( $_SESSION['textcolor']!=sha1($strCode) ) $error = $L['Type_code'];
    }
  }

  // check secret_a
  if ( empty($error) )
  {
    $_POST['secret_q'] = QTconv($_POST['secret_q'],'3');
    $_POST['secret_a'] = QTconv($_POST['secret_a'],'3');
    if ( empty($_POST['secret_a']) ) $error=$L['Secret_question'].' '.L('invalid');
  }

 // check parentmail
  if ( empty($error) )
  {
    if ( isset($_SESSION[QT]['register_coppa']) && $_SESSION[QT]['register_coppa']=='1' && $strChild!='0' )
    {
      $_POST['parentmail'] = trim($_POST['parentmail']);
      if ( !QTismail($_POST['parentmail']) ) $error=$L['Parent_mail'].' '.L('Invalid');
    }
  }
  if ( !isset($_POST['parentmail']) ) $_POST['parentmail'] = '';

  // --------
  // register user
  // --------
  
  if ( empty($error) )
  {
    include 'bin/class/qt_class_smtp.php';

    if ( $_SESSION[QT]['register_mode']==='backoffice' )
    {
      // Send email
      $strSubject = $_SESSION[QT]['site_name'].' - Registration request';
      $strMessage = "This user request access to the board {$_SESSION[QT]['site_name']}.\nUsername: %s\nEmail: %s";
      $strFile = GetLang().'mail_request.php';
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$_POST['title'],$_POST['mail']);
      QTmail($_SESSION[QT]['admin_email'],QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QT_HTML_CHAR);
      $oHtml->PageMsg( NULL, '<h2>'.L('Request_completed').'</h2><p>'.L('Reg_mail').'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '350px' );
    }
    else
    {
      // email code
      if ( $_SESSION[QT]['register_mode']==='email' ) $_POST['pwd'] = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);

      $id = sUser::AddUser($_POST['title'],$_POST['pwd'],$_POST['mail'],$_POST['role'],'0',$_POST['parentmail']);

      // Unregister global sys (will be recomputed on next page)
      sMem::Clear('sys_lastmember');

      // send email
      $strSubject = $_SESSION[QT]['site_name'].' - Welcome';
      $strMessage = "Please find here after your login and password to access the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
      $strFile = GetLang().'mail_registred.php';
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$_POST['title'],$_POST['pwd']);
      QTmail($_POST['mail'],QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QT_HTML_CHAR);

      // parent mail
      if ( isset($_SESSION[QT]['register_coppa']) && $_SESSION[QT]['register_coppa']=='1' && $strChild!='0' )
      {
        $strSubject = $_SESSION[QT]['site_name'].' - Welcome';
        $strFile = GetLang().'mail_registred_coppa.php';
        if ( file_exists($strFile) ) include $strFile;
        if ( empty($strMessage) ) $strMessage = "We inform you that your children has registered on the team {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s\nYour agreement is required to activte this account.";
        $strMessage = sprintf($strMessage,$_POST['title'],$_POST['pwd']);
        $strMessage = wordwrap($strMessage,70,"\r\n");
        QTmail($_POST['parentmail'],$strSubject,$strMessage);
      }
      
      // END MESSAGE
      if ( $_SESSION[QT]['register_mode']==='email' )
      {
        $oVIP->exiturl = 'qti_index.php';
        $oVIP->exitname = cLang::ObjectName();
      }
      else
      {
        $L['Reg_mail'] = '&nbsp;';
        $oVIP->exiturl = 'qti_login.php?dfltname='.urlencode($_POST['title']);
        $oVIP->exitname = L('Login');
      }
      $oHtml->PageMsg( NULL, '<h2>'.$L['Register_completed'].'</h2><p>'.$L['Reg_mail'].'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '350px' );
    }
  }
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.L('Missing').': '.L('Choose_name').'")); return false; }
  if (theForm.mail.value.length==0) { alert(qtHtmldecode("'.L('Missing').': '.L('Your_mail').'")); return false; }
  if (theForm.code.value.length==0) { alert(qtHtmldecode("'.L('Missing').': '.L('Security').'")); return false; }
  if (theForm.code.value=="QT") { alert(qtHtmldecode("'.L('Missing').': '.L('Security').'")); return false; }
  return null;
}
function MinChar(strField,strValue)
{
  if ( strValue.length>0 && strValue.length<4 )
  {
  document.getElementById(strField+"_err").innerHTML="<br />'.$L['E_min_4_char'].'";
  return null;
  }
  else
  {
  document.getElementById(strField+"_err").innerHTML="";
  return null;
  }
}
</script>
';
$oHtml->scripts_jq[] = '
$(function() {
  $("#title").blur(function() {
    $.post("bin/qti_j_exists.php",
       {f:"name",v:$("#title").val(),e1:"'.$L['E_min_4_char'].'",e2:"'.L('Already_used').'"},
       function(data) { if ( data.length>0 ) document.getElementById("title_err").innerHTML=data; });
  });
});
';
include 'qti_inc_hd.php';

// DEFAULT VALUE RECOVERY (na)

if ( !isset($_POST['title']) ) $_POST['title']='';
if ( !isset($_POST['pwd']) ) $_POST['pwd']='';
if ( !isset($_POST['conpwd']) ) $_POST['conpwd']='';
if ( !isset($_POST['mail']) ) $_POST['mail']='';
if ( !isset($_POST['parentmail']) ) $_POST['parentmail']='';
if ( !isset($_POST['secret_q']) ) $_POST['secret_q']='';
if ( !isset($_POST['secret_a']) ) $_POST['secret_a']='';

if ( !isset($_SESSION[QT]['register_mode']) ) $_SESSION[QT]['register_mode']='direct';
if ( !isset($_SESSION[QT]['register_safe']) ) $_SESSION[QT]['register_safe']='text';

if ( $_SESSION[QT]['register_safe']=='text' )
{
  $keycode = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
  $_SESSION['textcolor'] = sha1($keycode);
}

if ( isset($_SESSION[QT]['register_coppa']) && $_SESSION[QT]['register_coppa']=='1' &&  $strChild!='0' )
{
  echo '<div class="scrollmessage">';
  $strFile = GetLang().'/sys_rules_coppa.txt';
  if ( file_exists($strFile) ) { include $strFile; } else { echo 'Missing file:<br />',$strFile; }
  echo '</div>';
}

$oHtml->Msgbox($oVIP->selfname);

echo '
<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
<table class="hidden">
<tr class="hidden">
<td class="hidden" style="width:370px;">
<div id="login">
<fieldset class="register">
<legend>',L('Username'),'</legend>
<i class="fa fa-user fa-lg" title="',QTstrh(L('Username')),'"/></i><input type="text" id="title" name="title" pattern=".{2}.*" size="25" maxlength="64" value="',$_POST['title'],'" onfocus="document.getElementById(\'title_err\').innerHTML=\'\';" placeholder="',L('Username'),'"/><br /><span id="title_err" class="error"></span><br />
';
if ( $_SESSION[QT]['register_mode']==='direct' )
{
echo '<i class="fa fa-lock fa-lg" title="',QTstrh(L('Password')),'"/></i><input type="password" id="pwd" name="pwd" pattern=".{4}.*" size="25" maxlength="40" value="',$_POST['pwd'],'" placeholder="',$L['Password'],'"/><span id="pwd_err" class="error"></span><br />';
echo '<i class="fa fa-lock fa-lg" title="',QTstrh(L('Confirm_password')),'"/></i><input type="password" id="conpwd" name="conpwd" pattern=".{4}.*" size="25" maxlength="40" value="',$_POST['conpwd'],'" placeholder="',$L['Confirm_password'],'"/><span id="conpwd_err" class="error"></span><br />';
}
else
{
echo L('Password_by_mail'),'<br />';
}
echo PHP_EOL,'</fieldset>',PHP_EOL;

echo '<fieldset class="register">
<legend>',$L['Email'],'</legend>
<i class="fa fa-envelope fa-lg" title="',QTstrh(L('Your_mail')),'"/></i><input type="email" id="mail" name="mail" size="32" maxlength="64" value="',$_POST['mail'],'" placeholder="',QTstrh(L('Your_mail')),'"/><span id="mail_err" class="error"></span><br />
';
if ( isset($_SESSION[QT]['register_coppa']) && $_SESSION[QT]['register_coppa']=='1' && $strChild!='0' ) echo '<i class="fa fa-envelope fa-lg" title="',QTstrh(L('Parent_mail')),'"/></i><input type="email" id="parentmail" name="parentmail" size="32" maxlength="64" value="',$_POST['parentmail'],'" placeholder="',QTstrh(L('Parent_mail')),'"/><br />';
echo PHP_EOL,'</fieldset>',PHP_EOL;

echo '<fieldset class="register">
<legend>',$L['Secret_question'],'</legend>
<select id="secret_q" name="secret_q">',QTasTag($L['Secret_q'],$_POST['secret_q']),'</select><br/>
<input type="text" id="secret_a" name="secret_a" size="32" maxlength="255" value="',QTstrh($_POST['secret_a']),'"/>
<br/>',$L['H_Secret_question'],'
</fieldset>
';

echo '<fieldset class="register captcha">
<legend>',L('Security'),'</legend>
';
if ( $_SESSION[QT]['register_safe']==='image' ) echo '<img width="100" height="35" src="admin/qti_icode.php" alt="security" style="text-align:right" /> <input type="text" name="code" pattern=".{6}.*" size="8" maxlength="8" value="QT" />';
if ( $_SESSION[QT]['register_safe']==='text' ) echo $keycode,'&nbsp;<input type="text" id="code" name="code" pattern=".{6}.*" size="8" maxlength="8" value="QT" />';
echo '
<br />',$L['Type_code'],'
</fieldset>
',(!empty($error) ? '<p class="error">'.$error.'</p>' : ''),'<input type="submit" name="ok" value="',$L['Register'],'" />
</div>
</td>
<td style="width:20px;">&nbsp;</td>
<td class="registrationhelp">',$L['Reg_help'],'</td>
</tr>
</table>
</form>
';

$oHtml->Msgbox(-1);

// HTML END

include 'qti_inc_ft.php';