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
if ( !sUser::CanView('V2') ) { $oHtml->PageMsg(11); return; }

$q = ''; // type of search (if missing use $q='s')
$s = ''; // section $s can be '*' or [int] (after argument checking only [int] is allowed)
$st = ''; // status $st can be '*' or [string]
QThttpvar('q s st','str str str');
if ( empty($q) ) $q='s';
if ( $s==='*' || $s==='' ) $s=-1;
if ( empty($st) ) $st='*';
if ( !is_int($s) ) $s=(int)$s;
if ( $q==='s' && $s<0 ) die('Missing argument $s');

// ---------
// SUBMITTED
// ---------

if ( isset($_POST['Mok']) )
{
  if ( $_POST['Maction']==='show_Z' ) $_SESSION[QT]['show_closed']='1';
  if ( $_POST['Maction']==='hide_Z' ) $_SESSION[QT]['show_closed']='0';
  if ( $_POST['Maction']==='newsontop' ) $_SESSION[QT]['news_on_top']='1';
  if ( $_POST['Maction']==='n10' ) $_SESSION[QT]['items_per_page']='10';
  if ( $_POST['Maction']==='n25' ) $_SESSION[QT]['items_per_page']='25';
  if ( $_POST['Maction']==='n50' ) $_SESSION[QT]['items_per_page']='50';
  if ( $_POST['Maction']==='n100' ) $_SESSION[QT]['items_per_page']='100';
}
if ( isset($_POST['Mok2']) )
{
  switch($_POST['Maction2'])
  {
  case 'nt': $oHtml->Redirect('qti_form_edit.php?s='.$s.'&amp;a=nt',L('New_item')); break;
  case 'Edit_start': $_SESSION[QT]['Items_Edit']='1'; break;
  case 'Edit_stop': $_SESSION[QT]['Items_Edit']='0'; break;
  default: $_SESSION[QT]['lastcolumn']=$_POST['Maction2']; break;
  }
}
if ( isset($_GET['newsnotop']) ) $_SESSION[QT]['news_on_top'] = '0';

// ---------
// INITIALISE
// ---------

$arrSEC = sMem::Get('sys_sections');
$arrSTA = sMem::Get('sys_statuses');

if ( $q==='s' )
{
  $oSEC = new cSection( isset($arrSEC[$s]) ? $arrSEC[$s] : $s );

  // exit if user role not granted
  if ( $oSEC->type==1 && (sUser::Role()==='V' || sUser::Role()==='U') )
  {
  $oVIP->selfname = $L['Section'];
  $oVIP->exitname = cLang::ObjectName(); // index name
  $oHtml->PageMsg( NULL, '<p>'.Error(12).'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>' );
  }
  if ( $oSEC->type==2 && sUser::Role()==='V' )
  {
  $oVIP->selfname = $L['Section'];
  $oVIP->exitname = cLang::ObjectName(); // index name
  $oHtml->PageMsg( NULL, '<p>'.Error(13).'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>' );
  }
  $oVIP->selfname = $L['Section'].': '.$oSEC->name;
}
else
{
  $oSEC = new cSection(); // section is null in case of search query
  $oVIP->selfname = L('Search_results');
}

$oVIP->selfurl = 'qti_items.php';
$oVIP->selfuri = GetURI();

$strCommand = '';
$arrMe[] = array();
$strRefineSearch = '';

$strOrder = $oSEC->ReadOption('order'); if ( empty($strOrder) ) $strOrder = 'lastpostdate'; // use section option
$strDirec = 'desc';                     if ( $strOrder==='title' ) $strDirec = 'asc'; // use asc as default direction in case of title ordering
$strLast = $oSEC->LastColumn(); // use section option lastcolumn
$intPage = 1;
$intLimit = 0;
if ( isset($_GET['page']) ) { $intLimit = (intval($_GET['page'])-1)*$_SESSION[QT]['items_per_page']; $intPage = intval($_GET['page']); }
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower(substr($_GET['dir'],0,4));

// Criteria sql: topics visible for current user ONLY

$strOnTop = '';

if ( $_SESSION[QT]['news_on_top']==='1' || ($_SESSION[QT]['news_on_top']==='2' && $oSEC->ReadOption('nt')!=='0') )
{
 // sqlsrv does not support ||, mysql requires additional setting to support ||, thus CONCAT is use with mysql and sqlsrv
 switch($oDB->type)
  {
  case 'mysql':
  case 'pdo.mysql':
  case 'sqlsrv':
  case 'pdo.sqlsrv': $strOnTop = 'CASE CONCAT(t.type,t.status) WHEN "AA" THEN "A" ELSE "Z" END as typea,'; break;
  default: $strOnTop = 'CASE (t.type||t.status) WHEN "AA" THEN "A" ELSE "Z" END as typea,'; break;
  }
}
$strFields = $strOnTop.'t.*,p.title,p.icon,p.id as postid,p.textmsg,p.issuedate,p.username';

