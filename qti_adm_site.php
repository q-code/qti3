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

$oVIP->selfurl = 'qti_adm_site.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_info'].'</span><br/>'.$L['Adm_general'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check sitename
  $str = $_POST['sitename'];
  if ( empty($str) ) { $str = 'QuickTicket'; $error = $L['Site_name'].' '.L('invalid'); }
  $_SESSION[QT]['site_name'] = $str;

  // check siteurl
  if ( empty($error) )
  {
    $str = trim($_POST['siteurl']); if ( substr($str,-1,1)=='/' ) $str = substr($str,0,-1);
    if ( empty($str) ) { $str = 'http://localhost'; $error = $L['Site_url'].' '.L('invalid'); }
    if ( !preg_match('/^(http:\/\/|https:\/\/)/',$str) ) $warning = $L['Site_url'].': '.$L['E_missing_http'];
    $_SESSION[QT]['site_url'] = $str;
  }

  // check indexname
  if ( empty($error) )
  {
    $str = trim($_POST['title']);
    if ( !empty($str) ) { $_SESSION[QT]['index_name'] = $str; } else { $error = $L['Name_of_index'].' '.L('invalid'); }
  }

  // check adminemail
  if ( empty($error) )
  {
    $str = trim($_POST['adminmail']);
    if ( QTismail($str) ) { $_SESSION[QT]['admin_email'] = $str; } else { $error = $L['Adm_e_mail'].' ['.$str.'] '.L('invalid'); }
  }

  // check smpt
  if ( empty($error) )
  {
    $_SESSION[QT]['use_smtp'] = $_POST['smtp'];
    if ( $_SESSION[QT]['use_smtp']=='1' )
    {
    $_SESSION[QT]['smtp_host'] = $_POST['smtphost'];
    $_SESSION[QT]['smtp_port'] = $_POST['smtpport'];
    $_SESSION[QT]['smtp_username'] = $_POST['smtpusr'];
    $_SESSION[QT]['smtp_password'] = $_POST['smtppwd'];
    if ( empty($_SESSION[QT]['smtp_host']) ) $error = 'Smtp host '.L('invalid');
    }
  }

  // save value
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($_SESSION[QT]['site_name']).'" WHERE param="site_name"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($_SESSION[QT]['site_url']).'"WHERE param="site_url"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($_SESSION[QT]['index_name']).'" WHERE param="index_name"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($_SESSION[QT]['admin_email']).'" WHERE param="admin_email"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($_SESSION[QT]['use_smtp']).'" WHERE param="use_smtp"');
    if ( $_SESSION[QT]['smtp_host']=='1' )
    {
    $oDB->Exec('DELETE FROM '.TABSETTING.' WHERE param="smtp_host" OR param="smtp_port" OR param="smtp_username" OR param="smtp_password"');
    $oDB->Exec('INSERT INTO '.TABSETTING.' VALUES ("smtp_host","'.QTstrd($_SESSION[QT]['smtp_host']).'","1")');
    $oDB->Exec('INSERT INTO '.TABSETTING.' VALUES ("smtp_port","'.QTstrd($_SESSION[QT]['smtp_port']).'","1")');
    $oDB->Exec('INSERT INTO '.TABSETTING.' VALUES ("smtp_username","'.QTstrd($_SESSION[QT]['smtp_username']).'","1")');
    $oDB->Exec('INSERT INTO '.TABSETTING.' VALUES ("smtp_password","'.QTstrd($_SESSION[QT]['smtp_password']).'","1")');
    }
    $str = trim($_POST['adminname']);
      $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($str).'" WHERE param="admin_name"');
      $_SESSION[QT]['admin_name'] = $str;
    $str = trim($_POST['adminaddr']);
      $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($str).'" WHERE param="admin_addr"');
      $_SESSION[QT]['admin_addr'] = $str;

    // save translations

    cLang::Delete('index','i');
    foreach($_POST as $key=>$str)
    {
      if ( substr($key,0,1)=='T' && !empty($str) ) cLang::Add('index',substr($key,1),'i',$_POST[$key]);
    }

    // register lang

    $_SESSION['L']['index'] = cLang::Get('index',QTiso());

    // exit
    $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
  }

}

if ( !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['site_url']) ) $warning = $L['Site_url'].': '.$L['E_missing_http'];

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
function smtpdisabled(str)
{
if (str=="0")
{
doc.getElementById("smtphost").disabled=true;
doc.getElementById("smtpport").disabled=true;
doc.getElementById("smtpusr").disabled=true;
doc.getElementById("smtppwd").disabled=true;
}
else
{
doc.getElementById("smtphost").disabled=false;
doc.getElementById("smtpport").disabled=false;
doc.getElementById("smtpusr").disabled=false;
doc.getElementById("smtppwd").disabled=false;
}
return null;
}
function PassInLink()
{
strHost = doc.getElementById("smtphost").value;
strPort = doc.getElementById("smtpport").value;
strUser = doc.getElementById("smtpusr").value;
doc.getElementById("smtplink").href="qti_ext_smtp.php?h=" + strHost + "&p=" + strPort + "&u=" + strUser;
doc.getElementById("smtplink").target="_blank";
return null;
}
</script>
';

