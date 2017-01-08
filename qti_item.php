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
if ( !sUser::CanView('V3') ) { $oHtml->PageMsg(11); return; }
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_post.css" />';

// ---------
// PRE SETTINGS
// ---------

$t = -1;
$page = 1;
QThttpvar('t page','int int');

if ( $t<0 ) die('Missing topic id... (t)');
if ( $page<1 ) $page=1;

$oTopic = new cTopic($t,sUser::Id()); //provide userid to update stats
$s = $oTopic->parentid;

$intLimit = 0;
if ( $page>1 ) $intLimit = ($page-1)*$_SESSION[QT]['replies_per_page'];

require 'bin/qti_fn_tags.php';

// --------
// INITIALISE
// --------

$oSEC = new cSection($s);
$oTopic = new cTopic($t,sUser::Id());
$bQR = ($_SESSION[QT]['show_quick_reply']==='1' || ($_SESSION[QT]['show_quick_reply']==='2' && $oSEC->ReadOption('qr')!=='0'));

// exit according to section settings
if ( $oSEC->type!=0 && !sUser::IsStaff() )
{
$oVIP->selfname = $L['Section'];
$oVIP->exitname = $L['Exit']; // index name
if ( $oSEC->type==1 ) $oHtml->PageMsg( NULL, '<p>'.Error(12).'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>' );
if ( $oSEC->type==2 && sUser::Role()==='V' ) $oHtml->PageMsg( NULL, '<p>'.Error(11).'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>' );
if ( $oSEC->type==2 && sUser::Role()==='U' && $oTopic->firstpostuser!=sUser::Id() ) $oHtml->PageMsg( NULL, '<p>'.L('E_item_private')."<br />".Error(11).'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>' );
}

$strCoord='';
$strCommand = '';
if (isset($_GET['view'])) $_SESSION[QT]['viewmode'] = strtolower(substr($_GET['view'],0,1));

$oVIP->selfurl = 'qti_item.php';
$oVIP->selfuri = GetURI('order,dir');
$oVIP->exiturl = 'qti_items.php?s='.$s;
$oVIP->selfname = $L['Messages'];

$strOrder = 'issuedate';
$strDirec = 'asc';
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = $_GET['dir'];

if ( sUser::IsStaff() ) include 'qti_inc_menu.php'; // define staff menu and process submitted staff actions

// ---------
// SUBMITTED
// ---------

if ( isset($_POST['addtag']) )
{
  $str = strip_tags(trim($_POST['tag']));
  $str = str_replace(',',';',$str);
  if ( substr($str,-1,1)==';' ) $str=substr($str,0,-1);
  if ( !empty($str) && $str!=='*' ) $oTopic->TagsAdd($str,$oSEC);
}
if ( isset($_POST['deltag']) )
{
  $str = strip_tags(trim($_POST['tag'])); if ( substr($str,-1,1)==',' || substr($str,-1,1)==';' ) $str=substr($str,0,-1);
  if ( !empty($str) ) $oTopic->TagsDel($str,$oSEC);
}

// MAP MODULE

if ( UseModule('map') ) { $strCheck=$s; include 'qtim_map_ini.php'; } else { $bMap=false; }