if ( $q==='s' )
{
  $strFrom = ' FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid=p.id';
  $strWhere = ' WHERE t.forum='.$oSEC->uid;
  if ( $oSEC->type==2 && !sUser::IsStaff() ) $strWhere .= ' AND (t.firstpostuser='.sUser::Id().' OR t.type="A")'; // Criteria sql: topics visible for current user ONLY
  if ( $_SESSION[QT]['show_closed']=='0' && $st==='*' ) $strWhere.=' AND t.status<>"Z"'; // Criteria sql: closed topic hidden
  $strCount = 'SELECT count(*) as countid FROM '.TABTOPIC.' t'.$strWhere;
}
else
{
  $strWhere = ' WHERE t.forum>=0';
  if ( $oSEC->type==2 && !sUser::IsStaff() ) $strWhere .= ' AND (t.firstpostuser='.sUser::Id().' OR t.type="A")';// Criteria sql: topics visible for current user ONLY
  if ( $_SESSION[QT]['show_closed']=='0' && $st==='*' ) $strWhere.=' AND t.status<>"Z"'; // Criteria sql: closed topic hidden
  include 'qti_items_qry.php';
}

// Count items visible for current user ONLY

$oDB->Query( $strCount );
$row = $oDB->Getrow();
$intCount = (int)$row['countid'];

// BUTTON LINE AND PAGER

if ( $q==='s' )
{
  if ( $oSEC->status=='1' )
  {
    $strCommand = '<a class="button pageaction disabled">'.L('E_section_closed').'</a>';
  }
  else
  {
    $strCommand = '<a class="button pageaction" href="qti_form_edit.php?s='.$oSEC->uid.'&amp;a=nt" accesskey="n">'.L('New_item').'</a>';
    if ( sUser::Role()==='V' && $_SESSION[QT]['visitor_right']<7 )
    {
    $strCommand = '<a class="button pageaction disabled">'.L('New_item').'</a>';
    }
  }
}

$strCommand .= '<a class="button pagesearch" href="'.Href('qti_search.php').'?'.GetURI('order,dir').'"><i class="i-search fa fa-search"></i></a>';

$strPager = MakePager($oVIP->selfurl.'?'.$oVIP->selfuri,$intCount,(int)$_SESSION[QT]['items_per_page'],$intPage);
if ($strPager!='') $strPager = '<p class="pager">'.L('Page').$strPager.'</p>';

// BUTTONS

$strCommand = $oVIP->BackButton().$strCommand;

// MAP

$bMap=false;
if ( UseModule('map') )
{
  include Translate('qtim_map.php');
  include 'qtim_map_lib.php';
  if ( QTgcanmap($q==='s' ? $oSEC->uid : 'S') ) $bMap=true;

  if ( $bMap ) $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qtim_map.css" />';

  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_map_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_map_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_map_hidelist']) ) $_SESSION[QT]['m_map_hidelist']=false;
}

// Usermenu

include 'qti_inc_menu.php';

// --------
// HTML START
// --------

// Using tags

if ( !empty($_SESSION[QT]['tags']) )
{

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qt_tag.css" />';
$oHtml->scripts_jq[] = '
$(function() {
  $(".tag").hover(function() {
    var oTag = $(this);
    if ( oTag.attr("title")!=="" ) return false;
    $.post("bin/qti_j_tagdesc.php",{s:"'.$s.'",term:oTag.html(),lang:"'.QTiso().'",e0:"('.L('No_description').')"}, function(data){oTag.attr({title:data});});
  });
});
';

}

// SSE (if section is not empty)

