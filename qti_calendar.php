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
if ( !isset($_GET['s']) ) die('Missing section id...');
if ( !sUser::CanView('V2') ) { $oHtml->PageMsg(11); return; }
if ( !sUser::CanViewCalendar() ) { $oHtml->PageMsg(101); return; }
if ( !isset($_SESSION[QT]['cal_shownews']) ) $_SESSION[QT]['cal_shownews']=FALSE;
if ( !isset($_SESSION[QT]['cal_showinsp']) ) $_SESSION[QT]['cal_showinsp']=FALSE;
if ( !isset($_SESSION[QT]['cal_showall']) ) $_SESSION[QT]['cal_showall']=FALSE;
if ( !isset($_SESSION[QT]['cal_showZ']) ) $_SESSION[QT]['cal_showZ']=FALSE;

// ---------
// FUNCTIONS
// ---------

function FirstDayDisplay($intYear,$intMonth,$intWeekstart=1)
{
  // search date of the first 'monday' (or weekstart if not 1)
  // before the beginning of the month (to display gey-out in the calendar)
  if ( $intWeekstart<1 || $intWeekstart>7 ) die ('FirstDayDisplay: Arg #3 must be an int (1-7)');

  $arr = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference
  $strWeekstart = $arr[$intWeekstart];
  $d = mktime(0,0,0,$intMonth,1,$intYear); // first day of the month
  if ( strtolower(date('l',$d))==$strWeekstart ) return $d;

  for($i=1;$i<8;++$i)
  {
    $d = strtotime('-1 day',$d);
    if ( strtolower(date('l',$d))==$strWeekstart ) return $d;
  }
  return $d;
}

function ArraySwap($arr,$n=1)
{
  // Move the first value to the end of the array. Action is repeated $n times. Keys are not moved.
  if ($n>0)
  {
    $arrK = array_keys($arr);
    while($n>0) { array_push($arr,array_shift($arr)); $n--; }
    $arrV = array_values($arr);
    $arr = array();
    for($i=0;$i<count($arrK);++$i) $arr[$arrK[$i]] = $arrV[$i];
  }
  return $arr;
}

// ---------
// INITIALISE
// ---------

$s = -1;
$v = 'firstpostdate';
QThttpvar('s v','int str');
if ( !in_array($v,array('firstpostdate','lastpostdate','wisheddate')) ) die('Wring calendar field');

$bSSE = false;
if ( $s>=0 && sAppMem::useSSE() ) $bSSE=true;

$intYear = intval(date('Y')); if ( isset($_GET['y']) ) $intYear = intval($_GET['y']);
$intYearP  = $intYear;
$intYearN  = $intYear;
$intMonth = intval(date('n')); if ( isset($_GET['m']) ) $intMonth = intval($_GET['m']);
$intMonthP = $intMonth-1; if ( $intMonthP<1 ) { $intMonthP=12; --$intYearP; }
$intMonthN = $intMonth+1; if ( $intMonthN>12 ) { $intMonthN=1; ++$intYearN; }
$strMonth  = '0'.$intMonth; $strMonth = substr($strMonth,-2,2);
$strMonthP = '0'.$intMonthP; $strMonthP = substr($strMonthP,-2,2);
$strMonthN = '0'.$intMonthN; $strMonthN = substr($strMonthN,-2,2);
$arrWeekCss = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference

$dToday  = mktime(0,0,0,date('n'),date('j'),date('Y'));
$dMonth  = mktime(0,0,0,$intMonth,1,$intYear); // First day of the month
$dMonthP = mktime(0,0,0,$intMonthP,1,$intYearP);
$dMonthN = mktime(0,0,0,$intMonthN,1,$intYearN);

if ( $intYear>2100 ) die('Invalid year');
if ( $intYear<1900 ) die('Invalid year');
if ( $intMonth>12 ) die('Invalid month');
if ( $intMonth<1 ) die('Invalid month');

// moderator settings

