<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2015 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
if ( !sUser::CanView('V4') ) { $oHtml->PageMsg(11); return; }

require 'bin/qt_lib_pdf.php';
include Translate(APP.'_stat.php');

// --------
// INITIALISE
// --------

$oVIP->selfurl = 'qti_stats.php';
$oVIP->selfname = $L['Statistics'];

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

if ( $s>=0 ) { $strSection = 'forum='.$s.' AND '; } else { $strSection=''; }
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

// --------
// INITIALISE RANGES
// --------

if ( $tt=='gt' || $tt=='dt' )
{
$arrYears = array($y-1,$y); // Normal is 1 year but for Trends analysis, 2 years
}
else
{
$arrYears = array($y);
}

include 'qti_stats_inc.php'; // Statistic computation

// --------
// PDF
// --------

if ( $ch['time']=='q' ) { $intWidth=16; } else { $intWidth=11; }

$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle( PdfClean($L['Statistics']) );
$pdf->SetAuthor( PdfClean($_SESSION[QT]['site_name']) );

switch($tt)
{

//--------
case 'g':
//--------

$pdf->SetSubject( PdfClean($L['H_Global']) );

$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(150);
$pdf->Ln();
$pdf->Cell(0, 10, PdfClean($L['H_Global']), 'T', 1, 'R');
$pdf->SetTextColor(0);

// Title

$pdf->SetFont('Arial','B',10);
$arr = QTarrget(GetSections(sUser::Role()));
$pdf->Cell(0, 8, PdfClean($y.($s>=0 ? ' '.$arr[$s] : '').($type=='I' ? ' '.$L['Inspections'] : '').(empty($tag) ? '' : ', '.$L['With_tag'].' '.str_replace(';',' '.$L['or'].' ',$tag))), '', 1);

// Table

$arrTable = array();

// Table header

  $arrRow = array();
  $oCell = new cPdfCell(' ',28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true); $arrRow[]=$oCell;
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( 'Q'.$i,                                                    $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( $L['dateMM'][$i],                                          $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( QTdatestr(DateAdd($strTendaysago,$i,'day'),'d/n','',false),$intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  }
  $oCell = new cPdfCell(($ch['time']=='d' ? '10 '.strtolower($L['Days']) : PdfClean($L['Year'])),$intWidth+3,5,'C',true); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body topics

  $arrRow = array();
  $oCell = new cPdfCell($L['Items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',         4, 5,'',true,array(0,0,102)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrTs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body messages

  $arrRow = array();
  $oCell = new cPdfCell($L['Replys'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(153,0,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrM[$y][$intBt]) ? $arrM[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrMs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body users

  $arrRow = array();
  $oCell = new cPdfCell($L['Users'].'*',28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(0,153,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrU[$y][$intBt]) ? $arrU[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrUs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Show table

$pdf->BasicTable($arrTable);
$pdf->Ln();

// Note

$pdf->SetFont('Arial','',8);
$pdf->SetTextColor(0);
$pdf->Cell(0, 10, '* '.PdfClean($L['Distinct_users']), '', 1);
$pdf->Ln(5);

// After values display, change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrM[$intYear] = QTarrayzero($arrM[$intYear]);
$arrU[$intYear] = QTarrayzero($arrU[$intYear]);
}

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') && !isset($_GET['oldgraph']) )
{

  // Standard inclusions (cache cannot be used with pdf)
  include 'pChart/pData.class';
  include 'pChart/pChart.class';

  $lang = QTiso();
  // charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['Items_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Items_per_'.$ch['time'].'_cumul'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    1,true); //cumul
  $strChart3 = QTpchart(
    $L['Replies_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrM[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'3'.$y.implode('',$ch).$lang,
    2);
  $strChart4 = QTpchart(
    $L['Users_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrU[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'4'.$y.implode('',$ch).$lang,
    3);

  // Topics
  $pdf->Image($strChart1,10,$pdf->GetY(),90);
  $pdf->Image($strChart2,100,$pdf->GetY(),90);
  $pdf->SetY($pdf->GetY()+60);
  $pdf->Image($strChart3,10,$pdf->GetY(),90);
  $pdf->Image($strChart4,100,$pdf->GetY(),90);
  $pdf->SetY($pdf->GetY()+60);

}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qti_main2.css') )
{

  $y = $pdf->GetY();
  $x = $pdf->GetX();

  // Topics

  $intTopValue = QTroof($arrT[$y]);
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y],92,30,$intTopValue,2,true,$L['Items_per_'.$ch['time']],'','1');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y],92,30,$intTopValue,2,'P',$L['Items_per_'.$ch['time']].' (%)','','1');
  }

  $pdf->SetXY($x+94,$y);

  $intTopValue = QTroof(QTcumul($arrT[$y]));
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,QTcumul($arrT[$y]),92,30,$intTopValue,2,true,$L['Items_per_'.$ch['time'].'_cumul'],'','1');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,QTcumul($arrT[$y]),92,30,$intTopValue,2,'P',$L['Items_per_'.$ch['time'].'_cumul'].' (%)','','1');
  }

  $pdf->SetXY($x,$y+50);

  // Replies & Users

  $intTopValue = QTroof($arrM[$y]);
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrM[$y],92,30,$intTopValue,2,true,$L['Replies_per_'.$ch['time']],'','2');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrM[$y],92,30,$intTopValue,2,'P',$L['Replies_per_'.$ch['time']].' (%)','','2');
  }

  $pdf->SetXY($x+94,$y+50);

  $intTopValue = QTroof($arrU[$y]);
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrU[$y],92,30,$intTopValue,2,true,$L['Users_per_'.$ch['time']],'','3');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrU[$y],92,30,$intTopValue,2,'P',$L['Users_per_'.$ch['time']].' (%)','','3');
  }

}
else
{
  $pdf->SetFont('Arial','',8);
  $pdf->Cell(0, 10, PdfClean('Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, '.$_SESSION[QT]['skin_dir'].'/qti_main2.css'), 'T', 1);
}


