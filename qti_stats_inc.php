<?php

// QuickTicket 2.3 build:20160703

// GLOBAL STATISTICS included in: qti_stats.php (and _csv,_pdf)

include 'bin/qt_lib_graph.php';

// Initialise array values. When a value is missing the display will show &middot;

if ( !isset($intStartyear) )
{
  $oDB->Query('SELECT min(firstpostdate) as startdate, max(firstpostdate) as lastdate FROM '.TABTOPIC );
  $row = $oDB->Getrow();
  if ( empty($row['startdate']) ) $row['startdate']=strval($y-1).'0101';
  if ( empty($row['lastdate']) ) $row['lastdate']=strval($y).'1231';
  $strLastdaysago = substr($row['lastdate'],0,8);
  $strTendaysago = DateAdd($strLastdaysago,-10,'day');
  $intStartyear = intval(substr($row['startdate'],0,4));
  $intStartmonth = intval(substr($row['startdate'],4,2));
  $intEndyear = intval(date('Y'));
  $intEndmonth = intval(date('n'));
}

switch($ch['time'])
{
case 'q': $intMaxBt=4; break;
case 'd': $intMaxBt=10; break;
case 'm': $intMaxBt=12; break;
default: die('Invalid blocktime');
}

$intCurrentYear = $y;
$strCurrentTendaysago = $strTendaysago;

$arrA = array(); // Abscise

// =========
// COUNT GLOBAL & GLOBAL TRENDS
// =========
if ( $tt=='g' || $tt=='gt' ) {
// =========

$arrT = array(); $arrTs = array(); // Topics,   Topics sum,   ($arrT can have null)
$arrM = array(); $arrMs = array(); // Replies,  Replies sum,
$arrU = array(); $arrUs = array(); // Users,    Users sum,

foreach($arrYears as $intYear) // GLOBAL has 1 year, GLOBAL TRENDS has 2 years
{
  $arrT[$intYear] = array();
  $arrM[$intYear] = array();
  $arrU[$intYear] = array();
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $arrA[$i]='Q'.$i;                                        $arrT[$intYear][$i]=null; $arrM[$intYear][$i]=null; $arrU[$intYear][$i]=null; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $arrA[$i]=$L['dateMM'][$i];                              $arrT[$intYear][$i]=null; $arrM[$intYear][$i]=null; $arrU[$intYear][$i]=null; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $arrA[$i]=substr(DateAdd($strTendaysago,$i,'day'),-2,2); $arrT[$intYear][$i]=null; $arrM[$intYear][$i]=null; $arrU[$intYear][$i]=null; } break;
  }
  $arrTs[$intYear] = 0;
  $arrMs[$intYear] = 0;
  $arrUs[$intYear] = 0;
}