$strOptions = '';
if ( isset($_GET['Maction']) )
{
  if ( $_GET['Maction']=='this' ) $_SESSION[QT]['cal_showall'] = false;
  if ( $_GET['Maction']=='all' ) $_SESSION[QT]['cal_showall'] = true;
  if ( $_GET['Maction']=='show_Z' ) $_SESSION[QT]['cal_showZ']=true;
  if ( $_GET['Maction']=='hide_Z' ) $_SESSION[QT]['cal_showZ']=false;
  if ( $_GET['Maction']=='hide_News' ) $_SESSION[QT]['cal_shownews'] = false;
  if ( $_GET['Maction']=='show_News' ) $_SESSION[QT]['cal_shownews'] = true;
  if ( $_GET['Maction']=='hide_Insp' ) $_SESSION[QT]['cal_showinsp'] = false;
  if ( $_GET['Maction']=='show_Insp' ) $_SESSION[QT]['cal_showinsp'] = true;
}
if ( !$_SESSION[QT]['cal_showZ'] ) $strOptions .= 'status<>"Z" AND ';
if ( !$_SESSION[QT]['cal_showall'] ) $strOptions .= 'forum='.$s.' AND ';
if ( !$_SESSION[QT]['cal_shownews'] ) $strOptions .= 'type<>"A" AND ';
if ( !$_SESSION[QT]['cal_showinsp'] ) $strOptions .= 'type<>"I" AND ';

$oSEC = new cSection($s);

$oVIP->selfurl = 'qti_calendar.php';
$oVIP->selfuri = 'qti_calendar.php?s='.$s.'&amp;v='.$v.'&amp;y='.$intYear.'&amp;m='.$intMonth;
$oVIP->selfname = $L['Section'].': '.$oSEC->name;

$arrS = sMem::Get('sys_statuses');

// Shift language names and cssWeek to match with weekstart setting, if not 1 (monday)

if ( QTI_WEEKSTART>1 )
{
  $L['dateDDD'] = ArraySwap($L['dateDDD'],intval(QTI_WEEKSTART)-1);
  $L['dateDD'] = ArraySwap($L['dateDD'],intval(QTI_WEEKSTART)-1);
  $L['dateD'] = ArraySwap($L['dateD'],intval(QTI_WEEKSTART)-1);
  $arrWeekCss = ArraySwap($arrWeekCss,intval(QTI_WEEKSTART)-1);
}

// MAP MODULE

if ( UseModule('map') )
{
  $strCheck=$s;
  include 'qtim_map_ini.php';
  if ( empty($jMapSections) && file_exists(APP.'m_map/config_map.php') ) include APP.'m_map/config_map.php';
}
else
{
  $bMap=false;
}

// --------
// LIST OF TOPICS PER DAY IN THIS FORUM
// --------

$arrTopics=array();
$arrTopicsN=array();
$intCountTopics=0;
$intCountTopicsP=0;
$intCountTopicsN=0;
$arrCountTopics=array();

$oDB->Query(
'SELECT id,forum,numid,type,status,'.$v.' as eventday,y,x FROM '.TABTOPIC.' WHERE '.$strOptions.
'('.SqlDateCondition(($intYearP*100+$intMonthP),$v,6).' OR '.SqlDateCondition(($intYear*100+$intMonth),$v,6).' OR '.SqlDateCondition(($intYearN*100+$intMonthN),$v,6).')'
);

while($row=$oDB->Getrow()) {
if ( !empty($row['eventday']) ) {
  $strYMD = substr($row['eventday'],0,8);
  $strM = substr($row['eventday'],4,2); $intM = intval($strM);
  $strD = substr($row['eventday'],6,2); $intD = intval($strD);

  if ( $strM==$strMonth || ($strM==$strMonthP && $intD>23) || ($strM==$strMonthN && $intD<7) )
  {
  if ( $intCountTopics<15 ) $arrTopics[$strYMD][]=$row;
  if ( !isset($arrCountTopics[$row['status']]) ) { $arrCountTopics[$row['status']]=1; } else { ++$arrCountTopics[$row['status']]; }
  }
  if ( $strM==$strMonth ) ++$intCountTopics;
  if ( $strM==$strMonthN ) { $arrTopicsN[$intD]=1; ++$intCountTopicsN; }
}}

