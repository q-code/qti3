<?php

/* ============
 * qt_lib_graph.php
 * ------------
 * version: 4.1 build:20160703
 * This is a library of public class
 * ------------
 * QTdataset()
 * QTroof()
 * QTcumul()
 * QTtrend()
 * QTarraymerge() attention: by default, null values becomes 0
 * QTarrayzero()
 * ------------
 * NOTE:
 * the function row_count have been removed because it is not supported by odbc
 * ============ */

function QTtablechart($arrHeader=array(),$arrSeries=array(),$arrSeriesColor=array(),$arrClasses=array(),$strSubtitle='')
{
  if ( !is_array($arrHeader) ) die('QTtablechart: Arg #1 must be an array');
  if ( !is_array($arrSeries) ) die('QTtablechart: Arg #2 must be an array');
  if ( !is_array($arrSeriesColor) ) die('QTtablechart: Arg #3 must be an array');
  if ( !is_array($arrClasses) ) die('QTtablechart: Arg #4 must be an array');

  // Default classes, dataset

  if ( !isset($arrClasses['table']) ) $arrClasses['table']='t-data';
  if ( !isset($arrClasses['tr']) ) $arrClasses['tr']='';
  if ( !isset($arrClasses['th']) ) $arrClasses['th']='';
  if ( !isset($arrClasses['td']) ) $arrClasses['td']='';
  if ( count($arrHeader)===0 && count($arrSeries)===0 )
  {
    $arrHeader=array('A','B','C');
    $arrSeries=array('Serie1'=>array(1,2,3),'Serie2'=>array(4,5,6),'Serie3'=>array(7,8,9));
  }

  // Header ($arrHeader includes column names, but not the serie name nor the serie color)

  echo '<table'.(empty($arrClasses['table']) ? '' : ' class="'.$arrClasses['table'].'"').'>',PHP_EOL;
  echo '<tr'.(empty($arrClasses['tr']) ? '' : ' class="'.$arrClasses['tr'].'"').'>',PHP_EOL;
  echo '<th class="'.(empty($arrClasses['th']) ? '' : $arrClasses['th']).'seriename">',(empty($strSubtitle) ? '&nbsp;' : $strSubtitle),'</th>';
  if ( count($arrSeriesColor)>0 ) echo '<th class="'.(empty($arrClasses['th']) ? '' : $arrClasses['th']).'seriecolor">&nbsp;</th>';
  $i=1; foreach ($arrHeader as $strHeader) { echo '<th class="'.(empty($arrClasses['th']) ? '' : $arrClasses['th']).'values'.($i===count($arrHeader) ? ' last' : '').'">',$strHeader,'</th>'; ++$i; }
  echo PHP_EOL,'</tr>',PHP_EOL;

  // Series

  foreach ($arrSeries as $strSerie=>$arrValues)
  {
    echo '<tr'.(empty($arrClasses['tr']) ? '' : ' class="'.$arrClasses['tr'].'"').'>',PHP_EOL;
    echo '<td class="'.(empty($arrClasses['td']) ? '' : $arrClasses['td']).'seriename">',$strSerie,'</td>',PHP_EOL;
    if ( count($arrSeriesColor)>0 ) echo '<td class="'.(empty($arrClasses['td']) ? '' : $arrClasses['td']).'seriecolor"><i class="fa fa-square" style="',(empty($arrSeriesColor[$strSerie]) ? '' : 'color:'.$arrSeriesColor[$strSerie].';'),'"></i></td>',PHP_EOL;
    $i=1; foreach ($arrValues as $strValue) { echo '<td class="'.(empty($arrClasses['td']) ? '' : $arrClasses['td']).'values'.($i===count($arrValues) ? ' last' : '').'">',$strValue,'</td>'; ++$i; } 
    echo PHP_EOL,'</tr>',PHP_EOL;
  }

  echo '</table>',PHP_EOL;
}

// ----------

