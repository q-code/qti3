<?php

// QTI 3.0 build:20160703

session_start();

if ( !isset($_SESSION['qti_setup_lang']) ) $_SESSION['qti_setup_lang']='en';

include 'qti_lang_'.$_SESSION['qti_setup_lang'].'.php';
include '../bin/config.php'; if ( $qti_dbsystem=='sqlite' ) $qti_database = '../'.$qti_database;
if ( isset($qti_install) ) { define('QT','qti'.substr($qti_install,-1)); } else { define('QT','qti'); }
include '../bin/class/qt_class_db.php';
include '../bin/qt_lib_sys.php';

function QTismail($str)
{
  if ( !is_string($str) ) die('QTismail: arg #1 must be a string');

  if ( $str!=trim($str) ) return false;
  if ( $str!=strip_tags($str) ) return false;
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$str) ) return false;
  return true;
}

$strAppl     = 'QuickTicket 3.0';
$strPrevUrl  = 'qti_setup_2.php';
$strNextUrl  = 'qti_setup_4.php';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Next'];

// Read admin_email setting

$oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
define('TABSETTING', $qti_prefix.'qtisetting');
GetSettings('param="admin_email"',true);
if ( !isset($_SESSION[QT]['admin_email']) ) $_SESSION[QT][admin_email]='';

// --------
// HTML START
// --------

include 'qti_setup_hd.php';

// Submitted

if ( !empty($_POST['admin_email']) )
{
  if ( QTismail($_POST['admin_email']) )
  {
    $_SESSION[QT]['admin_email'] = $_POST['admin_email'];
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['admin_email'].'" WHERE param="admin_email"');
    if ( empty($oDB->error) )
    {
    echo '<div class="setup_ok">',$L['S_save'],'</div>';
    }
    else
    {
    echo '<div class="setup_err">',sprintf ($L['E_connect'],$qti_database,$qti_host),'</div>';
    }
  }
  else
  {
  echo '<div class="setup_err">Invalid e-mail</div>';
  }
}

// Form

echo '<h2>',$L['Board_email'],'</h2>
<form method="post" name="install" action="qti_setup_3.php">
<table>
<tr>
<td><input type="email" name="admin_email" value="',$_SESSION[QT]['admin_email'],'" size="34" maxlength="255"/>&nbsp;<input type="submit" name="ok" value="',$L['Ok'],'"/></td>
<td style="width:40%"><div class="setup_help">',$L['Help_3'],'</div></td>
</tr>
</table>
</form>
';

include 'qti_setup_ft.php';