// --------
// HTML START
// --------

$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_calendar.css" />';
$oHtml->scripts_end[] = '<script type="text/javascript">
function show_gmap(latlng,id="gmapCalendar")
{
  if ( !doc.getElementById(id) ) return;
  if ( latlng=="" ) { doc.getElementById(id).style.visibility="hidden"; return; }
  doc.getElementById(id).style.visibility="visible";
  gmapPan(latlng);
}
if ( doc.getElementById("gmapCalendar") ) doc.getElementById("gmapCalendar").style.visibility="hidden";
</script>
';
$oHtml->scripts_jq[] = '
$(function() {
  $(".jmouseover").mouseover(function() {
    var id = $(this).attr("id");
    var ico = $("img",this).clone(); ico.attr("id",id+"-itemicon-preview");
    $.ajax({
      url: "bin/qti_j_topic.php",
      data: { term: id, iso: "'.QTiso().'", lang: "'.GetLang().'"},
      success: function(data) { if ( data.length>0 ) { document.getElementById("previewcontainer").innerHTML=data; $("#preview-itemicon").html(ico); } }
    });
  });
});
';

if ( $bSSE )
{
if ( empty($oHtml->scripts_jq) ) $oHtml->scripts_jq[] = '/* use jquery */';
$oHtml->scripts[] = '<script type="text/javascript">
var cseMaxRows = '.(defined('SSE_MAX_ROWS') ? SSE_MAX_ROWS : 2).';
var cseShowZ = '.($_SESSION[QT]['cal_showZ'] ? 1 : 0).';

if(typeof(EventSource)=="undefined")
{
  window.setTimeout(function(){location.reload(true);}, 120000); // use refresh (120s) when browser does not support SSE
}
else
{
  var sseOrigin = "'.(defined('SSE_ORIGIN') ? SSE_ORIGIN : 'http://localhost').'";
  window.setTimeout(function(){
  var script = document.createElement("script");
  script.src = "bin/js/qti_cse_calendar.js";
  document.getElementsByTagName("head")[0].appendChild(script);
  },'.(defined('SSE_LATENCY') ? SSE_LATENCY : 10000).');
 }
</script>';
}

include 'qti_inc_hd.php';

// Moderator actions
if ( sUser::IsStaff() )
{
echo '<div class="data-hd-r">
<div id="optionsbar">
<form method="get" action="',Href(),'" id="modaction">
',L('Role_'.sUser::Role()),':&nbsp;<input type="hidden" name="s" value="',$s,'" />
<input type="hidden" name="v" value="',$v,'" />
<input type="hidden" name="y" value="',$intYear,'" />
<input type="hidden" name="m" value="',$intMonth,'" />
<select name="Maction" onchange="document.getElementById(\'modaction\').submit();">
<option value="">&nbsp;</option>
';
if ($_SESSION[QT]['cal_showZ'])    { echo '<option value="hide_Z">&#9745; ',L('Item_closed_show'),'</option>'; } else { echo '<option value="show_Z">&#9744; ',L('Item_closed_show'),'</option>'; }
if ($_SESSION[QT]['cal_shownews']) { echo '<option value="hide_News">&#9745; ',L('Item_news_show'),'</option>';} else { echo '<option value="show_News">&#9744; ',L('Item_news_show'),'</option>'; }
if ($_SESSION[QT]['cal_showinsp']) { echo '<option value="hide_Insp">&#9745; ',L('Item_insp_show'),'</option>';} else { echo '<option value="show_Insp">&#9744; ',L('Item_insp_show'),'</option>'; }
if ($_SESSION[QT]['cal_showall'])  { echo '<option value="this">&#9745; ',L('Item_show_all'),'</option>'; } else { echo '<option value="all">&#9744; ',L('Item_show_all'),'</option>'; }

echo '
</select><input type="submit" name="Mok" value="',$L['Ok'],'" id="action_ok" />
<script type="text/javascript">
document.getElementById("action_ok").style.display="none";
</script>
</form>
</div>
</div>
';
}

