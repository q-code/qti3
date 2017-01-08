<?php

// QuickTicket 3.0 build:20160703

function GetSerie($arrX,$arrXs,$y,$intMaxBt)
{
  $arrValues = array();
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt) 
  {
  if ( isset($arrX[$y][$intBt]) ) { $arrValues[$intBt]=$arrX[$y][$intBt]; } else { $arrValues[$intBt]='&middot;'; }
  }
  $arrValues[$intMaxBt+1]=$arrXs[$y];
  return $arrValues;
}

function GetSerieDelta($arrX,$arrXs,$y,$intMaxBt,$bPercent=false,$strNA='&middot;',$bRedGreen=true)
{
  $arrValues = array();
  for ($intBt=1;$intBt<=$intMaxBt;++$intBt)
  {
    $arrValues[$intBt] = '0';
    $i = QTtrend((isset($arrX[$y][$intBt]) ? $arrX[$y][$intBt] : 0),(isset($arrX[$y-1][$intBt]) ? $arrX[$y-1][$intBt] : 0),$bPercent);
    if ( isset($i) )
    {
      $arrValues[$intBt] = $i.($bPercent ? '%' : '');
      if ( $i>0 && $bRedGreen ) $arrValues[$intBt] = '<span style="color:red">'.'+'.$arrValues[$intBt].'</span>';
      if ( $i<0 && $bRedGreen ) $arrValues[$intBt] = '<span style="color:green">'.$arrValues[$intBt].'</span>';
    }
    else
    {
      $arrValues[$intBt] = $strNA;
    } 
  }
  $arrValues[$intMaxBt+1] = 0;
  $i = QTtrend($arrXs[$y],$arrXs[$y-1],$bPercent);
  if ( isset($i) )
  {
    $arrValues[$intBt] = $i.($bPercent ? '%' : '');
    if ( $i>0 && $bRedGreen ) $arrValues[$intMaxBt+1] = '<span style="color:red">'.'+'.$arrValues[$intBt].'</span>';
    if ( $i<0 && $bRedGreen ) $arrValues[$intMaxBt+1] = '<span style="color:green">'.$arrValues[$intBt].'</span>';
  }
  else
  {
    $arrValues[$intMaxBt+1] = $strNA;
  }
  return $arrValues;
}

function GetAbscise($strTime='m',$intMaxBt=13,$strTendaysago=-10)
{
  global $L;
  $arr = array();
  switch($strTime)
  {
  case 'q': for ($i=1;$i<=$intMaxBt;++$i) { $arr[$i]='Q'.$i; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;++$i) { $arr[$i]=substr($L['dateMM'][$i],0,2); } break; // 2 chars only
  case 'd': for ($i=1;$i<=$intMaxBt;++$i) { $arr[$i]=substr(DateAdd($strTendaysago,$i,'day'),-2,2); } break;
  }
  return $arr;
}

function CheckChartCache($Cache)
{
  $intHandle = opendir('pChart/Cache');
  $i = 0;
  while ( false!==($strFile = readdir($intHandle)) ) ++$i;
  closedir($intHandle);
  if ( $i>60 || isset($_GET['clearcache']) ) $Cache->ClearCache();
}
