<?php

// Quickicket 3.0 build:20160703

if ( !isset($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); exit; }
if ( $_GET['term']==='' ) exit;

$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$strRole = ''; if ( isset($_GET['r']) ) $strRole = strtoupper($_GET['r']);
if ( $strRole==='A' ) $strRole = 'role="A" AND ';
if ( $strRole==='M' ) $strRole = '(role="A" OR role="M") AND ';

include 'class/qt_class_db.php';
include 'config.php';

// query

$oDBAJAX = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDBAJAX->error) ) return;

$oDBAJAX->Query('SELECT name,role FROM '.$qti_prefix.'qtiuser WHERE id>0 AND '.$strRole.' UPPER(name) like "'.addslashes(strtoupper($_GET['term'])).'%"');

// format: result item + result info (as a json array with index "rItem","rInfo" )

$arr = array();
$json = array();
while($row=$oDBAJAX->GetRow())
{
  $arr[] = $row['name'];
  $json[] = array('rItem'=>$row['name'],'rInfo'=>$row['role']);
  if ( count($json)>=10 ) break;
}

if ( count($json)<8 )
{
  $oDBAJAX->Query('SELECT name,role FROM '.$qti_prefix.'qtiuser WHERE id>0 AND '.$strRole.' UPPER(name) like "%'.addslashes(strtoupper($_GET['term'])).'%"');
  while($row=$oDBAJAX->GetRow())
  {
    if ( !in_array($row['name'],$arr) )
    {
      $arr[] = $row['name'];
      $json[] = array('rItem'=>$row['name'],'rInfo'=>$row['role']);
      if ( count($json)>=10 ) break;
    }
  }
}

// error handling
if ( empty($json) ) $json[]=array('rItem'=>'','rInfo'=>$e0.', '.$e1);

// response

echo json_encode($json);