break;

//--------
case 'gt':
//--------

$pdf->SetSubject( PdfClean($L['H_Global_trends']) );

$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(150);
$pdf->Ln();
$pdf->Cell(0, 10, PdfClean($L['H_Global_trends']), 'T', 1, 'R');
$pdf->SetTextColor(0);

$pdf->SetFont('Arial','B',10);
$arr = QTarrget(GetSections(sUser::Role()));
$pdf->Cell(0, 8, PdfClean(($y-1).'-'.$y.($s>=0 ? ' '.$arr[$s] : '').($type=='I' ? ' '.$L['Inspections'] : '').(empty($tag) ? '' : ', '.$L['With_tag'].' '.str_replace(';',' '.$L['or'].' ',$tag))), '', 1);

// -----
foreach($arrYears as $y) {
// -----

$arrTable = array();

// Table header

  $arrRow = array();
  $oCell = new cPdfCell($y,28,5,'',true); $oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true); $arrRow[]=$oCell;
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( 'Q'.$i,                                                    $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( $L['dateMM'][$i],                                          $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( QTdatestr(DateAdd($strTendaysago,$i,'day'),'d/n',''),$intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  }
  $oCell = new cPdfCell(($ch['time']=='d' ? '10 '.strtolower($L['Days']) : PdfClean($L['Year'])),$intWidth+3,5,'C',true); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body topics

  $arrRow = array();
  $oCell = new cPdfCell($L['Items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ', 4, 5,'',true,array(0,0,102)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrTs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body messages

  $arrRow = array();
  $oCell = new cPdfCell($L['Replys'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ', 4, 5,'',true,array(153,0,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrM[$y][$intBt]) ? $arrM[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrMs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body users

  $arrRow = array();
  $oCell = new cPdfCell($L['Users'].'*',28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ', 4, 5,'',true,array(0,153,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrU[$y][$intBt]) ? $arrU[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrUs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Show table

$pdf->BasicTable($arrTable);
$pdf->Ln(1);

// -----
}
// -----

$arrTable = array();

// Table header

  $arrRow = array();
  $oCell = new cPdfCell($L['Trends'],28,5,'',true); $oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true); $arrRow[]=$oCell;
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( 'Q'.$i,                                                    $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( $L['dateMM'][$i],                                          $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( QTdatestr(DateAdd($strTendaysago,$i,'day'),'d/n',''),$intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  }
  $oCell = new cPdfCell(($ch['time']=='d' ? '10 '.strtolower($L['Days']) : PdfClean($L['Year'])),$intWidth+3,5,'C',true); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body topic

  $arrRow = array();
  $oCell = new cPdfCell($L['Items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(0,0,102)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
    $oCell = new cPdfCell('0',$intWidth);
    $i = QTtrend((isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : 0),(isset($arrT[$y-1][$intBt]) ? $arrT[$y-1][$intBt] : 0),$ch['trend']=='p');
    if ( isset($i) )
    {
      $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
      if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
      if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
    }
    else
    {
      $oCell->s = '.';
    }
    $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell('0',$intWidth); $oCell->font=array('Arial','B',8);
  $i = QTtrend($arrTs[$y],$arrTs[$y-1],$ch['trend']=='p');
  if ( isset($i) )
  {
    $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
    if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
    if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
  }
  else
  {
    $oCell->s = '.';
  }
  $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body messages

  $arrRow = array();
  $oCell = new cPdfCell($L['Replys'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(153,0,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
    $oCell = new cPdfCell('0',$intWidth);
    $i = QTtrend((isset($arrM[$y][$intBt]) ? $arrM[$y][$intBt] : 0),(isset($arrM[$y-1][$intBt]) ? $arrM[$y-1][$intBt] : 0),$ch['trend']=='p');
    if ( isset($i) )
    {
      $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
      if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
      if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
    }
    else
    {
      $oCell->s = '.';
    }
    $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell('0',$intWidth); $oCell->font=array('Arial','B',8);
  $i = QTtrend($arrMs[$y],$arrMs[$y-1],$ch['trend']=='p');
  if ( isset($i) )
  {
    $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
    if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
    if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
  }
  else
  {
    $oCell->s = '.';
  }
  $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body users

  $arrRow = array();
  $oCell = new cPdfCell($L['Users'].'*',28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(0,153,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
    $oCell = new cPdfCell('0',$intWidth);
    $i = QTtrend((isset($arrU[$y][$intBt]) ? $arrU[$y][$intBt] : 0),(isset($arrU[$y-1][$intBt]) ? $arrU[$y-1][$intBt] : 0),$ch['trend']=='p');
    $i = QTtrend($arrU[$y][$intBt],$arrU[$y-1][$intBt],$ch['trend']=='p');
    if ( isset($i) )
    {
      $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
      if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
      if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
    }
    else
    {
      $oCell->s = '.';
    }
    $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell('0',$intWidth); $oCell->font=array('Arial','B',8);
  $i = QTtrend($arrUs[$y],$arrUs[$y-1],$ch['trend']=='p');
  if ( isset($i) )
  {
    $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
    if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
    if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
  }
  else
  {
    $oCell->s = '.';
  }
  $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// End table

$pdf->BasicTable($arrTable);
$pdf->Ln(3);

$pdf->SetTextColor(0);

// Note

$pdf->SetFont('Arial','',8);
$pdf->Cell(0, 10, '* '.PdfClean($L['Distinct_users']), '', 1);

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrM[$intYear] = QTarrayzero($arrM[$intYear]);
$arrU[$intYear] = QTarrayzero($arrU[$intYear]);
}

// GRAPH

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') && !isset($_GET['oldgraph']) )
{
  // Standard inclusions (cache cannot be used with pdf)
  include 'pChart/pData.class';
  include 'pChart/pChart.class';

  $lang = QTiso();
  // charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['Items_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y-1],'Serie2'=>$arrT[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch),
    1);
  $strChart2 = QTpchart(
    $L['Replies_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrM[$y-1],'Serie2'=>$arrM[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch),
    2);
  $strChart3 = QTpchart(
    $L['Users_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrU[$y-1],'Serie2'=>$arrU[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'3'.$y.implode('',$ch),
    3);

  $pdf->Image($strChart1,10,$pdf->GetY(),180); //,380,230,'PNG');
  $pdf->AddPage();
  $pdf->Image($strChart2,10,$pdf->GetY(),180); //,380,230,'PNG');
  $pdf->SetY($pdf->GetY()+80);
  $pdf->Image($strChart3,10,$pdf->GetY(),180); //,380,230,'PNG');
  $pdf->SetY($pdf->GetY()+80);
}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qti_main2.css') )
{
  $y = $pdf->GetY();
  $x = $pdf->GetX();

  // TOPIC first serie

  $intTopValue = QTroof( array(max($arrT[$y-1]),max($arrT[$y])) );
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y-1],92,30,$intTopValue,2,true,$L['Items_per_'.$bt].' '.($y-1),'','1');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y-1],92,30,$intTopValue,2,'P',$L['Items_per_'.$bt].' (%)'.' '.($y-1),'','1');
  }

  $pdf->SetXY($x+94,$y);

  // TOPIC second serie

  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y],92,30,$intTopValue,2,true,$L['Items_per_'.$bt].' '.$y,'','1');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y],92,30,$intTopValue,2,'P',$L['Items_per_'.$bt].' (%)'.' '.$y,'','1');
  }

  $pdf->SetXY($x,$y+47);

  // MESSAGES first serie

  $intTopValue = QTroof( array(max($arrM[$y-1]),max($arrM[$y])) );
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrM[$y-1],92,30,$intTopValue,2,true,$L['Items_per_'.$bt].' '.($y-1),'','2');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrM[$y-1],92,30,$intTopValue,2,'P',$L['Items_per_'.$bt].' (%)'.' '.($y-1),'','2');
  }

  $pdf->SetXY($x+94,$y+47);

  // MESSAGES second serie

  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrM[$y],92,30,$intTopValue,2,true,$L['Messages_per_'.$bt].' '.$y,'','2');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrM[$y],92,30,$intTopValue,2,'P',$L['Messages_per_'.$bt].' (%)'.' '.$y,'','2');
  }

  $pdf->SetXY($x,$y+94);

  // USERS first serie

  $intTopValue = QTroof( array(max($arrU[$y-1]),max($arrU[$y])) );
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrU[$y-1],92,30,$intTopValue,2,true,$L['Users_per_'.$bt].' '.($y-1),'','3');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrU[$y-1],92,30,$intTopValue,2,'P',$L['Users_per_'.$bt].' (%)'.' '.($y-1),'','3');
  }

  $pdf->SetXY($x+94,$y+94);

  // USERS second serie

  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrU[$y],92,30,$intTopValue,2,true,$L['Users_per_'.$bt].' '.$y,'','3');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrU[$y],92,30,$intTopValue,2,'P',$L['Users_per_'.$bt].' (%)'.' '.$y,'','3');
  }

  $pdf->SetXY($x,$y+138);
}
else
{
  $pdf->SetFont('Arial','',8);
  $pdf->Cell(0, 10, PdfClean('Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, '.$_SESSION[QT]['skin_dir'].'/qti_main2.css'), 'T', 1);
}

