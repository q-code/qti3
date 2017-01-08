<?php

// QuickTicket 3.0 build:20160703

include 'bin/qt_lib_sys.php';
include 'bin/qt_lib_txt.php';
include 'bin/class/qt_class_db.php';
include 'bin/qti_fn_base.php';

// Protection against injection (accept only 3 'lang')
$id = strip_tags($_POST['id']);
$dir = strip_tags($_POST['dir']);
$roles = array('A'=>'Administrator','M'=>'Moderator','U'=>'User','V'=>'Visitor');
if ( isset($_POST['roles']) )
{
  $arr = Explode(';',strip_tags($_POST['roles']));
  if ( isset($arr[0]) ) $roles['A']=$arr[0];
  if ( isset($arr[1]) ) $roles['M']=$arr[1];
  if ( isset($arr[2]) ) $roles['U']=$arr[2];
  if ( isset($arr[3]) ) $roles['V']=$arr[3];
}
$id = intval(substr($id,1));

include 'config.php';

$oDBAJAX = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDBAJAX->error) ) exit;

// query

$oDBAJAX->Query('SELECT * FROM '.$qti_prefix.'qtiuser WHERE id='.$id);
$row = $oDBAJAX->GetRow();

//output the response

$strUserImage = AsUserImg($dir.$row['photo'],$row['name'],'');
if ( empty($row['photo']) && isset($_POST['ph']) ) $strUserImage = '<img src="'.$_POST['ph'].'" title="'.$row['name'].'" alt="(user)">';

echo AsImgBox(
  $strUserImage,
  $row['name'].'<br/>('.(isset($roles[$row['role']]) ? $roles[$row['role']] : 'unknown role').')'.(empty($row['location']) ? '' : '<br/>'.$row['location'])
  );