// --------
// MAIN CALENDAR
// --------

$dFirstDay = FirstDayDisplay($intYear,$intMonth,QTI_WEEKSTART);
$intWeeknumber = intval(date('W',$dFirstDay));

// DISPLAY MAIN CALENDAR

$arrYears = array($intYear-1=>$intYear-1,$intYear,$intYear+1);
if ( !isset($arrYears[intval(date('Y'))]) ) $arrYears[intval(date('Y'))]=intval(date('Y'));

echo '
<form method="get" action="',Href(),'">
<table class="cal-header">
<tr>
<td class="cal-title">';
if ( date('n',$dMonth)>1 ) { echo '<a class="button" href="',Href(),'?s=',$s,'&amp;v=',$v,'&amp;y=',$intYear,'&amp;m='.(date('n',$dMonth)-1).'"><i class="fa fa-chevron-left"></i></a>'; } else { echo '<a class="button disabled" href="javascript:void(0)"><i class="fa fa-chevron-left"></i></a>'; }
if ( date('n',$dMonth)<12 ) { echo '<a class="button" href="',Href(),'?s=',$s,'&amp;v=',$v,'&amp;y=',$intYear,'&amp;m='.(date('n',$dMonth)+1).'"><i class="fa fa-chevron-right"></i></a>'; } else { echo '<a class="button disabled" href="javascript:void(0)"><i class="fa fa-chevron-right"></i></a>'; }
echo ' '.$L['dateMMM'][date('n',$dMonth)],($intYear!=intval(date('Y')) ? ' '.$intYear : '');
echo '</td>
<td class="cal-ctrl">',$L['Display_at'],' <select name="v" onchange="this.form.submit();">
<option value="firstpostdate"',($v=='firstpostdate' ? QSEL : ''),'>',$L['First_message'],'</option>
<option value="lastpostdate"',($v=='lastpostdate' ? QSEL : ''),'>',$L['Last_message'],'</option>
<option value="wisheddate"',($v=='wisheddate' ? QSEL : ''),'>',$L['Wisheddate'],'</option>
</select> ',$L['Month'],' <select name="m">
';
for ($i=1;$i<13;++$i)
{
echo '<option',($i==date('n') ? ' class="bold" ' : ''),' value="',$i,'"',($i==$intMonth ? QSEL : ''),'>',$L['dateMMM'][$i],'</option>';
}
echo '</select> ';
echo '<select name="y">',QTasTag($arrYears,$intYear),'</select> ';
echo '<input type="hidden" name="s" id="s" value="',$s,'" /><input type="submit" name="calOk" id="calOk" value="',$L['Ok'],'" />
</tr>
</table>
</form>
';

echo '<table id="calendar">',PHP_EOL;
echo '<tr>';
echo '<th class="week">&nbsp;</th>';
for ($i=1;$i<8;++$i)
{
  echo '<th style="width:95px">',$L['dateDDD'][$i],'</th>';
}
echo '</tr>',PHP_EOL;

