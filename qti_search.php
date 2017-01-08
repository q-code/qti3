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
require 'bin/qti_fn_tags.php';

// INITIALISE

$oVIP->selfurl = 'qti_search.php';
$oVIP->selfname = L('Search');
$oVIP->exitname = L('Search');

$q = '';  // query model
$v = '';  // input value (ref)
$v2 = '';
$s = '*';  // section filter can be '*' or [int]
$st = '*';
$y = 0;

QThttpvar('q v v2 s st y','str str str str str int');

if ( isset($_POST['s_ref']) ) $s = strip_tags($_POST['s_ref']);
if ( isset($_POST['s_kw']) ) $s = strip_tags($_POST['s_kw']);
if ( isset($_POST['s_adv']) ) $s = strip_tags($_POST['s_adv']);
if ( isset($_POST['s_usr']) ) $s = strip_tags($_POST['s_usr']);

if ( $s==='' || $s==='-1' ) $s='*';
if ( $st==='' ) $st='*';
if ( $s!=='*' ) $s=(int)$s;
if ( $st!=='*' ) $st=strtoupper($st);

$arrSections = sMem::Get('sys_sections');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) && !empty($q) )
{
  $str=''; // criterias (other than filters)
  switch($q)
  {
  case 'ref':
    if ( empty($v) ) $error = $L['Ref'].' '.L('invalid');
    // support direct open when #id is used as ref
    if ( $v[0]==="#" )
    {
      $v = substr($v,1);
      if ( is_numeric($v) ) $oHtml->Redirect('qti_item.php?t='.$v);
    }
    $str = '&amp;v='.urlencode($v);
    break;
  case 'kw':
    if ( empty($v) ) $error = $L['Keywords'].' '.L('invalid');
    $str = '&amp;v='.urlencode($v).'&amp;v2='.(isset($_POST['v2']) ? '1' : '0');
    break;
  case 'adv':
    if ( $v2==='-' || $v2==='--' || $st==='-' ) $error = $L['Date'].' & '.$L['Status'].' '.L('invalid');
    if ( $v2==='*' && $st==='*' && empty($v) ) $error = $L['Date'].' & '.$L['Status'].' & Tag '.L('invalid');
    if ( $v===';' ) $error = 'Tag '.L('invalid');
    $str = '&amp;st='.$st.'&amp;y='.$y.'&amp;v2='.$v2.'&amp;v='.urlencode($v);
    break;
  case 'user':
  case 'actor':
		$id = sUser::GetUserId($_POST['username']); // return false if wrong name or empty post)
		if ( $id===false && substr($_POST['username'],0,1)==="#" && is_numeric(substr($_POST['username'],1)) ) { $id=(int)substr($_POST['username'],1); $_POST['username']='#'.$id; }
		if ( $id===false ) { $error = '<p class="error">'.L('Username').' '.L('unknown').'...</p>'; }
    $str = '&amp;v='.$id.'&amp;v2='.urlencode($_POST['username']);
    break;
  default: die('Unknown criteria '.$q);
  }
  // redirect
  if ( empty($error) )
  {
    $oHtml->Redirect('qti_items.php?q='.$q.'&amp;s='.$s.$str);
    exit;
  }
}

// --------
// HTML START
// --------

$arrTags = cSection::GetTagsUsed(-1);
if ( count($arrTags)>0 ) $arrTags = TagsDesc($arrTags);
$str  = '';
foreach($arrTags as $strKey=>$strDesc) { $str .= '"'.$strKey.'",'; }
$str = substr($str,0,-1);

