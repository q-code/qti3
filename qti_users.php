<?php

/**
* PHP version 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
if ( !sUser::CanView('V4') ) { $oHtml->PageMsg(11); return; }

include Translate(APP.'_reg.php');

// CHANGE USER INTERFACE

if (isset($_GET['view'])) $_SESSION[QT]['viewmode'] = strtolower(substr($_GET['view'],0,1));

// INITIALISE
$intIPP=25; //items per page (instead of $_SESSION[QT]['items_per_page'])

// Protection against injection: 'order','group','dir' are controlled
$strGroup = 'all';
$strOrder = 'name';
$strDirec = 'asc';
$intLimit = 0;
$intPage  = 1;
if ( isset($_GET['group']) ) { $strGroup = substr($_GET['group'],0,7); } // protection against injection
if ( isset($_GET['page']) )  { $intLimit = (intval($_GET['page'])-1)*$intIPP; $intPage = intval($_GET['page']); }
if ( isset($_GET['order']) ) { $strOrder = strip_tags(substr($_GET['order'],0,15)); } // protection against injection
if ( isset($_GET['dir']) )   { if ( strtolower($_GET['dir'])=='desc' ) $strDirec = 'desc'; } // protection against injection

$oVIP->selfurl = 'qti_users.php';
$oVIP->selfname = $L['Memberlist'];

// MAP MODULE

$bMap=false;
if ( UseModule('map') )
{
  include Translate('qtim_map.php');
  include 'qtim_map_lib.php';
  if ( QTgcanmap('U') ) $bMap=true;
  if ( $bMap ) $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qtim_map.css" />';

  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_map_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_map_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_map_hidelist']) ) $_SESSION[QT]['m_map_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_map_symbols']) ) $_SESSION[QT]['m_map_symbols']='0';
  $oSettings = getMapSectionSettings('U');
}

// COUNT

switch($strGroup)
{
  case 'all': $strWhere = ''; break;
  case '0': $strWhere = ' AND '.FirstCharCase('name','a-z'); break;
  default:
    $arr = explode('|',$strGroup);
    $arrOr = array();
    foreach($arr as $str)
    {
    $i = strlen($str);
    $arrOr[] = FirstCharCase('name','u',$i).'="'.strtoupper($str).'"';
    }
    $strWhere = ' AND ('.implode(' OR ',$arrOr).')';
    break;
}

$intTotal = cVIP::SysCount('members');
if ($strGroup=='all')
{
$intCount = $intTotal;
}
else
{
$oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE id>0'.$strWhere);
$row = $oDB->Getrow();
$intCount = $row['countid'];
}

// User menu

if ( sUser::IsStaff() ) include 'qti_inc_menu.php';

// --------
// HTML START
// --------

include 'qti_inc_hd.php';

// --------
// Title and top 5
// --------

// Top 5 (float right)

echo '<div class="infobox" id="topparticipants">',PHP_EOL;
echo '<h1>',$L['Top_participants'],'</h1>',PHP_EOL;
echo '<table>',PHP_EOL;

$oDB->Query( LimitSQL('name, id, numpost FROM '.TABUSER.' WHERE id>0','numpost DESC',0,5) );
for ($i=0;$i<($_SESSION[QT]['viewmode']=='c' ? 2 : 5);++$i)
{
$row = $oDB->Getrow();
if (!$row) break;
$str = isset($row['name'][15]) ? QTstrh($row['name']) : '';
echo '<tr><td><a href="qti_user.php?id=',$row['id'],'" title="'.$str.'">',QTtrunc($row['name'],15),'</a></td><td class="right">',$row['numpost'],'</td></tr>',PHP_EOL;
}
echo '</table>',PHP_EOL,'</div>',PHP_EOL;

// New user (float right, hidden)

if ( !empty($strUserform) ) echo $strUserform;

// Title

echo '<h2>',$oVIP->selfname,'</h2>';
echo '<p>',( $strGroup=='all' ? $intTotal.' '.L('Users') : $intCount.' / '.$intTotal.' '.L('Users') ),( isset($strPageMenu) ? ' &middot; '.$strPageMenu : '' ),'</p>',PHP_EOL;

echo '<div class="separator"></div>',PHP_EOL;


// --------
// Button line and pager
// --------

// -- build pager --

$strPager = MakePager("qti_users.php?group=$strGroup&order=$strOrder&dir=$strDirec",$intCount,$intIPP,$intPage);
if ( !empty($strPager) ) $strPager = '<p class="pager">'.$L['Page'].$strPager.'</p>';
if ( $intCount<$intTotal ) $strPager = '<span class="small">'.$intCount.' '.L('found_from').' '.$intTotal.' '.L('users').'</span>'.(empty($strPager) ? '' : ' | '.$strPager);

// -- Display button line (if more that tpp users) and pager --

// GROUP LINE

if ( $intCount>$intIPP || isset($_GET['group']) )
{
  // optimize groups in lettres bar
  if ( $intCount>500 ) { $intChars=1; } else { $intChars=($intCount>$intIPP*2 ? 2 : 3); }
  $strGroups = HtmlLettres( Href().'?'.GetURI('group,page'), $strGroup, $L['All'], 'lettres clear', L('Username').' '.L('starting_with').' ', $intChars );
}

if ($intTotal>$intIPP) echo $strGroups,PHP_EOL;

// end if no result
if ($intCount==0)
{
  if ( !empty($strPager) ) echo '<table class="pagertop"><tr><td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL;

  $table = new cTable('t1','t-user',$intCount);
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('None').'...</p>',true,'','r1');

  include 'qti_inc_ft.php';
  exit;
}

// --------
// Memberlist
// --------

$bCompact = FALSE;
if ( $_SESSION[QT]['avatar']=='0' ||  $_SESSION[QT]['viewmode']=='c' ) $bCompact = true;

// === TABLE DEFINITION ===

$table = new cTable('t1','t-user',$intCount);
$table->activecol = 'user'.$strOrder;
$table->activelink = '<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;page=1&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'">%s</a> <i class="fa fa-caret-'.($strDirec=='asc' ? 'up' : 'down').'"><i/>';
// column headers
if ( $bCompact )
{
$table->th['username'] = new cTableHead($L['Username'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=name&amp;dir=asc&amp;page=1">%s</a>'); $table->th['username']->Add('style','width:150px');
}
else
{
$table->th['userphoto'] = new cTableHead('<i class="fa fa-camera" title="'.L('Picture').'"></i>');
$table->th['username'] = new cTableHead($L['Username'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=name&amp;dir=asc&amp;page=1">%s</a>');
}
if ( $bMap ) { $table->th['usermarker'] = new cTableHead('&nbsp;'); $table->th['usermarker']->Add('style','width:15px'); }
$table->th['userrole'] = new cTableHead($L['Role'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=role&amp;dir=asc&amp;page=1">%s</a>');
$table->th['usercontact'] = new cTableHead($L['Contact']);
$table->th['userlocation'] = new cTableHead($L['Location'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=location&amp;dir=asc&amp;page=1">%s</a>');
$table->th['usernumpost'] = new cTableHead($L['Messages'],'','center','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=numpost&amp;dir=desc&amp;page=1">%s</a>');
// create column data (from headers identifiers) and add class to all
foreach($table->th as $key=>$th) { $table->th[$key]->Add('class','c-'.$key); $table->td[$key] = new cTableData('','','c-'.$key); }

// === TABLE START DISPLAY ===

// Pager

if ( !empty($strPager) ) echo '<table class="pagertop"><tr><td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL;

// Table

echo PHP_EOL;
echo $table->Start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $table->GetTHrow().PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

$oDB->Query( LimitSQL('* FROM '.TABUSER.' WHERE id>0'.$strWhere, $strOrder.' '.$strDirec, $intLimit,$intIPP,$intCount) );

$intWhile=0;
$strAlt='r1';
while($row=$oDB->Getrow())
{
	// privacy control for map and location field
	if ( sUser::IsPrivate($row['privacy'],$row['id']) ) { $row['y']=null; $row['x']=null; }

	// prepare row
	$table->row = new cTableRow('','t-user '.$strAlt.' hover');

	// prepare values, and insert value into the cells
  $table->SetTDcontent( FormatItemRow($table->GetTHnames(),$row,$bMap), false ); // adding extra columns not allowed

	//show row content
	echo $table->GetTDrow().PHP_EOL;

	if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }

	// map settings
	if ( $bMap && !QTgempty($row['x']) && !QTgempty($row['y']) )
	{
	  $y = (float)$row['y']; $x = (float)$row['x'];
	  $strPname = QTconv($row['name'],'U');
	  $strPinfo = $row['name'].'<br/><a class="gmap" href="'.Href('qti_user.php').'?id='.$row['id'].'">'.L('Profile').'<\/a>';
	  if ( !empty($row['photo']) ) $strPinfo = AsImg(QTI_DIR_PIC.$row['photo'],'',$row['name'],'markerprofileimage').$strPinfo;
	  $oMapPoint = new cMapPoint($y,$x,$strPname,$strPinfo);

    // add extra $oMapPoint properties (if defined in section settings)
    if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
	  $arrExtData[(int)$row['id']] = $oMapPoint;
	}

  ++$intWhile;
	//odbcbreak
	if ( $intWhile>=$intIPP ) break;
}

// === TABLE END DISPLAY ===

echo '</tbody>
</table>
';

// -- Display pager --

// Pager

if ( !empty($strPager) ) echo '<table class="pagerbot"><tr><td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL,PHP_EOL;

// MAP MODULE, Show map

if ( $bMap )
{
  echo '<!-- Map module -->',PHP_EOL;
  if ( count($arrExtData)==0 )
  {
    echo '<p class="gmap nomap">'.L('map_No_coordinates').'</p>';
    $bMap=false;
  }
  else
  {
    //select zoomto (maximum 20 items in the list)
    $str = '';
    if ( count($arrExtData)>1 )
    {
      $str = '<p class="gmap commands" style="margin:0 0 4px 0"><a class="gmap" href="javascript:void(0)" onclick="zoomToFullExtend(); return false;">'.$L['map_zoomtoall'].'</a> | '.L('Show').' <select class="gmap" id="zoomto" name="zoomto" size="1" onchange="gmapPan(this.value);">';
      $str .= '<option class="small_gmap" value="'.$_SESSION[QT]['m_map_gcenter'].'"> </option>';
      $i=0;
      foreach($arrExtData as $oMapPoint)
      {
      $str .= '<option class="small_gmap" value="'.$oMapPoint->y.','.$oMapPoint->x.'">'.$oMapPoint->title.'</option>';
      ++$i; if ( $i>20 ) break;
      }
      $str .= '</select></p>'.PHP_EOL;
    }

    echo '<div class="gmap">',PHP_EOL;
    echo ($_SESSION[QT]['m_map_hidelist'] ? '' : $str.PHP_EOL.'<div id="map_canvas"></div>'.PHP_EOL);
    echo '<p class="gmap" style="margin:4px 0 0 0">',sprintf($L['map_items'],strtolower( L('User',count($arrExtData))),strtolower(L('User',$intCount)) ),'</p>',PHP_EOL;
    echo '</div>',PHP_EOL;

    // Show/Hide

    if ( $_SESSION[QT]['m_map_hidelist'] )
    {
    echo '<div class="canvashandler"><a class="canvashandler" href="',Href(),'?showmap"><i class="fa fa-caret-down"></i> ',$L['map_Show_map'],'</a></div>',PHP_EOL;
    }
    else
    {
    echo '<div class="canvashandler"><a class="canvashandler" href="',Href(),'?hidemap"><i class="fa fa-caret-up"></i> ',$L['map_Hide_map'],'</a></div>',PHP_EOL;
    }
  }
  echo '<!-- Map module end -->',PHP_EOL;
}

// --------
// HTML END
// --------

// MAP MODULE

if ( $bMap && !$_SESSION[QT]['m_map_hidelist'] )
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