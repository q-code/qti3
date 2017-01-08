<?php

// QuickTicket 3.0 build:20160703

session_start();
require 'bin/init.php';
if ( !sUser::CanView('V4') ) { $oHtml->PageMsg(11); return; }

include Translate(APP.'_stat.php');
include 'bin/qti_fn_stat.php';

// --------
// INITIALISE
// --------

$strCSV = '';

$s = -1;   // section filter
$y = date('Y'); if ( intval(date('n'))<2 ) $y--; // year filter
$type = ''; // type filter
$tag = ''; // tags filter
$tt = 'g'; // tab: g=global, gt=globaltrend, d=detail, dt=detailtrend
$ch = array('time'=>'m','type'=>'b','value'=>'a','trend'=>'a'); // chart parameters
// [0] blocktime: m=month, q=quarter, d=10days
// [1] graph type: b=bar, l=line, B=bar+variation, L=line+variation
// [2] graphics reals: a=actual, p=percent
// [3] trends reals: a=actual, p=percent

// --------
// SUBMITTED
// --------

QThttpvar('y s type tag tt','int int str str str',true,true,false);

// Check if data selected (only for user defined count stat)

if ( $tt=='n' ) {
if ( !isset($_SESSION[QT]['statF'][0]) ) {
  $oVIP->exiturl = 'qti_stats.php';
  $oHtml->PageMsg( 'CSV', '<p>No data...</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 3 );
}}

// Check submitted value

if ( $s>=0 ) { $strSection = 'section='.$s.' AND '; } else { $strSection=''; }
if ( !empty($type) ) { $strType='type="'.strtoupper(substr($type,0,1)).'" AND '; } else { $strType=''; }
$strTags = '';
if ( !empty($tag) )
{
  if ( substr($tag,-1,1)==';' ) $tag = substr($tag,0,-1);
  $arrTags = explode(';',$tag);
  $str = '';
  foreach($arrTags as $strTag)
  {
  if ( !empty($str) ) $str .= ' OR ';
  $str .= 'UPPER(tags) LIKE "%'.strtoupper($strTag).'%"';
  }
  if ( !empty($str) ) $strTags = ' ('.$str.') AND ';
}
if ( isset($_GET['ch']) )
{
  $str = strip_tags($_GET['ch']);
  if ( strlen($str)>0 ) $ch['time'] = substr($str,0,1); // blocktime
  if ( strlen($str)>1 ) $ch['type'] = substr($str,1,1); // graph type
  if ( strlen($str)>2 ) $ch['value'] = substr($str,2,1); // value type
  if ( strlen($str)>3 ) $ch['trend'] = substr($str,3,1); // trends value type
}

// ------
// OUTPUT
// ------

if ( $tt=='gt' || $tt=='dt' ) { $arrYears = array($y-1,$y); } else { $arrYears = array($y); } // Normal is 1 year but for Trends analysis, 2 years

include 'qti_stats_inc.php';

// Table header

$arr = QTarrget(GetSections(sUser::Role()));
$strCSV .= '"'.implode(' ',$arrYears).($s>=0 ? ' ('.$arr[$s].')' : '').(empty($tag) ? '' : ', '.$L['With_tag'].' '.str_replace(';',' '.$L['or'].' ',$tag)).'"<br />';

// -----
foreach($arrYears as $y) {
// -----

// Table header

$strCSV .= '"'.$y.'";';

switch($ch['time'])
{
case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"Q'.$i.'";'; } break;
case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"'.$L['dateMM'][$i].'";'; } break;
case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"'.QTdatestr( DateAdd($strTendaysago,$i,'day'),'d M','' ).'";'; } break;
}
$strCSV .= '"'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'"<br />';

// Table body

if ( $tt=='g' || $tt=='gt' )
{

  $strCSV .= '"'.$L['Items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : '0').';'; }
  $strCSV .= $arrTs[$y].'<br />';

  $strCSV .= '"'.$L['Replys'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrM[$y][$intBt]) ? $arrM[$y][$intBt] : '0').';'; }
  $strCSV .= $arrMs[$y].'<br />';

  $strCSV .= '"'.$L['Users'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrU[$y][$intBt]) ? $arrU[$y][$intBt] : '0').';'; }
  $strCSV .= $arrUs[$y].'<br />';

}

