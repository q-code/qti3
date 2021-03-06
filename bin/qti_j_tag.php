<?php

// build:20121228

if ( !isset($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); exit; }
if ( $_GET['term']==='' ) exit;

$e5 = 'Category not yet used'; if ( isset($_GET['e5']) ) $e5 = $_GET['e5'];
$s = '*'; if ( isset($_GET['s']) ) $s = $_GET['s'];
if ( $s==='' || $s==='-1' ) $s='*';
$lang = 'en'; if ( isset($_GET['lang ']) ) $lang  = $_GET['lang'];
$strWhere = 'WHERE t.id>=0';
if ( $s!=='*' ) $strWhere .= ' AND t.forum='.$s;

include 'config.php';
include 'class/qt_class_db.php';

$arr=array(); // primary results where key is the tags and value is the number tickets using the tag
$arr2=array(); // extra results (added at bottom of the primary results)

// Query

$oDBAJAX = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDBAJAX->error) ) return;

// (1) Get existing tags like A* then *A* (attention, sql must use %A% and like A* must be controlled inside each tags)

$oDBAJAX->Query( 'SELECT t.tags FROM '.$qti_prefix.'qtitopic t '.$strWhere.' AND UPPER(t.tags) LIKE "%'.addslashes(strtoupper($_GET['term'])).'%"' );
while($row=$oDBAJAX->GetRow())
{
  $arrTags=explode(';',$row['tags']);
  foreach($arrTags as $tag)
  {
    if ( count($arr)<8 && stripos($tag,$_GET['term'])===0 )
    {
      if ( !isset($arr[$tag]) ) $arr[$tag] = 0;
      ++$arr[$tag];
      continue;
    }
    if ( count($arr2)<5 && stripos($tag,$_GET['term'])>0 )
    {
      if ( !isset($arr2[$tag]) ) $arr2[$tag] = 0;
      ++$arr2[$tag];
    }
  }
}

if ( count($arr)<10 )
{
  include 'qti_fn_tags.php';

  // (2) search in predefined tags for this section
  if ( $s!=='*' )
  {
    $arrTags = TagsRead($lang,$s,false);// search matching in section tags
    foreach($arrTags as $tag=>$strDesc)
    {
      if ( count($arr)>11 ) break;
      if ( isset($arr[$tag]) ) continue;
      if ( stripos($tag,$_GET['term'])===0 ) { $arr[$tag] = 0; continue; }
      if ( count($arr2)<5 && stripos($tag, $_GET['term'])>0 && !isset($arr2[$tag]) ) $arr2[$tag] = 0;
    }
  }

  // (3) search matching in common tags
  if ( count($arr)<10 )
  {
    $arrTags = TagsRead($lang,'*');
    foreach($arrTags as $tag=>$strDesc)
    {
      if ( count($arr)>11 ) break;
      if ( isset($arr[$tag]) ) continue;
      if ( stripos($tag,$_GET['term'])===0 ) { $arr[$tag] = 0; continue; }
      if ( count($arr2)<5 && stripos($tag, $_GET['term'])>0 && !isset($arr2[$tag]) ) $arr2[$tag] = 0;
    }
  }
}

// merge arr+arr2 (max 11)
foreach($arr2 as $tag=>$i) if ( count($arr)<11 ) $arr[$tag]=$i;

// format: result item + result info (as a json array with index "rItem","rInfo" )

$json = array();
if ( count($arr)===0 )
{
  $json[]=array('rItem'=>'','rInfo'=>$e5);
}
else
{
  foreach($arr as $key=>$id) $json[]=array('rItem'=>$key,'rInfo'=>$id);
}

// response
echo json_encode($json);