include APP.'_adm_inc_hd.php';

// FORM

echo '<form method="post" action="',$oVIP->selfurl,'">
<h2 class="subtitle">',$L['General_site'],'</h2>
<table class="t-data horiz">
';
echo '<tr title="',$L['H_Site_name'],'">
<th><label for="sitename">',$L['Site_name'],'</label></th>
<td><input required type="text" id="sitename" name="sitename" size="50" maxlength="64" value="',QTstrh($_SESSION[QT]['site_name']),'" onchange="bEdited=true;"/></td>
</tr>
';
echo '<tr title="',$L['H_Site_url'],'">
<th><label for="siteurl">',$L['Site_url'],'</label></th>
<td><input required type="url" id="siteurl" name="siteurl" pattern="^(http://|https://).*" size="50" maxlength="255" value="',QTstrh($_SESSION[QT]['site_url']),'" onchange="bEdited=true;"/></td>
</tr>
';
echo '<tr title="',$L['H_Name_of_index'],'">
<th><label for="title">',$L['Name_of_index'],'</label></th>
<td>
<input required type="text" id="title" name="title" size="50" maxlength="64" value="',QTstrh($_SESSION[QT]['index_name']),'" style="background-color:#FFFF99" onchange="bEdited=true;"/></td>
</tr>
<tr>
<th>',$L['Name_of_index'],'<br/>',$L['Translations'],' *</th>
<td>
<table class="subtable">
';
$arrTrans = cLang::Get('index','*','i');
include 'bin/qti_lang.php'; // this creates $arrLang
foreach($arrLang as $strIso=>$arr)
{
  $str = empty($arrTrans[$strIso]) ? '' : $arrTrans[$strIso];
  echo '<tr><td style="width:25px"><span title="',$arr[1],'">',$arr[0],'</span></td><td><input title="',$L['Name_of_index'],' (',$strIso,')" type="text" id="T',$strIso,'" name="T',$strIso,'" size="45" maxlength="64" value="',QTstrh($str),'"/></td></tr>',PHP_EOL;
}
echo '
</table>
</td>
</tr>
<tr>
<td class="blanko" colspan="2">* <span class="small">',sprintf($L['E_no_translation'],QTstrh($_SESSION[QT]['index_name'])),'</span></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',$L['Contact'],'</h2>
<table class="t-data horiz">
';
echo '<tr title="',$L['H_Admin_e_mail'],'">
<th><label for="adminmail">',$L['Adm_e_mail'],'</label></th>
<td><input required type="email" id="adminmail" name="adminmail" size="50" maxlength="255" value="',QTstrh($_SESSION[QT]['admin_email']),'" onchange="bEdited=true;"/></td>
</tr>
';
echo '<tr title="',$L['Adm_name'],'">
<th><label for="adminname">',$L['Adm_name'],'</label></th>
<td><input type="text" id="adminname" name="adminname" size="50" maxlength="255" value="',QTstrh($_SESSION[QT]['admin_name']),'" onchange="bEdited=true;"/></td>
</tr>
';
echo '<tr title="',$L['Adm_addr'],'">
<th><label for="adminaddr">',$L['Adm_addr'],'</label></th>
<td><input type="text" id="adminaddr" name="adminaddr" size="50" maxlength="255" value="',QTstrh($_SESSION[QT]['admin_addr']),'" onchange="bEdited=true;"/></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',$L['Email_settings'],'</h2>
<table class="t-data horiz">
';
echo '<tr title="',$L['H_Use_smtp'],'">
<th><label for="smtp">',$L['Use_smtp'],'</label></th>
<td><select id="smtp" name="smtp" onchange="smtpdisabled(this.value); bEdited=true;">',QTasTag(array(L('N'),L('Y')),(int)$_SESSION[QT]['use_smtp']),'</select></td>
</tr>
';
echo '<tr title="',$L['H_Use_smtp'],'">
<th><label for="smtphost">Smtp host</label></th>
<td>
<input type="text" id="smtphost" name="smtphost" size="28" maxlength="64" value="',$_SESSION[QT]['smtp_host'],'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/>
 port <input type="text" id="smtpport" name="smtpport" size="4" maxlength="6" value="',(isset($_SESSION[QT]['smtp_port']) ? $_SESSION[QT]['smtp_port'] : '25'),'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/>
</td>
</tr>
';
echo '<tr title="',$L['H_Use_smtp'],'">
<th><label for="smtpusr">Smtp username</label></th>
<td><input type="text" id="smtpusr" name="smtpusr" size="28" maxlength="64" value="',QTstrh($_SESSION[QT]['smtp_username']),'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/></td>
</tr>
';
echo '<tr title="',$L['H_Use_smtp'],'">
<th><label for="smtppwd">Smtp password</label></th>
<td><input type="text" id="smtppwd" name="smtppwd" size="28" maxlength="64" value="',QTstrh($_SESSION[QT]['smtp_password']),'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/> <a id="smtplink" href="qti_ext_smtp.php" onclick="PassInLink()">test smtp</a></td>
</tr>
</table>
';
echo '
<p style="margin:0 0 5px 0;text-align:center"><input type="submit" name="ok" value="',L('Save'),'"/></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';