if ( $intCount>0 && sAppMem::useSSE() )
{

if ( empty($oHtml->scripts_jq) ) $oHtml->scripts_jq[] = '/* use jquery */';
$oHtml->scripts[] = '<script type="text/javascript">
var cseMaxRows = '.(defined('SSE_MAX_ROWS') ? SSE_MAX_ROWS : 2).';
var cseShowZ = '.$_SESSION[QT]['show_closed'].';
if(typeof(EventSource)=="undefined")
{
  window.setTimeout(function(){location.reload(true);}, 120000); // use refresh (120s) when browser does not support SSE
}
else
{
  var sseOrigin = "'.(defined('SSE_ORIGIN') ? SSE_ORIGIN : 'http://localhost').'";
  var cseStatusnames = '.json_encode(cTopic::Statuses('T',true)).';
  var cseTypenames = '.json_encode(cTopic::Types()).';
  window.setTimeout(function(){
  var script = document.createElement("script");
  script.src = "bin/js/qti_cse_items.js";
  document.getElementsByTagName("head")[0].appendChild(script);
  },'.(defined('SSE_LATENCY') ? SSE_LATENCY : 10000).');
}
</script>';
}

include 'qti_inc_hd.php';

if ( !empty($strDataTitles) || !empty($strPageMenu) )
{
echo '<div id="data-hd">',PHP_EOL;
if ( !empty($strDataTitles) ) echo '<div id="data-hd-l">',$strDataTitles,'</div>',PHP_EOL;
echo '<div id="data-hd-r">',(empty($strPageMenu) ? '&nbsp;' : $strPageMenu),'</div>',PHP_EOL;
echo '</div>',PHP_EOL;
}

// PAGE COMMANDS and PAGER

echo '
<table class="pagertop"><tr>
<td class="pagerleft">',$strCommand,'</td>
<td class="pagerright">',$strPager,'</td>
</tr></table>
';

// End if no results

if ( $intCount==0 )
{
  $table = new cTable('t1','t-item',$intCount);
  $table->th['void'] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.$L['E_no_item'].'...</p>',true,'','r1');
  include 'qti_inc_ft.php';
  return;
}

// DATA COMMANDS

if ( $strDataCommand )
{
  echo PHP_EOL,'<form id="form_items" method="post" action="qti_items_edit.php"><input type="hidden" name="uri" value="'.$oVIP->selfuri.'"/><input type="hidden" id="form_items_action" name="a" value=""/>',PHP_EOL;
  echo '<p class="datacommand top"><i class="fa fa-level-down fa-lg fa-rotate-270" style="margin:0 12px"></i>'.$strDataCommand.'</p>'.PHP_EOL;
}

// LIST TOPICS

// Last column: can be '0' (moderator requests no-field)

if ( isset($_SESSION[QT]['lastcolumn']) ) $strLast = $_SESSION[QT]['lastcolumn'];
if ( empty($strLast) || $strLast==='none' ) $strLast = '';

// === TABLE DEFINITION ===

