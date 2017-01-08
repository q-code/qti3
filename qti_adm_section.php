<?php

/**
* PHP version 5
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
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die($L['E_admin']);

// INITIALISE

$s = -1;
$tt = 0; // 0:definition, 1:display or 9:translation
QThttpvar('s tt','int int');
if ( $s<0 ) die('Missing parameters');
if ( $tt!=0 && $tt!=1 && $tt!=9 ) die('Missing parameters');

$oVIP->selfurl = 'qti_adm_section.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Section_upd'];
$oVIP->exiturl = 'qti_adm_sections.php';
$oVIP->exitname = '<i class="fa fa-chevron-circle-left fa-lg"></i> '.$L['Sections'];

$arrDomains = GetDomains();
$arrStaff = GetUsers('M');

$oSEC = new cSection($s);

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) && $tt==0 )
{
  // CHECK MANDATORY VALUE

  $str = trim($_POST['title']);
  if ( empty($str) ) { $str='Untitled'; $error = $L['Title'].' '.L('invalid'); }

  if ( empty($error) )
  {
    $oSEC->pid = intval($_POST['domain']);
    $oSEC->title = $str;
    //$oSEC->name = $str;
    $oSEC->type = intval($_POST['type']);
    $oSEC->status = intval($_POST['status']);
    $oSEC->notify = intval($_POST['notify']);
    if ( isset($_POST['modname']) )
    {
      if ( $_POST['modname']!=$_POST['modnameold'] )
      {
      $oSEC->modname = $_POST['modname'];
      $oSEC->modid = array_search($_POST['modname'],$arrStaff);
      if ( $oSEC->modid==FALSE || empty($oSEC->modid) ) { $oSEC->modid=1; $oSEC->modname=$arrStaff[1]; $warning=$L['Role_M'].' '.L('invalid'); }
      }
    }
    if ( isset($_POST['modid']) )
    {
      if ( $_POST['modid']!=$_POST['modidold'] )
      {
        $oSEC->modname = $arrStaff[$_POST['modid']];
        $oSEC->modid = (int)$_POST['modid'];
      }
    }
    $oSEC->titlefield = intval($_POST['titlefield']);
    $oSEC->numfield = trim($_POST['numfield']); if ( strlen($oSEC->numfield)==0 ) $oSEC->numfield='N';
    $oSEC->prefix = $_POST['prefix'];
    $oSEC->wisheddate = intval($_POST['wisheddate']);
    $oSEC->wisheddflt = 0; if ( $oSEC->wisheddate==2 && isset($_POST['wisheddflt']) ) $oSEC->wisheddflt=intval($_POST['wisheddflt']);
    $oSEC->notifycc = intval($_POST['alternate']);
    if ( $oSEC->notify==0 && $oSEC->notifycc!=0 )
    {
      $oSEC->notifycc=0;
      $warning=$L['Item_no_notify'];
    }
  }

  // SAVE

  if ( empty($error) )
  {
    // Update

    $strQ = 'UPDATE '.TABSECTION.' SET';
    $strQ .= ' domainid='.$oSEC->pid;
    $strQ .= ',title="'.QTstrd($oSEC->title,64).'"';
    $strQ .= ',type="'.$oSEC->type.'"';
    $strQ .= ',status="'.$oSEC->status.'"';
    $strQ .= ',notify="'.$oSEC->notify.'"';
    $strQ .= ',moderator='.$oSEC->modid;
    $strQ .= ',moderatorname="'.QTstrd($oSEC->modname,24).'"';
    $strQ .= ',titlefield="'.$oSEC->titlefield.'"';
    $strQ .= ',numfield="'.$oSEC->numfield.'"';
    $strQ .= ',alternate="'.$oSEC->notifycc.'"';
    $strQ .= ',wisheddate="'.($oSEC->wisheddate+$oSEC->wisheddflt).'"';
    $strQ .= ',prefix="'.$oSEC->prefix.'"';
    $strQ .= ' WHERE id='.$oSEC->uid;
    $oDB->Query($strQ);

    // Unregister
    $_SESSION['L'] = array();
    sMem::Clear('sys_sections');

    // Exit
    $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
  }

}

if ( isset($_POST['ok']) && $tt==1 )
{
  $oSEC->options = 'order='.$_POST['dfltorder'];
  $oSEC->options .= ';last='.$_POST['lastcolumn'];
  $oSEC->options .= ';logo='.$_POST['sectionlogo'];
  if ( isset($_POST['sd']) ) $oSEC->options .= ';sd='.$_POST['sd'];
  if ( isset($_POST['nt']) ) $oSEC->options .= ';nt='.$_POST['nt'];
  if ( isset($_POST['qr']) ) $oSEC->options .= ';qr='.$_POST['qr'];
  $oDB->Exec('UPDATE '.TABSECTION.' SET options="'.$oSEC->options.'" WHERE id='.$oSEC->uid );

  // Unregister
  if ( isset($_SESSION['L']) ) $_SESSION['L'] = array();
  sMem::Clear('sys_sections');

  // Exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

if ( isset($_POST['ok']) && $tt==9 )
{
    // Translations
    cLang::Delete(array('sec','secdesc'),'s'.$oSEC->uid);
    foreach($_POST as $key=>$str)
    {
      if ( substr($key,0,1)=='T' && !empty($str) ) cLang::Add('sec',substr($key,1),'s'.$oSEC->uid,$_POST[$key]);
      if ( substr($key,0,1)=='D' && !empty($str) ) cLang::Add('secdesc',substr($key,1),'s'.$oSEC->uid,$_POST[$key]);
    }

    // Unregister
    if ( isset($_SESSION['L']) ) $_SESSION['L'] = array();
    sMem::Clear('sys_sections');

    // Exit
    $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

if ( count($arrStaff)>15 && $tt==0 )
{
$oHtml->scripts_jq[] = '
var e0 = "'.L('No_result').'";
var e1 = "'.L('Try_without_options').'";
$(function() {
  $( "#modname" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "bin/qti_j_name.php",
        dataType: "json",
        data: { term: request.term, r:"M", e0: e0, e1: e1 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#modname" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#modname" ).val( ui.item.rItem );
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
}

include APP.'_adm_inc_hd.php';

$arrDest = $arrDomains;
Unset($arrDest[$oSEC->pid]);

// DISPLAY TABS

$arrTabs = array(0=>L('Settings'),1=>$L['Display_options'],9=>$L['Translations']);
echo HtmlTabs($arrTabs, $oVIP->selfurl.'?s='.$s, $tt, 6, L('E_editing'));

// DISPLAY TAB PANEL

echo '<div class="pan">
<div class="pan-top">',QTstrh($oSEC->title),' &middot; ',$arrTabs[$tt],'</div>
';

// FORM 0

if ( $tt==0 )
{

echo '
<script type="text/javascript">
function wisheddfltdisabled(str)
{
  if (str=="2")
  {
  document.getElementById("wisheddflt").disabled=false;
  }
  else
  {
  document.getElementById("wisheddflt").disabled=true;
  }
  return null;
}
</script>
';

echo '<form method="post" action="',$oVIP->selfurl,'?s=',$s,'&amp;tt=',$tt,'">
<h2 class="subtitle">',$L['Definition'],'</h2>
<table class="t-data horiz">
<tr>
<th style="width:150px; text-align:right"><span class="texthead"><label for="title">',$L['Title'],'</label></span></th>
<td><input required type="text" id="title" name="title" size="55" maxlength="64" value="',QTstrh($oSEC->title,64),'" style="background-color:#FFFF99;" onchange="bEdited=true;"/></td>
</tr>
<tr>
<th style="width:150px; text-align:right"><span class="texthead">',$L['Domain'],'</span></th>
<td><select name="domain" onchange="bEdited=true;">
<option value="',$oSEC->pid,'"',QSEL,'>',$arrDomains[$oSEC->pid],'</option>',QTasTag($arrDest,'',array('format'=>$L['Move_to'].': %s')),'</select></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',$L['Properties'],'</h2>
<table class="t-data horiz">
<tr>
<th style="text-align: right; width:150px"><span class="texthead"><label for="type">',$L['Type'],'</label></span></th>
<td>
<select id="type" name="type" onchange="bEdited=true;">
<option value="1"',($oSEC->type==1 ? QSEL : ''),'>',$L['Section_type'][1],'</option>
<option value="0"',($oSEC->type==0 ? QSEL : ''),'>',$L['Section_type'][0],'</option>
<option value="2"',($oSEC->type==2 ? QSEL : ''),'>',$L['Section_type'][2],'</option>
</select>
 ',$L['Status'],' <select id="status" name="status" onchange="bEdited=true;">
<option value="0"',($oSEC->status==0 ? QSEL : ''),'>',$L['Section_status'][0],'</option>
<option value="1"',($oSEC->status==1 ? QSEL : ''),'>',$L['Section_status'][1],'</option>
</select>
 ',$L['Notification'],' <select id="notify" name="notify" onchange="bEdited=true;">
<option value="1"',($oSEC->notify==1 ? QSEL : ''),'>',L('Y'),'</option>
<option value="0"',($oSEC->notify==0 ? QSEL : ''),'>',L('N'),'</option>
</select>
</td>
</tr>
<tr>
';
if ( count($arrStaff)>15 )
{
echo '<th style="width:150px; text-align:right"><span class="texthead">',L('Coordinator'),'</span></th>
<td><input type="hidden" name="modnameold" value="',$oSEC->modname,'" onchange="bEdited=true;"/><input name="modname" id="modname" size="20" maxlength="24" value="',$oSEC->modname,'" onchange="bEdited=true;"/></td>';
}
else
{
echo '<th style="width:150px; text-align:right"><span class="texthead">',L('Coordinator'),'</span></th>
<td><input type="hidden" name="modidold" value="',$oSEC->modid,'" onchange="bEdited=true;"/><select name="modid" id="modid" onchange="bEdited=true;">',QTasTag($arrStaff,$oSEC->modid,array('current'=>$oSEC->modid,'classC'=>'bold')),'</select></td>';
}
echo '
</tr>
</table>
<h2 class="subtitle">',$L['Specific_fields'],'</h2>
<table class="t-data horiz">
<tr>
<th style="text-align: right; width:150px"><span class="texthead"><label for="numfield">',$L['Show_item_id'],'</label></span></th>
<td><input type="text" id="numfield" size="12" maxlength="24" name="numfield" value="',($oSEC->numfield=='N' ? '' : $oSEC->numfield),'" onchange="bEdited=true;"/>&nbsp;<span class="small">',$L['H_Show_item_id'],'</span></td>
</tr>
<tr>
<th style="text-align: right; width:150px"><span class="texthead"><label for="titlefield">',$L['Show_item_title'],'</label></span></th>
<td><select id="titlefield" name="titlefield" onchange="bEdited=true;">',QTasTag($L['Item_title'],$oSEC->titlefield),'</select>&nbsp;<span class="small">',$L['H_Show_item_title'],'</span></td>
</tr>
<tr title="',$L['H_Item_prefix'],'">
<th style="text-align: right; width:150px"><span class="texthead"><label for="prefix">',$L['Item_prefix'],'</label></span></th>
<td>
<select id="prefix" name="prefix" onchange="bEdited=true;">
',QTasTag($L['Prefix_serie'],$oSEC->prefix),'
<option value="0"',($oSEC->prefix=='0' ? QSEL : ''),'>','(',$L['None'],')','</option>
</select>&nbsp;<a class="small" href="qti_ext_prefix.php" target="_blank">',$L['Item_prefix_demo'],'</a>
</td>
</tr>
<tr>
<th style="text-align: right; width:150px"><span class="texthead"><label for="numfield">',$L['Show_item_wisheddate'],'</label></span></th>
<td><select id="wisheddate" name="wisheddate" onchange="wisheddfltdisabled(this.value); bEdited=true;">',QTasTag($L['Item_wisheddate'],$oSEC->wisheddate),'</select> default <select id="wisheddflt" name="wisheddflt" onchange="bEdited=true;"',($oSEC->wisheddate!=2 ? QDIS : ''),'>',QTasTag(array(0=>$L['None'],$L['dateSQL']['Today'],$L['Day'].' +1',$L['Day'].' +2'),$oSEC->wisheddflt),'</select></td>
</tr>
<tr title="',$L['H_Show_item_notify'],'">
<th style="text-align: right; width:150px"><span class="texthead"><label for="alternate">',$L['Show_item_notify'],'</label></span></th>
<td><select id="alternate" name="alternate" onchange="bEdited=true;">',QTasTag($L['Item_notify'],$oSEC->notifycc),'</select></td>
</tr>
</table>
';
echo '<p class="submit">
<input type="submit" name="ok" value="',L('Save'),'"/>
</p>
</form>
';

}

// FORM 1

if ( $tt==1 )
{

$strLogo = $oSEC->ReadOption('logo');
$strFile='';
if ( file_exists('upload/section/'.$s.'.gif') ) $strFile = $s.'.gif';
if ( file_exists('upload/section/'.$s.'.jpg') ) $strFile = $s.'.jpg';
if ( file_exists('upload/section/'.$s.'.png') ) $strFile = $s.'.png';
if ( file_exists('upload/section/'.$s.'.jpeg') ) $strFile = $s.'.jpeg';
$strOrder = $oSEC->ReadOption('order'); if ( empty($strOrder) ) $strOrder='lastpostdate';
$strLast = $oSEC->LastColumn(); if ( empty($strLast) ) $strLast='none';

echo '
<script type="text/javascript">
function switchimage(strId)
{
  var strDefault="'.$_SESSION[QT]['skin_dir'].'/ico_section_'.$oSEC->type.'_'.$oSEC->status.'.gif";
  var strSpecific="upload/section/',$strFile,'";
  document.getElementById(strId).src=(document.getElementById(strId).src.search(strDefault)==-1 ? strDefault : strSpecific);
  return null;
}
</script>
';

echo '<form method="post" action="',$oVIP->selfurl,'?s=',$s,'&amp;tt=',$tt,'">
<table class="t-data horiz">
<tr>
<th><span class="texthead">Logo</span></th>
<td><select id="sectionlogo" name="sectionlogo" onchange="bEdited=true; switchimage(\'idlogo\');">
<option value=""',(empty($strLogo) ? QSEL : ''),'>',L('Default'),'</option>
';
if ( !empty($strFile) ) echo '<option value="'.$strFile.'"'.(empty($strLogo) ? '' : QSEL).'>'.L('Specific_image').'</option>';
echo '
</select> ',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'ico i-sec','vertical-align:middle','','idlogo'),' <a href="qti_adm_section_img.php?id=',$s,'">',$L['Add'],'/',$L['Remove'],'</a>
</td>
</tr>
';
$arr = array('lastpostdate'=>'Date of the last post','numid'=>'Reference number','title'=>'Title');
echo '<tr>
<th><span class="texthead">Tickets order</span></th>
<td>
<select name="dfltorder" id="dfltorder" onchange="bEdited=true;">',QTasTag($arr,$strOrder),'</select>
</td>
</tr>
';
$arr = array('views'=>$L['Views'],'status'=>$L['Status'],'actorname'=>L('Actor'));
if ( $oSEC->wisheddate!=0 ) $arr['wisheddate']=L('Wisheddate');
if ( $oSEC->notifycc!=0 ) $arr['notifiedname']=L('Notified_user');
$arr['tags']=$L['Tags'];
$arr['id']='Id';
$arr['none']='('.$L['None'].')';
echo '<tr>
<th><span class="texthead"><label for="lastcolumn">',L('Infofield'),'</label></span></th>
<td><select id="lastcolumn" name="lastcolumn" onchange="bEdited=true;">',QTasTag($arr,$strLast),'</select></td>
</tr>
</table>
<br/>
<table class="t-data horiz">
';

$str = $_SESSION[QT]['section_desc']==='2' ? $oSEC->ReadOption('sd') : $_SESSION[QT]['section_desc']; //global setting overwrite section setting
echo '<tr title="',L('H_Repeat_section_description'),'">
<th><span class="texthead"><label for="sd">',L('Repeat_section_description'),'</label></span></th>
<td><select id="sd" name="sd" onchange="bEdited=true;"',($_SESSION[QT]['section_desc']!=='2' ? QDIS : ''),'>
<option value="1"',($str==='1' ? QSEL : ''),'>',L('Y'),'</option>
<option value="0"',($str==='0' ? QSEL : ''),'>',L('N'),'</option>
</select>',($_SESSION[QT]['section_desc']!=='2' ? ' <span class="small disabled">'.L('Is_locked_by_the_layout_skin').'</span>' : ''),'</td>
</tr>
';

$str = $_SESSION[QT]['news_on_top']==='2' ? $oSEC->ReadOption('nt') : $_SESSION[QT]['news_on_top']; //global setting overwrite section setting
echo '<tr title="',L('H_Show_news_on_top'),'">
<th><span class="texthead"><label for="nt">',L('Show_news_on_top'),'</label></span></th>
<td><select id="nt" name="nt" onchange="bEdited=true;"',($_SESSION[QT]['news_on_top']!=='2' ? QDIS : ''),'>
<option value="1"',($str==='1' ? QSEL : ''),'>',L('Y'),'</option>
<option value="0"',($str==='0' ? QSEL : ''),'>',L('N'),'</option>
</select>',($_SESSION[QT]['news_on_top']!=='2' ? ' <span class="small disabled">'.L('Is_locked_by_the_layout_skin').'</span>' : ''),'</td>
</tr>
';

$str = $_SESSION[QT]['show_quick_reply']==='2' ? $oSEC->ReadOption('qr') : $_SESSION[QT]['show_quick_reply']; //global setting overwrite section setting
echo '<tr title="',L('H_Show_quick_reply'),'">
<th><span class="texthead"><label for="qr">',L('Show_quick_reply'),'</label></span></th>
<td><select id="qr" name="qr" onchange="bEdited=true;"',($_SESSION[QT]['show_quick_reply']!=='2' ? QDIS : ''),'>
<option value="1"',($str==='1' ? QSEL : ''),'>',L('Y'),'</option>
<option value="0"',($str==='0' ? QSEL : ''),'>',L('N'),'</option>
</select>',($_SESSION[QT]['show_quick_reply']!=='2' ? ' <span class="small disabled">'.L('Is_locked_by_the_layout_skin').'</span>' : ''),'</td>
</tr>
';

echo '</table>
<p class="submit"><input type="submit" name="ok" value="',L('Save'),'"/></p>
</form>
';

}

// FORM 9

if ( $tt==9 )
{

$arrTrans = cLang::Get('sec','*','s'.$oSEC->uid);
$arrDescTrans = cLang::Get('secdesc','*','s'.$oSEC->uid);

echo '<p style="margin:4px">',sprintf($L['E_no_translation'],$oSEC->title),'</p>
<form method="post" action="',$oVIP->selfurl,'?s=',$s,'&amp;tt=',$tt,'">
<table class="t-data horiz">
<tr>
<th>',$L['Title'],'</th>
<td>
<table>';
include 'bin/qti_lang.php'; // this creates $arrLang
foreach($arrLang as $strIso=>$arr)
{
  $str = '';
  if ( !empty($arrTrans[$strIso]) ) $str = $arrTrans[$strIso];
  echo '
  <tr>
  <td style="width:30px"><span title="',$arr[1],'">',$arr[0],'</span></td>
  <td><input style="width:250px" title="',$L['Section'],' (',$strIso,')" type="text" id="T',$strIso,'" name="T',$strIso,'" size="30" maxlength="64" value="',QTstrh($str),'" onchange="bEdited=true;"/>&nbsp;</td>
  </tr>
  ';
}
echo '</table>
</td>
</tr>
<tr>
<th>',$L['Description'],'</th>
<td>
<table>';
foreach($arrLang as $strIso=>$arr)
{
  $str = '';
  if ( !empty($arrDescTrans[$strIso]) ) $str = $arrDescTrans[$strIso];
  echo '
  <tr>
  <td style="width:30px"><span title="',$arr[1],'">',$arr[0],'</span></td>
  <td><textarea style="width:250px" title="',$L['Description'],' (',$strIso,')" id="D',$strIso,'" name="D',$strIso,'" cols="45" rows="2" onchange="bEdited=true;">',QTstrh($str),'</textarea></td>
  </tr>
  ';
}
echo '</table>
</td>
</tr>
</table>
';
echo '<p class="submit">
<input type="submit" name="ok" value="',L('Save'),'"/>
</p>
</form>
';

}

// END TABS

echo '
</div>
<p id="page-ft"><a href="',$oVIP->exiturl,'" onclick="return qtEdited(bEdited,\'',$L['E_editing'],'\');">',$oVIP->exitname,'</a></p>
';

// HTML END

include APP.'_adm_inc_ft.php';