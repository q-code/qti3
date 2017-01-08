<?php

/**
* PHP 5
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
include Translate(APP.'_stat.php');

include 'bin/qti_fn_stat.php';

// --------
// INITIALISE
// --------

$oVIP->selfurl = 'qti_stats.php';
$oVIP->selfname = $L['Statistics'];
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_search.css" />';
$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_graph.css" />';

$s = '*';   // section filter
$y = date('Y'); if ( intval(date('n'))<2 ) $y--; // year filter
$type = ''; // type filter
$tag = ''; // tags filter
$tt = 'g'; // tab: g=global, gt=globaltrend, d=detail, dt=detailtrend
$ch = array('time'=>'m','type'=>'b','value'=>'a','trend'=>'a'); // chart parameters
// blocktime: m=month, q=quarter, d=10days
// graph type: b=bar, l=line, B=bar+variation, L=line+variation
// graphics reals: a=actual, p=percent
// trends reals: a=actual, p=percent
$lang = QTiso();
$arrSeries = array();

$arrSections = sMem::Get('sys_sections');

// --------
// SUBMITTED
// --------

QThttpvar('y s type tag tt','int str str str str',true,true,false);

if ( $s==='*' ) { $strSection=''; } else { $strSection = 'forum='.$s.' AND '; $s=(int)$s; }
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

if ( $tt=='g' || $tt=='d' ) $ch['type'] = strtolower($ch['type']);

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

$oDB->Query('SELECT count(id) as countid, min(firstpostdate) as startdate, max(firstpostdate) as lastdate FROM '.TABTOPIC );
$row = $oDB->Getrow();
if ( empty($row['startdate']) ) $row['startdate']=strval($y-1).'0101';
if ( empty($row['lastdate']) ) $row['lastdate']=strval($y).'1231';
$intTopics = intval($row['countid']);
$strLastdaysago = substr($row['lastdate'],0,8);
$strTendaysago = DateAdd($strLastdaysago,-10,'day');
$intStartyear = intval(substr($row['startdate'],0,4));
$intStartmonth = intval(substr($row['startdate'],4,2));
$intEndyear = intval(date('Y'));
$intEndmonth = intval(date('n'));

// --------
// HTML START
// --------

$strFilter = 's='.$s.'&amp;y='.$y.'&amp;type='.$type.'&amp;tag='.$tag;

$oHtml->scripts[] = '<script type="text/javascript">
function split( val ) { return val.split( "," ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }
var e0 = "'.L('No_result').'";
var e5 = "'.L('Tag_not_used').'";
</script>';
$oHtml->scripts_jq[] = '
$(function() {

  $("#tag").autocomplete({
   source: function(request, response) {
     $.ajax({
       url: "bin/qti_j_tag.php",
       dataType: "json",
       data: { term: extractLast( request.term ), s:"'.$s.'", lang:"'.QTiso().'", e5:e5 },
       success: function(data) { response(data); }
     });
   },
   search: function() {
     // custom minLength
     var term = extractLast( this.value );
     if ( term.length < 1 ) { return false; }
   },
   focus: function( event, ui ) { return false; },
   select: function( event, ui ) {
     var terms = split( this.value );
     terms.pop(); // remove current input
     if ( ui.item.rItem.length==0 ) return false;
     terms.push( ui.item.rItem ); // add the selected item
     terms.push( "" ); // add placeholder to get the comma-and-space at the end
     this.value = terms.join( "'.QT_HTML_SEPARATOR.'" );
     return false;
   }
  })
  .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
   return $( "<li></li>" )
     .data( "item.autocomplete", item )
     .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
     .appendTo( ul );
  };

});
';

include 'qti_inc_hd.php';

// TITLE and OPTIONS

if ( $intTopics>2 )
{
$arrY = array(); // all possible years
for ($i=$intStartyear;$i<=$intEndyear;++$i) $arrY[$i]=$i;
$arrT = array('*'=>L('All'),'T'=>L('Item'),'A'=>L('News')); // all possible types
echo '<div class="statoptions">
<form method="get" action="',Href(),'">
<h2>',L('Options'),'</h2>
<table>
<tr>
<td>',L('Year'),'</td>
<td>',(count($arrSections)>0 ? L('Section') : '&nbsp;'),'</td>
<td>',L('Type'),'</td>
<td>',( $_SESSION[QT]['tags']=='0' ? S : L('Tag')),'</td>
<td>&nbsp;</td>
</tr>
<tr>
<td><input type="hidden" name="tt" value="',$tt,'"/><select name="y" id="y">',QTasTag($arrY,(int)$y),'</select></td>
<td>',(count($arrSections)>0 ? '<select name="s" id="s">'.Sectionlist($s,array(),array(),L('In_all_sections')).'</select>' : '&nbsp;'),'</td>
<td><select name="type" id="type">',QTasTag($arrT,$type),'</select></td>
<td>',( $_SESSION[QT]['tags']=='0' ? S : '<input type="text" id="tag" name="tag" size="18" value="'.$tag.'"/>'),'</td>
<td><input type="hidden" name="ch" value="',implode('',$ch),'"/><input type="submit" name="ok" value="',$L['Ok'],'"/></td>
</tr>
</table>
</form>
</div>
';
}
echo '<h1>',L('Statistics'),'</h1>',PHP_EOL;
echo '<div class="separator"></div>',PHP_EOL;

// STATISTIC TABS and GRAPHIC MENUS definition

$arrTabs = array();
$arrTabs['g'] = array('tabname'=>$L['Global'],'tabdesc'=>$L['H_Global']);
$arrTabs['gt'] = array('tabname'=>$L['Global_trends'],'tabdesc'=>$L['H_Global_trends']);
$arrTabs['d'] = array('tabname'=>$L['Details'],'tabdesc'=>$L['H_Details']);
$arrTabs['dt'] = array('tabname'=>$L['Details_trends'],'tabdesc'=>$L['H_Details_trends']);

$arrMenuTime = array(); // Block time: q=quarter, m=month, d=10days
if ( $ch['time']=='q' ) { $arrMenuTime[]=$L['Per_q']; } else { $arr=$ch; $arr['time']='q'; $arrMenuTime[]='<a href="'.$oVIP->selfurl.'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Per_q'].'</a>'; }
if ( $ch['time']=='m' ) { $arrMenuTime[]=$L['Per_m']; } else { $arr=$ch; $arr['time']='m'; $arrMenuTime[]='<a href="'.$oVIP->selfurl.'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Per_m'].'</a>'; }
if ( $ch['time']=='d' ) { $arrMenuTime[]=$L['Per_d']; } else { $arr=$ch; $arr['time']='d'; $arrMenuTime[]='<a href="'.$oVIP->selfurl.'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Per_d'].'</a>'; }

$arrMenuType = array(); // Chart tyle: b=bar, l=line, B=bar+variation labels, L=line+variation labels
if ( $ch['type']=='b' ) { $arrMenuType[]=$L['Chart_bar']; }  else { $arr=$ch; $arr['type']='b'; $arrMenuType[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Chart_bar'].'</a>'; }
if ( $ch['type']=='l' ) { $arrMenuType[]=$L['Chart_line']; } else { $arr=$ch; $arr['type']='l'; $arrMenuType[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Chart_line'].'</a>'; }
if ( $tt=='gt' || $tt=='dt' )
{
if ( $ch['type']=='B' ) { $arrMenuType[]=$L['Chart_bar_var']; } else { $arr=$ch; $arr['type']='B'; $arrMenuType[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Chart_bar_var'].'</a>'; }
if ( $ch['type']=='L' ) { $arrMenuType[]=$L['Chart_line_var']; } else { $arr=$ch; $arr['type']='L'; $arrMenuType[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Chart_line_var'].'</a>'; }
}

$arrMenuValue = array(); // Value type: a=actual, p=percent
if ( $ch['value']=='a' ) { $arrMenuValue[]=$L['Per_a']; } else { $arr=$ch; $arr['value']='a'; $arrMenuValue[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Per_a'].'</a>'; }
if ( $ch['value']=='p' ) { $arrMenuValue[]=$L['Per_p']; } else { $arr=$ch; $arr['value']='p'; $arrMenuValue[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Per_p'].'</a>'; }

$arrMenuTrend = array(); // Trend value type: a=actual, p=percent
if ( $ch['trend']=='a' ) { $arrMenuTrend[]=$L['Per_a']; } else { $arr=$ch; $arr['trend']='a'; $arrMenuTrend[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Per_a'].'</a>'; }
if ( $ch['trend']=='p' ) { $arrMenuTrend[]=$L['Per_p']; } else { $arr=$ch; $arr['trend']='p'; $arrMenuTrend[]='<a href="'.Href().'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$arr).'">'.$L['Per_p'].'</a>'; }

echo HtmlTabs($arrTabs, $oVIP->selfurl.'?'.$strFilter.'&amp;ch='.implode('',$ch), $tt);

echo '<div class="pan">
<div class="pan-top">',$arrTabs[$tt]['tabdesc'],'</div>
';

// Statistic computation

include 'qti_stats_inc.php';

// Table header definition

$arrHeader = array();
switch($ch['time'])
{
case 'q': for ($i=1;$i<=$intMaxBt;++$i) $arrHeader[$i]='Q'.$i; break;
case 'm': for ($i=1;$i<=$intMaxBt;++$i) $arrHeader[$i]=$L['dateMM'][$i]; break;
case 'd': for ($i=1;$i<=$intMaxBt;++$i) $arrHeader[$i]=str_replace(' ','<br/>',QTdatestr(DateAdd($strTendaysago,$i,'day'),'d M','')); break;
}
$arrHeader[$intMaxBt+1] = '<span class="bold">'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'</span>';

// DISPLAY title & option

echo '<table class="header">',PHP_EOL;
echo '<tr>';
$str = $y; if ($tt=='gt' || $tt=='dt' ) $str = ($y-1).'-'.$str;
echo '<td class="title"><h2>',$str,($s!=='*' ? ' '.$arrSections[$s] : ''),($type=='A' ? ' '.$L['Newss'] : ''),(empty($tag) ? '' : ', '.$L['With_tag'].' '.str_replace(';',' '.$L['or'].' ',$tag)),'</h2></td>',PHP_EOL;
echo '<td class="controls">',implode(' &middot; ',$arrMenuTime),'</td>';
echo '</tr>',PHP_EOL;
echo '</table>',PHP_EOL;

// Display panel content

include 'qti_stats_out.php';

echo '
</div>
';

// CSV

if ( file_exists('qti_stats_csv.php') )
{
  echo '<p style="margin:2px;text-align:right"><a class="csv" href="',Href('qti_stats_csv.php'),'?tt='.$tt.'&amp;'.$strFilter.'&amp;ch='.implode('',$ch).'" title="'.$L['H_Csv'].'">',$L['Csv'],'</a></p>';
}

// HTML END

include 'qti_inc_ft.php';