<?php

// QTI 3.0 build:20160703

// Uri extra arguments

if ( isset($_GET['v']) ) { $v = trim(urldecode(strip_tags($_GET['v']))); } else { $v=''; }
if ( isset($_GET['v2']) ) { $v2 = trim(urldecode(strip_tags($_GET['v2']))); } else { $v2=''; }
if ( isset($_GET['st']) ) { $st = trim(urldecode(strip_tags($_GET['st']))); } else { $st='*'; }
if ( isset($_GET['y']) ) { $intYear = (int)strip_tags($_GET['y']);  } else { $intYear=date('Y'); }
if ( !empty($v) ) $v = str_replace('"','',$v);
if ( !empty($v2) ) $v2 = str_replace('"','',$v2);
if ( !isset($s) || $s==='*' ) $s=-1;
if ( !is_int($s) ) die('Invalid argument $s. Must be [int] or "*"');
if ( !isset($st) ) $st='*';
if ( $s>=0 ) $strWhere = ' WHERE t.forum='.$s;
if ( !isset($strWhere) ) $strWhere = ' WHERE t.forum>=0';
if ( $st!=='*' ) $strWhere .= ' AND t.status="'.$st.'"';

switch($q)
{
case 'ref':

  $oVIP->selfname = $L['Search_by_ref'];
  break;

case 'kw':

  $oVIP->selfname = $L['Search_by_key'];
  if ( empty($v) ) $error = $L['Keywords'].' '.Error(1);
  if ( !empty($v2) ) $v2='1';
  if ( strlen($v)>64 ) die('Invalid argument #v');
  break;

case 'kw2':

  $oVIP->selfname = $L['Search_by_key'];
  if ( empty($v) ) $error = $L['Keywords'].' '.Error(1);
  if ( !empty($v2) ) $v2='1';
  if ( strlen($v)>100 ) die('Invalid argument #v');
  $v = str_replace(' ',QT_HTML_SEPARATOR,$v);
  break;

case 'user':
case 'actor':

  if ( $v=='' ) $error = 'Userid '.Error(1);
  $v = (int)$v;
  if ( $v<0 ) $error = 'Userid '.Error(1);
  $v2 = str_replace('"','',$v2);
  break;

case 'adv': // time status tags

  $oVIP->selfname = L('Search_by_tag');
  if ( empty($v) && empty($v2) ) $error = $L['Time'].' '.$L['Tag'].' '.Error(1);
  $v = trim(urldecode($v));
  if ( strlen($v)>100 ) die('Invalid argument #v');
  if ( $intYear<2000 || $intYear>2100 ) die('Invalid argument #y');
  break;

case 'news':

  $oVIP->selfname = L('Search');
  break;

case 'last':

  $oVIP->selfname = L('Search_by_date');
  break;

}

// stop if error

if ( !empty($error) ) $oHtml->PageMsg( NULL, '<p>'.$error.'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>' );

// QUERY DEFINITION

// Format main argument $v as array of values

$arrV = array();
$arrVlbl = array();
if ( strlen(trim($v))>0 )
{
  $arr = explode(QT_HTML_SEPARATOR,strtoupper(trim($v)));
  foreach($arr as $str) { $str=trim($str); if ( $str!=='' ) $arrV[]=$str; }
  $arrV = array_unique($arrV);
  switch($q)
  {
  case 'user':
  case 'actor': $arrVlbl = array('"'.$v2.'"'); break;
  default: $arrVlbl = array_map( create_function('$n','return \'"\'.$n.\'"\';'), $arrV);  break;
  }
}

// Query definition