$table = new cTable('t1','t-item',$intCount);
$table->activecol = $strOrder;
$table->activelink = '<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'">%s</a>&nbsp;<i class="fa fa-caret-'.($strDirec=='asc' ? 'up' : 'down').'"><i/>';
// column headers
if ( !empty($_SESSION[QT]['Items_Edit']) )
{
$table->th['checkbox'] = new cTableHead('<input type="checkbox" name="t1-cb-all" id="t1-cb-all" />','','c-checkbox');
}
$table->th['icon'] = new cTableHead('&bull;','','','<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order=icon&amp;dir=asc">%s</a>');
if ( $q=='ref' || ($oSEC->numfield !=='N' && $oSEC->numfield !=='') ) $table->th['numid'] = new cTableHead($L['Ref'],'','','<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order=numid&amp;dir=desc">%s</a>');
if ( !empty($oSEC->prefix) ) $table->th['prefix'] = new cTableHead('&nbsp;');
$table->th['title'] = new cTableHead($L['Items'],'','','<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order=title&amp;dir=asc">%s</a>');
if ( $q!=='s' && $s<0 ) $table->th['sectiontitle'] = new cTableHead($L['Section']);
$table->th['firstpostname'] = new cTableHead($L['Author'],'','','<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order=firstpostname&amp;dir=asc">%s</a>');
$table->th['lastpostdate'] = new cTableHead($L['Last_message'],'','','<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order=lastpostdate&amp;dir=desc">%s</a>');
$table->th['replies'] = new cTableHead($L['Replys'],'','','<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order=replies&amp;dir=desc">%s</a>');
if ( !empty($strLast) ) $table->th[$strLast] = new cTableHead(ucfirst(L($strLast)),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order='.$strLast.'&amp;dir=desc">%s</a>');

// create column data (from headers identifiers) and add class to all
foreach($table->th as $key=>$th)
{
  $table->th[$key]->Add('class','c-'.$key);
  $table->td[$key] = new cTableData('','','c-'.$key);
}

// prepare dynamic style for 'status'
if ( isset($table->td['status']) )
{
  $arrStatusStyles=array();
  foreach($arrSTA as $id=>$arr) if ( !empty($arr['color']) ) $arrStatusStyles[$id] = 'background:'.$arr['color'];
  $table->td['status']->dynamicValues = $arrStatusStyles;
}

// pre-compute user's items (max 1000 most recents)
if ( QTI_MY_REPLY && sUser::GetUserInfo('numpost',0,true)>0 )
{
  if ( $q==='s' )
  {
    $oDB->Query( 'SELECT p.topic,p.issuedate FROM '.TABPOST.' p WHERE p.forum='.$s.' AND p.type="R" AND p.userid='.sUser::Id().' ORDER BY p.issuedate DESC' );
    while($row=$oDB->Getrow()) { $arrMe[(int)$row['topic']] = (string)$row['issuedate']; if ( count($arrMe)>1000 ) break; }
  }
  else
  {
    $str = str_replace('p.type="P"','p.type="R"',$strWhere); // as seaching for replies, change p.type in criteria
    if ( strpos($str,'p.type="R"')===false ) $str .= ' AND p.type="R"'; // add p.type if missing
    $oDB->Query( 'SELECT p.topic,p.issuedate '.$strFrom.$str.' AND p.userid='.sUser::Id().' ORDER BY p.issuedate DESC' );
    while($row=$oDB->Getrow()) { $arrMe[(int)$row['topic']] = (string)$row['issuedate']; if ( count($arrMe)>1000 ) break; }
  }
}

// === TABLE START DISPLAY ===

$strTableheader = $table->GetTHrow();

echo PHP_EOL;
echo $table->Start().PHP_EOL;
echo '<tbody>'.PHP_EOL;

// ========
if ( $strOrder==='icon' ) { $strOrder='status'; }
if ( $strOrder==='title' ) { $strFullOrder='p.title'; } else { $strFullOrder='t.'.$strOrder; }
$oDB->Query( LimitSQL( $strFields.$strFrom.$strWhere, (empty($strOnTop) ? '' : 'typea ASC, ').$strFullOrder.' '.strtoupper($strDirec), $intLimit, (int)$_SESSION[QT]['items_per_page'] ) );
// ========

$intWhile=0;
$strAlt='r1';
$arrTags=array();
$strTableheaderrows='';
while($row=$oDB->Getrow())
{
  // prepare row
  $table->row = new cTableRow('t1-tr-'.$row['id'],'t-item '.$strAlt.' hover rowlight');

  // check if map applicable in case of search results
  $bRowMap=$bMap;
  if ( $bRowMap && !empty($q) && !QTgcanmap($oSEC->uid) ) $bRowMap=false; // skip map processing when search result includes an item from a section having mapping off

  // prepare values, and insert value into the cells

  $table->SetTDcontent( FormatItemRow($table->GetTHnames(),$row,$bRowMap,$arrSEC), false ); // adding extra columns not allowed

  // add id in each cell
  foreach($table->td as $tdname=>$value) $table->td[$tdname]->Add('id','t'.$row['id'].'-c-'.$tdname);

  // handle dynamic style
  if ( isset($table->td['status']) && is_a($table->td['status'],'cTableData') ) $table->td['status']->AddDynamicAttr('style', $row['status']);

  // Show row content
  if ( !empty($strOnTop) && $row['type']==='A' && $row['status']!=='Z' )
  {
  $strTableheaderrows .= $table->GetTDrow().PHP_EOL;
  }
  else
  {
  echo $table->GetTDrow().PHP_EOL;
  }

  if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }

  // map settings
  if ( $bRowMap && !QTgempty($row['x']) && !QTgempty($row['y']) )
  {
    $y = (float)$row['y']; $x = (float)$row['x'];
    $strIco = ''; if ( isset($row['type']) && isset($row['status']) ) $strIco = cTopic::MakeIcon($row['type'],$row['status'],false,'',$_SESSION[QT]['skin_dir']).' ';
    $strRef = ''; if ( isset($row['numid']) && isset($row['forum']) && isset($arrSEC[(int)$row['forum']]['numfield']) ) $strRef = QTstrh(cTopic::GetRef($row['numid'],$arrSEC[(int)$row['forum']]['numfield'])).' ';
    $strTitle = ''; if ( !empty($row['title']) ) $strTitle = QTstrh($row['title'],24).(isset($row['title'][25]) ? '...' : '');
    $strAttr = ''; if ( isset($row['firstpostdate']) && isset($row['firstpostname']) ) $strAttr = L('By').' '.QTconv($row['firstpostname']).' ('.QTdatestr($row['firstpostdate'],'$','$',true,true).')<br/>';
    if ( isset($row['replies']) ) $strAttr .= L('Reply',(int)$row['replies']).' ';
    $strPname = $strRef.$strTitle;
    $strPinfo = $strIco.$strRef.'<br/>'.$strTitle.'<br/><span class="small">'.$strAttr.'</span> <a class="gmap" href="'.Href('qti_item.php').'?t='.$row['id'].'">'.QTstrh(L('Open')).'</a>';
    $oMapPoint = new cMapPoint($y,$x,$strPname,$strPinfo);

    // add extra $oMapPoint properties (if defined in section settings)
    $oSettings = getMapSectionSettings($q==='s' ? $s : 'S');
    if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
    $arrExtData[(int)$row['id']] = $oMapPoint;
  }

  // collect tags

  if (QTI_LIST_TAG && !empty($_SESSION[QT]['tags']) && count($arrTags)<51 )
  {
  if ( !empty($row['tags']) ) $arrTags = array_unique(array_merge($arrTags,explode(';',$row['tags'])));
  }

  ++$intWhile;
  if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;
}

