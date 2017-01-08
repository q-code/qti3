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
 * @copyright  2014 The PHP Group
 * @version    3.0 build:20160703
 */

session_start();
require 'bin/init.php';
if ( sUser::Role()!='A' ) die(Error(13));

include Translate(APP.'_adm.php');
include Translate(APP.'_reg.php');

// ---------
// INITIALISE
// ---------

$strGroups='';
$intIPP=25; //items per page

$oVIP->selfurl = 'qti_adm_users.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Users'];
$oVIP->exiturl = 'qti_adm_users.php';
$oVIP->exitname = '&laquo; '.$L['Users'];

// INITIALISE

$oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE id>0');
$row = $oDB->Getrow();
$intUsers = (int)$row['countid'];

$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br />'.$L['Users'].' ('.$intUsers.')';

$strGroup = 'all';
$intLimit = 0;
$intPage  = 1;
$strOrder = 'name';
$strDirec = 'asc';
$strOrder2 = ',name ASC';
$strCateg = 'all';
$intChecked = -1; // allow checking an id (-1 means no check)

// security check 1
if ( isset($_GET['group']) ) $strGroup = substr($_GET['group'],0,7);
if ( isset($_GET['page']) ) $intPage = (int)$_GET['page'];
if ( isset($_GET['order']) ) $strOrder = strip_tags($_GET['order']);
if ( isset($_GET['dir']) ) $strDirec = strtolower(strip_tags($_GET['dir']));
if ( isset($_GET['cat']) ) $strCateg = strip_tags($_GET['cat']);

// security check 2 (no long argument)
if ( isset($strOrder[12]) ) die('Invalid argument #order');
if ( isset($strDirec[4]) ) die('Invalid argument #dir');

$intLimit = ($intPage-1)*25;

// User menu

include 'qti_inc_menu.php';

// Prepare to check the last created user (in case of user added in qte_inc_menu.php or if requested by URI)

