<?php

// QuickTicket 3.0 build:20160703

switch($tt)
{

//--------
case 'g':
//--------

$arrSeries[$L['Items']] = GetSerie($arrT,$arrTs,$y,$intMaxBt);
$arrSeries[$L['Replys']] = GetSerie($arrM,$arrMs,$y,$intMaxBt);
$arrSeries[$L['Users'].'*'] = GetSerie($arrU,$arrUs,$y,$intMaxBt);
// add link for serie month and output screen
if ( $ch['time']=='m' )
{
  for($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  if ( !empty($arrT[$y][$intBt]) ) $arrSeries[$L['Items']][$intBt]='<a href="'.Href('qti_items.php').'?q=adv&amp;y='.$y.'&amp;v2='.$intBt.'&amp;s='.$s.'">'.$arrT[$y][$intBt].'</a>';
  }
  if ( !empty($arrTs[$y]) ) $arrSeries[$L['Items']][$intMaxBt+1]='<a href="'.Href('qti_items.php').'?q=adv&amp;y='.$y.'&amp;v2=y&amp;s='.$s.'">'.$arrTs[$y].'</a>';

  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
  if ( !empty($arrU[$y][$intBt]) ) $arrSeries[$L['Users'].'*'][$intBt]='<a href="'.Href('qti_stat.php').'?y='.$y.'&amp;m='.$intBt.'&amp;s='.$s.'">'.$arrU[$y][$intBt].'</a>';
  }
  if ( !empty($arrUs[$y]) ) $arrSeries[$L['Users'].'*'][$intMaxBt+1]='<a href="'.Href('qti_stat.php').'?y='.$y.'&amp;s='.$s.'">'.$arrUs[$y].'</a>';
}

$arrSeriesColor = array($L['Items']=>'#000066',$L['Replys']=>'#990099',$L['Users'].'*'=>'#009999');

QTtablechart($arrHeader,$arrSeries,$arrSeriesColor);

echo '*  <span class="small">',$L['Distinct_users'],'</span>
';

// After values display, change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrM[$intYear] = QTarrayzero($arrM[$intYear]);
$arrU[$intYear] = QTarrayzero($arrU[$intYear]);
}

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') )
{
  // Abscise Label
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  if ( file_exists('pChart/pCache.class') )
  {
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);
  }

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

  // DISPLAY

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuType),' | ',implode(' &middot; ',$arrMenuValue),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;

  echo '<table class="layout">',PHP_EOL;
  echo '<tr>',PHP_EOL;
  echo '<td class="col1"><img class="pchart" src="'.$strChart1.'"/></td>',PHP_EOL;
  echo '<td class="col2"><img class="pchart" src="'.$strChart2.'"/></td>',PHP_EOL;
  echo '</tr>';
  echo '<tr>',PHP_EOL;
  echo '<td class="col1"><img class="pchart" src="'.$strChart3.'"/></td>',PHP_EOL;
  echo '<td class="col2"><img class="pchart" src="'.$strChart4.'"/></td>',PHP_EOL;
  echo '</tr>';
  echo '</table>';

}
elseif ( file_exists('bin/qt_lib_graph.php') )
{

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuValue),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;

  // Topics & cumulated topics

  echo '<table class="layout">',PHP_EOL;
  echo '<tr>',PHP_EOL;

  echo '<td class="col1">',PHP_EOL;

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrT[$y]),320,100,QTroof($arrT[$y]),3,true,$L['Items_per_'.$ch['time']],'','1');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y])),350,100,100,3,'P',$L['Items_per_'.$ch['time']].' (%)','','1');
  }

  echo '</td>',PHP_EOL;
  echo '<td class="col2">',PHP_EOL;

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,QTcumul($arrT[$y])),320,100,QTroof($arrT[$y]),3,true,$L['Items_per_'.$ch['time'].'_cumul'],'','1');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTcumul(QTpercent($arrT[$y],2))),350,100,100,3,'P',$L['Items_per_'.$ch['time'].'_cumul'].' (%)','','1');
  }
  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  // Replies & Users

  echo '<tr>',PHP_EOL;

  echo '<td class="col1">',PHP_EOL;

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrM[$y]),320,100,QTroof($arrM[$y]),3,true,$L['Replies_per_'.$ch['time']],'','2');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrM[$y])),350,100,100,3,'P',$L['Replies_per_'.$ch['time']].' (%)','','2');
  }

  echo '</td>',PHP_EOL;
  echo '<td class="col2">',PHP_EOL;

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrU[$y]),320,100,QTroof($arrM[$y]),3,true,$L['Users_per_'.$ch['time']],'','3');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrU[$y])),350,100,100,3,'P',$L['Users_per_'.$ch['time']].' (%)','','3');
  }
  echo '</td>',PHP_EOL;
  echo '</tr></table>',PHP_EOL;

}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php</p>';
}
break;