$iShift=0;
for ($intWeek=0;$intWeek<6;++$intWeek)
{
  if ( $intWeeknumber>52 ) $intWeeknumber=1;
  echo '<tr>';
  echo '<th class="week">',$intWeeknumber,'</th>'; ++$intWeeknumber;
  for ($intDay=1;$intDay<8;++$intDay)
  {
    $d = strtotime("+$iShift days",$dFirstDay); ++$iShift;
    $intShiftYear = date('Y',$d);
    $intShiftMonth = date('n',$d);
    $intShiftDay = date('j',$d);
    $intShiftItem = (int)date('Ymd',$d);

		echo '<td class="',$arrWeekCss[$intDay],( date('n',$dMonth)!=date('n',$d) ? ' dateout' : ''),'"',(date('Ymd',$dToday)==date('Ymd',$d) ? ' id="datetoday"' : ''),'>';
		echo '<p class="datenumber">',$intShiftDay,'</p><p class="dateicon">&nbsp;';
		// date info topic
		if ( isset($arrTopics[$intShiftItem]) )
		{
			$intTopics = 0;
			foreach($arrTopics[$intShiftItem] as $intKey=>$arrValues)
			{
				++$intTopics;
				$oTopic = new cTopic($arrValues);

				if ( $bMap ) {
				if ( !empty($oTopic->y) && !empty($oTopic->x) ) {

					$strPname = $intShiftDay.' '.$L['dateMMM'][date('n',$dMonth)].' - ';
					if ( $s==$oTopic->parentid ) { $strPname .= ($oSEC->numfield=='N' ? '' : sprintf($oSEC->numfield,$oTopic->numid)); } else { $strPname .= sprintf('%03s',$oTopic->numid); }
					$strPname .= ' '.$arrS[$oTopic->status]['statusname'];
					$strPlink = '<a class="gmap" href="'.Href('qti_item.php').'?t='.$oTopic->id.'">'.L('Item').'</a>';
					$strPinfo = '<span class="small bold">Lat: '.QTdd2dms($oTopic->y).' <br />Lon: '.QTdd2dms($oTopic->x).'</span><br /><span class="small">DD: '.round($oTopic->y,8).', '.round($oTopic->x,8).'</span><br />'.$strPlink;
					$oMapPoint = new cMapPoint($oTopic->y,$oTopic->x,$strPname,$strPname.'<br />'.$strPinfo);

          // add extra $oMapPoint properties (if defined in section settings)
          $oSettings = getMapSectionSettings($oTopic->parentid,false,$jMapSections);
          if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
          $arrExtData[(int)$row['id']] = $oMapPoint;

				}}

				// icon
				$strTicon = $oTopic->GetIcon($_SESSION[QT]['skin_dir'],'',($oSEC->numfield=='N' ? '%s' : sprintf($oSEC->numfield,$oTopic->numid).' - %s'),'t'.$oTopic->id.'-itemicon');

				$str='';
				if ( $intTopics>=12 )
				{
					echo '...';
					break;
				}
				else
				{
					$str = ' onmouseover="show_gmap(\'\');"';
					if ( $bMap ) {
					if ( $bMapGoogle && !$_SESSION[QT]['m_map_hidelist'] && !empty($oTopic->y) && !empty($oTopic->x) ) {
					$str = ' onmouseover="show_gmap(\''.$oTopic->y.','.$oTopic->x.'\');"';
					}}
					echo '<a class="jmouseover',($oTopic->parentid==$s ? '' : ' othersection'),'" id="t',$oTopic->id,'"',$str,' href="',Href('qti_item.php'),'?t=',$oTopic->id,'">',$strTicon,'</a> ';
				}
			}
		}
  }
  echo '</tr>',PHP_EOL;
  if ( $intShiftMonth>$intMonth && $intShiftYear==$intYear ) break;
}

echo '</table>

';

// --------
// NEXT MONTH
// --------

$dFirstDay = FirstDayDisplay($intYearN,$intMonthN,QTI_WEEKSTART);

// DISPLAY SUBDATA

echo '<table class="cal-footer">
<tr>
<td class="cal-next">
';

// DISPLAY NEXT MONTH

echo '<h2>',$L['dateMMM'][date('n',$dMonthN)],($intYearN!=$intYear ? ' '.$intYearN : ''),'</h2>',PHP_EOL;
echo '<table id="calendarnext">',PHP_EOL;
echo '<tr>';
for ($intDay=1;$intDay<8;++$intDay)
{
echo '<th class="date" title="'.QTstrh($L['dateDDD'][$intDay]).'">',$L['dateD'][$intDay],'</th>';
}
echo '</tr>',PHP_EOL;

  $iShift=0;
  for ($intWeek=0;$intWeek<6;++$intWeek)
  {
    echo '<tr>';
    for ($intDay=1;$intDay<8;++$intDay)
    {
      $d = strtotime("+$iShift days",$dFirstDay); ++$iShift;
      $intShiftYear = date('Y',$d);
      $intShiftMonth = date('n',$d);
      $intShiftDay = date('j',$d);
      // date number
      if ( date('n',$dMonthN)==date('n',$d) )
      {
        echo '<td class="date ',$arrWeekCss[$intDay],'"',(date('Ymd',$dToday)==date('Ymd',$d) ? ' id="datetoday"' : ''),'>';
        if ( !empty($arrTopicsN[$intShiftDay]) )
        {
          echo '<a href="',Href('qti_calendar.php'),'?s=',$s,'&amp;y=',$intYearN,'&amp;m=',$intMonthN,'">',$intShiftDay,'</a> ';
        }
        else
        {
          echo $intShiftDay;
        }
      }
      else
      {
        echo '<td class="date dateout">';
        echo $intShiftDay;
      }
      echo '</td>';
    }
    echo '</tr>',PHP_EOL;
    if ( $intShiftMonth>$intMonthN && $intShiftYear==$intYearN ) break;
  }

