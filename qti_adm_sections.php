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
* @copyright  2015 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die(Error(13));

function arrShift($arr,$oObj,$strDir)
{
  // Shifts an element up/down in the list. Keys are changed into numeric (0..n) except when $oObj is not found or impossible to move
  $arrS = array_values($arr); // Keys are replaced by an integer (0..n)
  $i = array_search($oObj,$arrS); // Search postition of $oObj, false if not found
  if ( $i===FALSE ) return $arr;
  if ( $i==0 && $strDir=='up' ) return $arr;
  if ( $i==(count($arr)-1) && $strDir=='down' ) return $arr;
  $arrO = $arrS;
  $intDir = ($strDir=='up' ? -1 : 1);
  $arrO[$i+$intDir] = $arrS[$i];
  $arrO[$i] = $arrS[$i+$intDir];
  return $arrO;
}

// INITIALISE

$a='';
$d=-1;
$s=-1;
QThttpvar('a d s','str int int');

$oVIP->selfurl = 'qti_adm_sections.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Sections'];

// --------
// SUBMITTED
// --------

// REODER DOMAINS/SECTION (enabled by java drag and drop)

if ( isset($_POST['neworder']) )
{
  $arrO = explode(';',$_POST['neworder']); // format of the domain id is "dom_{i}"
  if ( count($arrO)>1 )
  {
    switch(substr($arrO[0],0,3))
    {
      case 'dom': foreach($arrO as $intKey=>$strId) $oDB->Exec('UPDATE '.TABDOMAIN.' SET titleorder='.$intKey.' WHERE id='.substr($strId,4) ); break;
      case 'sec': foreach($arrO as $intKey=>$strId) $oDB->Exec('UPDATE '.TABSECTION.' SET titleorder='.$intKey.' WHERE id='.substr($strId,4) ); break;
      default: die('invalid command');
    }
    sMem::Clear('sys_domains');
    sMem::Clear('sys_sections');
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  }
}

// ADD DOMAIN

