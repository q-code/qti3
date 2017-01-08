<?php // QuickTicket 3 build:20160703

define('APP', 'qti'); // application file prefix (this is required and cannot be changed)
if ( !isset($qti_root) ) $qti_root='';

// -----------------
// DB and constant config
// -----------------
require $qti_root.'bin/config.php';
if ( isset($qti_install) ) { define('QT',APP.substr($qti_install,-1)); } else { define('QT',APP); }
require $qti_root.'bin/config_web.php';
include $qti_root.'bin/config_sse.php';

// -----------------
// System constants (this CANNOT be changed by webmasters)
// -----------------
if ( !defined('PHP_VERSION_ID') ) { $version=explode('.',PHP_VERSION); define('PHP_VERSION_ID',($version[0]*10000+$version[1]*100+$version[2])); }
define('TABDOMAIN', $qti_prefix.'qtidomain');
define('TABSECTION', $qti_prefix.'qtiforum');
define('TABUSER', $qti_prefix.'qtiuser');
define('TABTOPIC', $qti_prefix.'qtitopic');
define('TABPOST', $qti_prefix.'qtipost');
define('TABSTATUS', $qti_prefix.'qtistatus');
define('TABSETTING', $qti_prefix.'qtisetting');
define('TABLANG', $qti_prefix.'qtilang');
define('QTIVERSION', '3.0 build:20160703');
define('QSEL', ' selected="selected"');
define('QCHE', ' checked="checked"');
define('QDIS', ' disabled="disabled"');
define('N', "\n");
define('S', '&nbsp;');
define('START', 1);
define('END', -1);
define('JQUERY_OFF', 'bin/js/jquery.min.js'); // jQuery resource when offline. This will be used if CDN is not possible.
define('JQUERYUI_OFF', 'bin/js/jquery-ui.min.js');
define('JQUERYUI_CSS_OFF', 'bin/css/jquery-ui-min.css');

// -----------------
// Class and functions
// -----------------
require $qti_root.'bin/qt_lib_sys.php';
require $qti_root.'bin/qt_lib_txt.php';
require $qti_root.'bin/qti_fn_base.php';
require $qti_root.'bin/qti_fn_html.php';
require $qti_root.'bin/class/qt_class_db.php';
require $qti_root.'bin/class/qt_class_mem.php';
require $qti_root.'bin/class/qt_abstracts.php';
require $qti_root.'bin/class/qt_class_html.php';
require $qti_root.'bin/class/qt_class_table.php';
require $qti_root.'bin/class/qti_class_sys.php';
require $qti_root.'bin/class/qti_class_user.php';
require $qti_root.'bin/class/qti_class_sec.php';
require $qti_root.'bin/class/qti_class_topic.php';
require $qti_root.'bin/class/qti_class_post.php';

// -----------------
//  Installation wizard (if file exists)
// -----------------
if ( empty($qti_install) && file_exists('install/index.php') )
{
  echo 'QuickTicket ',QTIVERSION,' <a href="install/index.php">starting installation</a>...';
  echo '<meta http-equiv="REFRESH" content="1;url=install/index.php">';
  exit;
}

// ----------------
// Initialise Classes and Memcache
// ----------------
$error = ''; // Required when server uses register_global_on
$warning = ''; // Required when server uses register_global_on
$arrExtData = array(); // Can be used by extensions

if ( !defined('MEMCACHE_HOST') ) define('MEMCACHE_HOST',false);
$memcache = sMem::CreateCache($warning); // returns memcache object or false (can also issue a $warning message if connection failed)

$oDB  = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br />Please contact the webmaster for further information.</p><p>The webmaster must check that server is up and running, and that the settings in the config file are correct for the database.</p>');

$oVIP = new cVIP();

// ----------------
// Load system parameters (attention some parameters can be reserved, thus not loaded)
// ----------------
if ( !isset($_SESSION[QT]) || !isset($_SESSION[QT]['site_name']) ) GetSettings('',true);

// check major parameters

if ( empty($_SESSION[QT]['skin_dir']) || strpos($_SESSION[QT]['skin_dir'],'skin/')===false ) $_SESSION[QT]['skin_dir']=$qti_root.'skin/default';
if ( empty($_SESSION[QT]['language']) ) $_SESSION[QT]['language']='english';
if ( substr($_SESSION[QT]['skin_dir'],0,5)!='skin/' ) $_SESSION[QT]['skin_dir'] = 'skin/'.$_SESSION[QT]['skin_dir'];