echo '</table>',PHP_EOL;

echo '</td>',PHP_EOL;
echo '<td class="cal-preview">',PHP_EOL;

// DISPLAY Preview

echo '<h2>',$L['Preview'],'</h2>',PHP_EOL;
echo '<script type="text/javascript"></script><noscript><p class="small">Your browser does not support JavaScript</p></noscript>';
echo '<div id="previewcontainer"></div>';

echo '</td>',PHP_EOL;

echo '<td class="cal-location">',PHP_EOL;

// DISPLAY MAP

if ( $bMap )
{
  echo '<!-- Map module -->',PHP_EOL;
  if ( count($arrExtData)>0 )
  { 
    $oCanvas = new cCanvas();
    $oCanvas->Header( $arrExtData );
    $oCanvas->Footer( sprintf(L('map_items'),count($arrExtData),L('item',$intCountTopics)) );
    echo $oCanvas->Render( false, 'gmapCalendar' );
  }
  else
  {
    echo '<p class="gmap nomap">'.L('map_No_coordinates').'</p>',PHP_EOL;
  }
  echo '<!-- Map module end -->',PHP_EOL;
}
echo '</td>',PHP_EOL;

echo '</tr>
</table>
';

$strDetailLegend = '<p class="preview_section"><b>'.$L['dateMMM'][date('n',$dMonth)].'</b> '.L('Item',$intCountTopics).'</p>';
foreach($arrCountTopics as $strKey=>$intValue)
{
$strDetailLegend .= '<p class="preview_section">'.$intValue.' '.$arrS[$strKey]['name'].'</p>';
}
$strDetailLegend .= '<br />';
$strDetailLegend .= '<p class="preview_section"><b>'.$L['dateMMM'][date('n',$dMonthN)].'</b> '.L('Item',$intCountTopicsN).'</p>';

// --------
// HTML END
// --------

// MAP MODULE

if ( $bMap )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_map_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_map_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_map_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_map_gcenter']));

  // center on the first item
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    break;
    }
  }
  // update center
  $_SESSION[QT]['m_map_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
      $user_symbol = $gmap_symbol; // required to reset symbol on each user
      $user_shadow = $gmap_shadow;
      if ( !empty($oMapPoint->icon) ) $user_symbol = $oMapPoint->icon;
      if ( !empty($oMapPoint->shadow) ) $user_shadow = $oMapPoint->shadow;
      $gmap_markers[] = QTgmapMarker($oMapPoint->y.','.$oMapPoint->x,false,$user_symbol,$oMapPoint->title,$oMapPoint->info,$user_shadow);
    }
  }
  $gmap_functions[] = '
  function zoomToFullExtend()
  {
    if ( markers.length<2 ) return;
    var bounds = new google.maps.LatLngBounds();
    for (var i=markers.length-1; i>=0; i--) bounds.extend(markers[i].getPosition());
    map.fitBounds(bounds);
  }
  function showLocation(address)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( marker )
        {
          marker.setPosition(results[0].geometry.location);
        } else {
          marker = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: "Move to define the default map center"});
        }
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

  ';
  include 'qtim_map_load.php';
}

include 'qti_inc_ft.php';