break;

//--------
case 'd':
//--------

$pdf->SetSubject( PdfClean($L['H_Details']) );

$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(150);
$pdf->Ln();
$pdf->Cell(0, 10, PdfClean($L['H_Details']), 'T', 1, 'R');
$pdf->SetTextColor(0);

// Title

$pdf->SetFont('Arial','B',10);
$arr = QTarrget(GetSections(sUser::Role()));
$pdf->Cell(0, 8, PdfClean( $y.($s>=0 ? ' '.$arr[$s] : '').($type=='I' ? ' '.$L['Inspections'] : '').(empty($tag) ? '' : ', '.$L['With_tag'].' '.str_replace(';',' '.$L['or'].' ',$tag)) ), '', 1);

// Table header

$arrTable = array();

  $arrRow = array();
  $oCell = new cPdfCell(' ',28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true); $arrRow[]=$oCell;
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( 'Q'.$i,                                                    $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( $L['dateMM'][$i],                                          $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( QTdatestr(DateAdd($strTendaysago,$i,'day'),'d/n','',false),$intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  }
  $oCell = new cPdfCell(($ch['time']=='d' ? '10 '.strtolower($L['Days']) : PdfClean($L['Year'])),$intWidth+3,5,'C',true); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body new topic

  $arrRow = array();
  $oCell = new cPdfCell($L['New_items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(0,0,102)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrN[$y][$intBt]) ? $arrN[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrNs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body closed topic

  $arrRow = array();
  $oCell = new cPdfCell($L['Closed_items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(153,0,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrC[$y][$intBt]) ? $arrC[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrCs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body backlog

  $arrRow = array();
  $oCell = new cPdfCell($L['Backlog'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(0,153,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : '0'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrTs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// End table

$pdf->BasicTable($arrTable);
$pdf->Ln();

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrN[$intYear] = QTarrayzero($arrN[$intYear]);
$arrC[$intYear] = QTarrayzero($arrC[$intYear]);
}

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') && !isset($_GET['oldgraph']) )
{
  // Standard inclusions (cache cannot be used with pdf)
  include 'pChart/pData.class';
  include 'pChart/pChart.class';

  $lang = QTiso();
  // charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['New_items'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrN[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Closed_items'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrC[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    2);
  $strChart3 = QTpchart(
    $L['Backlog'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'3'.$y.implode('',$ch).$lang,
    3);
  $strChart4 = QTpchart(
    $L['Backlog_cumul'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'4'.$y.implode('',$ch).$lang,
    3,true); //cumul

  // Topics
  $pdf->Image($strChart1,10,$pdf->GetY(),90);
  $pdf->Image($strChart2,100,$pdf->GetY(),90);
  $pdf->SetY($pdf->GetY()+60);
  $pdf->Image($strChart3,10,$pdf->GetY(),90);
  $pdf->Image($strChart4,100,$pdf->GetY(),90);
  $pdf->SetY($pdf->GetY()+60);
}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qti_main2.css') )
{
  $y = $pdf->GetY();
  $x = $pdf->GetX();

  // Topics

  $intTopValue = QTroof($arrN[$y]);
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrN[$y],false,92,30,$intTopValue,2,true,$L['New_items'],'','1');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrN[$y],false,92,30,$intTopValue,2,'P',$L['New_items'].' (%)','','1');
  }

  $pdf->SetXY($x+94,$y);

  $intTopValue = QTroof($arrC[$y]);
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrC[$y],92,30,$intTopValue,2,true,$L['Closed_items'],'','2');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrC[$y],92,30,$intTopValue,2,'P',$L['Closed_items'].' (%)','','2');
  }

  $pdf->SetXY($x,$y+50);

  // backlog

  $intTopValue = QTroof($arrT[$y]);
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y],92,30,$intTopValue,2,true,$L['Backlog'],'','3');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrT[$y],92,30,$intTopValue,2,'P',$L['Backlog'].' (%)','','3');
  }

  $pdf->SetXY($x+94,$y+50);

  $intTopValue = QTroof(QTcumul($arrT[$y]));
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,QTcumul($arrT[$y]),92,30,$intTopValue,2,true,$L['Backlog_cumul'],'','3');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,QTcumul($arrT[$y]),92,30,$intTopValue,2,'P',$L['Backlog_cumul'].' (%)','','3');
  }
}
else
{
  $pdf->SetFont('Arial','',8);
  $pdf->Cell(0, 10, PdfClean('Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, '.$_SESSION[QT]['skin_dir'].'/qti_main2.css'), 'T', 1);
}