// === TABLE END DISPLAY ===

echo '</tbody>',PHP_EOL;
echo '<thead>',PHP_EOL;
echo $strTableheader,PHP_EOL;
if ( !empty($strTableheaderrows) )
{
  echo $strTableheaderrows;
  if ( $intWhile>1 ) echo '<tr><th id="newsontop" colspan="',count($table->td),'"><a href="'.Href().'?'.GetURI('newsontop').'&amp;newsnotop'.'" title="'.L('Disable').'">',L('News_on_top'),'&nbsp;<i class="fa fa-times-circle"></i></a></th></tr>',PHP_EOL;
}
echo '</thead>',PHP_EOL;
echo '</table>',PHP_EOL;

// DATA COMMANDS

if ( !empty($_SESSION[QT]['Items_Edit']) ) echo '</form>'.PHP_EOL;
if ( $intWhile>4 && $strDataCommand ) echo '<p class="datacommand bot"><i class="fa fa-level-up fa-lg fa-rotate-90" style="margin:0 10px 0 15px"></i>'.$strDataCommand.'</p>'.PHP_EOL;

// PAGE COMMANDS AND PAGER

$strCsv = (sUser::Role()==='V' ? '' : HtmlCsvLink(Href('qti_items_csv.php').'?'.$oVIP->selfuri,$intCount,$intPage));
$strPager = $strCsv.(empty($strPager) ? '' :' &middot; ').$strPager;

echo '
<table class="pagerbot"><tr>
<td class="pagerleft">',($intWhile>3 ? $strCommand : ''),'</td>
<td class="pagerright">',$strPager,'</td>
</tr></table>
';

// TAGS FILTRING

if ( QTI_LIST_TAG && !empty($_SESSION[QT]['tags']) && count($arrTags)>0 )
{
  echo '<div class="tagbox">',PHP_EOL;
  echo '<p class="title">'.$L['Show_only_tag'],':</p>',PHP_EOL;
  echo '<p class="content"><i class="fa fa-tags fa-lg" title="'.QTstrh(L('Tags')).'"></i> ';
  foreach($arrTags as $strTag) echo '<a class="tag" title="" href="qti_items.php?s=',$s,'&amp;q=adv&amp;v2=*&amp;v=',urlencode($strTag),'">',$strTag,'</a>';
  echo '</p>',PHP_EOL,'</div>',PHP_EOL;
}

// MAP MODULE, Show map

if ( $bMap )
{
  echo PHP_EOL,'<!-- Map module -->',PHP_EOL;
  if ( count($arrExtData)==0 )
  {
    echo '<p class="gmap nomap">'.L('map_No_coordinates').'</p>';
    $bMap=false;
  }
  else
  {
    $oCanvas = new cCanvas();
    $oCanvas->Header( $arrExtData );
    $oCanvas->Footer( sprintf(L('map_items'),L('item',count($arrExtData)),L('item',$intCount)) );
    echo $oCanvas->Render(true);
  }
  echo '<!-- Map module end -->',PHP_EOL,PHP_EOL;
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

if ( isset($_GET['cb']) )
{
$oHtml->scripts_end[] = '<script type="text/javascript">
var ids = ['.$_GET['cb'].'];
qtCheckboxIds(ids);
</script>';
}

include 'qti_inc_ft.php';