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
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2015 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
if ( sUser::Role()!='A' ) die(Error(13));

include Translate(APP.'_adm.php');
if ( !isset($_GET['a'])) die('Wrong action');

// --------
// INITIALISE
// --------

$a = ''; // mandatory action
$d = -1; // domain (or days)
$s = -1; // section
$t = -1; // topic (or move target)
$p = -1; // post
$v = ''; // value
$ok = ''; // submitted
QThttpvar('a d s t p v ids ok','str int int int int str str str');

$oVIP->selfurl = 'qti_adm_change.php';
$oVIP->selfname = 'QuickTicket command';

// --------
// EXECUTE COMMAND
// --------

switch($a)
{

// --------------
case 'deletedomain':
// --------------

  if ( $d<1 ) die('Wrong id '.$d);

  $oVIP->selfname = $L['Domain_del'];
  $oVIP->exiturl = 'qti_adm_sections.php';
  $oVIP->exitname = L('Exit');

  // ask destination
  if ( empty($ok) )
  {

    $arrDomains = GetDomains();
    $strTitle = $arrDomains[$d];
    $arrSections = QTarrget(GetSections(sUser::Role(),$d));

    // list the domain content
    if ( count($arrSections)==0 )
    {
      $strDcont = '<span class="small">0 '.$L['Section'].'</span>';
    }
    else
    {
      $strDcont = '';
      foreach($arrSections as $intKey=>$strValue)
      {
      $strDcont .= '<span class="small">'.$L['Section'].': '.$strValue.'</span><br />';
      }
    }

    // list of domain destination
    if ( count($arrSections)>0 )
    {
      $arrDdest = array(); // array_diff_key() not supported in php<5.1
      foreach($arrDomains as $intKey=>$strValue) { if ( $intKey!=$d ) $arrDdest[$intKey]=$strValue; }

      $strDdest = '<tr>
      <th>'.$L['Sections'].'</th>
      <td><select name="t" size="1">'.QTasTag($arrDdest,'',array('format'=>$L['Move_to'].': %s')).'</select></td>
      </tr>';
    }
    else
    {
      $strDdest = '';
    }

    // form
    $oHtml->PageMsgAdm
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th>'.$L['Title'].'</td>
    <td><b>'.$strTitle.'</b></td>
    </tr>
    <tr>
    <th>'.$L['Containing'].'</th>
    <td>'.$strDcont.'</td>
    </tr>'.PHP_EOL.$strDdest.'
    <tr>
    <th>&nbsp;</th>
    <td>
    <input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="d" value="'.$d.'" />
    <input type="submit" name="ok" value="'.L('Delete').'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.$L['Cancel'].'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
    </td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // Delete domain

  require 'bin/class/qti_class_dom.php';
  if ( $t>=0 ) cDomain::MoveItems($d,$t);
  cDomain::Drop($d);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'deletesection':
// --------------

  if ( $s<1 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Section_del'];
  $oVIP->exiturl = 'qti_adm_sections.php';
  $oVIP->exitname = L('Exit');

  $oSEC = new cSection($s);

  // ask confirmation
  if ( empty($ok) )
  {
    // list topics
    if ( $oSEC->items>0 )
    {
      $strList = '<tr><th>&nbsp;</th><td><span class="bold">'.$L['H_Items_delete'].'</span></br><a href="qti_adm_change.php?a=topicmoveall&amp;s='.$s.'&amp;d=10">'.$L['Adm_items_move'].' &raquo;</a></td></tr>';
    }
    else
    {
      $strList = '';
    }

    $oHtml->PageMsgAdm
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th>'.$L['Section'].'</td>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <th>'.$L['Containing'].'</th>
    <td>'.L('Item',$oSEC->items).', '.L('Reply',$oSEC->replies).'</td>
    </tr>
    '.$strList.'
    <tr>
    <th>&nbsp;</th>
    <td>
    <input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="s" value="'.$s.'" />
    <input type="submit" name="ok" value="'.L('Delete').'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.$L['Cancel'].'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
    </td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // Delete section

  $oSEC->DeleteItems('*',false,'',false); // no need to update section stats
  cSection::Drop($s);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'status_del':
// --------------

  if ( $v=='A' || $v=='Z' ) die('Wrong id '.$v);

  $oVIP->selfname = L('Delete').' '.strtolower($L['Status']);
  $oVIP->exiturl = 'qti_adm_statuses.php';
  $oVIP->exitname = L('Exit');

  // ask confirmation
  if ( empty($ok) || !isset($_GET['to']) )
  {
    // list of status destination
    $strSdest = '';
    $arrS = sMem::Get('sys_statuses');
    foreach($arrS as $strKey=>$arrStatus)
    {
      if ( $strKey!=$v ) $strSdest .= '<option value="'.$strKey.'" />'.$strKey.' - '.$arrStatus['statusname'].'</option>';
    }

    $oHtml->PageMsgAdm
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th style="width:150px;">'.$L['Status'].'</th>
    <td>'.$v.'&nbsp;&nbsp;'.AsImg($_SESSION[QT]['skin_dir'].'/'.$arrS[$v]['icon'],'-',$arrS[$v]['statusname'],'ico').'&nbsp;&nbsp;'.$arrS[$v]['name'].'</td>
    </tr>'.( empty($arrS[$v]['statusdesc']) ? '' : '<tr><th>'.$L['Description'].'</th><td>'.$arrS[$v]['statusdesc'].'</td></tr>' ).'
    <tr>
    <th>'.$L['Change'].'</th>
    <td>'.$L['H_Status_move'].' <select name="to" size="1" class="small">'.$strSdest.'</select></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td>
    <input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="v" value="'.$v.'" />
    <input type="submit" name="ok" value="'.L('Delete').'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.$L['Cancel'].'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // Delete status

  sStatus::Delete($v,$_GET['to']);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicdeleteall':
// --------------

  if ( $s<0 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Adm_items_delete'];
  $oVIP->exiturl  = 'qti_adm_topic.php?d='.$d;
  $oVIP->exitname = L('Exit');

  $oSEC = new cSection($s);
  $intCount = $oSEC->items; // number of topics before the action
  $intClosed = $oSEC->itemsZ;
  $intNews= cSection::CountItems($oSEC->uid,'news');

  // ask confirmation
  if ( empty($ok) )
  {
    $oHtml->PageMsgAdm
    (
    NULL,
    '<p><span class="bold">'.$L['All'].'</span> &middot; <a href="'.Href().'?a=topicdeleteyear&amp;s='.$s.'&amp;d='.$d.'">'.$L['By'].' '.strtolower($L['Year']).'</a></p>
    <form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th>'.$L['Section'].'</th>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <th>'.$L['Containing'].'</th>
    <td>'.L('Item',$oSEC->items).' ('.L('News',$intNews).', '.L('Reply',$oSEC->replies).')</td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><span class="bold">'.$L['H_Items_delete'].'</span></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td>
    <input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="s" value="'.$s.'" />
    <input type="hidden" name="d" value="'.$d.'" />
    <input type="submit" name="ok" value="'.L('Delete').'" /> <span class="small">('.$oSEC->items.')</span>&nbsp;&nbsp;'.( $intClosed>0 ? ' <input type="submit" name="ok" value="'.$L['Delete_closed'].'" /> <span class="small">('.$intClosed.')</span>' : '').'</td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // delete items (or closed only)
  $oSEC->DeleteItems('*',$ok==$L['Delete_closed']);
  $intCount = $intCount - $oSEC->items;

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'].'<br/>'.L('Items_deleted').': '.$intCount;
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicdeleteyear':
// --------------

  if ( $s<0 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Adm_items_delete'];
  $oVIP->exiturl  = 'qti_adm_topic.php?d='.$d;
  $oVIP->exitname = L('Exit');

  $oSEC = new cSection($s);

  // Recompute stats

  $intCount = cSection::CountItems($oSEC->uid,'topics'); // number of topics before the action
  $intYear = intval(date('Y'));
  $arrYears = array('old'=>($intYear-4).' and older',($intYear-3)=>$intYear-3,$intYear-2,$intYear-1,$intYear);
  foreach($arrYears as $strKey=>$strValue) $arrYears[$strKey] .= strtolower(' -- '.L('Item',cSection::CountItems($oSEC->uid,'topics',10,$strKey)).' ('.L('Closed',cSection::CountItems($oSEC->uid,'topicsZ',10,$strKey)).')');

  // ask confirmation and ask year
  if ( empty($ok) || empty($_GET['v']) )
  {
    $oHtml->PageMsgAdm
    (
    NULL,
    '<p><a href="'.Href().'?a=topicdeleteall&amp;s='.$s.'&amp;d='.$d.'">'.$L['All'].'</a> &middot; <span class="bold">'.$L['By'].' '.strtolower($L['Year']).'</span></p>
    <form method="get" action="'.$oVIP->selfurl.'">
    <table  class="t-data horiz">
    <tr>
    <th>'.$L['Section'].'</td>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <th>'.$L['Year'].'</th>
    <td><select id="v" name="v">'.QTasTag($arrYears).'</select></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><i><b>'.$L['H_Items_delete'].'</b></i></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td>
    <input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="s" value="'.$s.'" />
    <input type="hidden" name="d" value="'.$d.'" />
    <input type="submit" name="ok" value="'.L('Delete').'" /> &nbsp;&nbsp; <input type="submit" name="ok" value="'.$L['Delete_closed'].'" /></td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // delete topics (or closed only)
  $oSEC->DeleteItems('*',$ok==$L['Delete_closed'],$v);
  $intCount = $intCount - $oSEC->items;

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'].'<br/>'.L('Items_deleted').': '.$intCount;
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicmoveall':
// --------------

  if ( $s<0 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Adm_items_move'];
  $oVIP->exiturl  = 'qti_adm_topic.php?d='.$d;
  $oVIP->exitname = L('Exit');

  $oSEC = new cSection($s);

  // Recompute stats

  $intCount = cSection::CountItems($oSEC->uid,'topics'); // number of topics before the action
  $intClosed = cSection::CountItems($oSEC->uid,'topicsZ');
  $intNews = cSection::CountItems($oSEC->uid,'news');
  $intInspections = cSection::CountItems($oSEC->uid,'inspections');

  // Ask confirmation
  if ( empty($ok) || $t<0 )
  {
    $oHtml->PageMsgAdm
    (
    NULL,
    '<p><span class="bold">'.$L['All'].'</span> &middot; <a href="'.Href().'?a=topicmoveyear&amp;s='.$s.'&amp;d='.$d.'">'.$L['By'].' '.strtolower($L['Year']).'</a></p>
    <form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th style="width:150px;">'.$L['Section'].'</td>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <th>'.$L['Containing'].'</th>
    <td>'.L('Item',$oSEC->items).' ('.L('News',$intNews).', '.L('Inspections',$intInspections).') , '.L('Reply',$oSEC->replies).'</td>
    </tr>
    <tr>
    <th>'.$L['Move_to'].'</th>
    <td><select name="t" size="1">'.Sectionlist(-1,$s).'</select></td>
    </tr>
    <tr>
    <th>'.$L['Ref'].'</th>
    <td><select name="p" size="1">
    <option value="1">'.$L['Move_keep'].'</option>
    <option value="0">'.$L['Move_reset'].'</option>
    <option value="2">'.$L['Move_follow'].'</option>
    </select></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="s" value="'.$s.'" />
    <input type="hidden" name="d" value="'.$d.'" />
    <input type="submit" name="ok" value="'.$L['Move'].'" /> <span class="small">('.$oSEC->items.')</span>&nbsp;&nbsp;'.( $intClosed>0 ? ' <input type="submit" name="ok" value="'.$L['Move_closed'].'" /> <span class="small">('.$intClosed.')</span>' : '').'</td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // move topics
  $oSEC->MoveTopics($t,$p,-1,$ok==$L['Move_closed']);
  $intCount = $intCount - $oSEC->items;

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'].'<br/>'.L('Items_moved').': '.$intCount;
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'topicmoveyear':
// --------------

  if ( $s<0 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Adm_items_move'];
  $oVIP->exiturl  = 'qti_adm_topic.php?d='.$d;
  $oVIP->exitname = L('Exit');

  $oSEC = new cSection($s);
  $intCount = $oSEC->items; // number of topics before the action
  $intYear = intval(date('Y'));
  $arrYears = array('old'=>($intYear-4).' and older',($intYear-3)=>$intYear-3,$intYear-2,$intYear-1,$intYear);
  foreach($arrYears as $strKey=>$strValue) $arrYears[$strKey] .= strtolower(' -- '.L('Item',cSection::CountItems($oSEC->uid,'topics',10,$strKey)).' ('.L('Closed',cSection::CountItems($oSEC->uid,'topicsZ',10,$strKey)).')');

  // ask confirmation and ask year

  if ( empty($ok) || empty($_GET['v']) || $t<0 )
  {
    $oHtml->PageMsgAdm
    (
    NULL,
    '<p><a href="'.Href().'?a=topicmoveall&amp;s='.$s.'&amp;d='.$d.'">'.$L['All'].'</a> &middot; <span class="bold">'.$L['By'].' '.strtolower($L['Year']).'</span></p>
    <form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th style="width:150px;">'.$L['Section'].'</td>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <th>'.$L['Year'].'</th>
    <td><select id="v" name="v">'.QTasTag($arrYears).'</select></td>
    </tr>
    <tr>
    <th>'.$L['Move_to'].'</th>
    <td><select name="t" size="1">'.Sectionlist(-1,$s).'</select></td>
    </tr>
    <tr>
    <th>'.$L['Ref'].'</th>
    <td><select name="p" size="1">
    <option value="1">'.$L['Move_keep'].'</option>
    <option value="0">'.$L['Move_reset'].'</option>
    <option value="2">'.$L['Move_follow'].'</option>
    </select></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="s" value="'.$s.'" />
    <input type="hidden" name="d" value="'.$d.'" />
    <input type="submit" name="ok" value="'.$L['Move'].'" /> &nbsp;&nbsp; <input type="submit" name="ok" value="'.$L['Move_closed'].'" /></td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // move topics
  $oSEC->MoveTopics($t,$p,-1,$ok==$L['Move_closed'],$v);
  $intCount = $intCount - $oSEC->items;

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'].'<br/>'.L('Items_moved').': '.$intCount;
  $oHtml->Redirect($oVIP->exiturl);
  break;


// --------------
case 'topicprune':
// --------------

  if ( $s<0 ) die('Wrong id '.$s);
  if ( $d<1 ) die('Wrong day '.$d);
  $intT = 0;
  $intA = 0;
  $intI = 0; // only one of these 3 variables will be set (see checkbox)
  if ( isset($_GET['tt']) ) $intT = intval($_GET['tt']);
  if ( isset($_GET['ta']) ) $intA = intval($_GET['ta']);
  if ( isset($_GET['ti']) ) $intI = intval($_GET['ti']);

  $oSEC = new cSection($s);
  $intUA = cSection::CountItems($oSEC->uid,'unrepliedA',$d); // only news
  $intUI = cSection::CountItems($oSEC->uid,'unrepliedI',$d); // only inspections
  $intUT = cSection::CountItems($oSEC->uid,'unrepliedT',$d); // only std tickets

  $oVIP->selfname = $L['Adm_items_prune'];
  $oVIP->exiturl = 'qti_adm_topic.php?d='.$d;
  $oVIP->exitname = L('Exit');

  // Ask confirmation

  if ( empty($ok) || ($intT+$intA+$intI)==0 )
  {
    $error = (!empty($ok) ? '<span class="error">'.$L['E_nothing_selected'].'</span><br />' : '');

    $oHtml->PageMsgAdm
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th>'.$L['Section'].'</td><td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <th>'.$L['Containing'].'</th><td>'.L('Unreplied_item',$intUT+$intUA+$intUI).'</td>
    </tr>
    <tr>
    <th>'.L('Delete').'</th><td>
    <input type="checkbox" id="ta" name="ta" value="'.$intUA.'"'.($intUA==0 ? QDIS : '').'/>&nbsp;<label for="ta">'.L('News',$intUA).'</label><br />
    <input type="checkbox" id="ti" name="ti" value="'.$intUI.'"'.($intUI==0 ? QDIS : '').'/>&nbsp;<label for="ti">'.L('Inspection',$intUI).'</label><br />
    <input type="checkbox" id="tt" name="tt" value="'.$intUT.'"'.($intUT==0 ? QDIS : '').'/>&nbsp;<label for="tt">'.L('Other',$intUT).'</label></td>
    </tr>
    <tr>
    <th>&nbsp;</th><td><span class="bold">'.sprintf($L['H_Items_prune'],$d).'</span></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td>
    <input type="hidden" name="a" value="'.$a.'" />
    <input type="hidden" name="d" value="'.$d.'" />
    <input type="hidden" name="s" value="'.$s.'" />
    <input type="submit" name="ok" value="'.L('Delete').'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.$L['Cancel'].'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
    </td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // Only std topics

  if ( $intT>0 )
  {
    $oDB->Query('SELECT t.id FROM '.TABTOPIC.' t WHERE t.forum='.$s.' AND t.replies=0 AND t.type="T" AND t.firstpostdate<"'.DateAdd(date('Ymd His'),-$d,'day').'"' );
    $strId = '';
    while($row=$oDB->Getrow()) $strId .= $row['id'].',';
    if ( !empty($strId) )
    {
      $strId = substr($strId,0,-1);
      // delete posts and topics
      $oDB->Exec('DELETE FROM '.TABPOST.' WHERE topic IN ('.$strId.')' );
      $oDB->Exec('DELETE FROM '.TABTOPIC.' WHERE id IN ('.$strId.')' );
    }
  }

  // Only news topics

  if ( $intA>0 )
  {
    $oDB->Query('SELECT t.id FROM '.TABTOPIC.' t WHERE t.forum='.$s.' AND t.replies=0 AND t.type="A" AND t.firstpostdate<"'.DateAdd(date('Ymd His'),-$d,'day').'"' );
    $strId = '';
    while($row=$oDB->Getrow()) $strId .= $row['id'].',';
    if ( !empty($strId) )
    {
      $strId = substr($strId,0,-1);
      // delete posts and topics
      $oDB->Exec('DELETE FROM '.TABPOST.' WHERE topic IN ('.$strId.')' );
      $oDB->Exec('DELETE FROM '.TABTOPIC.' WHERE id IN ('.$strId.')' );
    }
  }

  // Only inspection topics

  if ( $intI>0 )
  {
    $oDB->Query('SELECT t.id FROM '.TABTOPIC.' t WHERE t.forum='.$s.' AND t.replies=0 AND t.type="I" AND t.firstpostdate<"'.DateAdd(date('Ymd His'),-$d,'day').'"' );
    $strId = '';
    while($row=$oDB->Getrow()) $strId .= $row['id'].',';
    if ( !empty($strId) )
    {
      $strId = substr($strId,0,-1);
      // delete posts and topics
      $oDB->Exec('DELETE FROM '.TABPOST.' WHERE topic IN ('.$strId.')' );
      $oDB->Exec('DELETE FROM '.TABTOPIC.' WHERE id IN ('.$strId.')' );
    }
  }

  // Update section stats

  $oSEC->UpdateStats(array('tags'=>$oSEC->tags),true,true);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'tags_del':
// --------------

  if ( isset($_GET['tt']) ) { $tt=strip_tags($_GET['tt']); } else { $tt='en'; }

  $oVIP->selfname = L('Delete').' CSV';
  $oVIP->exiturl = 'qti_adm_tags.php?tt='.$tt;
  $oVIP->exitname = L('Exit');


  // Ask confirmation

  if ( empty($ok) )
  {
    $oHtml->PageMsgAdm
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="t-data horiz">
    <tr>
    <th style="width:150px">File</td>
    <td>'.$v.'</td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><input type="hidden" name="a" value="'.$a.'" /><input type="hidden" name="tt" value="'.$tt.'" /><input type="hidden" name="v" value="'.$v.'" /><input type="submit" name="ok" value="'.L('Delete').'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.$L['Cancel'].'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
    </td>
    </tr>
    </table>
    </form>
    <p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',
    0,
    '550px'
    );
    exit;
  }

  // Delete

  if ( file_exists('upload/'.$v) ) unlink('upload/'.$v);

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
default:
// --------------

  echo 'Unknown action';
  break;

// --------------
}

$oHtml->PageMsgAdm('!','<p>Command ['.$a.'] failled...</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>',2);