if ( $bMap ) {
if ( !empty($oTopic->y) && !empty($oTopic->x) ) {

  $y = floatval($oTopic->y);
  $x = floatval($oTopic->x);
  $strIco = ''; if ( isset($oTopic->type) && isset($oTopic->status) ) $strIco = cTopic::MakeIcon($oTopic->type,$oTopic->status,false,'',$_SESSION[QT]['skin_dir']).' ';
  $strPname = cTopic::GetRef($oTopic->numid,$oSEC->numfield,$na='');
  $strPinfo = $strIco.(empty($strPname) ? '' : $strPname.'<br/>').( $_SESSION[QT]['viewmode']==='c' ? '' : 'Lat: '.QTdd2dms($y).' <br />Lon: '.QTdd2dms($x).'<br />DD: '.round($oTopic->y,8).', '.round($oTopic->x,8) );
  $oMapPoint = new cMapPoint($y,$x,$strPname,'<p class="gmap">'.$strPinfo.'</p>');

  // add extra $oMapPoint properties (if defined in section settings)
  $oSettings = getMapSectionSettings($s);
  if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;

  $arrExtData = array($oMapPoint);
  $strCoord = '<a href="javascript:void(0)"'.($bMapGoogle && !$_SESSION[QT]['m_map_hidelist'] ? ' onclick="gmapPan(\''.$y.','.$x.'\'); return false;"' : '').' title="'.$L['Coord'].': '.round($y,8).','.round($x,8).'"><i class="fa fa-map-marker" title="'.L('latlon').' '.QTdd2dms($y).','.QTdd2dms($x).'"></i></a>';
  $strPcoord = '<a href="javascript:void(0)"'.($bMapGoogle && !$_SESSION[QT]['m_map_hidelist'] ? ' onclick="gmapPan(\''.$y.','.$x.'\'); return false;"' : '').' title="'.L('map_Center').'"><i class="fa fa-map-marker fa-lg"></i></a> Lat,Lon: '.QTdd2dms($y).','.QTdd2dms($x).( $_SESSION[QT]['viewmode']==='c' ? '' : ' DD: '.round($oTopic->y,8).','.round($oTopic->x,8) );

}}

// --------
// HTML START
// --------

if ( $bQR && QTI_BBC )
{
  $oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qti_bbc.js"></script>';
  $intBbc=1; // buttons for quick reply
}

