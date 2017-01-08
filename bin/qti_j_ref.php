<?php

// QuickTicket 3.0 build:20160703

if ( !isset($_GET['term']) ) { echo json_encode(array(array('rItem'=>'configuration error','rInfo'=>'no term','rSelect'=>''))); return; }
if ( $_GET['term']==='' ) return;

$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'Try without options'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$s = '*'; if ( isset($_GET['s']) ) $s = $_GET['s'];
if ( $s==='' || $s==='-1' ) $s='*';

include 'class/qt_class_db.php';
include 'config.php';

// queryr

$oDBAJAX = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDBAJAX->error) ) exit;

$id = (int)$_GET['term'];
$strWhere = ($s==='*' ? 't.id>=0' : 't.forum='.$s).' AND t.numid='.$id;

$oDBAJAX->Query('SELECT t.*,p.title,s.numfield FROM '.$qti_prefix.'qtitopic t INNER JOIN '.$qti_prefix.'qtipost p ON t.firstpostid = p.id INNER JOIN '.$qti_prefix.'qtiforum s ON s.id = t.forum WHERE '.$strWhere);

$arr = array();
while($row=$oDBAJAX->GetRow())
{
  $format = empty($row['numfield']) ? '%03s' : $row['numfield'];
  $ref = sprintf($format,$row['numid']);
  $id = (int)$row['id'];
  $image = 'envelope';
  //if ( $row['posttype']==='R' ) $image='reply';
  if ( $row['type']==='I' ) $image='check';
  if ( $row['type']==='A' ) $image='thumb-tack fa-rotate-270';
  if ( !isset($arr[$id]) )
  {
    $arr[$id] = array(
      'rItem'=>$ref,
      'rInfo'=>substr($row['title'],0,25).(isset($row['title'][25]) ? '&hellip;' : ''),
      'rSelect'=>'#'.$id,
      'rImage'=>'<i class="ajax fa fa-'.$image.'"></i>'
      );
  }
  if ( count($arr)>8 ) break;
}

// Response

if ( count($arr)==0 )
{
  echo json_encode( array(array('rItem'=>$e0.'.', 'rInfo'=>'', 'rSelect'=>'')) );
}
else
{
  echo json_encode( $arr );
}