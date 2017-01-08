<?php

// QuickTicket 3.0 build:20160703

if ( !isset($_GET['term']) || $_GET['term']==='' ) { echo json_encode(array(array('rItem'=>'configuration error','rInfo'=>'','rSelect'=>''))); exit; }

$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'Try without options'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$s = '*'; if ( isset($_GET['s']) ) $s = $_GET['s'];
if ( $s==='' || $s==='-1' ) $s='*';
$v2 = 0;
if ( isset($_GET['v2']) ) $v2 = ($_GET['v2']=='true' ? 1 : 0); // 1=in title only, 0=in title or text

include 'class/qt_class_db.php';
include 'config.php';

$oDBAJAX = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDBAJAX->error) ) exit;

$kw = addslashes(strtoupper($_GET['term']));

$strWhere = ($s==='*' ? 't.id>=0' : 't.forum='.$s);
switch($oDBAJAX->type)
{
  case 'pdo.sqlsrv':
  case 'sqlsrv':$strWhere .= ' AND (UPPER(p.title) LIKE "%'.$kw.'%"'.($v2 ? ')' : ' OR UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE "%'.$kw.'%")'); break;
  case 'db2':   $strWhere .= ' AND (UPPER(p.title) LIKE "%'.$kw.'%"'.($v2 ? ')' : ' OR UPPER(p.textmsg2) LIKE "%'.$kw.'%")'); break;
  default:      $strWhere .= ' AND (UPPER(p.title) LIKE "%'.$kw.'%"'.($v2 ? ')' : ' OR UPPER(p.textmsg) LIKE "%'.$kw.'%")'); break;
}

// queryr

$oDBAJAX->Query('SELECT t.*,p.title,p.textmsg,s.numfield,p.type as posttype FROM '.$qti_prefix.'qtitopic t INNER JOIN '.$qti_prefix.'qtipost p ON p.topic=t.id INNER JOIN '.$qti_prefix.'qtiforum s ON s.id = t.forum WHERE '.$strWhere);

$arr = array();
while($row=$oDBAJAX->GetRow())
{

  $format = empty($row['numfield']) ? '%03s' : $row['numfield'];
  $ref = sprintf($format,$row['numid']);
  $id = (int)$row['id'];
  $image = 'envelope';
  if ( $row['posttype']==='R' ) $image='reply';
  if ( $row['type']==='I' ) $image='check';
  if ( $row['type']==='A' ) $image='thumb-tack fa-rotate-270';

  if ( strpos(strtoupper($row['title']),strtoupper($_GET['term'])) !== false ) $row['textmsg'] = $row['title']; // when title contains the term, use title instead of textmsg
  $n = stripos($row['textmsg'],$_GET['term']);
  if ($n<0) continue;
  if ($n>10) { $n-=10; } else { $n=0; }
  $str = substr($row['textmsg'],$n,25);
  if ( $n>0 ) $str = '&hellip;'.$str;
  if ( isset($row['textmsg'][$n+25]) ) $str .='&hellip;';
  if ( !isset($arr[$id]) )
    $arr[$id] = array(
      'rItem'=>$ref,
      'rInfo'=>$str,
      'rSelect'=>str_replace('&hellip;','',$str),
      'rImage'=>'<i class="ajax fa fa-'.$image.'"></i>' );
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