//--------
case 'gt':
//--------

foreach($arrYears as $y)
{
$arrSeries[$L['Items']] = GetSerie($arrT,$arrTs,$y,$intMaxBt);
$arrSeries[$L['Replys']] = GetSerie($arrM,$arrMs,$y,$intMaxBt);
$arrSeries[$L['Users'].'*'] = GetSerie($arrU,$arrUs,$y,$intMaxBt);
$arrSeriesColor = array($L['Items']=>($y==$intCurrentYear ? '#000066' : '#00AFFF'),$L['Replys']=>($y==$intCurrentYear ? '#990099' : '#F1B8FF'),$L['Users'].'*'=>($y==$intCurrentYear ? '#009999' : '#00E7B7'));
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array(),$y);
}

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Trends'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuTrend),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  
$arrSeries[$L['Items']] = GetSerieDelta($arrT,$arrTs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeries[$L['Replys']] = GetSerieDelta($arrM,$arrMs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeries[$L['Users'].'*'] = GetSerieDelta($arrU,$arrUs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeriesColor = array($L['Items']=>'',$L['Replys']=>'',$L['Users'].'*'=>'');
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array(),L('Trends'));

echo '*  <span class="small">',$L['Distinct_users'],'</span>
';

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrM[$intYear] = QTarrayzero($arrM[$intYear]);
$arrU[$intYear] = QTarrayzero($arrU[$intYear]);
}

// -----
// GRAPH
// -----

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') )
{
  // abscise Label
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  if ( file_exists('pChart/pCache.class') )
  {
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);
  }

  // charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul
  $strChart1 = QTpchart(
    $L['Items_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y-1],'Serie2'=>$arrT[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Replies_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrM[$y-1],'Serie2'=>$arrM[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    2);
  $strChart3 = QTpchart(
    $L['Users_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrU[$y-1],'Serie2'=>$arrU[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'3'.$y.implode('',$ch).$lang,
    3);

  // -------
  // DISPLAY
  // -------

  echo '<div style="margin:0 auto; width:600px">',PHP_EOL;
  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuType),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  echo '<img class="pchart" src="'.$strChart1.'"/><br/>';
  echo '<img class="pchart" src="'.$strChart2.'"/><br/>';
  echo '<img class="pchart" src="'.$strChart3.'"/><br/>';
  echo '</div>',PHP_EOL;

}
elseif ( file_exists('bin/qt_lib_graph.php') )
{

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuValue),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  
  // TOPIC first serie

  $intTopY = QTroof( array(max($arrT[$y-1]),max($arrT[$y])) );

  echo '<table class="layout">',PHP_EOL;
  echo '<tr>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y-1])),350,100,$intTopY,3,'P',$L['Items_per_'.$ch['time']].' (%)'.' '.($y-1));
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,$arrT[$y-1]),320,100,$intTopY,3,true,$L['Items_per_'.$ch['time']].' '.($y-1));
  }

  echo '</td>',PHP_EOL;
  echo '<td>&nbsp;</td>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  // TOPIC second serie

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y])),350,100,$intTopY,3,'P',$L['Items_per_'.$ch['time']].' (%)'.' '.$y);
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,$arrT[$y]),320,100,$intTopY,3,true,$L['Items_per_'.$ch['time']].' '.$y);
  }

  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  // MESSAGE first serie

  $intTopY = QTroof( array(max($arrM[$y-1]),max($arrM[$y])) );

  echo '<tr>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrM[$y-1])),350,100,$intTopY,3,'P',$L['Replies_per_'.$ch['time']].' (%)'.' '.($y-1),'','2');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,$arrM[$y-1]),320,100,$intTopY,3,true,$L['Replies_per_'.$ch['time']].' '.($y-1),'','2');
  }

  echo '</td>',PHP_EOL;
  echo '<td>&nbsp;</td>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  // MESSAGE second serie

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrM[$y])),350,100,$intTopY,3,'P',$L['Replies_per_'.$ch['time']].' (%)'.' '.$y,'','2');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,$arrM[$y]),320,100,$intTopY,3,true,$L['Replies_per_'.$ch['time']].' '.$y,'','2');
  }

  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  // USER first serie

  $intTopY = QTroof( array(max($arrU[$y-1]),max($arrU[$y])) );

  echo '<tr>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrU[$y-1]),320,100,$intTopY,3,true,$L['Users_per_'.$ch['time']].' '.($y-1),'','3');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrU[$y-1])),350,100,$intTopY,3,'P',$L['Users_per_'.$ch['time']].' (%)'.' '.($y-1),'','3');
  }

  echo '</td>',PHP_EOL;
  echo '<td>&nbsp;</td>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  // USER second serie

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrU[$y]),320,100,$intTopY,3,true,$L['Users_per_'.$ch['time']].' '.$y,'','3');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrU[$y])),350,100,$intTopY,3,'P',$L['Users_per_'.$ch['time']].' (%)'.' '.$y,'','3');
  }

  echo '</td>',PHP_EOL;
  echo '</tr></table>',PHP_EOL;

}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php</p>';
}

