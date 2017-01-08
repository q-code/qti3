<?php

// QuickTicket 3.0 build:20160703

session_start();

if ( !isset($_SESSION['qti_setup_lang']) ) $_SESSION['qti_setup_lang']='en';
$strLang=$_SESSION['qti_setup_lang']; // remember in ordrer to restore after reset (end of page)

include 'qti_lang_'.$strLang.'.php';
include '../bin/config.php';
include '../bin/class/qt_class_db.php';

$strAppl = 'QuickTicket';
$strPrevUrl = 'qti_setup_2.php';
$strNextUrl = '../qti_login.php?dfltname=Admin';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Finish'];
$strMessage = '';

// CHECK DB VERSION (in case of update)

$oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br />Check that server is up and running.<br />Check that the settings in the file <b>bin/config.php</b> are correct for your database.</p>');

$oDB->Query('SELECT setting FROM '.$qti_prefix.'qtisetting WHERE param="version"');
$row=$oDB->Getrow();

// UPGRADE 1.9 TO 2.0

if ( $row['setting']=='1.9' )
{
  $oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("tags", "M", "1")');
  $oDB->Exec('DELETE FROM '.$qti_prefix.'qtisetting WHERE param="javamail"');

  switch($oDB->type)
  {
  case 'mysql4':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD sortfield varchar(24)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD logo varchar(24)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD stats varchar(255)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD tags varchar(255)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD photo varchar(24)');
    break;
  case 'sqlite':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD sortfield text');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD logo text');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD stats text');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD tags text');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD photo text');
    break;
  case 'oci':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD sortfield varchar2(24)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD logo varchar2(24)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD stats varchar2(255)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD tags varchar2(255)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD photo varchar2(24)');
    break;
  default:
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD sortfield varchar(24)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD logo varchar(24)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD stats varchar(255)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD tags varchar(4000)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD photo varchar(24)');
    break;
  }
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="2.0" WHERE param="version"');

  // update section stats

  $oDB->Query('SELECT id,topics,replies FROM '.$qti_prefix.'qtiforum');
  $arrList = array();
  while($row = $oDB->Getrow()) { $arrList[]=$row; }
  foreach($arrList as $arr) { $oDB->Exec('UPDATE '.$qti_prefix.'qtiforum SET stats="topics='.(empty($arr['topics']) ? '0' : $arr['topics']).';replies='.(empty($arr['replies']) ? '0' : $arr['replies']).';tags=0" WHERE id='.$arr['id']); }

  // update photo from avatar

  $oDB->Query('SELECT id,avatar FROM '.$qti_prefix.'qtiuser');
  $arr = array();
  while($row=$oDB->Getrow())
  {
    if ( !empty($row['avatar']) ) $arr[$row['id']]=$row['avatar'];
  }
  foreach($arr as $strKey=>$strValue)
  {
  $oDB->Exec('UPDATE '.$qti_prefix.'qtiuser SET photo="'.$strKey.'.'.$strValue.'" WHERE id='.$strKey);
  }

  // update uploaded document

  $oDB->Query('SELECT id,attach FROM '.$qti_prefix.'qtipost');
  $arr = array();
  while($row=$oDB->Getrow())
  {
    if ( !empty($row['attach']) ) $arr[$row['id']]=$row['attach'];
  }

  foreach($arr as $strKey=>$strValue)
  {
  $oDB->Exec('UPDATE '.$qti_prefix.'qtipost SET attach="'.$strKey.'_'.$strValue.'" WHERE id='.$strKey);
  }

  $row['setting']='2.0';
  $strMessage .= '<p>Database upgraded to 2.0</p>';

}

// UPDAGRADE 2.0 TO 2.1

if ( $row['setting']=='2.0' )
{
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD statusdate text');
    break;
  case 'oci':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD statusdate varchar2(20)');
    break;
  default:
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD statusdate varchar(20)');
    break;
  }
  $oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("sys_change", "0", "1")');
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="2.1" WHERE param="version"');

  $row['setting']='2.1';
  $strMessage .= '<p>Database upgraded to 2.1</p>';
}

// UPDAGRADE 2.1 TO 2.2

