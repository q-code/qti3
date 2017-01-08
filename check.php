<?php

// QuickTicket 3.0 build:20160703

// --------
// HTML start
// --------
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">

<head>
<title>Quickticket installation checker</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<meta name="description" content="QT QuickTicket trouble ticket management" />
<meta name="keywords" content="Ticket,issuelist,troubleticket,management,faq,knowledge,qt-cute,OpenSource" />
<meta name="author" content="qt-cute.org" />
<meta name="language" content="en,fr,nl" />
<link rel="stylesheet" href="admin/qti_main.css">
<style type="text/css">
p.check {margin:5px 0 0 0; padding:0}
p.endcheck {margin:5px 0 0 0; padding:5px; border:solid 1px #aaaaaa}
span.ok {color:#00aa00; background-color:inherit}
span.nok {color:#ff0000; background-color:inherit}
div.footer_copy {width:100%; text-align:right}
a.footer_copy {color:blue; background-color:inherit; font-size:8pt}
</style>
</head>';

echo '<body>

<!-- PAGE CONTROL -->
<div class="qti_page">
<table class="qti_page" width="700"  style="margin:5px">
<tr class="qti_page">
<td class="qti_page">

<!-- HEADER BANNER -->
<div id="banner"><img src="admin/qti_logo.gif" width="150" height="50" style="border-width:0" alt="QuickTicket" title="QuickTicket" /></div>

<!-- BODY MAIN -->
<table width="100%"  style="border:1px solid #AAAAAA;">
<tr>
<td style="padding:5px 10px 5px 10px;">

';

// --------
// 1 CONFIG
// --------

echo '<p style="margin:0;text-align:right">QuickTicket 3.0 build:20160703</p>';

echo '<h1>Checking your configuration</h1>';

$error = '';

// 1 file exist

  echo '<p class="check">Checking installed files... ';

  if ( !file_exists('bin/config.php') ) $error .= 'File <b>config.php</b> is not in the <b>bin</b> directory. Communication with database is impossible.<br />';
  if ( !file_exists('bin/config_web.php') ) $error .= 'File <b>config_web.php</b> is not in the <b>bin</b> directory. Constants are missing.<br />';
  if ( !file_exists('bin/qt_lib_sys.php') ) $error .= 'File <b>qt_lib_sys.php</b> is not in the <b>bin</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/qt_lib_txt.php') ) $error .= 'File <b>qt_lib_txt.php</b> is not in the <b>bin</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/init.php') ) $error .= 'File <b>init.php</b> is not in the <b>bin</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/qti_fn_base.php') ) $error .= 'File <b>qti_fn_base.php</b> is not in the <b>bin</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/qti_fn_html.php') ) $error .= 'File <b>qti_fn_html.php</b> is not in the <b>bin</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/class/qt_class_db.php') ) $error .= 'File <b>qt_class_db.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/class/qt_abstracts.php') ) $error .= 'File <b>qt_abstracts.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/class/qti_class_sec.php') ) $error .= 'File <b>qti_class_sec.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/class/qti_class_topic.php') ) $error .= 'File <b>qti_class_topic.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/class/qti_class_post.php') ) $error .= 'File <b>qti_class_post.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br />';
  if ( !file_exists('bin/class/qti_class_sys.php') ) $error .= 'File <b>qti_class_sys.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br />';

  if ( empty($error) )
  {
  echo '<span class="ok">Main files found.</span></p>';
  }
  else
  {
  die('<span class="nok">'.$error.'</span></p>');
  }