break;

//--------
case 'dt':
//--------

$pdf->SetSubject( PdfClean($L['H_Details_trends']) );

$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(150);
$pdf->Ln();
$pdf->Cell(0, 10, PdfClean($L['H_Details_trends']), 'T', 1, 'R');
$pdf->SetTextColor(0);

$pdf->SetFont('Arial','B',10);
$arr = QTarrget(GetSections(sUser::Role()));
$pdf->Cell(0, 8, PdfClean( ($y-1).'-'.$y.($s>=0 ? ' '.$arr[$s] : '').($type=='I' ? ' '.$L['Inspections'] : '').(empty($tag) ? '' : ', '.$L['With_tag'].' '.str_replace(';',' '.$L['or'].' ',$tag)) ), '', 1);

// -----
foreach($arrYears as $y) {
// -----

$arrTable = array();

// Table header

  $arrRow = array();
  $oCell = new cPdfCell($y,28,5,'',true); $oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true); $arrRow[]=$oCell;
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( 'Q'.$i,                                              $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( $L['dateMM'][$i],                                    $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( QTdatestr(DateAdd($strTendaysago,$i,'day'),'d/n',''),$intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  }
  $oCell = new cPdfCell(($ch['time']=='d' ? '10 '.strtolower($L['Days']) : PdfClean($L['Year'])),$intWidth+3,5,'C',true); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body new topics

  $arrRow = array();
  $oCell = new cPdfCell($L['New_items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(0,0,102)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrN[$y][$intBt]) ? $arrN[$y][$intBt] : '.'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrNs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table body closed topics

  $arrRow = array();
  $oCell = new cPdfCell($L['Closed_items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(153,0,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  $oCell = new cPdfCell((isset($arrC[$y][$intBt]) ? $arrC[$y][$intBt] : '.'),$intWidth); $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell($arrCs[$y],$intWidth+3,5,'C');$oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// End table

$pdf->BasicTable($arrTable);
$pdf->Ln(1);

// -----
}
// -----

$arrTable = array();

// Table header

  $arrRow = array();
  $oCell = new cPdfCell($L['Trends'],28,5,'',true); $oCell->font=array('Arial','B',8); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true); $arrRow[]=$oCell;
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( 'Q'.$i,                                              $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( $L['dateMM'][$i],                                    $intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $oCell = new cPdfCell( QTdatestr(DateAdd($strTendaysago,$i,'day'),'d/n',''),$intWidth,5,'C',true); $arrRow[]=$oCell; } break;
  }
  $oCell = new cPdfCell(($ch['time']=='d' ? '10 '.strtolower($L['Days']) : PdfClean($L['Year'])),$intWidth+3,5,'C',true); $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table new topics

  $arrRow = array();
  $oCell = new cPdfCell($L['New_items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(0,0,102)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
    $oCell = new cPdfCell('0',$intWidth);
    $i = QTtrend((isset($arrN[$y][$intBt]) ? $arrN[$y][$intBt] : 0),(isset($arrN[$y-1][$intBt]) ? $arrN[$y-1][$intBt] : 0),$ch['trend']=='p');
    if ( isset($i) )
    {
      $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
      if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
      if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
    }
    else
    {
      $oCell->s = '.';
    }
    $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell('0',$intWidth); $oCell->font=array('Arial','B',8);
  $i = QTtrend($arrNs[$y],$arrNs[$y-1],$ch['trend']=='p');
  if ( isset($i) )
  {
    $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
    if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
    if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
  }
  else
  {
    $oCell->s = '.';
  }
  $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// Table closed topics

  $arrRow = array();
  $oCell = new cPdfCell($L['Closed_items'],28,5,'',true); $arrRow[]=$oCell;
  $oCell = new cPdfCell(' ',4, 5,'',true,array(153,0,153)); $arrRow[]=$oCell;
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
    $oCell = new cPdfCell('0',$intWidth);
    $i = QTtrend((isset($arrC[$y][$intBt]) ? $arrC[$y][$intBt] : 0),(isset($arrC[$y-1][$intBt]) ? $arrC[$y-1][$intBt] : 0),$ch['trend']=='p');
    if ( isset($i) )
    {
      $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
      if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
      if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
    }
    else
    {
      $oCell->s = '.';
    }
    $arrRow[]=$oCell;
  }
  $oCell = new cPdfCell('0',$intWidth); $oCell->font=array('Arial','B',8);
  $i = QTtrend($arrCs[$y],$arrCs[$y-1],$ch['trend']=='p');
  if ( isset($i) )
  {
    $oCell->s = $i.($ch['trend']=='p' ? '%' : '');
    if ( $i>0 ) { $oCell->color=array(255,0,0); $oCell->s = '+'.$oCell->s; }
    if ( $i<0 ) { $oCell->color=array(0,255,0); $oCell->s = $oCell->s; }
  }
  else
  {
    $oCell->s = '.';
  }
  $arrRow[]=$oCell;
  $arrTable[] = $arrRow;

// End table

$pdf->BasicTable($arrTable);
$pdf->Ln(3);

$pdf->SetTextColor(0);

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrN[$intYear] = QTarrayzero($arrN[$intYear]);
$arrC[$intYear] = QTarrayzero($arrC[$intYear]);
}

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') && !isset($_GET['oldgraph']) )
{
  // Standard inclusions (cache cannot be used with pdf)
  include 'pChart/pData.class';
  include 'pChart/pChart.class';

  // QTpchart(charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul)
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['Items_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrN[$y-1],'Serie2'=>$arrN[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).QTiso(),
    1);
  $strChart2 = QTpchart(
    $L['Replies_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrC[$y-1],'Serie2'=>$arrC[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).QTiso(),
    2);

  $pdf->Image($strChart1,10,$pdf->GetY(),180);
  $pdf->SetY($pdf->GetY()+75);
  $pdf->Image($strChart2,10,$pdf->GetY(),180);
  $pdf->SetY($pdf->GetY()+75);
}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qti_main2.css') )
{
  $y = $pdf->GetY();
  $x = $pdf->GetX();

  // NEW TOPICS first serie

  $intTopValue = QTroof( array(max($arrN[$y-1]),max($arrN[$y-1])) );
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrN[$y-1],92,30,$intTopValue,2,true,$L['New_items'].' '.($y-1),'','1');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrN[$y-1],92,30,$intTopValue,2,'P',$L['New_items'].' (%)'.' '.($y-1),'','1');
  }

  $pdf->SetXY($x+94,$y);

  // NEW TOPICS second serie

  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrN[$y],92,30,$intTopValue,2,true,$L['New_items'].' '.$y,'','1');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrN[$y],92,30,$intTopValue,2,'P',$L['New_items'].' (%)'.' '.$y,'','1');
  }

  $pdf->SetXY($x,$y+47);

  // CLOSED TOPICS first serie

  $intTopValue = QTroof( array(max($arrC[$y-1]),max($arrC[$y-1])) );
  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrC[$y-1],92,30,$intTopValue,2,true,$L['Closed_items'].' '.($y-1),'','2');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrC[$y-1],92,30,$intTopValue,2,'P',$L['Closed_items'].' (%)'.' '.($y-1),'','2');
  }

  $pdf->SetXY($x+94,$y+47);

  // CLOSED TOPICS second serie

  if ( $ch['value']=='a' )
  {
  $pdf = QTbarchartpdf($pdf,$arrC[$y],92,30,$intTopValue,2,true,$L['Closed_items'].' '.$y,'','2');
  }
  else
  {
  $pdf = QTbarchartpdf($pdf,$arrC[$y],92,30,$intTopValue,2,'P',$L['Closed_items'].' (%)'.' '.$y,'','2');
  }

  $pdf->SetXY($x,$y+94);
}
else
{
  $pdf->SetFont('Arial','',8);
  $pdf->Cell(0, 10, PdfClean('Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, '.$_SESSION[QT]['skin_dir'].'/qti_main2.css'), 'T', 1);
}

break;

//--------
default:
//--------

die('Invalid tab');

}

$pdf->Output();

?>