if ( !empty($_SESSION[QT]['tags']) )
{

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qt_tag.css" />';
$oHtml->scripts['modernizr']='<script type="text/javascript" src="bin/js/modernizr.custom.js"></script>';

$oHtml->scripts[] = '<script type="text/javascript">
function showEdittags()
{
  var doc = document;
  if (doc.getElementById("edittags") && doc.getElementById("endedittags") && doc.getElementById("tag") && doc.getElementById("addtag") && doc.getElementById("deltag"))
  {
    var s = (doc.getElementById("tag").style.display=="none" || doc.getElementById("tag").style.display=="" ? "inline-block" : "none");
    doc.getElementById("tag").style.display=s;
    doc.getElementById("addtag").style.display=s;
    doc.getElementById("deltag").style.display=s;
    if ( s=="none" )
    {
    doc.getElementById("edittags").style.display="inline-block";
    doc.getElementById("endedittags").style.display="none";
    }
    else
    {
    doc.getElementById("edittags").style.display="none";
    doc.getElementById("endedittags").style.display="inline-block";
    doc.getElementById("tag").focus();
    }
  }
}
function split( val ) { return val.split( "," ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }
</script>';
}

$oHtml->scripts_jq[] = '
$(function() {

  var e0 = "'.L('No_description').'";
  var e5 = "'.L('Tag_not_used').'";

   $(".tag").hover(function() {
     var oTag = $(this);
     if ( oTag.attr("title")!=="" ) return false;
     $.post("bin/qti_j_tagdesc.php",{s:"'.$s.'",term:oTag.html(),lang:"'.QTiso().'",e0:e0}, function(data) { oTag.attr({title:data}); } );
   });

   $("#tag").autocomplete({
     source: function(request, response) {
       $.ajax({
         url: "bin/qti_j_tag.php",
         dataType: "json",
         data: { term: extractLast( request.term ), s:'.$s.', lang:"'.QTiso().'", addtags:true, e5:e5 },
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

// BUTTONS

if ( $oSEC->status=='1' )
{
  $strCommand = '<a class="button pageaction disabled">'.L('E_section_closed').'</a>';
}
else
{
  $strCommand = '<a class="button pageaction" href="qti_form_edit.php?t='.$oTopic->id.'&amp;a=re" accesskey="r">'.L('Post_reply').'</a>';
  if ( $oTopic->status=='Z' ) $strCommand = '<a class="button pageaction disabled">'.L('Item_closed').'</a>';

  if ( (sUser::Role()==='V') && ($_SESSION[QT]['visitor_right']<6) )
  {
  $strCommand = '<a class="button pageaction disabled">'.L('Post_reply').'</a>';
  }
}

$strCommand = $oVIP->BackButton().$strCommand;

// ITEM DESCRIPTION AND MAP

$strDescr = '';
$strLocation = '';
// Item description
if ( $_SESSION[QT]['section_desc']==='1' || ($_SESSION[QT]['section_desc']==='2' && $oSEC->ReadOption('qr')!=='0') )
{
  $str = cTopic::GetRef($oTopic->numid,$oSEC->numfield,$na='').' ';
  switch($oTopic->type)
  {
  case 'T': $str .= $oTopic->GetStatusName('unknown status'); break;
  case 'I': $str .= L('Inspection');  break;
  case 'A': $str .= L('News');  break;
  }
  $strDescr .= '<p id="data-title">'.$str.'</p>';
}

// map module
if ( $bMap )
{
  if ( !QTgemptycoord($oTopic) )
  {
    $oCanvas = new cCanvas();
    if ( $_SESSION[QT]['viewmode']!=='c' && ($oTopic->firstpostuser==sUser::Id() || sUser::IsStaff()) ) $oCanvas->Footer(L('map_editmove'));
    if ( isset($strPcoord) ) $oCanvas->Footer($strPcoord);
    $strLocation = $oCanvas->Render(false,'','gmap item'.($_SESSION[QT]['viewmode']==='c' ? ' compact' : ''));
  }
  else
  {
  $strLocation .= '<p class="gmap nomap">'.L('map_No_coordinates').'</p>'.PHP_EOL;
  }
}

if ( !empty($strDescr) || !empty($strLocation) || !empty($strPageMenu) )
{
echo '<div id="data-hd">',PHP_EOL;
if ( !empty($strDescr) ) echo '<div id="data-hd-l">',$strDescr,'</div>',PHP_EOL;
echo '<div id="data-hd-r">',(empty($strLocation) ? '' : $strLocation),(empty($strPageMenu) ? '' : $strPageMenu),'</div>',PHP_EOL;
echo '</div>',PHP_EOL;
}

// --------
// TOPIC MESSAGE
// --------

echo '
<table class="pagertop">
<tr>
<td class="pagerleft">',$strCommand,'</td>
</tr>
</table>
';

/* ====== */
$oDB->Query('SELECT p.*, u.role, u.location, u.photo, u.signature, u.privacy FROM '.TABPOST.' p, '.TABUSER.' u WHERE p.userid = u.id AND p.type="P" AND p.topic='.$oTopic->id );
$iMsgNum = $intLimit;
$intWhile= 0;
$strAlt  = 'r1';
/* ====== */

$row=$oDB->Getrow();
$iMsgNum = $iMsgNum+1;
$oPost = new cPost($row); // READ POST + USERINFO
    // Apply privacy
	  if ( $row['privacy']<2 && !sUser::IsStaff() && sUser::Id()!=$oPost->userid ) $oPost->userloca='';

if ( $_SESSION[QT]['viewmode']=='c' ) $oPost->text = QTcompact($oPost->text,0);
$strButton = '<p class="post-button">';
if ( sUser::Auth() )
{
  if ( $oTopic->status!='Z' && $oTopic->status!='0' && !$oSEC->status )
  {
  $strButton .= '<a class="post-button" href="'.Href('qti_form_edit.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;a=qu&amp;p='.$oPost->id.'">'.$L['Quote'].'</a> ';
  }
  if ( $oPost->CanEdit() )
  {
  $strButton .= '<a class="post-button" href="'.Href('qti_form_edit.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;p='.$oPost->id.'&amp;a=ed">'.$L['Edit'].'</a> ';
  $strButton .= '<a class="post-button" href="'.Href('qti_change.php').'?a=topicdelete&amp;s='.$oSEC->uid.'&amp;t='.$oTopic->id.'">'.$L['Delete'].'</a> ';
  if ( $oTopic->type==='I' && $oPost->type==='P' ) $strButton .= '<a class="post-button" href="'.Href('qti_change.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;p='.$oPost->id.'&amp;a=topicparam">'.L('Parameters').'</a> ';
  }
}
if ( !empty($oTopic->actorid) && $oTopic->actorid>0 ) $strButton .= '<span class="post-modif">'.L('Handled_by').' '.($oTopic->actorid===sUser::Id() ? L('me') : $oTopic->actorname).'.</span> ';
if ( !empty($oPost->modifuser) ) $strButton .= '<span class="post-modif">'.L('Modified_by').' '.($oPost->modifuser===sUser::Id() ? L('me') : $oPost->modifname).' ('.QTdatestr($oPost->modifdate,'$','$',true).').</span>';
$strButton .= '</p>'.PHP_EOL;

// SHOW FIRST MESSAGE

$oAvatar=true;
if ( $oTopic->type==='I' )
{
  $optionstat = $oTopic->ReadOptions('Istat'); if ( empty($optionstat) ) $optionstat='mean'; // default if empty options
  $optionlevel= $oTopic->ReadOptions('Ilevel'); if ( empty($optionlevel) ) $optionlevel='3'; // default if empty options
  $oAvatar = AsImgBox( '<span class="small">('.L('I_v_'.$optionstat).')</span><br />'.ValueScalebar($oTopic->z,$optionlevel), ValueName($oTopic->z,$optionlevel), 'picboxmsg' );
}

$oPost->Show($oSEC,$oTopic,$oAvatar,$strButton,$_SESSION[QT]['skin_dir'],$strAlt,$_SESSION[QT]['viewmode']==='c');
if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
++$intWhile;

// SHOW TAGS

if ( $oPost->type==='P' && $_SESSION[QT]['tags']!='0' )
{
  $arrTags=explode(';',$oTopic->tags);

  $bTagEditor = false;
  if ( $oTopic->status!='1' )
  {
  if ( sUser::IsStaff()  ) $bTagEditor=true;
  if ( $_SESSION[QT]['tags']=='U' && sUser::Id()===$oTopic->firstpostuser ) $bTagEditor=true; // 'U'=members can edit in his own ticket
  if ( $_SESSION[QT]['tags']=='U+' && sUser::Role()==='U' ) $bTagEditor=true; // 'U+'=members can edit any tickets
  if ( $_SESSION[QT]['tags']=='V' ) $bTagEditor=true; // 'V'=Visitor can edit any tickets
  }

  if ( $bTagEditor )
  {
    echo '<form method="post" action="',Href(),'?s=',$s,'&amp;t=',$t,'">',PHP_EOL;
    echo '<div class="tags"><i class="tags fa fa-tag'.(count($arrTags)>1 ? 's' : ' fa-lg').'" title="'.L('Tags').'"></i> ';
    foreach($arrTags as $strTag)
    {
    if ( !empty($strTag) ) echo '<span class="tag" title="" onclick="document.getElementById(\'tag\').value=this.innerHTML;">',$strTag,'</span>';
    }
    echo (count($arrTags)>5 ? '</div><div class="tags">' : ''),PHP_EOL;
    echo '<input type="hidden" name="s" value="',$s,'" />';
    echo '<input type="hidden" name="t" value="',$t,'" />';
    echo '<input type="text" id="tag" size="20" name="tag" maxlength="24" value="" />';
    echo '<input type="submit" id="addtag" name="addtag" title="',$L['Add'],'" value="+" onclick="if (document.getElementById(\'tag\').value==\'\') {return false;} else { return null;}" />';
    echo '<input type="submit" id="deltag" name="deltag" title="',$L['Delete_tags'],'" value="-"  onclick="if (document.getElementById(\'tag\').value==\'\') {return false;} else { return null;}" />';
    echo '<img id="endedittags" src="',$_SESSION[QT]['skin_dir'],'/ico_tags_hide.gif" title="',$L['Close'],'" onclick="showEdittags(); return false;"/>';
    echo '<img id="edittags" src="',$_SESSION[QT]['skin_dir'],'/ico_tags_show.gif" title="',$L['Edit'],'" onclick="showEdittags(); return false;"/>';
    echo '</div></form>',PHP_EOL;
  }
  else
  {
    if ( !empty($oTopic->tags) )
    {
    echo '<div class="tags"><i class="tags fa fa-tag'.(count($arrTags)>1 ? 's' : ' fa-lg').'" title="'.L('Tags').'"></i> ';
    foreach($arrTags as $strTag) echo '<span class="tag" title="">',$strTag,'</span> ';
    echo '</div>';
    }
  }
}

// --------
// REPLIES
// --------

// Count replies

$oDB->Query('SELECT count(id) as countid FROM '.TABPOST.' WHERE type<>"P" AND topic='.$oTopic->id );
$row = $oDB->Getrow();
$intReplies = (int)$row['countid'];

// Pager

$strPager = MakePager( $oVIP->selfurl.'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&dir='.$strDirec, $intReplies, (int)$_SESSION[QT]['replies_per_page'], $page );
if ( $strPager!='' ) $strPager = '<p class="pager">'.L('Page').$strPager.'</p>';
if ( $oTopic->type!=='I' && $intReplies>2 ) $strPager .= '<p class="sorter"><a href="'.Href().'?'.GetURI('dir,page').'&amp;dir='.($strDirec==='desc' ? 'asc' : 'desc').'" title="'.L('Show').': '.($strDirec==='desc' ? L('first').' &gt; '.L('last') : L('last').' &gt; '.L('first')).'"><i class="fa fa-refresh fa-rotate-90 fa-lg"></i></a></p>';

// Separator

if ( $intReplies>0 ) echo '<div class="separator"></div>',PHP_EOL;

// ======
$oDB->Query( LimitSQL(
'p.*, u.role,u.location,u.photo,u.signature,u.privacy FROM '.TABPOST.' p, '.TABUSER.' u WHERE p.userid = u.id AND p.type<>"P" AND p.topic='.$oTopic->id,
'p.'.$strOrder.' '.$strDirec.($strOrder!='issuedate' ? ',p.issuedate desc' : ''),
$intLimit,
$_SESSION[QT]['replies_per_page'],
$intReplies
));
// ======
$iMsgNum = $intLimit;
$intWhile= 0;
$strAlt  = 'r1';
// ======

// ::::::::
if ( $oTopic->type==='I' ) {
// ::::::::

// Pager

if ( !empty($strPager) ) echo '<table class="pagertop"><tr><td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL;

// Table

$table = new cTable('t1','t-item',$intReplies);
$table->activecol = $strOrder;
$table->activelink = '<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'">%s</a>&nbsp;<i class="fa fa-caret-'.($strDirec=='asc' ? 'up' : 'down').'"><i/>';
$table->th['issuedate'] = new cTableHead( L('Date'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=issuedate&amp;dir=desc">%s</a>' );
$table->th['title'] = new cTableHead( L('Score'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=title&amp;dir=desc">%s</a>' );
$table->th['textmsg'] = new cTableHead( L('Message')); //,'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=textmsg&amp;dir=desc">%s</a>');
$table->th['author'] = new cTableHead( L('Author'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=author&amp;dir=asc">%s</a>' );
$table->th['action'] = new cTableHead( L('Action') );
foreach($table->th as $key=>$th) { $table->th[$key]->Add('class','c-'.$key); }

// Show table

echo $table->Start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $table->GetTHrow().PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

while ( $row=$oDB->Getrow() )
{
  $oPost = new cPost($row); // READ POST + USERINFO
  // Apply privacy
	if ( $row['privacy']<2 && !sUser::IsStaff() && sUser::Id()!=$oPost->userid ) $oPost->userloca='';

  // compact long message
  $oPost->text = QTcompact($oPost->text,200);
  if ( strlen($oPost->text)>65 && $_SESSION[QT]['viewmode']=='c' ) $oPost->text = substr($oPost->text,0,60).'<a class="small" href="'.Href('qti_change.php').'?a=post&amp;s='.$s.'&amp;t='.$t.'&amp;p='.$oPost->id.'">[...]</a>';
  if ( strlen($oPost->text)>160 ) $oPost->text = substr($oPost->text,0,150).'<a class="small" href="'.Href('qti_change.php').'?a=post&amp;s='.$s.'&amp;t='.$t.'&amp;p='.$oPost->id.'">[...]</a>';

  if ( $oPost->icon!='00' ) $oPost->text = AsImg($_SESSION[QT]['skin_dir'].'/ico_prefix_'.$oSEC->prefix.'_'.$oPost->icon.'.gif','[o]',$L['Ico_prefix'][$oSEC->prefix.'_'.$oPost->icon],'prefix').' '.$oPost->text;
  $strButton = '';
  if ( sUser::Auth() )
  {
    if ( !$oTopic->status && !$oSEC->status )
    {
    $strButton .= '<a href="'.Href('qti_form_edit.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;a=qu&amp;p='.$oPost->id.'" class="button">'.$L['Quote'].'</a>';
    }
    if ( $oPost->CanEdit() )
    {
    $strButton .= '<a href="'.Href('qti_form_edit.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;p='.$oPost->id.'&amp;a=ed" class="button">'.$L['Edit'].'</a>';
    $strButton .= '<a href="'.Href('qti_form_del.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;p='.$oPost->id.'&amp;a=de" class="button">'.$L['Delete'].'</a>';
    }
  }
  if ( !empty($oPost->modifuser) ) $strButton .= '<span class="small">&nbsp;'.$L['Modified_by'].' <a href="'.Href('qti_user.php').'?id='.$oPost->modifuser.'" class="small">'.$oPost->modifname.'</a> ('.QTdatestr($oPost->modifdate,'$','$',true).')</span>';

  // Show inspection row

  echo '<tr class="t-item ',$strAlt,' hover">';
  echo '<td class="c-issuedate">'.QTdatestr($oPost->issuedate,'$','$',true).'</td>';
  echo '<td class="c-title">'.$oPost->GetScoreImage($oTopic,true,'<br/>').'</td>';
  echo '<td class="c-textmsg">'.$oPost->text.(empty($oPost->text) ? '&nbsp;' : '').'</td>';
  echo '<td class="c-author"><a href="',Href('qti_user.php'),'?id=',$oPost->userid,'">',$oPost->username,'</a></td>';
  echo '<td class="c-action">';
  if ( sUser::Auth() && $oPost->CanEdit() )
  {
  echo '<a href="',Href('qti_form_edit.php'),'?a=ed&amp;s=',$oPost->section,'&amp;t=',$oPost->topic,'&amp;p=',$oPost->id,'">',$L['Edit'],'</a>&nbsp;&middot;&nbsp;<a href="',Href('qti_form_del.php'),'?a=de&amp;s=',$oPost->section,'&amp;t=',$oPost->topic,'&amp;p=',$oPost->id,'">',$L['Delete'],'</a>';
  }
  else
  {
  echo S;
  }
  echo '</td>';
  echo '</tr>',PHP_EOL;

  if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
  ++$intWhile;
  if ( $intWhile>=$_SESSION[QT]['replies_per_page'] ) break;
}

if ( $intWhile===0 ) echo '<tr class="t-item"><td colspan="6">(0 '.$L['Reply'].')</td></tr>
';

// End table display

echo '</tbody>
</table>
';

// End pager

if ( !empty($strPager) ) echo '<table class="pagerbot"><tr><td class="pagerright">',$strPager,'</td></tr></table>'.PHP_EOL;

// ::::::::
}
else
{
// ::::::::

// Pager

if ( !empty($strPager) ) echo '<table class="pagertop"><tr><td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL;

// Reply blocks

while($row=$oDB->Getrow())
{
  $iMsgNum = $iMsgNum+1;
  $iMsgNumOrdered = $iMsgNum;
  if ( $strDirec==='desc' ) $iMsgNumOrdered = $intReplies+1-$iMsgNum;

  $oPost = new cPost($row,$iMsgNumOrdered);  // READ POST + USERINFO

  // Apply privacy
	if ( $row['privacy']<2 && !sUser::IsStaff() && sUser::Id()!=$oPost->userid ) $oPost->userloca='';


  if ( $_SESSION[QT]['viewmode']==='c' ) $oPost->text = QTcompact($oPost->text,0);
  $strButton = '<p class="post-button">';
  if ( sUser::Auth() )
  {
    if ( !$oTopic->status && !$oSEC->status )
    {
    $strButton .= '<a class="post-button" href="'.Href('qti_form_edit.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;a=qu&amp;p='.$oPost->id.'">'.$L['Quote'].'</a> ';
    }
    if ( $oPost->userid==sUser::Id() || sUser::IsStaff() )
    {
    $strButton .= '<a class="post-button" href="'.Href('qti_form_edit.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;p='.$oPost->id.'&amp;a=ed">'.$L['Edit'].'</a> ';
    $strButton .= '<a class="post-button" href="'.Href('qti_form_del.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;p='.$oPost->id.'&amp;a=de">'.$L['Delete'].'</a> ';
    if ( $oPost->type=='P' && !empty($oTopic->options) )$strButton .= '<a class="post-button" href="'.Href('qti_change.php').'?s='.$oSEC->uid.'&amp;t='.$oTopic->id.'&amp;p='.$oPost->id.'&amp;a=topicparam">'.L('Parameters').'</a> ';
    }
  }
  if ( !empty($oPost->modifuser) ) $strButton .= '<span class="post-modif">&nbsp;'.$L['Modified_by'].' <a href="'.Href('qti_user.php').'?id='.$oPost->modifuser.'" class="small">'.$oPost->modifname.'</a> ('.QTdatestr($oPost->modifdate,'$','$',true).')</span>';
  $strButton .= '</p>'.PHP_EOL;

  // Show reply block

  $oPost->Show($oSEC,$oTopic,true,$strButton,$_SESSION[QT]['skin_dir'],$strAlt,$_SESSION[QT]['viewmode']==='c');
  if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
  ++$intWhile;

  // END

  if ( $intWhile<$intReplies ) echo '<div class="separator"></div>',PHP_EOL;

}

// End pager

if ( !empty($strPager) ) echo '<table class="pagerbot"><tr><td class="pagerright">',$strPager,'</td></tr></table>'.PHP_EOL;

// ::::::::
}
// ::::::::

// BUTTON LINE AND PAGER
if ( $oTopic->type!='I' && $intReplies>2 )
{
echo '
<table class="pagerbot">
<tr>
<td class="pagerleft">',str_replace(' accesskey="r"','',$strCommand),'</td>
</tr>
</table>
';
}

// --------
// QUICK REPLY
// --------

if ( $bQR ) include 'qti_item_inc_qr.php';

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

  // First item is the item's location and symbol
  if ( isset($arrExtData[$oTopic->uid]) )
  {
    // symbol by role
    $oMapPoint = $arrExtData[$oTopic->uid];
    if ( !empty($oMapPoint->icon) ) $gmap_symbol = $oMapPoint->icon;
    if ( !empty($oMapPoint->shadow) ) $gmap_shadow = $oMapPoint->shadow;

    // center on first item
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
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
      $strSymbol = $gmap_symbol; // required to reset symbol on each user
      $strShadow = $gmap_shadow;
      if ( !empty($oMapPoint->icon) ) $strSymbol  = $oMapPoint->icon;
      if ( !empty($oMapPoint->shadow) ) $strShadow = $oMapPoint->shadow;
      $gmap_markers[] = QTgmapMarker($oMapPoint->y.','.$oMapPoint->x, false, $strSymbol, $oMapPoint->title, $oMapPoint->info, $strShadow );
    }
  }

  include 'qtim_map_load.php';
}

include 'qti_inc_ft.php';