// change language if required (by coockies or by the menu)

$str=QTiso();
if ( isset($_COOKIE[QT.'_cooklang']) ) $str=substr($_COOKIE[QT.'_cooklang'],0,2);
if ( isset($_GET['lx']) ) $str=substr($_GET['lx'],0,2);
if ( $str!=QTiso() && !empty($str) )
{
  include $qti_root.'bin/qti_lang.php';
  if ( array_key_exists($str,$arrLang) )
  {
    $_SESSION[QT]['language'] = $arrLang[$str][2];
    if ( isset($_COOKIE[QT.'_cooklang']) ) setcookie(QT.'_cooklang', $str, time()+60*60*24*100, '/');
    // unset dictionnaries
    $_SESSION['L'] = array();
    sMem::Clear('sys_domains');
    sMem::Clear('sys_sections');
    sMem::Clear('sys_statuses');
  }
  else
  {
    die('Wrong iso code language');
  }
}

// ----------------
// Initialise variable
// ----------------
if ( !isset($_SESSION[QT]['viewmode']) ) $_SESSION[QT]['viewmode']=strtolower(QTI_DFLT_VIEWMODE);
if ( !isset($_SESSION[QT]['userlang']) ) $_SESSION[QT]['userlang']='1';
if ( !isset($_SESSION['L']) ) $_SESSION['L'] = array();

QTcheckL('index;domain;sec;secdesc'); // check and set $_SESSION['L'][...]

include $qti_root.GetLang().'qti_main.php';
include $qti_root.GetLang().'qti_icon.php';

// ----------------
// Load memcache
// ----------------
sMem::Get('sys_domains');
sMem::Get('sys_sections'); // store all sections
sMem::Get('sys_statuses');
sMem::Get('sys_types');
sMem::Get('sys_members');
sMem::Get('sys_lastmember');

// ----------------
// Default HTML settings
// ----------------
$oHtml = new cHtml();
$oHtml->html = '<html xmlns="http://www.w3.org/1999/xhtml" dir="'.QT_HTML_DIR.'" xml:lang="'.QT_HTML_LANG.'" lang="'.QT_HTML_LANG.'" class="no-js">';
$oHtml->title = QTstrh($_SESSION[QT]['site_name']);
$oHtml->metas['charset'] = '<meta charset="'.QT_HTML_CHAR.'" />';
$oHtml->metas['description'] = '<meta name="description" content="QT QuickTicket" />';
$oHtml->metas['keywords'] = '<meta name="keywords" content="quickticket,trouble ticket,knowledge,qt-cute,OpenSource" />';
$oHtml->metas['author'] = '<meta name="author" content="qt-cute.org" />';
$oHtml->metas['viewport'] = '<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5" />';
$oHtml->links['icon'] = '<link rel="shortcut icon" href="'.$_SESSION[QT]['skin_dir'].'/qti_icon.ico" />';
$oHtml->links['cssBase'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qt_base.css" />'; // attention qt_base
$oHtml->links['cssLayout'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_layout.css" />';
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_main.css" />';
$oHtml->links['cssWebicon'] = '<link rel="stylesheet" href="'.WEBICONS_CDN.'">';
$oHtml->scripts['base'] = '<script type="text/javascript" src="bin/js/qti_base.js"></script>';

// ----------------
// Check user in case of coockie login
// ----------------
if ( $oVIP->coockieconfirm )
{
  include 'qti_inc_hd.php';
  $oHtml->Msgbox(L('Login'));
  echo '<h2>'.L('Welcome').' '.sUser::Name().' ?</h2><p><a href="'.Href($oVIP->exiturl).'">'.L('Continue').'</a>&nbsp; &middot; &nbsp;<a href="'.Href('qti_login.php?a=out').'">'.sprintf(L('Welcome_not'),sUser::Name()).'</a></p>';
  $oHtml->Msgbox(-1);
  include 'qti_inc_ft.php';
  exit;
}

// -----------------
//  Time setting (for PHP >=5.2)
// -----------------
if ( PHP_VERSION_ID>=50200 ) {
if ( isset($_SESSION[QT]['defaulttimezone']) ) {
if ( $_SESSION[QT]['defaulttimezone']!=='' ) {

date_default_timezone_set($_SESSION[QT]['defaulttimezone']);

}}}