// 2 config is correct

  echo '<p class="check">Checking config.php... ';

  include 'bin/config.php';

  if ( !isset($qti_dbsystem) ) $error .= 'Variable <b>$qti_dbsystem</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';
  if ( !isset($qti_host) ) $error .= 'Variable <b>$qti_host</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';
  if ( !isset($qti_database) ) $error .= 'Variable <b>$qti_database</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';
  if ( !isset($qti_prefix) ) $error .= 'Variable <b>$qti_prefix</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';
  if ( !isset($qti_user) ) $error .= 'Variable <b>$qti_user</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';
  if ( !isset($qti_pwd) ) $error .= 'Variable <b>$qti_pwd</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';

  if ( !empty($error) )  die('<span class="nok">'.$error.'</span>');

  // check db type
  if ( !in_array($qti_dbsystem,array('pdo.mysql','mysql','pdo.sqlsrv','sqlsrv','pdo.pg','pg','pdo.firebird','ibase','pdo.sqlite','sqlite','db2','pdo.oci','oci')) ) die('Unknown db type '.$qti_dbsystem);
  // check other values
  if ( empty($qti_host) ) $error .= 'Variable <b>$qti_host</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';
  if ( empty($qti_database) ) $error .= 'Variable <b>$qti_database</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br />';
  if ( !empty($error) ) die($error);

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  die('<span class="nok">'.$error.'</span></p>');
  }