function QTarraymerge($arrK=array('A','B','C','D','E'),$arrV=array(100,20,30,50,0),$bZero=true)
{
  // QTarraymerge merges 2 arrays: thake the first array as index and the second as values
  // This is usefull for QTsimplegraph() that requires index to contains the abscise labels.
  // @arrK: Array of keynames (with integer index 0..n)
  // @arrV: Array of values (with integer index 0..n)
  // @bZero: Change null value to 0 (default is TRUE)

  if ( !is_array($arrK) ) die('QTarraymerge: Arg #1 must be an array');
  if ( !is_array($arrV) ) die('QTarraymerge: Arg #2 must be an array');
  if ( count($arrK)!=count($arrV) ) die('QTarraymerge: Array #1 and #2 must have the same size');
  $arrK = array_values($arrK);
  $arrV = array_values($arrV);
  $arr = array();
  for ($i=0;$i<count($arrK);++$i)
  {
    $arr[$arrK[$i]]=$arrV[$i];
    if ( $bZero && empty($arrV[$i]) ) $arr[$arrK[$i]]=0;
  }
  return $arr;
}

// ----------

function QTarrayzero($arr)
{
  // Change empty (or null) values to 0 in the array
  if ( !is_array($arr) ) die('QTarrayzero: Arg #1 must be an array');
  foreach($arr as $strKey=>$oValue) if ( empty($oValue) ) $arr[$strKey]=0;
  return $arr;
}

// ----------

function QTroof($arr)
{
  // Returns 5,10,20,30,50,100,200,500,1000 or n000 ABOVE the maximum value in the serie.
  // $arr can a a single number
  if ( is_numeric($arr) ) $arr = array($arr);
  if ( !is_array($arr) ) die('QTroof: Arg #1 must be an array');
  $intTop = 5;
  $i = max($arr);
  if ( $i>1000 ) return (floor($i/1000)+1)*1000;
  if ( $i>500 ) return 1000;
  if ( $i>5 ) $intTop = 10;
  if ( $i>10 ) $intTop = 20;
  if ( $i>20 ) $intTop = 30;
  if ( $i>30 ) $intTop = 50;
  if ( $i>50 ) $intTop = 100;
  if ( $i>100 ) $intTop = 200;
  if ( $i>200 ) $intTop = 500;
  return $intTop;
}

// ----------

function QTcumul($arr,$d=0)
{
  // Cumulate the values in the array: 1,2,3,2,1 becomes 1,3,6,8,9
  // $d defines the number of decimals
  // Note: Result is an array of float values
  if ( !is_array($arr) ) die ('QTcumul: Arg #1 must be an array');
  $arrC = array();
  $i=0;
  foreach($arr as $strKey=>$aValue)
  {
    $i += $aValue;
    $arrC[$strKey]=round($i,$d);
  }
  return $arrC;
}

// ----------

function QTpercent($arr,$d=0,$b=true)
{
  // Returns the percentage of each value in the serie: 100,20,30,50 becomes 50,10,15,25 (%)
  // $d defines the number of decimals
  // $b set TRUE to get a percent value (e.g.: 50 in unit %). Use FALSE to get de ratio (e.g.: 0.5)
  // Note: Result is an array of float values
  if ( !is_array($arr) ) die ('QTpercent: Arg #1 must be an array');
  if ( !is_int($d) ) die ('QTpercent: Arg #2 must be an integer');
  if ( !$b && $d<1 ) $d=1; // ratio must have at least 1 decimal
  $arrP = array();
  $intTotal = array_sum($arr);
  foreach($arr as $strKey=>$oValue)
  {
    $i = (empty($oValue) ? 0 : $oValue/$intTotal);
    if ( $b ) $i = $i*100;
    $arrP[$strKey]=round($i,$d);
  }
  return $arrP;
}

// ----------

function QTtrend($i,$j,$bPercent=false,$d=0)
{
  // Returns $i - $j (or the percentage of variation)
  // $bPercent set TRUE to have the result in percent
  // $d defines the number of decimals
  // Note: Result is a float value

  if ( !isset($i) ) return 0;
  if ( !isset($j) ) return 0;
  $i = $i-$j;
  if ( $bPercent && $i!==0 )
  {
    if ( $j===0 ) $j=1;
    $i = round( ($i/$j)*100,$d );
  }
  return $i;
}

// ----------