if ( isset($_POST['add_dom']) )
{
  $str = trim($_POST['title']);
  if ( empty($str) ) $error = $L['Domain'].'/'.$L['Section'].' '.Error(1);

  if ( empty($error) )
  {
    require 'bin/class/qti_class_dom.php';
    cDomain::Create($str,null);
    sMem::Clear('sys_domains');
    $_SESSION['pagedialog'] = 'O|'.$L['S_insert'];
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// ADD SECTION

if ( isset($_POST['add_sec']) )
{
  $str = trim($_POST['title']);
  if ( empty($str) ) $error = $L['Domain'].'/'.$L['Section'].' '.Error(1);

  // Add section
  if ( empty($error) )
  {
    cSection::Create($str,(int)$_POST['indomain']);
    sMem::Clear('sys_sections');
    $_SESSION['pagedialog'] = 'O|'.$L['S_insert'];
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// Move domain/section

if ( !empty($a) )
{
  if ( $a=='d_up' || $a=='d_down' )
  {
    $oDB->Query('SELECT id FROM '.TABDOMAIN.' ORDER BY titleorder');
    $arrList = array();
    while($row=$oDB->Getrow()) $arrList[]=intval($row['id']);
    $arrO = array_values(arrShift($arrList,$d,substr($a,2)));
    foreach($arrO as $intKey=>$intId) $oDB->Exec('UPDATE '.TABDOMAIN.' SET titleorder='.$intKey.' WHERE id='.$intId);
    sMem::Clear('sys_domains');
    sMem::Clear('sys_sections');
  }
  if ( $a=='f_up' || $a=='f_down' )
  {
    $oDB->Query('SELECT id FROM '.TABSECTION.' WHERE domainid='.$d.' ORDER BY titleorder');
    $arrList = array();
    while($row=$oDB->Getrow()) $arrList[]=intval($row['id']);
    $arrO = array_values(arrShift($arrList,$s,substr($a,2)));
    foreach($arrO as $intKey=>$intId) $oDB->Exec('UPDATE '.TABSECTION.' SET titleorder='.$intKey.' WHERE id='.$intId);
    sMem::Clear('sys_sections');
  }
}

// --------
// HTML START
// --------

$arrDomains = GetDomains(); 

if ( count($arrDomains)>50 ) {
  $warning='You have too much domains. Try to remove unused domains.'; $_SESSION['pagedialog'] = 'W|'.$warning;
}
$arrSections = GetSections('A',-2); // Optimisation: get all sections at once (grouped by domain)
if ( count($arrSections)>100 ) {
  $warning='You have too much sections. Try to remove unused sections.'; $_SESSION['pagedialog'] = 'W|'.$warning;
}

$oHtml->scripts[] = '<script type="text/javascript">
function ValidateForm(theForm)
{
if (theForm.title.value.length==0) { alert(qtHtmldecode("'.L('Missing').': '.$L['Domain'].'/'.$L['Section'].'")); return false; }
return null;
}
function ToggleForms()
{
doc.getElementById("addform").style.display = ( doc.getElementById("addform").style.display=="none" ? "block" : "none" );
doc.getElementById("toggleforms-arrow").setAttribute("class", (doc.getElementById("addform").style.display=="none" ? "fa fa-caret-down" : "fa fa-caret-up"));
}
function orderbox(b)
{
doc.getElementById("domorderbox").style.display=(b ? "block" : "none");
}
</script>
';

$oHtml->scripts_jq[] = '$(function() {

// Return a helper with preserved width of cells
var fixHelper = function(e, ui) {
ui.children().each(function() {
$(this).width($(this).width());
});
return ui;
};

$("tbody.sortable").sortable({
	items:"tr",
	handle:"td:first",
	helper: fixHelper,
	axis: "y",
	containment:"parent",
	cursor: "n-resize",
	tolerance:"pointer",
	update: function(e,ui) {
	var arrOrder = ui.item.parent().sortable("toArray");
	document.getElementById("neworder").value=arrOrder.join(";");
	document.getElementById("neworder_save").click();
	}
	}).disableSelection();

$("#domorder").sortable({
  axis: "y",
  cursor: "n-resize",
  containment: "parent",
  tolerance:"pointer",
  update: function() { var arrOrder = $("#domorder").sortable("toArray"); document.getElementById("neworder").value=arrOrder.join(";"); }
  }).disableSelection();

});
';

include APP.'_adm_inc_hd.php';

echo '
<p id="page-ft"><a id="toggleforms" href="javascript:void(0)" onclick="ToggleForms(); return false;"><i class="fa fa-plus-circle fa-lg"></i> ',$L['Add'],' ',$L['Domain'],'/',$L['Section'],'&nbsp;<i id="toggleforms-arrow" class="fa fa-caret-down"></i></a></p>
<div id="addform">
<div id="adddomain">
<form method="post" action="qti_adm_sections.php" onsubmit="return ValidateForm(this);">
<table>
<tr>
<td style="width:120px;">',L('Domain_add'),'</td>
<td><input required id="domain" name="title" type="text" size="30" maxlength="64"/></td>
<td style="text-align:right"><input class="inline" id="add_dom" name="add_dom" type="submit" value="',L('Add'),'"/></td>
</tr>
</table>
</form>
</div>
<div id="addsection">
<form method="post" action="qti_adm_sections.php" onsubmit="return ValidateForm(this);">
<table>
<tr>
<td style="width:120px;">',L('Section_add'),'</td>
<td><input required id="section" name="title" type="text" size="30" maxlength="64"/> ',L('in_domain'),' <select name="indomain" size="1">',QTasTag($arrDomains),'</select></td>
<td style="text-align:right"><input class="inline" name="add_sec" type="submit" value="',L('Add'),'"/></td>
</tr>
</table>
</form>
</div>
</div>
';
if ( !isset($_POST['title']) ) echo '<script type="text/javascript">ToggleForms();</script>';

echo '
<table class="t-sec">
<tr class="t-sec">
<th class="c-handler">&nbsp;</th>
<th class="c-section" colspan="2">',$L['Domain'],'/',$L['Section'],'</th>
<th class="c-ref">',$L['Ref'],'</th>
<th class="c-coordinator">',L('Coordinator'),'</th>
<th class="c-action">',$L['Action'],'</th>
<th class="c-move">',$L['Move'],'</th>
</tr>
';

$i=0;
$bSortableDomains = count($arrDomains)>1;
foreach($arrDomains as $intDomain=>$strDomain)
{
  echo '<tr class="t-sec">',PHP_EOL;
  echo '<td class="c-handler group">',($bSortableDomains ? '<span class="draghandler" title="'.L('Move').'" onmousedown="orderbox(true); return false;">&nbsp;</span>' : '&nbsp;'),'</td>',PHP_EOL;
  echo '<td class="c-section group" colspan="2">',QTstrh($strDomain),'</td>',PHP_EOL;
  echo '<td class="c-ref group">&nbsp;</td>',PHP_EOL;
  echo '<td class="c-coordinator group">&nbsp;</td>',PHP_EOL;
  echo '<td class="c-action group"><a class="smalm" href="qti_adm_domain.php?d=',$intDomain,'">',$L['Edit'],'</a>';
  echo ' &middot; ',($intDomain==0 ? '<span class="disabled">'.L('Delete').'</span>' : '<a class="small" href="qti_adm_change.php?a=deletedomain&amp;d='.$intDomain.'">'.L('Delete').'</a>'),'</td>';
  echo '<td class="c-move group">';
  $strUp = '<span class="ctrl"><i class="fa fa-caret-up" title="'.L('Up').'"></i></span>';
  $strDw = '<span class="ctrl"><i class="fa fa-caret-down" title="'.L('Down').'"></i></span>';
  if ( count($arrDomains)>1 )
  {
    if ( $i>0 ) $strUp = '<a class="ctrl" href="qti_adm_sections.php?d='.$intDomain.'&amp;a=d_up"><i class="ctrl fa fa-caret-up" title="'.L('Up').'"></i></a>';
    if ( $i<count($arrDomains)-1 ) $strDw = '<a class="ctrl" href="qti_adm_sections.php?d='.$intDomain.'&amp;a=d_down"><i class="ctrl fa fa-caret-down" title="'.L('Down').'"></i></a>';
  }
   echo $strUp.'&nbsp;'.$strDw;
  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  ++$i;
  $j = 0;

  if ( isset($arrSections[$intDomain]) ) {
  if ( count($arrSections[$intDomain])>0 ) {

    $bSortable = count($arrSections[$intDomain])>1;

    echo '<tbody ',($bSortable ? ' class="sortable"' : ''),'>',PHP_EOL;
    foreach($arrSections[$intDomain] as $intSecid=>$arrSection)
    {
      $oSEC = new cSection($arrSection);
      $strUp = '<span class="ctrl"><i class="fa fa-caret-up" title="'.L('Up').'"></i></span>';
      $strDw = '<span class="ctrl"><i class="fa fa-caret-down" title="'.L('Down').'"></i></span>';
      echo '<tr class="t-sec" id="sec_'.$oSEC->uid.'">';
      echo '<td class="c-handler">',($bSortable ? '<span class="draghandler" title="'.L('Move').'">&nbsp;</span>' : '&nbsp;'),'</td>',PHP_EOL;
      echo '<td class="c-icon">',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i-sec'),'</td>';
      echo '<td class="c-section"><a class="sectionname" href="qti_adm_section.php?s=',$oSEC->uid,'">',QTstrh($oSEC->name),'</a><br/><span class="small">',$L['Section_type'][$oSEC->type],($oSEC->status==1 ? ', '.strtolower($L['Section_status'][1]) : ''),'</span> ',AsImg('admin/ico_notify_'.( $oSEC->notify==1 ? 'on' : 'off' ).'.gif','N',$L['Notification'].': '.( $oSEC->notify==1 ? L('Y') : L('N') ),'ico'),'</td>';
      echo '<td class="c-ref">',( $oSEC->numfield=='N' ? '<span class="disabled">'.L('N').'</span>' : sprintf($oSEC->numfield,1) ),'</td>';
      echo '<td class="c-coordinator">',$oSEC->modname,'</td>';
      echo '<td class="c-action"><a class="small" href="qti_adm_section.php?s=',$oSEC->uid,'">',$L['Edit'],'</a>';
      echo ' &middot; ',($intSecid==0 ? '<span class="disabled">'.L('Delete').'</span>' : '<a class="small" href="qti_adm_change.php?a=deletesection&amp;s='.$intSecid.'">'.L('Delete').'</a>'),'</td>';
      echo '<td class="c-move">';
      if ( count($arrSections[$intDomain])>1 )
      {
        if ( $j>0 ) $strUp = '<a class="ctrl" href="qti_adm_sections.php?d='.$intDomain.'&amp;s='.$intSecid.'&amp;a=f_up"><i class="fa fa-caret-up" title="'.L('Up').'"></i></a>';
        if ( $j<count($arrSections[$intDomain])-1 ) $strDw = '<a class="ctrl" href="qti_adm_sections.php?d='.$intDomain.'&amp;s='.$intSecid.'&amp;a=f_down"><i class="fa fa-caret-down" title="'.L('Down').'"></i></a>';
      }
      echo $strUp.'&nbsp;'.$strDw;
      ++$j;
      echo '</td></tr>',PHP_EOL;
    }

  }}

  echo '</tbody>',PHP_EOL;
}

echo '</table>
';

// DOMAIN ORDER TOOL

if ( count($arrDomains)>1 )
{

echo '
<div id="domorderbox">
<p class="top">Reorder domains<br/>(drag and drop to reorder)</p>
<ul id="domorder">
';
foreach($arrDomains as $intDomain=>$strDomain) echo '<li id="dom_'.$intDomain.'" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>',QTtrunc($strDomain,20),'</li>',PHP_EOL;
echo '</ul>
<form id="form_order" method="post" action="qti_adm_sections.php">
<p class="bottom"><input type="hidden" name="neworder" id="neworder" value="" /><input type="submit" id="neworder_save" name="neworder_save" value="',L('Save'),'" /> <input type="button" name="neworder_cancel" value="',L('Cancel'),'" onclick="orderbox(false);"/></p>
</form>
</div>
';

}

// HTML END

include APP.'_adm_inc_ft.php';