if ( isset($_GET['cid']) )  $intChecked = (int)strip_tags($_GET['cid']); // allow checking an id. Note checklast overridres this id
if ( isset($_POST['cid']) ) $intChecked = (int)strip_tags($_POST['cid']);
if ( isset($_POST['checklast']) || isset($_GET['checklast']) )
{
  $oDB->Query('SELECT max(id) as countid FROM '.TABUSER); // Find last id. This overrides the cid value !
  $row = $oDB->Getrow();
  $intChecked = (int)$row['countid'];
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qt_table_cb.js"></script>
<script type="text/javascript">
function datasetcontrol_click(checkboxname,action)
{
  var checkboxes = document.getElementsByName(checkboxname);
  var n = 0;
  for (var i=0; i<checkboxes.length; ++i) if ( checkboxes[i].checked ) ++n;
  if ( n>0 )
  {
  document.getElementById("form_users_action").value=action;
  document.getElementById("form_users").submit();
  return;
  }
  else
  {
  alert(qtHtmldecode("'.L('No_selected_row').'"));
  return false;
  }
}
</script>';
$oHtml->scripts_jq[] = '
$(function() {

  // CHECKBOX ALL ROWS
  $("input[id=\'t1-cb-all\']").click(function() { qtCheckboxAll("t1","cb-all","t1-cb[]",true); });

  // Check box when clicking on colomn (except checkbox and c-name)
  $("#t1 td:not(.c-checkbox,.c-photo)").click(function() { qtCheckboxToggle(this.parentNode.id.replace("-tr-","-cb-")); });

  // SHIFT-CLICK CHECKBOX
  var lastChecked1 = null;
  var lastChecked2 = null;
  $("input[name=\'t1-cb[]\']").click(function(event) {

    if(!lastChecked1) lastChecked1 = this;
    if(event.shiftKey)
    {
      var start = $("input[name=\'t1-cb[]\']").index(this);
      var end = $("input[name=\'t1-cb[]\']").index(lastChecked1);
      for(var i=Math.min(start,end);i<=Math.max(start,end);++i)
      {
      $("input[name=\'t1-cb[]\']")[i].checked = lastChecked1.checked;
      qtHighlight("t1-tr-"+$("input[name=\'t1-cb[]\']")[i].value,lastChecked1.checked);
      }
    }
    lastChecked1 = this;
    qtHighlight("t1-tr-"+this.value,this.checked);
  });

});
';

include APP.'_adm_inc_hd.php';

// Add user(s) form

echo '<p>',(empty($strPageMenu) ? '' : $strPageMenu.' | '),'<a href="qti_adm_users_imp.php">',$L['Users_import_csv'],'...</a> | ';
if ( $intUsers>5 ) echo ( $strCateg=='all' ? '<a href="qti_adm_users.php?cat=FM">'.L('Filters').'...</a>' : '<a href="qti_adm_users.php?cat=all">'.L('Filter_remove').'</a>');
echo '</p>';
if ( !empty($strUserform) ) echo $strUserform;

// --------
// Category subform (children)
// --------

switch($strCateg)
{
case 'FM': $str = L('Users_without_post'); break;
case 'SM': $str = L('Users_without_post_since_one_year'); break;
case 'TOP': $str = L('Top_users'); break;
default: 'All';
}

if ( $strCateg!='all' )
{
echo '<div class="filterusers"><h1>',$str.'</h1>',PHP_EOL;
echo '<p>';
echo '<a href="qti_adm_users.php?cat=FM"'.($strCateg=='FM' ? ' onclick="return false;"' : '').'>'.L('Users_without_post').'</a>';
echo ' | <a href="qti_adm_users.php?cat=SM"'.($strCateg=='SM' ? ' onclick="return false;"' : '').'>'.L('Users_without_post_since_one_year').'</a>';
echo '</p></div>',PHP_EOL;
}

// --------
//  and pager
// --------

switch($strGroup)
{
  case 'all': $strWhere = ' WHERE id>0'; break;
  case '0': $strWhere = ' WHERE id>0 AND '.FirstCharCase('name','a-z'); break;
  default:
    $arr = explode('|',$strGroup);
    $arrOr = array();
    foreach($arr as $str)
    {
    $i = strlen($str);
    $arrOr[] = FirstCharCase('name','u',$i).'="'.strtoupper($str).'"';
    }
    $strWhere = ' WHERE id>0 AND ('.implode(' OR ',$arrOr).')';
    break;
}

if ( $strCateg=='FM' ) $strWhere .= ' AND id>1 AND numpost=0'; // false members
if ( $strCateg=='SM' ) $strWhere .= ' AND id>1  AND lastdate<"'.DateAdd(date('Ymd His'),-1,'year').'"'; //sleeping members

$oDB->Query('SELECT count(id) as countid FROM '.TABUSER.$strWhere);
$row = $oDB->Getrow();
$intCount = (int)$row['countid'];

// -- build pager --

$strPager = MakePager("qti_adm_users.php?cat=$strCateg&group=$strGroup&order=$strOrder&dir=$strDirec",$intCount,$intIPP,$intPage);
$strPager = empty($strPager) ? '' : L('Page').$strPager;
if ( $intCount<$intUsers ) $strPager = '<span class="small">'.$intCount.' '.L('found_from').' '.$intUsers.' '.L('users').'</span>'.(empty($strPager) ? '' : ' | '.$strPager);
$strPager = (empty($strPager) ? '' : '<p class="pager">'.$strPager.'</p>');

// -- Display lettres bar --
if ( $intCount>$intIPP || $strGroup!='all' )
{
  // optimize groups in lettres bar
  if ( $intCount>500 ) { $intChars=1; } else { $intChars=($intCount>$intIPP*2 ? 2 : 3); }
  // lettres bar
  echo PHP_EOL,HtmlLettres(Href().'?'.GetURI('group,page'), $strGroup, L('All'), 'lettres clear', L('Username').' '.L('starting_with').' ', $intChars),PHP_EOL;
}

// --------
// Memberlist
// --------

$table = new cTable('t1','t-item',$intCount);

if ( $intCount!=0 )
{
  echo PHP_EOL,'<form id="form_users" method="post" action="qti_adm_users_edit.php?src=adm"><input type="hidden" id="form_users_action" name="a" value=""/>',PHP_EOL;
  echo '<table class="pagertop"><tr><td class="pagerleft"><i class="fa fa-level-down fa-lg fa-rotate-270" style="margin:0 10px 0 15px"></i>'.$strDataCommand.'</td><td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL;

  // === TABLE DEFINITION ===
  $table->activecol = $strOrder;
  $table->activelink = '<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'">%s</a>&nbsp;<i class="fa fa-caret-'.($strDirec=='asc' ? 'up' : 'down').'"><i/>';
  $table->th['checkbox'] = new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="t1-cb-all" id="t1-cb-all" />'),'','c-checkbox');
  $table->th['name'] = new cTableHead(L('User'),'','c-name','<a href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=name&amp;dir=asc">%s</a>');
  $table->th['photo'] = new cTableHead('<i class="fa fa-camera" title="'.L('Picture').'"></i>','','c-photo');
  $table->th['role'] = new cTableHead($L['Role'],'','c-role','<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=role&amp;dir=asc">%s</a>');
  $table->th['numpost'] = new cTableHead('<i class="fa fa-comments" title="'.L('Messages').'"></i>','','c-numpost','<a href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=numpost&amp;dir=desc">%s</a>');
  if ( $strCateg=='FM' || $strCateg=='SC' )
  {
  $table->th['firstdate'] = new cTableHead(L('Joined'),'','c-joined','<a href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=firstdate&amp;dir=desc">%s</a>');
  }
  else
  {
  $table->th['lastdate'] = new cTableHead(L('Last_message').' (ip)','','c-lastmessage','<a href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=lastdate&amp;dir=desc">%s</a>');
  }
  $table->th['closed'] = new cTableHead('<i class="fa fa-ban" title="'.L('Ban').'"></i>','','c-ban','<a href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=closed&amp;dir=desc">%s</a>');
  $table->th['id'] = new cTableHead('Id','','c-firstname','<a href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=id&amp;dir=asc">%s</a>');
  // create column data (from headers identifiers) and add class to all
  foreach($table->th as $key=>$th)
  {
    $table->td[$key] = new cTableData();
    $table->td[$key]->Add('class','c-'.$key);
  }

  // === TABLE START DISPLAY ===

  echo PHP_EOL;
  echo $table->Start().PHP_EOL;
  echo '<thead>'.PHP_EOL;
  echo $table->GetTHrow().PHP_EOL;
  echo '</thead>'.PHP_EOL;
  echo '<tbody>'.PHP_EOL;

  //-- LIMIT QUERY --
  $strState = 'id,name,closed,role,numpost,firstdate,lastdate,ip,photo FROM '.TABUSER.$strWhere;
  $oDB->Query( LimitSQL($strState,$strOrder.' '.strtoupper($strDirec).($strOrder==='name' ? '' : $strOrder2),$intLimit,$intIPP+20) );
  // --------

  $strAlt='r1';
  $arrRow=array(); // rendered row. To remove duplicate in seach result
  $intRow=0; // count row displayed

  while($row=$oDB->Getrow())
  {
    if ( in_array((int)$row['id'], $arrRow) ) continue; // this remove duplicate users in case of search result

    $arrRow[] = (int)$row['id'];
    if ( empty($row['name']) ) $row['name']='('.L('unknown').')';
    $bChecked = $row['id']==$intChecked;
    
    $strLock = L('N');
    if ( $row['closed']=='1' ) $strLock = '1';
    if ( $row['closed']=='2' ) $strLock = '10';
    if ( $row['closed']=='3' ) $strLock = '20';
    if ( $row['closed']=='4' ) $strLock = '30';

    // prepare row
    $str = isset($row['name'][24]) ? QTstrh($row['name']) : ''; // title for long name
    $table->row = new cTableRow( 't1-tr-'.$row['id'], 't-item hover rowlight '.$strAlt.($bChecked ? ' checked' : '') );
    $table->td['checkbox']->content = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'"'.($bChecked ? QCHE : '').'/>'; if ($row['id']<2) $table->td['checkbox']->content = '&nbsp;';
    $table->td['name']->content = '<a href="qti_user.php?id='.$row['id'].'" title="'.$str.'">'.QTtrunc($row['name'],24).'</a>';;
    $table->td['photo']->content = ( empty($row['photo']) ? '' : AsImgPopup('usr_'.$row['id'],$qti_root.QTI_DIR_PIC.$row['photo'],'&rect;'));
    $table->td['role']->content = L('Role_'.strtoupper($row['role']));
    $table->td['numpost']->content = $row['numpost'];
    if ( $strCateg=='FM' || $strCateg=='SC' )
    {
    $table->td['firstdate']->content = empty($row['firstdate']) ? '' : QTdatestr($row['firstdate'],'$','',true);
    }
    else
    {
    $table->td['lastdate']->content = (empty($row['lastdate']) ? '' : QTdatestr($row['lastdate'],'$','',true)) . (empty($row['ip']) ? '' : ' ('.$row['ip'].')');
    }
    $table->td['closed']->content = $strLock;
    $table->td['id']->content = $row['id'];


    echo $table->GetTDrow().PHP_EOL;
    if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
    ++$intRow; if ( $intRow>=$intIPP ) break;

  }

  // === TABLE END DISPLAY ===

  echo '</tbody>',PHP_EOL;
  echo '</table>',PHP_EOL;
  echo '<table class="pagerbot"><tr>',($intRow>3 ? '<td class="pagerleft"><i class="fa fa-level-up fa-lg fa-rotate-90" style="margin:0 10px 0 15px"></i>'.$strDataCommand.'</td>' : ''),'<td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL;
  echo '</form>',PHP_EOL;

}
else
{
  if ( !empty($strPager) ) echo '<table class="pagerbot"><tr><td class="pagerright">',$strPager,'</td></tr></table>',PHP_EOL;
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('None').'...</p>',true,'','r1');
}