// @arrValues: Array of values. Use the index as label). Maximum 55 values.
// @bCumul: Show as cumulative
// @intWidth: Width of the whole graph (in pixel).
// @intHeight: Maximum height of the bars (in pixel).
// @intLabel: Add the labels (n characters) at the bottom of the bars. 0 means no label
// @bValue: Add the value (or precent) on top of the bars. [False|True|"P"]
// @strTitle: Add a title on top
// @strSeriename: Add a seriename at the bottom
// @$intTopValue: Displayed top value

function QTbarchart($arrValues=array('A'=>100,'B'=>20,'C'=>30,'D'=>50,'E'=>0),$intWidth=100,$intHeight=100,$intTopValue=-1,$intLabel=3,$bValue=false,$strTitle='',$strSeriename='',$strColor='1',$onerror=null)
{

  // CHECKS

  if ( !is_array($arrValues) ) { if ( isset($onerror) ) return $onerror; return 'Arg #1 must be an array'; }

  $intCount = count($arrValues);
  if ( $intCount>55 ) { if ( isset($onerror) ) return $onerror; return 'maxumum 55 values...'; }
  if ( $intWidth<50 ) { if ( isset($onerror) ) return $onerror; return 'minimum width:50px...'; }
  $intColwidth = intval( ($intWidth-10)/$intCount );
  $intTotal = array_sum($arrValues);
  $intMax = max($arrValues);
  if ( $intTopValue>$intMax ) $intMax = $intTopValue;
  $arrPercmaxs = array_map( create_function('$n', 'return ($n==0 ? 0 : $n/'.$intMax.');'), $arrValues );

  // START GRAPH and TITLE

  echo '<div class="qtgraph">',PHP_EOL;
  echo '<p class="title">',$strTitle,'</p>',PHP_EOL;

  echo '<table class="qtgraph" style="width:',$intWidth,'px;">',PHP_EOL;

  // VALUES

  if ( $bValue )
  {
    echo '<tr class="qtgraph_value">',PHP_EOL;
    foreach($arrValues as $iValue)
    {
    echo '<td class="qtgraph_value color',$strColor,'" style="width:',$intColwidth,'px">',$iValue,($bValue==='P' && $iValue<100 ? '%' : ''),'</td>',PHP_EOL;
    }
    echo '</tr>',PHP_EOL;
  }

  // BARS

  echo '<tr class="qtgraph_bar">',PHP_EOL;
  foreach($arrPercmaxs as $iPercent)
  {
    $intPx = (int)($iPercent*$intHeight); if ( $intPx===0 ) $intPx=1;
    echo '<td class="qtgraph_bar" style="width:',$intColwidth,'px; height:',$intHeight,'px;">';
    echo '<img class="qtgraph_bar color',$strColor,'" src="bin/qt_lib_graph.gif" style="width:',$intColwidth,'px;height:',$intPx,'px;" />';
    echo '</td>',PHP_EOL;
  }
  echo '</tr>';

  // LABELS

  if ( $intLabel>0 )
  {
    echo '<tr class="qtgraph_label">',PHP_EOL;
    foreach($arrValues as $strLabel=>$iValue)
    {
    echo '<td class="qtgraph_label" style="width:',$intColwidth,'px">',substr($strLabel,0,$intLabel),'</td>',PHP_EOL;
    }
    echo '</tr>',PHP_EOL;
  }

  // SERIENAME

  if ( !empty($strSeriename) )
  {
    echo '<tr class="qtgraph_serie">',PHP_EOL;
    echo '<td class="qtgraph_serie" colspan="',$intCount,'">',$strSeriename,'</td>',PHP_EOL;
    echo '</tr>',PHP_EOL;
  }

  echo '</table>',PHP_EOL;
  echo '</div>',PHP_EOL;

}

// ----------