if ( $tt=='d' )
{

  $strCSV .= '"'.$L['New_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrN[$y][$intBt]) ? $arrN[$y][$intBt] : '0').';'; }
  $strCSV .= $arrNs[$y].'<br />';

  $strCSV .= '"'.$L['Closed_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrC[$y][$intBt]) ? $arrC[$y][$intBt] : '0').';'; }
  $strCSV .= $arrCs[$y].'<br />';

  $strCSV .= '"'.$L['Backlog'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : '0').';'; }
  $strCSV .= $arrTs[$y].'<br />';

}

if ( $tt=='dt' )
{

  $strCSV .= '"'.$L['New_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrN[$y][$intBt]) ? $arrN[$y][$intBt] : '0').';'; }
  $strCSV .= $arrNs[$y].'<br />';

  $strCSV .= '"'.$L['Closed_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) { $strCSV .= (isset($arrC[$y][$intBt]) ? $arrC[$y][$intBt] : '0').';'; }
  $strCSV .= $arrCs[$y].'<br /><br />';

}

// -----
}
// -----

// add trends [gt] if several years

if ( $tt=='gt' )
{
  // Table header

  $strCSV .= '"'.$L['Trends'].'";';

  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"Q'.$i.'";'; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"'.$L['dateMM'][$i].'";'; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"'.QTdatestr( DateAdd($strTendaysago,$i,'day'),'d M','' ).'";'; } break;
  }
  $strCSV .= '"'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'"<br />';

  // Table body
  $arrSeries[0] = GetSerieDelta($arrT,$arrTs,$y,$intMaxBt,$ch['trend']=='p','0',false); // Topics, delta without color
  $arrSeries[1] = GetSerieDelta($arrM,$arrMs,$y,$intMaxBt,$ch['trend']=='p','0',false); // Replies
  $arrSeries[2] = GetSerieDelta($arrU,$arrUs,$y,$intMaxBt,$ch['trend']=='p','0',false); // Users

  $strCSV .= '"'.$L['Items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) $strCSV .= (isset($arrSeries[0][$intBt]) ? $arrSeries[0][$intBt] : '0').';';
  $strCSV .= '<br />';

  $strCSV .= '"'.$L['Replys'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) $strCSV .= (isset($arrSeries[1][$intBt]) ? $arrSeries[1][$intBt] : '0').';';
  $strCSV .= '<br />';

  $strCSV .= '"'.$L['Users'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) $strCSV .= (isset($arrSeries[2][$intBt]) ? $arrSeries[2][$intBt] : '0').';';
  $strCSV .= '<br />';

}

// add trends [dt] if several years

if ( $tt=='dt' )
{

  // Table header

  $strCSV .= '"'.$L['Trends'].'";';

  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"Q'.$i.'";'; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"'.$L['dateMM'][$i].'";'; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $strCSV .= '"'.QTdatestr( DateAdd($strTendaysago,$i,'day'),'d M','' ).'";'; } break;
  }
  $strCSV .= '"'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'"<br />';

  // Table body

  $arrSeries[0] = GetSerieDelta($arrN,$arrNs,$y,$intMaxBt,$ch['trend']=='p','0',false); // New_topics, delta without color
  $arrSeries[1] = GetSerieDelta($arrC,$arrCs,$y,$intMaxBt,$ch['trend']=='p','0',false); // Closed_topics

  $strCSV .= '"'.$L['New_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) $strCSV .= (isset($arrSeries[0][$intBt]) ? $arrSeries[0][$intBt] : '0').';';
  $strCSV .= '<br />';

  $strCSV .= '"'.$L['Closed_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) $strCSV .= (isset($arrSeries[1][$intBt]) ? $arrSeries[1][$intBt] : '0').';';
  $strCSV .= '<br />';

}

// ------
// Export
// ------

if ( !headers_sent() )
{
  $strCSV = str_replace('<br />',"\r\n",$strCSV);
  header('Content-Type: text/csv; charset='.QT_HTML_CHAR);
  header('Content-Disposition: attachment; filename="global_stat_'.$y.'.csv"');
}

echo $strCSV;

?>