<?php

// QuickTicket 3.0 build:20160703

include 'class/qt_class_db.php';
include 'config.php';

if ( !isset($_POST['v']) ) { echo ' '; exit; }
if ( !isset($_POST['f']) ) $_POST['f']='name';
if ( get_magic_quotes_gpc() ) $_POST['v'] = stripslashes($_POST['v']);

if ( strlen($_POST['v'])==0 ) { echo ' '; exit; }

if ( strlen($_POST['v'])<4 )
{
  if ( isset($_POST['e1']) ) { echo $_POST['e1']; } else { echo 'Minium 4 characters'; }
}
else
{
  $oDBAJAX = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
  if ( !empty($oDBAJAX->error) ) return;
  $oDBAJAX->Query('SELECT count(*) as countid FROM '.$qti_prefix.'qtiuser WHERE '.$_POST['f'].'="'.htmlspecialchars(addslashes($_POST['v']),ENT_QUOTES).'"' );
  $row = $oDBAJAX->GetRow();
  if ( $row['countid']>0 )
  {
  if ( isset($_POST['e2']) ) { echo $_POST['e2']; } else { echo 'Already used'; }
  }
  else
  {
  echo ' ';
  }
}