switch($q)
{
case 'ref':

  if ( count($arrV)>0 )
  {
    for($i=0;$i<count($arrV);++$i) $arrV[$i]='t.numid='.$arrV[$i];
    $strWhere .= ' AND p.type="P" AND ('.implode(' OR ',$arrV).')';
  }
  $strFrom = ' FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.id=p.topic';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'kw':

  // full word criteria

  for($i=0;$i<count($arrV);++$i)
  {
    switch($oDB->type)
    {
    case 'pdo.sqlsrv':
    case 'sqlsrv': $arrV[$i] = 'UPPER(CAST(p.title AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"'.(empty($v2) ? ' OR UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"' : ''); break;
    case 'db2':    $arrV[$i] = 'UPPER(p.title) LIKE "%'.strtoupper($arrV[$i]).'%"'.(empty($v2) ? ' OR UPPER(p.textmsg2) LIKE "%'.strtoupper($arrV[$i]).'%"' : ''); break;
    default:       $arrV[$i] = 'UPPER(p.title) LIKE "%'.strtoupper($arrV[$i]).'%"'.(empty($v2) ? ' OR UPPER(p.textmsg) LIKE "%'.strtoupper($arrV[$i]).'%"' : ''); break;
    }
  }
  $strFrom   = ' FROM '.TABPOST.' p INNER JOIN '.TABTOPIC.' t ON t.id=p.topic';
  $strWhere .= ' AND ('.implode(' OR ',$arrV).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'kw2':

  // separated words criteria

  $arrVt = array();
  $arrVm = array();

  for($i=0;$i<count($arrV);++$i)
  {
    switch($oDB->type)
    {
    case 'pdo.sqlsrv':
    case 'sqlsrv': $arrVt[$i] = 'UPPER(CAST(p.title AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    default:       $arrVt[$i] = 'UPPER(p.title) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    }
    if ( empty($v2) )
    {
    switch($oDB->type)
    {
    case 'pdo.sqlsrv':
    case 'sqlsrv': $arrVm[$i] = 'UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    case 'db2':    $arrVm[$i] = 'UPPER(p.textmsg2) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    default:       $arrVm[$i] = 'UPPER(p.textmsg) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    }
    }
  }
  $strFrom   = ' FROM '.TABPOST.' p INNER JOIN '.TABTOPIC.' t ON t.id=p.topic';
  $strWhere .= ' AND ('.implode(' AND ',$arrVt).')';
  if ( empty($v2) ) $strWhere .= ' OR ('.implode(' AND ',$arrVm).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'last':

  // get the lastpost date

  $oDB->Query('SELECT max(p.issuedate) as f1 FROM '.TABPOST.' p ');
  $row = $oDB->Getrow();
  if ( empty($row['f1']) ) $row['f1'] = date('Ymd');
  $strDate = DateAdd($row['f1'],-7,'day');

  // query post of this day

  $strFields .= ',p.id as postid,p.textmsg,p.issuedate,p.username';
  $strFrom   = ' FROM '.TABPOST.' p INNER JOIN '.TABTOPIC.' t ON t.id=p.topic';
  $strWhere .= ' AND ';
  switch($oDB->type)
  {
  case 'pdo.pg':
  case 'pg': $strWhere .= 'SUBSTRING(p.issuedate,1,8)>"'.$strDate.'"'; break;
  case 'pdo.firebird':
  case 'ibase': $strWhere .= 'SUBSTRING(p.issuedate FROM 1 FOR 8)>"'.$strDate.'"'; break;
  case 'pdo.sqlite':
  case 'sqlite':
  case 'pdo.oci':
  case 'oci':
  case 'db2': $strWhere .= 'SUBSTR(p.issuedate,1,8)>"'.$strDate.'"'; break;
  default: $strWhere .= 'LEFT(p.issuedate,8)>"'.$strDate.'"';
  }
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'user':

  for($i=0;$i<count($arrV);++$i) $arrV[$i]='p.userid='.$arrV[$i];

  $strFields .= ',p.id as postid,p.textmsg,p.issuedate,p.username';
  $strFrom    = ' FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.id=p.topic ';
  $strWhere  .= ' AND p.type="P" AND ('.implode(' OR ',$arrV).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'actor':

  for($i=0;$i<count($arrV);++$i) $arrV[$i]='t.actorid='.$arrV[$i];

  $strFields .= ',p.id as postid,p.textmsg,p.issuedate,p.username';
  $strFrom    = ' FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.id=p.topic ';
  $strWhere  .= ' AND p.type="P" AND ('.implode(' OR ',$arrV).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'adv':

  // time

  if ( $v2=='m' ) $v2=date('n'); // date this week (by default)
  if ( $v2=='*' )
  {
    $str = 't.firstpostdate>"0"';
  }
  elseif ( $v2=='y' )
  {
    // this year
    switch($oDB->type)
    {
    case 'pg': $str = 'SUBSTRING(t.firstpostdate,1,4)="'.$intYear.'"'; break;
    case 'ibase': $str = 'SUBSTRING(t.firstpostdate FROM 1 FOR 4)="'.$intYear.'"'; break;
    case 'sqlite':
    case 'db2':
    case 'oci': $str = 'SUBSTR(t.firstpostdate,1,4)="'.$intYear.'"'; break;
    default: $str = 'LEFT(t.firstpostdate,4)="'.$intYear.'"';
    }
  }
  elseif ( $v2=='w' )
  {
    // this week
    switch($oDB->type)
    {
    case 'pdo.pg':
    case 'pg': $str = 'SUBSTRING(t.firstpostdate,1,8)>"'.DateAdd(date('Ymd'),-8,'day').'"'; break;
    case 'pdo.sqlite':
    case 'sqlite':
    case 'db2':
    case 'pdo.oci':
    case 'oci': $str = 'SUBSTR(t.firstpostdate,1,8)>"'.DateAdd(date('Ymd'),-8,'day').'"'; break;
    case 'ibase': $str = 'SUBSTRING(t.firstpostdate FROM 1 FOR 8)>"'.DateAdd(date('Ymd'),-8,'day').'"'; break;
    default: $str = 'LEFT(t.firstpostdate,8)>"'.DateAdd(date('Ymd'),-8,'day').'"';
    }
  }
  else
  {
    // the month
    $intMonth = intval($v2);
    // check if month from previous year
    if ( $intYear==date('Y') && $intMonth>date('n') ) $intYear = $intYear-1;

    switch($oDB->type)
    {
    case 'pdo.pg':
    case 'pg': $str = 'SUBSTRING(t.firstpostdate,1,6)="'.($intYear*100+$intMonth).'"'; break;
    case 'pdo.sqlite':
    case 'sqlite':
    case 'db2':
    case 'pdo.oci':
    case 'oci': $str = 'SUBSTR(t.firstpostdate,1,6)="'.($intYear*100+$intMonth).'"'; break;
    case 'ibase': $str = 'SUBSTRING(t.firstpostdate FROM 1 FOR 6)="'.($intYear*100+$intMonth).'"'; break;
    default: $str = 'LEFT(t.firstpostdate,6)="'.($intYear*100+$intMonth).'"';
    }
  }
  $strWhere .= ' AND '.$str;

  // Only Topics

  $strWhere .= ' AND p.type="P"';

  // Topics Tags

  if ( count($arrV)>0 )
  {
    for($i=0;$i<count($arrV);++$i)
    {
      switch($oDB->type)
      {
      case 'pdo.sqlsrv':
      case 'sqlsrv':$arrV[$i] = 'UPPER(CAST(t.tags AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
      default:      $arrV[$i] = 'UPPER(t.tags) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
      }
    }
    $strWhere .= ' AND ('.implode(' OR ',$arrV).')';
    $strLast='tags';
  }

  $strFrom = ' FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.id=p.topic';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'news':

  $strFields .= ',p.id as postid,p.textmsg,p.issuedate,p.username';
  $strFrom = ' FROM '.TABPOST.' p INNER JOIN '.TABTOPIC.' t ON t.id=p.topic';
  $strWhere .= ' AND t.type="A"';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

default:

  die('Undefined query method: '.$q);
  break;
}