<?php

/**
* PHP versions 4 and 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QTI
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    3.0 build:20121227
*/

session_start();
require 'bin/init.php';
if ( !sUser::CanView('V2') ) { $oHtml->PageMsg(11); return; }

// ---------
// INITIALISE
// ---------

$size = ( isset($_GET['size']) ? strip_tags($_GET['size']) : 'all');
$intCount = (int)$_GET['n'];
$intLimit = 0;
$intLen = (int)$_SESSION[QT]['items_per_page'];

// Check arguments

if ( empty($size) || $intCount <= $intLen ) $size='all';
if ( strlen($size)>6 ) die('Invalid argument');
if ( substr($size,0,1)!='p' && substr($size,0,1)!='m' && $size!=='all') die('Invalid argument');
if ( substr($size,0,1)=='p' )
{
  $i = (int)substr($size,1);
  if ( empty($i) || $i<0 ) die('Invalid argument');
  if ( ($i-1) > $intCount/$intLen ) die('Invalid argument');
}
if ( substr($size,0,1)=='m' )
{
  if ( $size!='m1' && $size!='m2' && $size!='m5' && $size!='m10' ) die('Invalid argument');
}
if ( $intCount>1000 && $size=='all' ) die('Invalid argument');
if ( $intCount<=1000 && substr($size,0,1)=='m' ) die('Invalid argument');
if ( $intCount>1000 && substr($size,0,1)=='p' ) die('Invalid argument');

// Uri arguments

$q = '';   // in case of search, query type
$s = '*';  // section filter can be '*' or [int]
$fs = '*';  // section filter ($fs will become $s if provided)

// Read Uri arguments

QThttpvar('s fs q','str str str');
if ( $fs==='' ) $fs='*';
if ( $s==='' ) $s='*';
if ( $fs!=='*' ) $s=(int)$fs; // $fs becomes $s in this page
if ( $s!=='*' ) $s=(int)$s;

// Section (can be an empty section in case of search result)

if ( $s==='*' )
{
  $oSEC = new cSection();
}
elseif ( $s<0 )
{
  $oHtml->Redirect();
}
else
{
  $oSEC = new cSection($s);
}

$strOrder = $oSEC->ReadOption('order'); if ( empty($strOrder) ) $strOrder = 'lastpostdate'; // use section option
$strDirec = 'desc';                     if ( $strOrder==='title' ) $strDirec = 'asc';       // use asc as default direction in case of title ordering
  if ( $q=='last' || $q=='user' ) { $strOrder='issuedate'; $strDirec='desc'; }
$strLast = $oSEC->LastColumn(); // use section option lastcolumn
  if ( isset($_SESSION[QT]['lastcolumn']) ) $strLast=$_SESSION[QT]['lastcolumn'];
  if ( empty($strLast) || $strLast==='none' ) $strLast = '';
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower(substr($_GET['dir'],0,4));

$strCSV = '';
$arrMe[] = array();
$arrSEC = sMem::Get('sys_sections');

// apply argument

if ( $size=='all') { $intLimit=0; $intLen=$intCount; }
if ( $size=='m1' ) { $intLimit=0; $intLen=999; }
if ( $size=='m2' ) { $intLimit=1000; $intLen=1000; }
if ( $size=='m5' ) { $intLimit=0; $intLen=4999; }
if ( $size=='m10') { $intLimit=5000; $intLen=5000; }
if ( substr($size,0,1)=='p' ) { $i = (int)substr($size,1); $intLimit = ($i-1)*$intLen; }

// Query

$strOnTop = '';
if ( $_SESSION[QT]['news_on_top']==1 ) $strOnTop = 'CASE t.type WHEN "A" THEN "A" ELSE "Z" END as typea,';

$strFields = $strOnTop.'t.*,p.title,p.icon,p.id as postid,p.textmsg,p.issuedate,p.username';

if ( $s>=0 && empty($q) )
{
  $strFrom = ' FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid=p.id';
  $strWhere = ' WHERE t.forum='.$oSEC->uid;
  // Criteria sql: topics visible for current user ONLY
  if ( $oSEC->type==2 && !sUser::IsStaff() ) $strWhere .= ' AND (t.firstpostuser='.sUser::Id().' OR t.type="A")';
  $strCount = 'SELECT count(*) as countid FROM '.TABTOPIC.' t'.$strWhere;
}
elseif ( !empty($q) )
{
  $strWhere = ' WHERE t.forum>=0';
  include 'qti_items_qry.php';
}
else
{
  die('Missing argument $s or $q...');
}

// Option to hide closed items

if ( $_SESSION[QT]['show_closed']=='0' ) $strWhere.=' AND t.status<>"1"';

// Count topics visible for current user ONLY

$oDB->Query( $strCount );
$row = $oDB->Getrow();
$intCount = (int)$row['countid'];

// --------
// HTML START
// --------

$table = new cTable();
$table->th['type'] = new cTableHead(L('Type'));
$table->th['numid'] = new cTableHead(L('Ref'));
$table->th['title'] = new cTableHead(L('Item'));
if ( !empty($q) && $s<0 ) $table->th['sectiontitle'] = new cTableHead(L('Section'));
$table->th['firstpostname'] = new cTableHead($L['Author']);
$table->th['firstpostdate'] = new cTableHead($L['First_message']);
$table->th['lastpost'] = new cTableHead($L['Last_message']);
$table->th['replies'] = new cTableHead($L['Replys']);
if ( !empty($strLast) && $strLast!=='id' ) $table->th[$strLast] = new cTableHead( ucfirst(L($strLast)) );
$table->th['id'] = new cTableHead('id');

foreach(array_keys($table->th) as $key) $strCSV .= ToCsv($table->th[$key]->content);
$strCSV = substr($strCSV,0,-1).PHP_EOL;

// ========
if ( $strOrder=='title' ) { $strFullOrder='p.title'; } else { $strFullOrder='t.'.$strOrder; }
$oDB->Query( LimitSQL( $strFields.$strFrom.$strWhere, (empty($strOnTop) ? '' : 'typea ASC, ').$strFullOrder.' '.strtoupper($strDirec), $intLimit, $intCount ) );
// ========
$intWhile=0;
while($row=$oDB->Getrow())
{
  $str = implode('',FormatCsvRow($table->GetTHnames(),$row,$arrSEC));
  if ( substr($str,-1,1)==';' ) $str = substr($str,0,-1);
  $strCSV .= $str.PHP_EOL;
  //odbcbreak
  ++$intWhile; if ( $intWhile>=$intCount ) break;
}
// ========

if ( isset($_GET['debug']) )
{
  echo $strCSV;
  exit;
}

if ( !headers_sent() )
{
  header('Content-Type: text/csv; charset='.QT_HTML_CHAR);
  header('Content-Disposition: attachment; filename="qti_'.date('YmdHi').'.csv"');
}

echo $strCSV;