// 3 test db connection

  echo '<p class="check">Connecting to database... ';

  include 'bin/class/qt_class_db.php';

  $oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);

  if ( empty($oDB->error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  die('<span class="nok">Connection with database failed.<br />Check that server is up and running.<br />Check that the settings in the file <b>bin/config.php</b> are correct for your database.</span></p>');
  }

// end CONFIG tests

  echo '<p class="endcheck">Configuration tests completed successfully.</p>';

// --------
// 2 DATABASE
// --------

$error = '';

echo '
<h1>Checking your database design</h1>
';

// 1 setting table

  echo '<p class="check">Checking setting table... ';

  $oDB->Query('SELECT setting FROM '.$qti_prefix.'qtisetting WHERE param="version"');
  if ( !empty($oDB->error) ) die("<br /><font color=red>Problem with table ".$qti_prefix."qtisetting</font>");
  $row = $oDB->Getrow();
  $strVersion = $row['setting'];

  echo '<span class="ok">Table [',$qti_prefix,'qtisetting] exists. Version is ',$strVersion,'.</span>';
  if ( !in_array(substr($strVersion,0,3),array('2.4','2.5','3.0')) ) die('<span class="nok">But data in this table refers to an incompatible version (must be version 2.5).</span></p>');
  echo '</p>';

// 2 domain table

  echo '<p class="check">Checking domain table... ';

  $oDB->Query('SELECT count(id) as countid FROM '.$qti_prefix.'qtidomain');
  if ( !empty($oDB->error) ) die("<br /><font color=red>Problem with table ".$qti_prefix."qtidomain</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qti_prefix,'qtidomain] exists. ',$intCount,' domain(s) found.</span></p>';

// 3 section table

  echo '<p class="check">Checking section table...';

  $oDB->Query('SELECT count(id) as countid FROM '.$qti_prefix.'qtiforum');
  if ( !empty($oDB->error) ) die("<br /><font color=red>Problem with table ".$qti_prefix."qtiforum</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qti_prefix,'qtiforum] exists. ',$intCount,' section(s) found.</span></p>';

// 4 topic table

  echo '<p class="check">Checking topic table...';

  $oDB->Query('SELECT count(id) as countid FROM '.$qti_prefix.'qtitopic');
  if ( !empty($oDB->error) ) die("<br /><font color=red>Problem with table ".$qti_prefix."qtitopic</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qti_prefix,'qtitopic] exists. ',$intCount,' topic(s) found.</span></p>';

// 5 post table

  echo '<p class="check">Checking post table...';

  $oDB->Query('SELECT count(id) as countid FROM '.$qti_prefix.'qtipost');
  if ( !empty($oDB->error) ) die("<br /><font color=red>Problem with table ".$qti_prefix."qtipost</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qti_prefix,'qtipost] exists. ',$intCount,' post(s) found.</span></p>';

// 6 user table

  echo '<p class="check">Checking user table... ';

  $oDB->Query('SELECT count(id) as countid FROM '.$qti_prefix.'qtiuser');
  if ( !empty($oDB->error) ) die("<br /><font color=red>Problem with table ".$qti_prefix."qtiuser</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qti_prefix,'qtiuser] exists. ',$intCount,' user(s) found.</span></p>';

// end DATABASE tests

  echo '<p class="endcheck">Database tests completed successfully.</p>';

// --------
// 3 LANGUAGE AND SKIN
// --------

$error = '';

echo '
<h1>Checking language and skin options</h1>
';

  echo '<p class="check">Files... ';

  $oDB->Query('SELECT setting FROM '.$qti_prefix.'qtisetting WHERE param="language"');
  $row = $oDB->Getrow();
  $str = $row['setting'];
  if ( empty($str) ) $error .= 'Setting <b>language</b> is not defined in the setting table. Application can only work with english.<br />';
  if ( !file_exists("language/$str/qti_main.php") ) $error .= "File <b>qti_main.php</b> is not in the <b>language/xxxx</b> directory.<br />";
  if ( !file_exists("language/$str/qti_adm.php") ) $error .= "File <b>qti_adm.php</b> is not in the <b>language/xxxx</b> directory.<br />";
  if ( !file_exists("language/$str/qti_icon.php") ) $error .= "File <b>qti_icon.php</b> is not in the <b>language/xxxx</b> directory.<br />";
  if ( !file_exists("language/$str/qti_reg.php") ) $error .= "File <b>qti_reg.php</b> is not in the <b>language/xxxx</b> directory.<br />";
  if ( !file_exists("language/$str/qti_zone.php") ) $error .= "File <b>qti_zone.php</b> is not in the <b>language/xxxx</b> directory.<br />";
  if ( $str!='english' )
  {
  if ( !file_exists("language/english/qti_main.php") ) $error .= "File <b>qti_main.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br />";
  if ( !file_exists("language/english/qti_adm.php") )  $error .= "File <b>qti_adm.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br />";
  if ( !file_exists("language/english/qti_icon.php") ) $error .= "File <b>qti_icon.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br />";
  if ( !file_exists("language/english/qti_reg.php") )  $error .= "File <b>qti_reg.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br />";
  if ( !file_exists("language/english/qti_zone.php") ) $error .= "File <b>qti_zone.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br />";
  }

  $oDB->Query('SELECT setting FROM '.$qti_prefix.'qtisetting WHERE param="skin_dir"');
  $row = $oDB->Getrow();
  $str = $row['setting']; if ( substr($str,0,5)!='skin/' ) $str = 'skin/'.$str;

  if ( empty($str) ) $error .= 'Setting <b>skin</b> is not defined in the setting table. Application will not display correctly.<br />';
  if ( !file_exists("$str/qti_main.css") ) $error .= "File <b>qti_main.css</b> is not in the <b>skin/xxxx</b> directory.<br />";
  if ( !file_exists("skin/default/qti_main.css") ) $error .= "File <b>qti_main.css</b> is not in the <b>skin/default</b> directory. Default skin is mandatory.<br />";

  if ( empty($error) )
  {
  echo '<span class="ok">Ok.</span>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span>';
  }

  echo '</p>';

// end LANGUAGE AND SKIN tests

  echo '<p class="endcheck">Language and skin files tested.</p>';

// --------
// 4 ADMINISTRATION TIPS
// --------

$error = '';

echo '
<h1>Administration tips</h1>
';

// 1 admin email

  echo '<p class="check">Email setting... ';

  $oDB->Query('SELECT setting FROM '.$qti_prefix.'qtisetting WHERE param="admin_email"');
  $row = $oDB->Getrow();
  $strMail = $row['setting'];
  if ( empty($strMail) )
  {
  $error .= 'Administrator e-mail is not yet defined. It\'s mandatory to define it!';
  }
  else
  {
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$strMail) ) $error .= 'Administrator e-mail format seams incorrect. Please check it';
  }

  if ( !empty($error) ) echo '<span class="nok">'.$error.'</span></p>';
  echo '<span class="ok">Done.</span></p>';
  $error = '';

// 2 admin password

  echo '<p class="check">Security check... ';

  $oDB->Query('SELECT pwd FROM '.$qti_prefix.'qtiuser WHERE id=1');
  $row = $oDB->Getrow();
  $strPwd = $row['pwd'];
  If ( $strPwd==sha1('Admin') ) $error .= 'Administrator password is still the initial password. It\'s recommended to change it !<br />';

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span></p>';
  }
  $error = '';

// 3 site url

  echo '<p class="check">Site url... ';

  $oDB->Query('SELECT setting FROM '.$qti_prefix.'qtisetting WHERE param="site_url"');
  $row = $oDB->Getrow();
  $strText = trim($row['setting']);
  if ( substr($strText,0,7)!="http://" && substr($strText,0,8)!="https://" )
  {
    $error .= 'Site url is not yet defined (or not starting by http://). It\'s mandatory to define it !<br />';
  }
  else
  {
    $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? 'http://' : 'https://' ).$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $strURL = substr($strURL,0,-10);
    if ( $strURL!=$strText ) $error .= 'Site url seams to be different that the current url. Please check it<br />';
  }

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span></p>';
  }
  $error = '';

// 4 avatar/upload folder permission

  echo '<p class="check">Folder permissions... ';

  if ( !is_dir('avatar') )
  {
    $error .= 'Directory <b>avatar</b> not found.<br />Please create this directory and make it writeable (chmod 777) if you want to allow avatars.<br />';
  }
  else
  {
    if ( !is_readable('avatar') ) $error .= 'Directory <b>avatar</b> is not readable. Change permissions (chmod 777) if you want to allow avatars.<br />';
    if ( !is_writable('avatar') ) $error .= 'Directory <b>avatar</b> is not writable. Change permissions (chmod 777) if you want to allow avatars.<br />';
  }

  if ( !is_dir('upload') )
  {
    $error .= '>Directory <b>upload</b> not found.<br />Please create this directory and make it writeable (chmod 777) if you want to allow uploads<br />';
  }
  else
  {
    if ( !is_readable('upload') ) $error .= 'Directory <b>upload</b> is not readable. Change permissions (chmod 777) if you want to allow uploads<br />';
    if ( !is_writable('upload') ) $error .= 'Directory <b>upload</b> is not writable. Change permissions (chmod 777) if you want to allow uploads<br />';
  }

  if ( !empty($error) ) echo '<span class="nok">',$error,'</span></p>';
  echo '<span class="ok">Done.</span></p>';
  $error = '';

echo '<p class="endcheck">Administration tips completed.</p>';

// --------
// 5 END
// --------

echo '
<h1>Result</h1>
';
echo 'The checker did not found blocking issues in your configuration.<br />';

  $oDB->Query('SELECT setting FROM '.$qti_prefix.'qtisetting WHERE param="board_offline"');
  $row = $oDB->Getrow();
  $strOff = $row['setting'];
  if ( $strOff=='1' ) echo 'Your board seams well installed, but is currently off-line.<br />Log as Administrator and go to the Administration panel to turn your board on-line.<br />';

echo '<br /><br /><a href="qti_index.php">Go to QuickTicket</a>';

// --------
// HTML END
// --------

echo '<!-- END BODY MAIN -->
</td>
</tr>
</table>
<!-- END BODY MAIN -->

<div class="footer_copy">
<span class="footer_copy">powered by <a href="http://www.qt-cute.org" class="footer_copy">QT-cute</a></span>
</div>

<!-- END PAGE CONTROL -->
</td>
</tr>
</table>
</div>
<!-- END PAGE CONTROL -->

</body>
</html>';