function QTbarchartpdf($pdf,$arrValues=array('A'=>100,'B'=>20,'C'=>30,'D'=>50,'E'=>0),$intWidth=100,$intHeight=50,$intTopValue=-1,$intLabel=2,$bValue=true,$strTitle='',$strSeriename='',$strColor='1',$onerror=null)
{
  // CHECKS

  if ( !is_array($arrValues) ) { if ( isset($onerror) ) return $onerror; return 'Arg #1 must be an array'; }

  $intCount = count($arrValues);
  if ( $intCount>55 ) { if ( isset($onerror) ) return $onerror; return 'maxumum 55 values...'; }
  if ( $intWidth<50 ) { if ( isset($onerror) ) return $onerror; return 'minimum width:50px...'; }
  $intColwidth = intval( ($intWidth-10)/$intCount );
  $intTotal = array_sum($arrValues);
  $intMax = max($arrValues);
  if ( $intTopValue>$intMax ) $intMax = $intTopValue;
  $arrPercmaxs = array_map( create_function('$n', 'return ($n==0 ? 0 : $n/'.$intMax.');'), $arrValues );

  // START GRAPH

  $y = $pdf->GetY();
  $x = $pdf->GetX();

  $pdf->Rect($x,$y,$intWidth,$intHeight+15);

  $x += 1; $xMin = $x;

  $intSx = ($intWidth-2)/$intCount;

  // TITLE

  if ( !empty($strTitle) )
  {
    $pdf->SetXY($x,$y);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell($intWidth,5,PdfClean($strTitle),0,0,'C');
    $y += 5;
  }

  // VALUES

  if ( $bValue )
  {
    foreach($arrValues as $iValue)
    {
      $pdf->SetXY($x,$y);
      $pdf->SetFont('Arial','',8);
      $pdf->Cell(floor($intWidth/$intCount),5,$iValue,0,0,'C');
      $x += $intSx;
    }
    $x = $xMin;
    $y += 5;
  }

  // BARS

  $y += $intHeight;

  if ( $bValue )
  {
    $pdf->SetFillColor(240);
    $pdf->Rect($x,$y-$intHeight,$intWidth-2,$intHeight,'F');

    if ( $strColor=='1' ) $pdf->SetFillColor(0,0,102);
    if ( $strColor=='2' ) $pdf->SetFillColor(153,0,153);
    if ( $strColor=='3' ) $pdf->SetFillColor(0,153,153);
    $pdf->SetDrawColor(255);

    foreach($arrPercmaxs as $strKey=>$iPercent)
    {
      $intSy = intval($iPercent*$intHeight); if ( $intSy==0 ) $intSy=1;
      $pdf->Rect($x,$y-$intSy,$intSx,$intSy,'DF');
        // label
        $pdf->SetXY($x,$y);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(floor($intWidth/$intCount),5,$strKey,0,0,'C');
      $x += $intSx;
    }
    $x = $xMin;
    $pdf->SetFillColor(255);
    $pdf->SetDrawColor(0);
  }
  $pdf->SetY($y+$intHeight);
  return $pdf;
}

// -----------
// charttitle,abscise,datasets,datasetnames,chartoptions,filename,color,cumul
// returns the path to the image file (cache or tmp)
// Attention, 255,255,255 is coded as transparent !