$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_search.css" />';
$oHtml->scripts[] = '<script type="text/javascript">
function Rolename(key)
{
	switch(key)
	{
	case "U": return "'.L('Role_U').'";
	case "M": return "'.L('Role_M').'";
	case "A": return "'.L('Role_A').'";
	default: return key;
	}
}
function split( val ) { return val.split( "'.QT_HTML_SEPARATOR.'" ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }
function Running()
{
  var ico = doc.getElementById("ico_option");
  if ( ico )
  {
  if (doc.getElementById("s").value=="*") { ico.className = ico.className.replace(" fa-spin",""); } else { if ( ico.className.indexOf("fa-spin")<0 ) ico.className += " fa-spin"; }
  }
}
function SearchOption(option,value)
{
  if (doc.getElementById(option+"_ref")) doc.getElementById(option+"_ref").value=value;
  if (doc.getElementById(option+"_kw")) doc.getElementById(option+"_kw").value=value;
  if (doc.getElementById(option+"_adv")) doc.getElementById(option+"_adv").value=value;
  if (doc.getElementById(option+"_usr")) doc.getElementById(option+"_usr").value=value;
  Running();
}
function ValidateForm(theForm)
{
  if ( theForm.id=="form_ref" ) if (document.getElementById("ref").value.length==0) { alert("Ref. - "+qtHtmldecode("'.L('Missing').'")); return false; }
  if ( theForm.id=="form_kw" ) if (document.getElementById("kw").value.length==0) { alert("Text - "+qtHtmldecode("'.L('Missing').'")); return false; }
  if ( theForm.id=="form_adv" )
  {
  if (doc.getElementById("date").value.length==0) { alert("Date - "+qtHtmldecode("'.L('Missing').'")); return false; }
  if (doc.getElementById("date").value.length==0) { alert("Date - "+qtHtmldecode("'.L('Missing').'")); return false; }
  }
  return null;
}
</script>
';
$oHtml->scripts_end[] = '<script type="text/javascript">
qtFocusEnd("ref");
if ( doc.getElementById("kw") )
{
  if (!doc.getElementById("ref") || doc.getElementById("kw").value.length>0 ) qtFocusEnd("kw");
}
if ( doc.getElementById("tag") )
{
  if (doc.getElementById("tag").value.length>0) qtFocusEnd("tag");
}
Running();
</script>';

$oHtml->scripts_jq[] = '
$(function() {

  var e0 = "'.L('No_result').'";
  var e1 = "'.L('Try_without_options').'";
  var e5 = "'.L('Tag_not_used').'";

  $( "#ref" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "bin/qti_j_ref.php",
        dataType: "json",
        data: { term: request.term, s: function(){return $("#s_ref").val();}, e0: e0, e1: e1 },
        success: function(data) { response(data); }
      });
    },
    select: function(event, ui) {
      if ( ui.item.rSelect.substr(0,1)=="#" ) window.location="qti_item.php?t="+ui.item.rSelect.substr(1);
      $( "#ref" ).val(ui.item.rSelect);
      return false;
    }
  })
  .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + ("rImage" in item && item.rImage!="" ? item.rImage : "") + item.rItem + " &middot; <span class=\"jinfo\">" + item.rInfo + "</span></a>" )
      .appendTo( ul );
  };

  $( "#kw" ).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "bin/qti_j_kw.php",
        dataType: "json",
        data: { term: extractLast( request.term ), s:function() { return $("#s_kw").val(); }, v2:function() { return $("#to").is(":checked"); }, e0:e0, e1:e1 },
        success: function(data) { response(data); }
      });
    },
    search: function() {
      // custom minLength
      var term = extractLast( this.value );
      if ( term.length < 2 ) { return false; }
    },
    focus: function( event, ui ) { return false; },
    select: function( event, ui ) {
        var terms = split( this.value );
        terms.pop(); // remove current input
        if ( ui.item.rSelect.length==0 ) return false;
        terms.push( ui.item.rSelect ); // add the selected item
        terms.push( "" ); // add placeholder to get the comma-and-space at the end
        this.value = terms.join( "'.QT_HTML_SEPARATOR.'" );
        return false;
      }
    })
  .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + ("rImage" in item && item.rImage!="" ? item.rImage : "") + item.rItem + " <span class=\"jinfo\">" + item.rInfo + "</span></a>" )
      .appendTo( ul );
  };

  $( "#tag" ).autocomplete({
      source: function(request, response) {
        $.ajax({
          url: "bin/qti_j_tagsearch.php",
          dataType: "json",
          data: { term: extractLast( request.term ), s: function() { return $("#s").val(); }, e0: e0, e1: e1 },
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
        .append( "<a class=\"jvalue\">" + item.rItem + "&nbsp; <span class=\"jinfo\">(" + item.rInfo + ")</span></a>" )
        .appendTo( ul );
    };

		$( "#username" ).autocomplete({
			minLength: 1,
			source: function(request, response) {
				$.ajax({
					url: "bin/qti_j_name.php",
					dataType: "json",
					data: { term: request.term, e0: e0 },
					success: function(data) { response(data); }
				});
			},
			select: function( event, ui ) {
				$( "#username" ).val( ui.item.rItem );
				return false;
			}
		})
		.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + Rolename(item.rInfo) + ")</span>") + "</a>" )
				.appendTo( ul );
		};

});
';

include 'qti_inc_hd.php';
include 'qti_inc_menu.php';

// SEARCH OPTION

echo '<h2>',$L['Search_option'],'</h2>
<div id="searchoptions">
<form>
<p><i id="ico_option" class="fa fa-cog fa-3x" style="vertical-align:middle;margin-right:10px"></i>',$L['Section'],'&nbsp;<select id="s" name="s" size="1" onchange="SearchOption(this.name,this.value);">',Sectionlist($s,array(),array(),$L['In_all_sections']),'</select></p>
</form>
</div>
';

echo '
<h2>',$L['Search_criteria'],'</h2>
<div class="searchcriteria">';

// ERROR MESSAGE

