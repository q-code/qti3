<?php

// QuickTicket 3.0 build:20160703 //!! css id

session_start();

function SqlDrop($strTable,$strConstrain=null)
{
  global $oDB;
  if ( isset($strConstrain) && $oDB->type=='oci' ) $oDB->Exec('ALTER TABLE '.$strTable.' DROP CONSTRAINT '.$strConstrain);
  $oDB->Query('DROP TABLE '.$strTable);
}

// INITIALISATION

include '../bin/class/qt_class_db.php';
include '../bin/config.php';
define ('TABDOMAIN', $qti_prefix.'qtidomain');
define ('TABSECTION', $qti_prefix.'qtiforum');
define ('TABUSER', $qti_prefix.'qtiuser');
define ('TABTOPIC', $qti_prefix.'qtitopic');
define ('TABPOST', $qti_prefix.'qtipost');
define ('TABSTATUS', $qti_prefix.'qtistatus');
define ('TABSETTING', $qti_prefix.'qtisetting');
define ('TABLANG', $qti_prefix.'qtilang');

$strAppl = 'QuickTicket 2.5';
include 'qti_lang_en.php';

// --------
// HTML START
// --------

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
<head>
<title>Uninstalling ',$strAppl,'</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<meta name="description" content="QT QuickTicket trouble ticket management" />
<meta name="keywords" content="Ticket,issuelist,troubleticket,management,faq,knowledge,qt-cute,OpenSource" />
<meta name="author" content="qt-cute.org" />
<meta name="language" content="en,fr,nl" />
<link rel="stylesheet" href="../admin/qti_main.css">
</head>

<body>

<!-- PAGE CONTROL //!!!!! -->
<div class="qti_page">
<table class="qti_page" width="750"  style="margin:5px">
<tr class="qti_page">
<td class="qti_page">

<!-- HEADER BANNER -->
<div id="banner"><img src="qti_logo.gif" width="150" height="50" style="border-width:0" alt="QuickTicket" title="QuickTicket" /></div>

<!-- BODY MAIN -->
<table width="100%"  style="border-style:solid;border-color:#AAAAAA;border-width:1px;">
<tr class="body">
<td class="body">

';

echo '1. <b>Opening database connection</b>... ';

$oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br />Check that server is up and running.<br />Check that the settings in the file <b>bin/config.php</b> are correct for your database.</p>');

echo 'done<br /><br />';

// SUBMITTED

if ( isset($_GET['a']) )
{
  switch($_GET['a'])
  {
  case 'Drop ALL tables':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.$qti_prefix.'qtipost'); echo 'done.<br />';
    echo ' Dropping Topic...'; SqlDrop(TABTOPIC,'pk_'.$qti_prefix.'qtitopic'); echo 'done.<br />';
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.$qti_prefix.'qtiforum'); echo 'done.<br />';
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.$qti_prefix.'qtidomain'); echo 'done.<br />';
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.$qti_prefix.'qtiuser'); echo 'done.<br />';
    echo ' Dropping Status...'; SqlDrop(TABSTATUS,'pk_'.$qti_prefix.'qtistatus'); echo 'done.<br />';
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo 'done.<br />';
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo 'done.<br />';
    break;
  case 'Drop table Post':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.$qti_prefix.'qtipost'); echo 'done.<br />'; break;
  case 'Drop table Topic':
    echo ' Dropping Topic...'; SqlDrop(TABTOPIC,'pk_'.$qti_prefix.'qtitopic'); echo 'done.<br />'; break;
  case 'Drop table Section':
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.$qti_prefix.'qtiforum'); echo 'done.<br />'; break;
  case 'Drop table Domain':
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.$qti_prefix.'qtidomain'); echo 'done.<br />'; break;
  case 'Drop table User':
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.$qti_prefix.'qtiuser'); echo 'done.<br />'; break;
  case 'Drop table Status':
    echo ' Dropping Status...'; SqlDrop(TABSTATUS,'pk_'.$qti_prefix.'qtistatus'); echo 'done.<br />'; break;
  case 'Drop table Setting':
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo 'done.<br />'; break;
  case 'Drop table Lang':
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo 'done.<br />'; break;
  case 'Add table Post':
    include 'qti_setup_post.php'; echo $_GET['a'],' done'; break;
  case 'Add table Topic':
    include 'qti_setup_topic.php'; echo $_GET['a'],' done'; break;
  case 'Add table Sectionm':
    include 'qti_setup_section.php'; echo $_GET['a'],' done'; break;
  case 'Add table Domain':
    include 'qti_setup_domain.php'; echo $_GET['a'],' done'; break;
  case 'Add table User':
    include 'qti_setup_user.php'; echo $_GET['a'],' done'; break;
  case 'Add table Status':
    include 'qti_setup_status.php'; echo $_GET['a'],' done'; break;
  case 'Add table Setting':
    include 'qti_setup_setting.php'; echo $_GET['a'],' done'; break;
  case 'Add table Lang':
    include 'qti_setup_lang.php'; echo $_GET['a'],' done'; break;
  }
}

// Tables do drop

echo '<br />2. <b>Drop the tables</b><br />';

echo '<form action="qti_droptables.php" method="get">';
echo '<input type="submit" name="a" value="Drop ALL tables" /> from the database ',$qti_database,'<br /><br />';
echo '<input type="submit" name="a" value="Drop table Post" /> ',TABPOST,'<br />';
echo '<input type="submit" name="a" value="Drop table Topic" /> ',TABTOPIC,'<br />';
echo '<input type="submit" name="a" value="Drop table User" /> ',TABUSER,'<br />';
echo '<input type="submit" name="a" value="Drop table Section" /> ',TABSECTION,'<br />';
echo '<input type="submit" name="a" value="Drop table Domain" /> ',TABDOMAIN,'<br />';
echo '<input type="submit" name="a" value="Drop table Status" /> ',TABSTATUS,'<br />';
echo '<input type="submit" name="a" value="Drop table Setting" /> ',TABSETTING,'<br />';
echo '<input type="submit" name="a" value="Drop table Lang" /> ',TABLANG,'<br /><br />';
echo '<input type="submit" name="a" value="Add table Post" /> ',TABPOST,'<br />';
echo '<input type="submit" name="a" value="Add table Topic" /> ',TABTOPIC,'<br />';
echo '<input type="submit" name="a" value="Add table User" /> ',TABUSER,'<br />';
echo '<input type="submit" name="a" value="Add table Section" /> ',TABSECTION,'<br />';
echo '<input type="submit" name="a" value="Add table Domain" /> ',TABDOMAIN,'<br />';
echo '<input type="submit" name="a" value="Add table Status" /> ',TABSTATUS,'<br />';
echo '<input type="submit" name="a" value="Add table Setting" /> ',TABSETTING,'<br />';
echo '<input type="submit" name="a" value="Add table Lang" /> ',TABLANG,'<br />';
echo '</form>';

echo '<p><a href="qti_setup.php">install &raquo;</a></p>';

// --------
// HTML END
// --------

echo '
<!-- END BODY MAIN -->
</td>
</tr>
</table>
<!-- END BODY MAIN -->

<div id="footer">
powered by <a href="http://www.qt-cute.org" class="footer_menu">QT-cute</a>
</div>

<!-- END PAGE CONTROL -->
</td>
</tr>
</table>
</div>
<!-- END PAGE CONTROL -->

</body>
</html>';