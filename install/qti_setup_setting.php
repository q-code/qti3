<?php

// QuickTicket 2 build:20160703

switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.$qti_prefix.'qtisetting (
  param varchar(24),
  setting varchar(255),
  loaded char(1)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.$qti_prefix.'qtisetting (
  param varchar(24),
  setting varchar(255),
  loaded char(1)
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.$qti_prefix.'qtisetting (
  param varchar(24),
  setting varchar(255),
  loaded char(1)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.$qti_prefix.'qtisetting (
  param text,
  setting text,
  loaded text
  )';
  break;

case 'pdo.firebird':
case 'ibase':
  $strQ='CREATE TABLE '.$qti_prefix.'qtisetting (
  param varchar(24),
  setting varchar(255),
  loaded char(1)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qti_prefix.'qtisetting (
  param varchar(24),
  setting varchar(255),
  loaded char(1)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.$qti_prefix.'qtisetting (
  param varchar2(24),
  setting varchar2(255),
  loaded char(1)
  )';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, firebird, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qti_prefix.'qtisetting',$qti_database,$qti_user),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("version", "3.0", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("board_offline", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("site_name", "QT-cute", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("site_url", "http://", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("home_name", "Home", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("home_url", "http://www.qt-cute.org", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("admin_email", "", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("admin_name", "", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("admin_addr", "", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("topics_per_forum", "1000", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("posts_per_item", "100", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("chars_per_post", "4000", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("lines_per_post", "250", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("time_zone", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_time_zone", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("home_menu", "0", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("posts_delay", "4", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("posts_per_day", "100", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("site_width", "800", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("register_safe", "text", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("smtp_password", "", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("smtp_username", "", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("smtp_host", "", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("use_smtp", "0", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_welcome", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("items_per_page", "20", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("replies_per_page", "20", "1")');
$str='english';
if ( $_SESSION['qti_setup_lang']=='fr' ) $str='francais';
if ( $_SESSION['qti_setup_lang']=='nl' ) $str='nederlands';
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("language", "'.$str.'", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("userlang", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("section_desc", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_banner", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_legend", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("index_name", "Support Centre", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("skin_dir", "default", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("avatar", "gif,jpg,jpeg,png", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("avatar_width", "150", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("avatar_height", "150", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("avatar_size", "30", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("formatdate", "j M Y", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("formattime", "G:i", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_id", "T-%03s", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_back", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("news_on_top", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_closed", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("register_mode", "direct", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("daylight", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("visitor_right", "5", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_quick_reply", "1", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_calendar", "U", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("upload", "U", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("upload_size", "2048", "1")');
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("show_stats", "U", "1")'); //v1.3
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("tags", "U", "1")'); //v2.0
$result=$oDB->Exec('INSERT INTO '.$qti_prefix.'qtisetting VALUES ("unreplied_days", "10", "1")'); //v3.0