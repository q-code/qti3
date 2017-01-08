<?php

// QuickTicket 3.0 build:20160703

session_start();

if ( !isset($_SESSION['qti_setup_lang']) ) $_SESSION['qti_setup_lang']='en';
if ( isset($_GET['error']) ) $_SESSION['showerror']=(int)$_GET['error'];
//$showerror = isset($_SESSION['showerror']) ? (int)$_SESSION['showerror'] : 1;

include 'qti_lang_'.$_SESSION['qti_setup_lang'].'.php';
include '../bin/config.php';

$strAppl = 'QuickTicket 3.0';
$strPrevUrl = 'qti_setup_1.php';
$strNextUrl = 'qti_setup_3.php';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Next'];

// --------
// HTML START
// --------

include 'qti_setup_hd.php';

if ( isset($_POST['ok']) )
{
  include '../bin/class/qt_class_db.php';
  include '../bin/qti_fn_base.php';

  if ( isset($_SESSION['qti_dbopwd']) )
  {
  $qti_user = $_SESSION['qti_dbologin'];
  $qti_pwd = $_SESSION['qti_dbopwd'];
  }

  define('TABDOMAIN', $qti_prefix.'qtidomain');
  define('TABSECTION', $qti_prefix.'qtiforum');
  define('TABUSER', $qti_prefix.'qtiuser');
  define('TABTOPIC', $qti_prefix.'qtitopic');
  define('TABPOST', $qti_prefix.'qtipost');
  define('TABSTATUS', $qti_prefix.'qtistatus');
  define('TABSETTING', $qti_prefix.'qtisetting');
  define('TABLANG', $qti_prefix.'qtilang');

  $oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);
  if ( !empty($oDB->error) ) die ('<p class="error">Connection with database failed.<br/>Please contact the webmaster for further information.</p><p>The webmaster must check that server is up and running, and that the settings in the config file are correct for the database.</p>');

  if ( empty($oDB->error) )
  {
    // Install the tables
    $strTable = TABSETTING;
    echo "<p>A) {$L['Installation']} SETTING... ";
    include 'qti_setup_setting.php';
    echo "{$L['Done']}, {$L['Default_setting']}<br />";
    $strTable = TABDOMAIN;
    echo "B) {$L['Installation']} DOMAIN... ";
    include 'qti_setup_domain.php';
    echo "{$L['Done']}, {$L['Default_domain']}<br />";
    $strTable = TABSECTION;
    echo "C) {$L['Installation']} FORUM... ";
    include 'qti_setup_section.php';
    echo "{$L['Done']}, {$L['Default_section']}<br />";
    $strTable = TABTOPIC;
    echo "D) {$L['Installation']} TOPIC... ";
    include 'qti_setup_topic.php';
    echo "{$L['Done']}<br />";
    $strTable = TABPOST;
    echo "E) {$L['Installation']} POST... ";
    include 'qti_setup_post.php';
    echo "{$L['Done']}<br />";
    $strTable = TABUSER;
    echo "F) {$L['Installation']} USER... ";
    include 'qti_setup_user.php';
    echo "{$L['Done']}, {$L['Default_user']}<br />";
    $strTable = TABSTATUS;
    echo "G) {$L['Installation']} STATUS... ";
    include 'qti_setup_status.php';
    echo "{$L['Done']}, {$L['Default_status']}<br />";
    $strTable = TABLANG;
    echo "H) {$L['Installation']} LANG... ";
    include 'qti_setup_lang.php';
    echo "{$L['Done']}</p>";
    if ($result==FALSE)
    {
      echo '<div class="setup_err">',sprintf ($L['E_install'],$strTable,$qti_database,$qti_user),'</div>';
    }
    else
    {
      echo '<div class="setup_ok">',$L['S_install'],'</div>';
      $_SESSION['qtiInstalled'] = true;
      // save the url
      $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? "http://" : "https://" ).$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
      $strURL = substr($strURL,0,-24);
      $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$strURL.'" WHERE param="site_url"');
    }
  }
  else
  {
    echo '<div class="setup_err">',sprintf ($L['E_connect'],$qti_database,$qti_host),'</div>';
  }

}
else
{
  echo '
  <h2>',$L['Install_db'],'</h2>
  <table>
  <tr valign="top">
  <td width="475" style="padding:5px">
  <form method="post" name="install" action="qti_setup_2.php" >
  <p>',$L['Upgrade2'],'</p>
  <p id="process" style="display:none">Processing...</p>
  <p id="control"><input class="submit" type="submit" name="ok" value="',sprintf($L['Create_tables'],$qti_database),'" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'block\'"/></p>
  <p>'.($qti_dbsystem==='sqlite' || $qti_dbsystem==='pdo.sqlite' ? 'Host '.$qti_host : '').'</p>
  </form>
  </td>
  <td><div class="setup_help">',$L['Help_2'],'</div></td>
  </tr>
  </table>
  ';
}

// --------
// HTML END
// --------

include 'qti_setup_ft.php';