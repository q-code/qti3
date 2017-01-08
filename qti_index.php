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

// --------
// SECURITY
// --------

if ( $_SESSION[QT]['board_offline']==='1' ) { EchoPage(99); return; }
if ( $_SESSION[QT]['visitor_right']<1 && sUser::Role()==='V' ) { $oHtml->PageMsg(11); return; }

// --------
// INITIALIZE
// --------

$arrStats = GetStats();

// Optimising queries: Search all lastpost and Sections attributes

$arrLastPostId = array();
$oDB->Query('SELECT forum,MAX(id) as maxid FROM '.TABPOST.' GROUP BY forum' );
while($row = $oDB->Getrow()) $arrLastPostId[(int)$row['forum']] = (int)$row['maxid'];
$arrSections = SectionsByDomain(); // Get all sections at once (grouped by domain)

// MYBOARD Count MyTopics and MyAssign

$bMyBoard = sUser::Role()!='V'; // no myboard for visitor
if ( $bMyBoard )
{
  $intMyTopics = 0;
  $intMyAssign = 0;

  // Count my topics [firstpostuser]
  $oDB->Query( 'SELECT count(id) as countid FROM '.TABTOPIC.' WHERE firstpostuser='.sUser::Id().' AND status<>:status', array(':status'=>'Z') );
  $row = $oDB->Getrow();
  $intMyTopics = (int)$row['countid'];

  // Count Assigned topics
  if ( sUser::IsStaff() )
  {
  $oDB->Query( 'SELECT count(id) as countid FROM '.TABTOPIC.' WHERE actorid='.sUser::Id().' AND status<>:status', array(':status'=>'Z') );
  $row = $oDB->Getrow();
  $intMyAssign = (int)$row['countid'];
  }

  // Activate my board
  if ( $intMyTopics===0 && $intMyAssign===0 ) { $bMyBoard=false; } else { $oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_myboard.css" />'; }
}

// --------
// HTML START
// --------

$oHtml->scripts_jq[] = '
$(function() {
  $( "tr.wayin" ).click(function() {
    var id = $(this).attr("id");
    if ( id.indexOf("-row")>=0 )
    {
      var lnk = document.getElementById("wayout-"+id);
      if ( lnk ) window.location.assign(lnk.href);
    }
  })
  .css("cursor","pointer");
});
';

if ( sAppMem::useSSE() )
{
$oHtml->scripts[] = '<script type="text/javascript">
if(typeof(EventSource)=="undefined")
{
  window.setTimeout(function(){location.reload(true);}, 120000); // use polyfill (refresh 120s) when browser does not support SSE
}
else
{
  var sseOrigin = "'.(defined('SSE_ORIGIN') ? SSE_ORIGIN : 'http://localhost').'";
  window.setTimeout(function(){
  var script = document.createElement("script");
  script.src = "bin/js/qti_cse_index.js";
  document.getElementsByTagName("head")[0].appendChild(script);
  },10000);
 }
</script>';
}

include 'qti_inc_hd.php';

// --------
// MY BOARD
// --------

if ( $bMyBoard )
{
  echo '<div class="myboard">',PHP_EOL;
  echo '<div class="myboardheader"><p class="title">',$L['My_last_item'],'</p>',PHP_EOL;
  echo '<p class="buttons">';

  if ( $intMyTopics>0 )
  {
  echo '<a class="button" href="',Href('qti_items.php'),'?q=user&amp;v=',sUser::Id(),'&amp;v2=',urlencode(sUser::Name()),'">',$L['All_my_items'],'&nbsp;(',$intMyTopics,')</a>';
  }
  else
  {
  echo '<span>',$L['None'],'</span>';
  }
  if ( sUser::IsStaff() ) {
  if ( $intMyAssign>0 ) {
    echo '<a  class="button" href="',Href('qti_items.php'),'?q=actor&amp;v=',sUser::Id(),'&amp;v2=',urlencode(sUser::Name()),'">',L('Handled_by').' '.L('me'),'&nbsp;(',$intMyAssign,')</a>';
  }}
  echo '</p>',PHP_EOL,'</div>',PHP_EOL;

  // MY LAST TICKET

  if ( $intMyTopics>0 )
  {
    $arr = sMem::Get('sys_sections'); // arrSections is by domain
    // User's query: tickets issued by user (for staff, tickets assigned to [lastpostuser])
    $oDB->Query( LimitSQL('t.*,p.icon,p.title,p.textmsg FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid = p.id WHERE t.'.($intMyTopics>0 && sUser::IsStaff() ? 'last' : 'first').'postuser='.sUser::Id().' AND t.type="T" AND t.status<>"Z"','t.lastpostdate DESC',0,1,1) );
    $row = $oDB->Getrow();
    if ( is_array($row) )
    {
      $oTopic = new cTopic($row);
      $strTitle = cTopic::MakeIcon( $oTopic->type, $oTopic->status, 'Ticket', 't'.$oTopic->id.'-itemicon', $_SESSION[QT]['skin_dir'], Href('qti_item.php?t='.$oTopic->id)).' ';
      $strTitle .= cTopic::GetRef( $oTopic->numid, $oTopic->parentid, '' ).' ';
      $strTitle .= '<a class="topic" href="'.Href('qti_item.php').'?t='.$oTopic->id.'">'.QTtrunc($oTopic->title,25).'</a>';
      $strTitle .= ' &middot; <span class="topicsection">'.L('Section').': '.(isset($arr[$oTopic->parentid]['title']) ? QTtrunc($arr[$oTopic->parentid]['title'],25) : 'unknown section').'</span>';
      $strFirstText = QTtrunc(QTunbbc(QTcompact($row['textmsg'],150,' ')),125);

      if ( $oTopic->firstpostid!=$oTopic->lastpostid )
      {
      $oPost = new cPost($oTopic->lastpostid);
      $strDate = QTdatestr($oTopic->firstpostdate,'$','$',true,true).', '.L('Last_message').' '.L('By').' '.QTtrunc($oPost->username,20);
      $strLastDate = QTdatestr($oPost->issuedate,'$','$',true,true).', '.L('Last_message').' '.L('By').' '.QTtrunc($oPost->username,20);
      $strLastText = QTtrunc(QTunbbc(QTcompact($oPost->text,150,' ')),120);
      }
      else
      {
      $strDate = QTdatestr($oTopic->firstpostdate,'$','$',true,true).', '.L('I_wrote');
      $strLastdate = '';
      $strLasttext = '';
      }
      echo '<div id="mylastitem" class="myboardcontent">',PHP_EOL;
      echo '<p class="title">'.$strTitle.'</p>',PHP_EOL;
      echo '<div class="messages">',PHP_EOL;
      echo '<p class="date">'.$strDate.'</p>',PHP_EOL;
      echo '<p class="content" onclick="window.location=\'qti_item.php?t='.$oTopic->id.'\';">'.$strFirstText.'</p>',PHP_EOL;
      if ( !empty($strLastText) )
      {
      echo '<p class="date">'.$strLastDate.'</p>',PHP_EOL;
      echo '<p class="content" onclick="window.location=\'qti_item.php?t='.$oTopic->id.'\';">'.$strLastText.'</p>',PHP_EOL;
      }
      echo '</div>',PHP_EOL;
      echo '</div>',PHP_EOL;
    }
  }
  echo '</div>',PHP_EOL;
}

// ----------------

$table = new cTable('','t-sec');
$table->th[0] = new cTableHead('&nbsp;','','c-icon');
$table->th[1] = new cTableHead('&nbsp;','','c-section');
$table->th[2] = new cTableHead($L['Last_message'],'','c-issue');
$table->th[3] = new cTableHead($L['Items'],'','c-items');
$table->th[4] = new cTableHead($L['Replys'],'','c-replies');
$table->td[0] = new cTableData('','','c-icon');
$table->td[1] = new cTableData('','','c-section');
$table->td[2] = new cTableData('','','c-issue');
$table->td[3] = new cTableData('','','c-items');
$table->td[4] = new cTableData('','','c-replies');

$intDom = 0;
$intSec = 0;
$intSumItems = 0; // sum of items (for visible section)
$intSumNotes = 0; // sum of notes in process (for visible sections)
foreach(sMem::Get('sys_domains') as $intDomid=>$strDomtitle)
{
  if ( isset($arrSections[$intDomid]) ) {
  if ( count($arrSections[$intDomid])>0 ) {

    ++$intDom;
    if ( $intDom>1 ) echo '<div class="separator"></div>',PHP_EOL;
    echo '<!-- domain ',$intDomid,': ',$strDomtitle,' -->',PHP_EOL;
    $table->row = new cTableRow('', 't-sec');
    echo $table->Start().PHP_EOL;
    echo '<thead>',PHP_EOL;
    $table->th[1]->content = $strDomtitle;
    echo $table->GetTHrow().PHP_EOL;
    echo '</thead>',PHP_EOL;
    echo '<tbody>',PHP_EOL;

    $strAlt='r1';

    foreach($arrSections[$intDomid] as $intSection=>$arrSection)
    {
      ++$intSec;
      $oSEC = new cSection($arrSection,(isset($arrLastPostId[$intSection]) ? $arrLastPostId[$intSection] : false)); //use query optimisation
      $strLastpost = '&nbsp;';
      if ( $arrStats[$intSection]['topics']>0 ) {
      if ( isset($oSEC->lastpostid) ) {
        $str = isset($oSEC->lastpostname[15]) ? $oSEC->lastpostname : '';
        $strLastpost = '<span id="s'.$oSEC->uid.'-lastpostdate">'.QTdatestr($oSEC->lastpostdate,'$','$',true,true,true).'</span>';
        $strLastpost .= ' <a id="s'.$oSEC->uid.'-lastposttopic" class="lastitem" href="'.Href('qti_item.php').'?t='.$oSEC->lastposttopic.'#p'.$oSEC->lastpostid.'" title="'.L('Goto_message').'">'.QTI_GOTOBUTTON.'</a><br/>'.L('by').' <a id="s'.$oSEC->uid.'-lastpostuser" href="qti_user.php?id='.$oSEC->lastpostuser.'" title="'.$str.'">'.QTtrunc($oSEC->lastpostname,15).'</a>';
      }}
      $table->row = new cTableRow('s'.$oSEC->uid.'-row', 't-sec '.$strAlt.' hover wayin');
      $table->td[0]->content = AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i-sec','',Href('qti_items.php?s='.$oSEC->uid));
      $table->td[1]->content = '<p class="section"><a id="wayout-s'.$oSEC->uid.'-row" class="section" href="'.Href('qti_items.php?s='.$oSEC->uid).'">'.QTstrh($oSEC->title).'</a></p>'.(empty($oSEC->descr) ? '' : '<p class="sectiondesc">'.QTstrh($oSEC->descr).'</p>');
      if ( !empty($_SESSION['QTdebugsql']) ) $table->td[1]->content .= 'stats: '.$oSEC->stats;
      $table->td[2]->content = $strLastpost;
      $table->td[2]->Add('id', 's'.$oSEC->uid.'-issue');
      $table->td[3]->content = (isset($arrStats[$intSection]) ? $arrStats[$intSection]['topics'] : 0);
      $table->td[3]->Add('id', 's'.$oSEC->uid.'-items');
      $table->td[4]->content = (isset($arrStats[$intSection]) ? $arrStats[$intSection]['replies'] : 0);
      $table->td[4]->Add('id', 's'.$oSEC->uid.'-replies');
      echo $table->GetTDrow().PHP_EOL;
      if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
    }
    echo '</tbody>',PHP_EOL;
    echo '</table>',PHP_EOL;

  }}
}

// No public section

if ( $intSec===0 ) echo '<p>',(sUser::Role()==='V' ? $L['E_no_public_section'] : $L['E_no_visible_section']),'</p>';

// HTML END

if ( isset($oSEC) ) unset($oSEC);

// DEBUG SSE
if ( isset($_SESSION['QTdebugsse']) && $_SESSION['QTdebugsse'] ) echo '<div id="serverData"></div>';

include 'qti_inc_ft.php';