break;

//--------
case 'd':
//--------

$arrSeries[$L['New_items']]=GetSerie($arrN,$arrNs,$y,$intMaxBt);
$arrSeries[$L['Closed_items']]=GetSerie($arrC,$arrCs,$y,$intMaxBt);
$arrSeries[$L['Backlog']]=GetSerie($arrT,$arrTs,$y,$intMaxBt);
$arrSeriesColor = array($L['New_items']=>'#000066',$L['Closed_items']=>'#990099',$L['Backlog']=>'#009999');

QTtablechart($arrHeader,$arrSeries,$arrSeriesColor);

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrN[$intYear] = QTarrayzero($arrN[$intYear]);
$arrC[$intYear] = QTarrayzero($arrC[$intYear]);
}

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') )
{
  // absciseLabel
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  if ( file_exists('pChart/pCache.class') )
  {
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);
  }

  // QTpchart(charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul)
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

  // -------
  // DISPLAY
  // -------

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuType),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  
  echo '<table class="layout">',PHP_EOL;
  echo '<tr>',PHP_EOL;
  echo '<td class="col1"><img class="pchart" src="'.$strChart1.'"/></td>',PHP_EOL;
  echo '<td class="col2"><img class="pchart" src="'.$strChart2.'"/></td>',PHP_EOL;
  echo '</tr>';
  echo '<tr>',PHP_EOL;
  echo '<td class="col1"><img class="pchart" src="'.$strChart3.'"/></td>',PHP_EOL;
  echo '<td class="col2"><img class="pchart" src="'.$strChart4.'"/></td>',PHP_EOL;
  echo '</tr>';
  echo '</table>';

}
elseif ( file_exists('bin/qt_lib_graph.php') )
{

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuValue),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  
  echo '<table class="t-item">',PHP_EOL;

  echo '<tr class="t-item">',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrN[$y])),350,100,QTroof(QTpercent($arrN[$y])),3,'P',$L['New_items'].' (%)');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,$arrN[$y]),320,100,QTroof($arrN[$y]),3,true,$L['New_items']);
  }

  echo '</td>',PHP_EOL;
  echo '<td>&nbsp;</td>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrC[$y])),350,100,QTroof(QTpercent($arrC[$y])),3,'P',$L['Closed_items'].' (%)','','2');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,$arrC[$y]),320,100,QTroof($arrC[$y]),3,true,$L['Closed_items'],'','2');
  }

  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  echo '<tr>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y])),350,100,100,3,'P',$L['Backlog'].' (%)','','3');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,$arrT[$y]),320,100,QTroof($arrT[$y]),3,true,$L['Backlog'],'','3');
  }

  echo '</td>',PHP_EOL;
  echo '<td>&nbsp;</td>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='p' )
  {
  QTbarchart(QTarraymerge($arrA,QTcumul(QTpercent($arrT[$y]))),350,100,100,3,'P',$L['Backlog_cumul'].' (%)','','3');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTcumul($arrT[$y])),320,100,QTroof(QTcumul($arrT[$y])),3,true,$L['Backlog_cumul'],'','3');
  }

  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;

}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php</p>';
}