// -----
foreach($arrYears as $intYear) {
// -----

if ( $intCurrentYear==$intYear ) { $strTendaysago = $strCurrentTendaysago; } else { $strTendaysago = DateAdd(substr($strTendaysago,0,8),-1,'year'); }

// COUNT TOPICS

for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition(($intYear*100+$intBt),'firstpostdate',6) );
  if ( $ch['time']=='q') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition(($intYear*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'firstpostdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'firstpostdate',8) );

  $row = $oDB->Getrow();
  $arrT[$intYear][$intBt] = intval($row['countid']);
  $arrTs[$intYear] += $arrT[$intYear][$intBt]; // total
}

// COUNT REPLIES

for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query('SELECT sum(replies) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition(($intYear*100+$intBt),'firstpostdate',6) );
  if ( $ch['time']=='q') $oDB->Query('SELECT sum(replies) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition(($intYear*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'firstpostdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query('SELECT sum(replies) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'firstpostdate',8) );

  $row = $oDB->Getrow();
  $arrM[$intYear][$intBt] = intval($row['countid']);
  $arrMs[$intYear] += $arrM[$intYear][$intBt]; // total
}

// COUNT USERS

for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime
  switch($oDB->type)
  {
  case 'pdo.mysql': 
  case 'pdo.sqlsrv': 
  case 'pdo.pg': 
  case 'pdo.firebird': 
  case 'mysql4': 
  case 'mysql': 
  case 'sqlsrv': 
  case 'pg': 
  case 'ibase':
    if ( $ch['time']=='m') $oDB->Query('SELECT COUNT(DISTINCT userid) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition(($intYear*100+$intBt),'issuedate',6) );
    if ( $ch['time']=='q') $oDB->Query('SELECT COUNT(DISTINCT userid) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition(($intYear*100+($intBt-1)*3),'issuedate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'issuedate',6,'<=') );
    if ( $ch['time']=='d') $oDB->Query('SELECT COUNT(DISTINCT userid) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'issuedate',8) );
    break;
  default:
    if ( $ch['time']=='m') $oDB->Query('SELECT COUNT(*) as countid FROM (SELECT DISTINCT userid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition(($intYear*100+$intBt),'issuedate',6).') as distinctusers' );
    if ( $ch['time']=='q') $oDB->Query('SELECT COUNT(*) as countid FROM (SELECT DISTINCT userid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition(($intYear*100+($intBt-1)*3),'issuedate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'issuedate',6,'<=').') as distinctusers' );
    if ( $ch['time']=='d') $oDB->Query('SELECT COUNT(*) as countid FROM (SELECT DISTINCT userid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'issuedate',8).') as distinctusers' );  
  }
  $row = $oDB->Getrow();
  $arrU[$intYear][$intBt] = intval($row['countid']);
}

  // compute total

  // compute per blocktime
  switch($oDB->type)
  {
  case 'pdo.mysql': 
  case 'pdo.sqlsrv': 
  case 'pdo.pg': 
  case 'pdo.firebird': 
  case 'mysql4': 
  case 'mysql': 
  case 'sqlsrv': 
  case 'pg': 
  case 'ibase':
    if ( $ch['time']=='m') $oDB->Query('SELECT COUNT(DISTINCT userid) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($intYear,'issuedate') );
    if ( $ch['time']=='q') $oDB->Query('SELECT COUNT(DISTINCT userid) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($intYear,'issuedate') );
    if ( $ch['time']=='d') $oDB->Query('SELECT COUNT(DISTINCT userid) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($strTendaysago,'issuedate',8,'>=') );
    break;
  default:
    if ( $ch['time']=='m') $oDB->Query('SELECT COUNT(*) as countid FROM (SELECT DISTINCT userid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($intYear,'issuedate').') as distinctusers' );
    if ( $ch['time']=='q') $oDB->Query('SELECT COUNT(*) as countid FROM (SELECT DISTINCT userid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($intYear,'issuedate').') as distinctusers' );
    if ( $ch['time']=='d') $oDB->Query('SELECT COUNT(*) as countid FROM (SELECT DISTINCT userid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($strTendaysago,'issuedate',8,'>=').') as distinctusers' );
  }

  $row = $oDB->Getrow();
  $arrUs[$intYear] = intval($row['countid']);

// -----
}
// -----

// =========
}
// =========

// =========
// COUNT DETAIL & DETAIL TRENDS
// =========
if ( $tt=='d' || $tt=='dt' ) {
// =========

$arrN = array(); $arrNs = array();
$arrC = array(); $arrCs = array();
$arrT = array(); $arrTs = array();

foreach($arrYears as $intYear)
{
  $arrN[$intYear] = array();
  $arrC[$intYear] = array();
  $arrT[$intYear] = array();
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) {  $arrA[$i]='Q'.$i;                                        $arrT[$intYear][$i]=null; $arrN[$intYear][$i]=null; $arrC[$intYear][$i]=null; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) {  $arrA[$i]=$L['dateMM'][$i];                              $arrT[$intYear][$i]=null; $arrN[$intYear][$i]=null; $arrC[$intYear][$i]=null; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) {  $arrA[$i]=substr(DateAdd($strTendaysago,$i,'day'),-2,2); $arrT[$intYear][$i]=null; $arrN[$intYear][$i]=null; $arrC[$intYear][$i]=null; } break;
  }
  $arrNs[$intYear] = 0;
  $arrCs[$intYear] = 0;
  $arrTs[$intYear] = 0;
}

// -----
foreach($arrYears as $intYear) {
// -----

if ( $intCurrentYear==$intYear ) { $strTendaysago = $strCurrentTendaysago; } else { $strTendaysago = DateAdd(substr($strTendaysago,0,8),-1,'year'); }

// COUNT NEW TOPICS

for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition(($intYear*100+$intBt),'firstpostdate',6) );
  if ( $ch['time']=='q') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition(($intYear*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'firstpostdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'firstpostdate',8) );

  $row = $oDB->Getrow();
  $arrN[$intYear][$intBt] = intval($row['countid']);
  $arrNs[$intYear] += $arrN[$intYear][$intBt]; // total
}

// COUNT CLOSED TOPICS

for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.' status="Z" AND '.SqlDateCondition(($intYear*100+$intBt),'statusdate',6) );
  if ( $ch['time']=='q') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.' status="Z" AND '.SqlDateCondition(($intYear*100+($intBt-1)*3),'statusdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'statusdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.$strType.$strTags.' status="Z" AND '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'statusdate',8) );

  $row = $oDB->Getrow();
  $arrC[$intYear][$intBt] = intval($row['countid']);
  $arrCs[$intYear] += $arrC[$intYear][$intBt]; // total
}

// COUNT BACKLOG

for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.'('.SqlDateCondition(($intYear*100+$intBt),'firstpostdate',6).' OR '.SqlDateCondition(($intYear*100+$intBt),'lastpostdate',6).')' );
  if ( $ch['time']=='q') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.'('.SqlDateCondition(($intYear*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'lastpostdate',6,'<=').')' );
  if ( $ch['time']=='d') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.'('.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'firstpostdate',8).' OR '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'lastpostdate',8).')' );

  $row = $oDB->Getrow();
  $arrT[$intYear][$intBt] = intval($row['countid']);
}

  // count total

  if ( $ch['time']=='m') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.'('.SqlDateCondition($intYear,'firstpostdate',4).' OR '.SqlDateCondition($intYear,'lastpostdate',4).')' );
  if ( $ch['time']=='q') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.'('.SqlDateCondition($intYear,'firstpostdate',4).' OR '.SqlDateCondition($intYear,'lastpostdate',4).')' );
  if ( $ch['time']=='d') $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE '.$strSection.'('.SqlDateCondition($strTendaysago,'firstpostdate',8).' OR '.SqlDateCondition($strTendaysago,'lastpostdate',8).')' );

  $row = $oDB->Getrow();
  $arrTs[$intYear] = intval($row['countid']);

// -----
}
// -----

// =========
}
// =========