if ( $row['setting']=='2.1' )
{
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD modifdate text');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD options text');
    break;
  case 'oci':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD modifdate varchar2(20)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD options varchar2(255)');
    break;
  default:
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD modifdate varchar(20)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiforum ADD options varchar(255)');
    break;
  }

  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="2.2" WHERE param="version"');
  $row['setting']='2.2';
  $strMessage .= '<p>Database upgraded to 2.2</p>';

  // update section options

  $oDB->Query('SELECT id,sortfield,infofield,logo FROM '.$qti_prefix.'qtiforum');
  $arr = array();
  while($row=$oDB->Getrow())
  {
    if ( $row['sortfield']=='lastpostdate' ) $row['sortfield']='0';
    if ( $row['infofield']=='N' ) $row['infofield']='0';
    $arr[$row['id']]='coord=0;order='.(empty($row['sortfield']) ? '0' : $row['sortfield']).';last='.(empty($row['infofield']) ? '0' : $row['infofield']).';logo='.(empty($row['logo']) ? '0' : $row['logo']);
  }
  foreach($arr as $strKey=>$strValue)
  {
    $oDB->Exec('UPDATE '.$qti_prefix.'qtiforum SET options="'.$strValue.'" WHERE id='.$strKey);
  }

  // update section stats

  foreach($arr as $strKey=>$strValue)
  {
    $oDB->Query('SELECT count(*) as countid FROM '.$qti_prefix.'qtitopic WHERE forum='.$strKey);
    $row = $oDB->Getrow();
    $str = 'topics='.$row['countid'];
    $oDB->Query('SELECT count(*) as countid FROM '.$qti_prefix.'qtitopic WHERE status="Z" AND forum='.$strKey.' AND type="T"');
    $row = $oDB->Getrow();
    $str .= ';topicsZ='.$row['countid'];
    $oDB->Query('SELECT count(*) as countid FROM '.$qti_prefix.'qtipost WHERE forum='.$strKey.' AND (type="R" OR type="F")');
    $row = $oDB->Getrow();
    $str .= ';replies='.$row['countid'];
    $oDB->Query('SELECT count(*) as countid FROM '.$qti_prefix.'qtipost p INNER JOIN '.$qti_prefix.'qtitopic t ON p.topic=t.id WHERE p.forum='.$strKey.' AND p.type<>"P" AND t.status="Z"');
    $row = $oDB->Getrow();
    $str .= ';repliesZ='.$row['countid'];
    $oDB->Query('SELECT count(*) as countid FROM '.$qti_prefix.'qtitopic WHERE tags<>"" AND forum='.$strKey);
    $row = $oDB->Getrow();
    $str .= ';tags='.$row['countid'];
    $oDB->Exec('UPDATE '.$qti_prefix.'qtiforum SET stats="'.$str.'" WHERE id='.$strKey);
  }

}

// UPDAGRADE 2.2 TO 2.3

if ( $row['setting']=='2.2' )
{
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="2.3" WHERE param="version"');

  $row['setting']='2.3';
  $strMessage .= '<p>Database upgraded to 2.3</p>';
}

// UPDAGRADE 2.3 TO 2.4

if ( $row['setting']=='2.3' )
{
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="2.4" WHERE param="version"');
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD param text');
    break;
  case 'oci':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD param varchar(255)');
    break;
  default:
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtitopic ADD param varchar(255)');
    break;
  }
  $row['setting']='2.4';
  $strMessage .= '<p>Database upgraded to 2.4</p>';
}

// UPDAGRADE 2.4 TO 2.5

if ( $row['setting']=='2.4' )
{
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="2.5" WHERE param="version"');
  switch($oDB->type)
  {
  case 'sqlite':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD secret_q text');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD secret_a text');
    break;
  case 'oci':
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD secret_q varchar2(255)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD secret_a varchar2(255)');
    break;
  default:
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD secret_q varchar(255)');
    $oDB->Exec('ALTER TABLE '.$qti_prefix.'qtiuser ADD secret_a varchar(255)');
    break;
  }
  $row['setting']='2.5';
  $strMessage .= '<p>Database upgraded to 2.5</p>';
}

if ( $row['setting']=='2.5' )
{
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="3.0" WHERE param="version"');
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET param="show_welcome" WHERE param="sys_welcome"');
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET param="posts_per_item" WHERE param="posts_per_topic"');
  $oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET param="items_per_page" WHERE param="topics_per_page"');
  $row['setting']='3.0';
  $strMessage .= '<p>Database upgraded to 3.0</p>';
}

// --------
// HTML START
// --------

include 'qti_setup_hd.php';

if (!empty($strMessage) ) echo $strMessage;

if ( isset($_SESSION['qtiInstalled']) )
{
echo '<p>Database 3.0 in place.</p>';
echo '<p>',$L['S_install_exit'],'</p>';
echo '<div style="width:350px; padding:10px; border-style:solid; border-color:#FF0000; border-width:1px; background-color:#EEEEEE">',$L['End_message'],'<br />',$L['User'],': <b>Admin</b><br />',$L['Password'],': <b>Admin</b><br /></div><br />';
}
else
{
echo $L['N_install'];
}

echo '<p><a href="../check.php">',$L['Check_install'],'</a></p>';

// --------
// HTML END
// --------

include 'qti_setup_ft.php';

// --------
// SESSION DESTROY
// --------

$_SESSION=array();
session_destroy();
if ( isset($qti_install) ) { define('QT','qti'.substr($qti_install,-1)); } else { define('QT','qti'); }
if ( isset($_COOKIE[QT.'_cookname']) ) setcookie(QT.'_cookname', '', time()+60*60*24*100, '/');
if ( isset($_COOKIE[QT.'_cookpass']) ) setcookie(QT.'_cookpass', '', time()+60*60*24*100, '/');
$_SESSION['qti_setup_lang']=$strLang; // restore language after reset