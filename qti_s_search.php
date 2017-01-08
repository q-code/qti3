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
if ( !sUser::CanView('V5') ) { $oHtml->PageMsg(11); return; }

// INITIALISE

$v = '';

$oVIP->selfurl = 'qti_s_search.php';
$oVIP->selfname = $L['Search'];

$bWithref=false;
if ( count(sMem::Get('sys_sections'))>0 )
{
  $arrSections = QTarrget(GetSections(sUser::Role(),-1,-1,true,'s.numfield<>"N"'));
  if ( count($arrSections)>0 ) $bWithref=true;
}

// ---------
// SUBMITTED
// ---------

if ( isset($_POST['ok']) )
{
  // security check

  $v = $_POST['v']; if ( get_magic_quotes_gpc() ) $v = stripslashes($v);
  $v = strip_tags($v);
  if ( $v==='' ) $error = $L['Keywords'].' '.L('invalid');

  // support direct open when #id is used as ref
  if ( $v[0]==="#" )
  {
    $v = substr($v,1);
    if ( is_numeric($v) ) $oHtml->Redirect('qti_item.php?t='.$v);
  }

  // read keys

  $arrKeys = explode(' ',$v);
  // convert stringnumber to int (if bWithref) and count the different types
  $iNumber = 0;
  $iString = 0;
  foreach($arrKeys as $intKey=>$strVal)
  {
    if ( is_numeric($strVal) && $bWithref )
    {
      if ( strstr($strVal,'.') ) $error = $L['E_ref_search'];
      if ( strstr($strVal,',') ) $error = $L['E_ref_search'];
      $arrKeys[$intKey]=intval($strVal);
      ++$iNumber;
    }
    else
    {
      ++$iString;
      if ( substr($strVal,0,1)=='"' && substr($strVal,-1,1)=='"' ) $arrKeys[$intKey]= substr($strVal,1,-1);
    }
  }
  // reject if different types exist
  if ( $iNumber>1 ) { $error = $L['H_Advanced']; $v = $arrKeys[0]; }
  if ( $iNumber>0 && $iString>0 ) { $error = $L['H_Advanced']; $v = $arrKeys[0]; }

  if ( empty($error) && $iNumber==1 ) $oHtml->Redirect('qti_items.php?q=ref&amp;v='.$arrKeys[0],$L['Search']);
  if ( empty($error) && $iString>0 ) $oHtml->Redirect('qti_items.php?q=kw&amp;v='.urlencode(implode('+',$arrKeys)),$L['Search']);

}

// --------
// HTML START
// --------

$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_search.css" />';
$oHtml->scripts[] = '<script type="text/javascript">
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert("'.L('Missing').'"); return false; }
  return true;
}
</script>
';
$oHtml->scripts_end[] = '<script type="text/javascript">
document.getElementById("refkw").focus();
</script>';

$oHtml->scripts_jq[] = '
var e0 = "'.L('No_result').'";
$(function() {
  $( "#refkw" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "bin/qti_j_refkw.php",
        dataType: "json",
        data: { term: request.term, e0: e0 },
        success: function(data) { response(data); }
      });
    },
    select: function(event, ui) {
      if ( "rSelect" in ui.item )
      {
      if ( ui.item.rSelect.substr(0,1)=="#") window.location="qti_item.php?t="+ui.item.rSelect.substr(1);
      $( "#refkw" ).val(ui.item.rSelect); return false;
      }
      $( "#refkw" ).val(ui.item.rItem);
      return false;
    }
  })
  .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + ("rImage" in item && item.rImage!="" ? item.rImage : "") + item.rItem + " &middot; <span class=\"jinfo\">" + item.rInfo + "</span></a>" )
      .appendTo( ul );
  };
});
';

include 'qti_inc_hd.php';
include 'qti_inc_menu.php';

// SIMPLE SEARCH
echo '<h2>',$L['Search'],'</h2>
<div id="s_search">
<form method="post" action="',Href(),'">
<table class="t-sec">
<tr class="t-sec">
<td class="c-icon"><i class="fa fa-search fa-2x"></i></td>
<td><input type="text" id="refkw" name="v" size="24" maxlength="32" value="',$v,'" required/>&nbsp;<input type="submit" id="ok" name="ok" value="',$L['Ok'],'" />',($bWithref ? ' <span class="small">'.$L['H_Advanced'].'</span>' : ''),'</td>
<td style="text-align:right"><a href="qti_search.php">',$L['Advanced_search'],'...</a></td>
</table>
</form>
</div>
';

if ( !empty($error) ) echo '<p class="error">',$error,'</p>';

// HTML END

include 'qti_inc_ft.php';