// Define bottom page command (add csv to $intCount (max 10000))

$strCsv ='';
$oVIP->selfuri = GetURI('page');
if ( sUser::Role()!='V' )
{
  if ( $intCount<=$_SESSION[QT]['items_per_page'] )
  {
    $strCsv = '<a class="csv" href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].'</a>';
  }
  else
  {
    $strCsv = '<a class="csv" href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=p'.$intPage.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' ('.L('page').')</a>';
    if ( $intCount<=1000 )                   $strCsv .= ' &middot; <a class="csv" href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' ('.L('all').')</a>';
    if ( $intCount>1000 && $intCount<=2000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m1&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-1000)</a> &middot; <a class="csv" href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m2&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1000-'.$intCount.')</a>';
    if ( $intCount>2000 && $intCount<=5000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m5&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a>';
    if ( $intCount>5000 )                    $strCsv .= ' &middot; <a class="csv" href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;sier=m5&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a> &middot; < class="csv"a href="'.Href('qti_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m10&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (5000-10000)</a>';
  }
}
if ( !empty($strCsv) )
{
  echo '<p class="right">',$strCsv,'</p>',PHP_EOL;
}

// --------
// HTML END
// --------

if ( isset($_GET['cb']) )
{
$oHtml->scripts_end[] = '<script type="text/javascript">
var ids = ['.$_GET['cb'].'];
qtCheckboxIds(ids);
</script>';
}
include APP.'_adm_inc_ft.php';