break;

//--------
case 'dt':
//--------

foreach($arrYears as $y)
{
$arrSeries[$L['New_items']] = GetSerie($arrN,$arrNs,$y,$intMaxBt);
$arrSeries[$L['Closed_items']] = GetSerie($arrC,$arrCs,$y,$intMaxBt);
$arrSeriesColor = array($L['New_items']=>($y==$intCurrentYear ? '#000066' : '#00AFFF'),$L['Closed_items']=>($y==$intCurrentYear ? '#990099' : '#F1B8FF'));
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array(),$y);
}

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Trends'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuTrend),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  
$arrSeries[$L['New_items']] = GetSerieDelta($arrN,$arrNs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeries[$L['Closed_items']] = GetSerieDelta($arrC,$arrCs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeriesColor = array($L['New_items']=>'',$L['Closed_items']=>'');
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array(),L('Trends'));

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrN[$intYear] = QTarrayzero($arrN[$intYear]);
$arrC[$intYear] = QTarrayzero($arrC[$intYear]);
}

// GRAPH

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') )
{
  // abscise Label (2 characters)
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  if ( file_exists('pChart/pCache.class') )
  {
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);
  }

  // QTpchart(charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul)
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['Items_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrN[$y-1],'Serie2'=>$arrN[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Replies_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrC[$y-1],'Serie2'=>$arrC[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    2);

  // DISPLAY

  echo '<div style="margin:0 auto; width:600px">',PHP_EOL;
  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuType),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  echo '<img class="pchart" src="'.$strChart1.'"/><br/>';
  echo '<img class="pchart" src="'.$strChart2.'"/><br/>';
  echo '</div>',PHP_EOL;

}
elseif ( file_exists('bin/qt_lib_graph.php') )
{

  echo '<table class="header">',PHP_EOL;
  echo '<tr>';
  echo '<td class="title"><h2>',L('Charts'),'</h2></td>',PHP_EOL;
  echo '<td class="controls">',implode(' &middot; ',$arrMenuValue),'</td>';
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  echo '<table class="t-item">',PHP_EOL;

  // first serie

  $intTopY = QTroof( array(max($arrN[$y-1]),max($arrN[$y])) );

  echo '<tr>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrN[$y-1]),320,100,$intTopY,3,true,$L['New_items'].' '.($y-1));
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrN[$y-1])),350,100,100,3,'P',$L['New_items'].' (%) '.($y-1));
  }

  echo '</td>',PHP_EOL;
  echo '<td>&nbsp;</td>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  // second serie

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrN[$y]),320,100,$intTopY,3,true,$L['New_items'].' '.$y);
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrN[$y])),350,100,100,3,'P',$L['New_items'].' (%) '.$y);
  }

  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  // 3d serie

  $intTopY = QTroof( array(max($arrC[$y-1]),max($arrC[$y])) );

  echo '<tr>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrC[$y-1]),320,100,$intTopY,3,true,$L['Closed_items'].' '.($y-1),'','2');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrC[$y-1])),350,100,100,3,'P',$L['Closed_items'].' (%) '.($y-1),'','2');
  }

  echo '</td>',PHP_EOL;
  echo '<td>&nbsp;</td>',PHP_EOL;
  echo '<td style="width:355px">',PHP_EOL;

  // 4th serie

  if ( $ch['value']=='a' )
  {
  QTbarchart(QTarraymerge($arrA,$arrC[$y]),320,100,$intTopY,3,true,$L['Closed_items'].' '.$y,'','2');
  }
  else
  {
  QTbarchart(QTarraymerge($arrA,QTpercent($arrC[$y])),350,100,$intTopY,3,'P',$L['Closed_items'].' (%) '.$y,'','2');
  }

  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  echo '</table>',PHP_EOL;
}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php</p>';
}

break;

//--------
default:
//--------
die('Invalid tab');
}