function QTpchart(
  $strTitle='Untitled',
  $arrA=array('A','B','C','D','E'),
  $arrS=array( 'Serie1'=>array(100,20,30,50,0) ),
  $arrN=array( 'Serie1'=>'Serie 1' ),
  $ch=array('time'=>'m','type'=>'b','value'=>'a','trend'=>'a'),
  $strFile='',
  $intColor=1,
  $bCumul=false)
{

  global $Cache; // can be null (not set)
  $strTitle = html_entity_decode($strTitle);
  $strPath = '';
  $DataSet = new pData;
  $DataSet->AddPoint($arrA,'Serie0');
  $DataSet->SetAbsciseLabelSerie('Serie0');
  $intMax=0;
  foreach($arrS as $strS=>$arrV )
  {
    if ( $bCumul )
    {
    $DataSet->AddPoint( ($ch['value']=='p' ? QTcumul(QTpercent($arrV)) : QTcumul($arrV)),$strS );
    $i = ($ch['value']=='p' ? max(QTcumul(QTpercent($arrV))) : max(QTcumul($arrV)));
    }
    else
    {
    $DataSet->AddPoint( ($ch['value']=='p' ? QTpercent($arrV) : $arrV),$strS );
    $i = ($ch['value']=='p' ? max(QTpercent($arrV)) : max($arrV));
    }
    if ( $i>$intMax ) $intMax=$i;
    $DataSet->AddSerie($strS);
    $DataSet->SetSerieName($arrN[$strS],$strS);
  }
  if ( is_object($Cache) ) $strHash = $Cache->GetHash($strFile,$DataSet->GetData());

  // Stop if in cache

  if ( is_object($Cache) ) { if ( $Cache->IsInCache($strFile,'',$strHash) ) return 'pChart/Cache/'.$strHash; }

  // Initialise the graph. --- ATTENTION 255,255,255 color is transparent in the image ---

  $intWidth = (count($arrS)===1 ? 380 : 600);
  $oChart = new pChart($intWidth,230);
  switch($intColor)
  {
  case 1:  if ( count($arrS)===1 ) { $oChart->setColorPalette(0,0,0,102);   } else { $oChart->setColorPalette(0,0,175,255);   $oChart->setColorPalette(1,0,0,102);   } break;
  case 2:  if ( count($arrS)===1 ) { $oChart->setColorPalette(0,153,0,153); } else { $oChart->setColorPalette(0,241,184,255); $oChart->setColorPalette(1,153,0,153); } break;
  default: if ( count($arrS)===1 ) { $oChart->setColorPalette(0,0,153,153); } else { $oChart->setColorPalette(0,0,231,183);   $oChart->setColorPalette(1,0,153,153); } break;
  }
  $oChart->setFontProperties('pChart/Fonts/tahoma.ttf',8);
  $oChart->setGraphArea(40,35,$intWidth-20,195);
  if ( $ch['value']==='p' ) { $intRoof=(QTroof($intMax)>50 ? 100 : 50); } else { $intRoof=QTroof($intMax); }
  $oChart->setFixedScale(0,$intRoof);
  $oChart->drawFilledRoundedRectangle(7,7,$intWidth-7,223,5,240,240,240);
  $oChart->drawGraphArea(254,254,254,true);
  $oChart->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,125,125,125,TRUE,0,0,TRUE);
  $oChart->drawGrid(4,TRUE,230,230,230,100);

  // Draw the 0 line
  $oChart->setFontProperties('pChart/Fonts/tahoma.ttf',6);
  $oChart->drawTreshold(0,143,55,72,TRUE,TRUE);

  // Draw the line/bar graph
  if ( $ch['type']==='l' || $ch['type']==='L' )
  {
  $oChart->drawFilledLineGraph($DataSet->GetData(),$DataSet->GetDataDescription(),40,TRUE);
  $oChart->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,254,254,254);
  }
  else
  {
  $oChart->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
  }

  // Trends label
  if ( $ch['type']==='B' || $ch['type']==='L' )
  {
    $series = array_keys($arrS); // the serie id
    if ( count($series)>1 )
    {
      $oChart->setFontProperties('pChart/Fonts/tahoma.ttf',7);
      for ($intBt=1;$intBt<=count($arrV);++$intBt)
      {
      $i = QTtrend($arrS[$series[1]][$intBt],$arrS[$series[0]][$intBt],$ch['trend']=='p');
      if ( $i>0 ) $oChart->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),$series[1],$arrA[$intBt],'+'.$i.($ch['trend']=='p' ? '%' : ''),220,220,220);
      if ( $i<0 ) $oChart->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),$series[1],$arrA[$intBt],$i.($ch['trend']=='p' ? '%' : ''),220,220,220);
      }
    }
  }
  // Finish the graph
  $oChart->setFontProperties('pChart/Fonts/tahoma.ttf',10);
  $oChart->drawTitle(40,25,$strTitle,50,50,50);
  $oChart->setFontProperties('pChart/Fonts/tahoma.ttf',9);
  if ( count($arrS)===1 )
  {
  $oChart->drawLegend($intWidth-70,15,$DataSet->GetDataDescription(),240,240,240,-1,-1,-1,0,0,0,false);
  }
  else
  {
  $oChart->drawLegend($intWidth-70,18,$DataSet->GetDataDescription(),254,254,254);
  }
  if ( is_object($Cache) ) $Cache->WriteToCache($strFile,$DataSet->GetData(),$oChart);
  $oChart->Render('pChart/tmp/'.$strFile.'.png');
  return 'pChart/tmp/'.$strFile.'.png';
}