if ( !empty($error) ) echo '<p class="error">',$error,'</p>';

// SEARCH BY REF

if ( count(sMem::Get('sys_sections'))>0 )
{
$arrSections = QTarrget(GetSections(sUser::Role(),-1,-1,true,'s.numfield<>"N"'));
if ( count($arrSections)>0 )
{
echo '<form method="post" id="form_ref" action="',Href(),'">
<table class="t-sec">
<tr class="t-sec">
<td class="c-icon"><i class="fa fa-search fa-2x"></i></td>
<td>',$L['Ref'],' <input type="text" id="ref" name="v" size="8" maxlength="32" value="'.($q=='ref' ? $v : '').'" pattern="\#{0,1}[0-9]{1,8}"/>&nbsp;<label for="title">',$L['H_Reference'],'</label></td>
<td style="text-align:right">
<input type="hidden" name="q" value="ref"/>
<input type="hidden" id="s_ref" name="s_ref" value="',$s,'"/>
<input type="submit" name="ok" value="',L('Search'),'" onclick="return ValidateForm(\'ref\');"/>
</td>
</table>
</form>
';
}
}

// SEARCH BY KEY

echo '<form method="post" id="form_kw" action="',Href(),'">
<table class="t-sec">
<tr class="t-sec">
<td class="c-icon"><i class="fa fa-search fa-2x"></i></td>
<td >',$L['Keywords'],' <input type="text" id="kw" name="v" size="40" maxlength="64" value="'.($q=='kw' ? $v : '').'"/>*&nbsp;<input type="checkbox" id="to" name="v2"',(empty($v2) ? '' : QCHE),' value="1"/> <label for="to">',$L['In_title_only'],'</label>
</td>
<td  style="text-align:right">
<input type="hidden" name="q" value="kw"/>
<input type="hidden" id="s_kw" name="s_kw" value="',$s,'"/>
<input type="submit" name="ok" value="',L('Search'),'" onclick="return ValidateForm(\'kw\');"/>
</td>
</table>
</form>
';

// SEARCH BY DATE & STATUS & TAGS

$arrS = sMem::Get('sys_statuses');
echo '<form method="post" action="',$oVIP->selfurl,'">
<table class="t-sec">
<tr class="t-sec">
<td class="c-icon"><i class="fa fa-search fa-2x"></i></td>
<td>',$L['Date'],' <select id="ti" name="v2" size="1">
<option value="*"',($v2==='*' || $v2==='' ? QSEL : ''),'>',$L['Any_time'],'</option>
<optgroup label="&nbsp;">
<option value="w"',($v2==='w' ? QSEL : ''),'>',$L['This_week'],'</option>
<option value="m"',($v2==='m' ? QSEL : ''),'>',$L['This_month'],'</option>
<option value="y"',($v2==='y' ? QSEL : ''),'>',$L['This_year'],'</option>
</optgroup>
<optgroup label="&nbsp;">
',QTasTag($L['dateMMM'],(int)$v2),'
</optgroup>
</select><input type="hidden" id="y" name="y" value="',date('Y'),'"/>
',$L['Status'],'&nbsp;<select id="st" name="st" size="1">
<option value="*"',($st==='*' ? QSEL : ''),'>',$L['Any_status'],'</option>
',QTasTag($arrS,$st),'</select>
';
if ( $_SESSION[QT]['tags']!='0' ) echo $L['With_tag'],' <input type="text" id="tag" name="v" size="30" value="'.($q=='adv' ? $v : '').'" class="small"/>*';
echo '</td>
<td class="right">
<input type="hidden" name="q" value="adv"/>
<input type="hidden" id="s_adv" name="s_adv" value="',$s,'"/>
<input type="submit" name="ok" value="',L('Search'),'"/>
</td>
</tr>
</table>
</form>
';
if ( $_SESSION[QT]['tags']!='0' ) echo '<p class="small">* ',$L['H_Tag_input'],'</p>';

// SEARCH NAME

echo '<form method="post" action="',$oVIP->selfurl,'">
<table class="t-sec">
<tr class="t-sec">
<td class="c-icon"><i class="fa fa-search fa-2x"></i></td>
<td>
<select name="q" size="1">
<option value="user"',($q==='user' ? QSEL : ''),'>',L('Author'),'</option>
<option value="actor"',($q==='actor' ? QSEL : ''),'>',L('Actor'),'</option>
</select> <input id="username" type="text" name="username" value="'.(empty($v2) ? '' : $v2).'" size="32" maxlenght="64"/>
</td>
<td class="right">
<input type="hidden" id="s_usr" name="s_usr" value="',$s,'"/>
<input type="submit" name="ok" value="',L('Search'),'"/>
</td>
</tr>
</table>
</form>
';

echo '
</div>
';

// HTML END

include 'qti_inc_ft.php';