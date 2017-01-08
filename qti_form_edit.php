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
* @copyright  2016 The PHP Group
* @version    3.0 build:20160703
*
* About text coding in the database
* This script will convert the text before inserting into the dabase as follow:
*
* 1) stripslashes
* 2) htmlspecialchar($text,ENT_QUOTES) <>&"' are converted to html
* 3) bbcodes remain UNCHANGED (they are converted while displayed)
*/

session_start();
require 'bin/init.php';
if ( !sUser::CanView('V6') ) die(Error(11));
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_post.css" />';

function DelayAcceptable()
{
  if ( empty($_SESSION[QT.'_usr_lastpost']) ) return true;
  $intMax = isset($_SESSION[QT]['posts_delay']) ? (int)$_SESSION[QT]['posts_delay'] : 5;
  if ( $_SESSION[QT.'_usr_lastpost']+$intMax >= time() ) return false;
  return true;
}

function PostsTodayAcceptable($intMax)
{
  if ( sUser::Id()<2 || !isset($_SESSION[QT.'_usr_posts']) || $_SESSION[QT.'_usr_posts']==0 ) return TRUE;

  // count if not yet defined
  if ( !isset($_SESSION[QT.'_usr_posts_today']) )
  {
    global $oDB;
    $oDB->Query('SELECT count(id) as td FROM '.TABPOST.' WHERE userid='.sUser::Id().' AND '.SqlDateCondition(date('Ymd'),'issuedate',8) );
    $row = $oDB->Getrow();
    $_SESSION[QT.'_usr_posts_today'] = (int)$row['td'];
  }
  if ( $_SESSION[QT.'_usr_posts_today']<$intMax ) return TRUE;
  return FALSE;
}

// --------
// INITIALISE
// --------

$a = -1;
$s = -1;
$t = -1;
$p = -1;
QThttpvar('a s t p','str int int int');

if ( !in_array($a,array('nt','re','ed','qu')) ) die('Missing parameters a');
$oTopic = new cTopic(($t>=0 ? $t : null));
if ( $s<0 ) $s = $oTopic->parentid; // can be -1 (new topic)
if ( $s<0 ) die('Missing parameters: section');

$oSEC = new cSection($s);
$oPost = new cPost(($p>=0 ? $p : null));

// check maximum post per day (not for staff)
if ( !sUser::IsStaff() ) {
if ( !PostsTodayAcceptable(intval($_SESSION[QT]['posts_per_day'])) ) {
  $error=$L['E_too_much'];
  // exit
  $oVIP->selfname = $L['Post_new_topic'];
  $oVIP->exiturl = 'qti_items.php?s='.$s;
  $oHtml->PageMsg( NULL, '<p>'.$error.'<p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>' );
}}

// initialise others

$now = date('Ymd His');
$bUpload = false;
$strBehalf = '';
$intNotified = -1;
$strNotified = '';

$oVIP->selfurl = 'qti_form_edit.php';
$oVIP->selfname = $L['Message'];
if ( $a=='nt' ) $oVIP->selfname = $L['New_item'];
if ( $a=='re' ) $oVIP->selfname = $L['Post_reply'];
if ( $a=='qu' ) $oVIP->selfname = $L['Post_reply'];
if ( $a=='ed' ) $oVIP->selfname = $L['Edit'];
$oVIP->exiturl = 'qti_item.php?t='.$t;
$oVIP->exitname = $L['Items'];

if ( !isset($_SESSION[QT.'_usr_posts']) ) $_SESSION[QT.'_usr_posts']=0;

// MAP

$bMap=false;
if ( UseModule('map') )
{
  include Translate('qtim_map.php');
  include 'qtim_map_lib.php';
  if ( QTgcanmap($s) ) $bMap=true;
  if ( $bMap )
  {
  $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qtim_map.css" />';
  if ( !isset($_SESSION[QT]['m_map_symbols']) ) $_SESSION[QT]['m_map_symbols']='0';
  }
}

// --------
// SUBMITTED
// --------

// ::::::::
if ( isset($_POST['dosend']) || isset($_POST['dopreview']) ) {
// ::::::::

if ( $a==='re' ) $oPost->type='R';
$error = $oPost->SetFromPost($a!='ed'); // FALSE means author must not be changed because editing an existing message
if ( $oTopic->type=='I' && $_POST['title']==='pc' && isset($_POST['titlevalue']) ) $oPost->title=trim($_POST['titlevalue']);
if ( $oTopic->type=='I' && $_POST['title']==='null' ) $oPost->title='null';
if ( $oTopic->type!='I' && $oPost->text=='') $error = $L['Message'].' '.L('invalid');

if ( isset($_POST['notifiedname']) )
{
  $strNotified = trim($_POST['notifiedname']); if ( get_magic_quotes_gpc() ) $strNotified = stripslashes($strNotified);
  // Complete if missing notified name
  if ( !empty($strNotified) )
  {
    $strNotified = htmlspecialchars($strNotified,ENT_QUOTES);
    $intNotified = current(array_keys(GetUsers('name',$strNotified) )); // can be FALSE when not found
    if ( !is_int($intNotified) ) { $intNotified=-1; $error=$L['Notify_also'].' '.L('invalid'); }
  }
}

if ( isset($_POST['wisheddate']) )
{
  if ( !empty($_POST['wisheddate']) )
  {
  $str = QTdatestr(trim($_POST['wisheddate']),'Ymd','');
  if ( !is_string($str) ) $error = $L['Wisheddate'].' '.Error(1);
  if ( substr($str,0,6)=='Cannot' ) $error = $L['Wisheddate'].' '.Error(1);
  if ( substr($str,0,4)=='1970' ) $error = $L['Wisheddate'].' '.Error(1);
  if ( empty($error) ) $oTopic->wisheddate = $str;
  }
}

if ( isset($_POST['coord']) )
{
  if ( get_magic_quotes_gpc() ) $_POST['coord'] = stripslashes($_POST['coord']);
  if ( !empty($_POST['coord']) )
  {
  $_POST['coord'] = QTstr2yx($_POST['coord']);
  if ($_POST['coord']===FALSE ) $error='Invalid coordinate format';
  }
}

// Mandatory submitted fields (in case of new topic)

if ( $a==='nt' )
{
if ( $oSEC->notifycc=='2' && $intNotified<0 ) $error = $L['Notify_also'].': '.L('Missing');
if ( $oSEC->wisheddate=='2' && empty($_POST['wisheddate']) ) $error = $L['Wisheddate'].': '.L('Missing');
if ( $oPost->title==='' && $oSEC->titlefield=='2' ) $error = $L['E_no_title'];
}
if ( $a==='ed' )
{
if ( $oPost->title==='' && $oSEC->titlefield=='2' ) $error = $L['E_no_title'];
}

// Mandatory submitted fields (new topic or reply)

if ( strlen($oPost->text)>$_SESSION[QT]['chars_per_post'] ) $error = $L['E_too_long'].' '.sprintf($L['E_char_max'], $_SESSION[QT]['chars_per_post']);
if ( substr_count($oPost->text,"\n")>$_SESSION[QT]['lines_per_post'] ) $error = $L['E_too_long'].' '.sprintf($L['E_line_max'], $_SESSION[QT]['lines_per_post']);
if ( strlen($oPost->text)>999 ) $oPost->text = substr( $oPost->text, 0, $_SESSION[QT]['chars_per_post'] );

$oTopic->preview = QTcompact(QTunbbc($oPost->text),250,' ');

// Check submitted rules (when sending the message)

if ( empty($error) && isset($_POST['dosend']) )
{
	// check maximum post per user/minutes
	if ( !DelayAcceptable() ) $error=L('E_wait');

	// check message
	if ( empty($error) )
	{
		// ----------
		// module antispam
		if ( UseModule('antispam') ) include 'qtim_antispam.php';
		// ----------
	}

	// Check upload
	if ( empty($error) ) {
	if ( $_SESSION[QT]['upload']!='0' ) {
	if ( !empty($_FILES['attach']['name']) ) {

		include 'bin/qti_upload.php';
		$error = InvalidUpload($_FILES['attach'],$arrFileextensions,$arrMimetypes,intval($_SESSION[QT]['upload_size'])*1024+16);
		if ( empty($error) )
		{
			$strFile = strtolower($_FILES['attach']['name']);
			$strExt = strrchr($strFile,'.');
			if ( $strExt ) $strFile = substr($strFile,0,-strlen($strExt));
			$strFile = AsFilename($strFile,200);
			$strFile .= $strExt;
			$bUpload=true;
		}

	}}}
}

// ::::::::
}
// ::::::::

// ::::::::
if ( isset($_POST['dosend']) && empty($error) ) {
// ::::::::

// Replace empty title of the first post
if ( $oPost->type==='P' ) $oPost->title = cPost::FillEmptyTitle($oPost->title,$oPost->text);

// SEND a new topic

if ( $a==='nt' )
{
  $oDB->BeginTransac();

		$oTopic->id = $oDB->Nextid(TABTOPIC);
		$oTopic->numid = $oDB->Nextid(TABTOPIC,'numid','WHERE forum='.$s);
		$oPost->id = $oDB->Nextid(TABPOST);
		$oPost->topic = $oTopic->id;
		$oPost->section = $s;
		$oTopic->parentid = $s;
			// if moderator post
			if ( isset($_POST['topictype']) ) $oTopic->type = $_POST['topictype'];
			if ( isset($_POST['topicstatus']) ) $oTopic->status = $_POST['topicstatus'];
		$oTopic->firstpostid = $oPost->id;
		$oTopic->lastpostid = $oPost->id;
		$oTopic->firstpostuser = $oPost->userid;
		$oTopic->firstpostname = $oPost->username;
		$oTopic->lastpostuser = $oPost->userid;
		$oTopic->lastpostname = $oPost->username;
		$oTopic->firstpostdate = $now;
		$oTopic->lastpostdate = $now;
			if ( $intNotified>=0 )
			{
				$oTopic->notifiedid = $intNotified;
				$oTopic->notifiedname = $strNotified;
			}
			// replace empty title
		$oPost->type = 'P';
		$oPost->issuedate = $now;
		$oTopic->title = $oPost->title;

		if ( $bUpload )
		{
			$strDir = TargetDir(QTI_DIR_DOC,$oPost->id);
			if ( !empty($_POST['oldattach']) ) if ( file_exists(QTI_DIR_DOC.$strDir.$oPost->id.'_'.$_POST['oldattach']) ) unlink(QTI_DIR_DOC.$strDir.$oPost->id.'_'.$_POST['oldattach']);
			copy($_FILES['attach']['tmp_name'],QTI_DIR_DOC.$strDir.$oPost->id.'_'.$strFile);
			unlink($_FILES['attach']['tmp_name']);
			$oPost->attach = $strDir.$oPost->id.'_'.$strFile;
		}
		$oPost->InsertPost(); // No topic stat (topic not yet created), No user stat (computed when inserting topic)
		$oTopic->InsertTopic(true,true,$oPost,$oSEC);

  $oDB->CommitTransac();

  $oSEC->UpdateStats(array('tags'=>$oSEC->tags));
  ++$_SESSION[QT.'_usr_posts'];

  // location insert
  if ( $bMap ) {
  if ( isset($_POST['coord']) ) {
  if ( !empty($_POST['coord']) ) {
    cTopic::SetCoord($oTopic->id,$_POST['coord']);
  }}}

  // ----------
  // module rss, except for hidden section (type=0)
  if ( $oSEC->type!=0 && UseModule('rss') ) { if ( $_SESSION[QT]['m_rss']==='1' ) include 'qtim_rss_inc.php'; }
  // ----------
}

// SEND a reply

if ( $a==='re' || $a==='qu' )
{
  $oDB->BeginTransac();

		$oPost->id = $oDB->Nextid(TABPOST);
		$oPost->topic = $t;
		$oPost->section = $s;
		$oPost->type = 'R';
		$oPost->issuedate = $now;
		if ( $bUpload )
		{
		$strDir = TargetDir(QTI_DIR_DOC,$oPost->id);
		if ( !empty($_POST['oldattach']) ) if ( file_exists(QTI_DIR_DOC.$strDir.$oPost->id.'_'.$_POST['oldattach']) ) unlink(QTI_DIR_DOC.$strDir.$oPost->id.'_'.$_POST['oldattach']);
		copy($_FILES['attach']['tmp_name'],QTI_DIR_DOC.$strDir.$oPost->id.'_'.$strFile);
		unlink($_FILES['attach']['tmp_name']);
		$oPost->attach = $strDir.$oPost->id.'_'.$strFile;
		}
		$oPost->InsertPost(false,true); // No update topic stat (done after), Update the user's stat

  $oDB->CommitTransac();

  $oTopic->UpdateStats(intval($_SESSION[QT]['posts_per_item'])); // Update topic stats and close topic if full (and lastpost topic info)

  ++$_SESSION[QT.'_usr_posts'];

  // topic type (from staff)
  if ( isset($_POST['topictype']) )
  {
    if ( $_POST['topictype']!=$_POST['oldtype'] )
    {
    $oTopic->type = $_POST['topictype'];
    cTopic::SetType($oTopic->parentid,$oTopic->id,$oTopic->type);
    }
  }

  // topic status (from staff)
  if ( isset($_POST['topicstatus']) ) {
  if ( $_POST['topicstatus']!=$_POST['oldstatus'] ) {
    $oTopic->SetStatus($_POST['topicstatus'],true,$oPost);
  }}

  // topic status (from user)
  if ( isset($_POST['topicstatususer']) ) {
  if ( $_POST['topicstatususer'][0]=='Z' ) {
    $oTopic->SetStatus('Z');
  }}
  ++$oSEC->replies;
  $oSEC->UpdateStats(array('topics'=>$oSEC->items,'tags'=>$oSEC->tags));
}

// SEND a edition

if ( $a=='ed' )
{
  // location update (or delete)

  if ( $bMap && isset($_POST['coord']) )
  {
    if ( empty($_POST['coord']) ) { cTopic::SetCoord($t,null); } else { cTopic::SetCoord($t,$_POST['coord']); } //z is not used
  }

  $strModif='';
  // modifdate+modifuser if editor is not the creator
  if ( $oPost->modifuser!=$oPost->userid ) $strModif=', modifdate="'.date('Ymd His').'", modifuser='.$oPost->modifuser.', modifname="'.$oPost->modifname.'"';
  // modifdate+modifuser if not the last message
  if ( $oTopic->lastpostid!=$oPost->id ) $strModif=', modifdate="'.date('Ymd His').'", modifuser='.$oPost->modifuser.', modifname="'.$oPost->modifname.'"';

  if ( $bUpload )
  {
    $strDir = TargetDir(QTI_DIR_DOC,$oPost->id);
    if ( !empty($_POST['oldattach']) ) if ( file_exists(QTI_DIR_DOC.$strDir.$oPost->id.'_'.$_POST['oldattach']) ) unlink(QTI_DIR_DOC.$strDir.$oPost->id.'_'.$_POST['oldattach']);
    copy($_FILES['attach']['tmp_name'],QTI_DIR_DOC.$strDir.$oPost->id.'_'.$strFile);
    unlink($_FILES['attach']['tmp_name']);
    $oPost->attach = $strDir.$oPost->id.'_'.$strFile;
  }

  // if drop attachement
  if ( isset($_POST['drop']) ) { if ( $_POST['drop'][0]=='1' ) $oPost->Dropattach(); }

  if ( $oDB->type==='db2' )
  {
  $oDB->Exec('UPDATE '.TABPOST.' SET title="'.addslashes(QTconv($oPost->title,'3',QTI_CONVERT_AMP,false)).'", icon="'.$oPost->icon.'",textmsg="'.addslashes(QTconv($oPost->text,'3',QTI_CONVERT_AMP,false)).'",",textmsg2="'.substr(addslashes(QTconv($oPost->text,'3',QTI_CONVERT_AMP,false)),0,255).'",attach="'.$oPost->attach.'" '.$strModif.' WHERE id='.$oPost->id );
  }
  else
  {
  $oDB->Exec('UPDATE '.TABPOST.' SET title="'.addslashes(QTconv($oPost->title,'3',QTI_CONVERT_AMP,false)).'", icon="'.$oPost->icon.'",textmsg="'.addslashes(QTconv($oPost->text,'3',QTI_CONVERT_AMP,false)).'",attach="'.$oPost->attach.'" '.$strModif.' WHERE id='.$oPost->id );
  }

  if ( isset($_POST['wisheddate']) ) $oDB->Exec('UPDATE '.TABTOPIC.' SET wisheddate="'.$oTopic->wisheddate.'",modifdate="'.date('Ymd His').'" WHERE id='.$t);

  // topic type (from staff)
  if ( isset($_POST['topictype']) )
  {
    if ( $_POST['topictype']!=$_POST['oldtype'] )
    {
    $oTopic->type = $_POST['topictype'];
    cTopic::SetType($oTopic->parentid,$oTopic->id,$oTopic->type,$oTopic->status);
    }
  }
  // topic status (from staff)
  if ( isset($_POST['topicstatus']) ) {
  if ( $_POST['topicstatus']!=$_POST['oldstatus'] ) {
    $oTopic->SetStatus($_POST['topicstatus']);
    if ( $_POST['topicstatus']=='Z' || $_POST['oldstatus']=='Z' ) $oSEC->UpdateStats(array('topics'=>$oSEC->items,'replies'=>$oSEC->replies,'tags'=>$oSEC->tags));
  }}
  // topic status (from user)
  if ( isset($_POST['topicstatususer']) ) {
  if ( $_POST['topicstatususer'][0]=='Z' ) {
    $oTopic->SetStatus('Z');
    $oSEC->UpdateStats(array('topics'=>$oSEC->items,'replies'=>$oSEC->replies,'tags'=>$oSEC->tags));
  }}
}

// Update inspection score
if ( $oTopic->type==='I' ) $oTopic->InspectionUpdateScore();

// EXIT

if ( $a=='nt' && $oTopic->type=='I' )
{
$oHtml->Redirect('qti_change.php?a=topicparam&amp;s='.$s.'&amp;t='.$oPost->topic);
}
else
{
$str = L('S_message_saved'); if ( $oSEC->numfield!='N' ) $str = '['.sprintf($oSEC->numfield,$oTopic->numid).'] '.$str;
$_SESSION['pagedialog'] = (empty($error) ? 'O|'.$str : 'E|'.$error);
$oHtml->redirect('qti_item.php?t='.$oPost->topic.'#'.$oPost->id);
}

// ::::::::
}
// ::::::::

// Use jquery (if staff of if using field date)
if ( sUser::IsStaff() )
{
$oHtml->scripts_jq[] = '
$(function() {
  $( "#behalf" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "bin/qti_j_name.php",
        dataType: "json",
        data: { term: request.term, e0: e0 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#behalf" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#behalf" ).val( ui.item.rItem );
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
}

if ( $oPost->type=='P' && $oSEC->wisheddate!=0 )
{
$oHtml->scripts['modernizr']='<script type="text/javascript" src="bin/js/modernizr.js"></script>';
$oHtml->scripts_jq[] = '
if ( !Modernizr.inputtypes.date ) $("#wisheddate").datepicker({dateFormat: "yy-mm-dd"});
';
}

// --------
// HTML START
// --------

if ( $a=='nt' )
{
  $oPost->icon = '00';
  $oPost->type = 'P';
  $oVIP->exiturl = 'qti_items.php?s='.$s;
}
if ( $a=='qu' )
{
  if ( $t<0 ) die('Missing parameters: topicid');
  if ( $p<0 ) die('Missing parameters: postid');
  $oPost->icon = '00';
  $oPost->title = '';
  $oPost->text = "[quote=$oPost->username]$oPost->text[/quote]";
  // rest must be as reply
  $a = 're';
}
if ( $a=='re' )
{
  if ( $t<0 ) die('Missing parameters: topicid');
  $oPost->icon = '00';
  $oPost->type = 'R';
}
if ( $a=='ed' )
{
  if ( $t<0 ) die('Missing parameters: topicid');
  if ( $p<0 ) die('Missing parameters: postid');
}

if ( QTI_BBC )
{
$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qti_bbc.js"></script>';
}

$oHtml->scripts[] = '<script type="text/javascript">
function HidePreviewContainer()
{
  if ( doc.getElementById("previewcontainer") ) doc.getElementById("previewcontainer").style.display="none";
  if ( doc.getElementById("fadecontainer") ) doc.getElementById("fadecontainer").style.display="none";
}
function ValidateForm(theButton)
{
  var theForm = theButton.form;
  if ( theButton.name=="dosend" )
  {
    theForm.action="'.Href().'";
    theForm.target="";
  }
  else
  {
    if ( theForm.attach )
    {
      if ( theForm.attach.value.length>1 )
      {
      theForm.action="'.Href('qti_form_preview.php').'";
      theForm.target="_blank";
      }
    }
  }
  return true;
}
function Rolename(key)
{
  switch(key)
  {
  case "U": return "'.L('Role_U').'";
  case "M": return "'.L('Role_M').'";
  case "A": return "'.L('Role_A').'";
  default: return "unknown role";
  }
}
var e0 = "'.L('No_result').'";
function onchangetype(type)
{
  if (doc.getElementById("topicstatus") )
  {
    var options = doc.getElementById("topicstatus").options;
    for(i=0;i<options.length;i++)
    {
    options[i].disabled = (type!="T" && options[i].value!="A" && options[i].value!="Z");
    if ( options[i].disabled && options[i].selected) options[0].selected=true;
    }
  }
}
</script>';

if ( CanPerform('upload',sUser::Role()) ) { $intBbc=3; } else { $intBbc=2; }

$bJauto=false;
  if ( sUser::IsStaff() ) $bJauto=true;
  if ( $oSEC->notify==1 && $oPost->type=='P' && $oSEC->notifycc!=0 ) $bJauto=true;
$bJdate=false;
  if ( $oSEC->wisheddate!=0 ) $bJdate=true;

// --------
// CONTENT
// --------

if ( QTI_BBC )
{
$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qti_bbc.js"></script>';
$intBbc=(CanPerform('upload',sUser::Role()) ? 3 : 2);
}

if ( $bMap )
{
  if ( !empty($oTopic->y) && !empty($oTopic->x) )
  {
    $strPname = substr($oPost->title,0,25);
    $strPinfo = '<p class="small">Lat: '.QTdd2dms($oTopic->y).' <br />Lon: '.QTdd2dms($oTopic->x).'<br /><br />DD: '.round($oTopic->y,8).', '.round($oTopic->x,8).'</p>';
    $oMapPoint = new cMapPoint($oTopic->y,$oTopic->x,$strPname,$strPinfo);

    // add extra $oMapPoint properties (if defined in section settings)
    $oSettings = getMapSectionSettings($s,true);
    if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
    $arrExtData[$oTopic->id] = $oMapPoint;
  }
  else
  {
    $oMapPoint = new cMapPoint(0,0);
  }
}

include 'qti_inc_hd.php';

// PREVIEW

if ( isset($_POST['dopreview']) && empty($error) )
{
  echo '<div id="previewcontainer" class="previewcontainer">',PHP_EOL;
  echo '<p style="float:right;margin:5px 0;text-align:right"><a href="javascript:void(0)" onclick="HidePreviewContainer();"><i class="close fa fa-times fa-lg" title="',L('Hide'),'"></i></a></p>';
  echo '<h2>',$L['Preview'],'</h2><br/>',PHP_EOL;

  // get user info
  $oDB->Query('SELECT signature,photo,location,role FROM '.TABUSER.' WHERE id='.$oPost->userid);
  $row = $oDB->Getrow();
  $oPost->userloca = $row['location'];
  $oPost->useravat = $row['photo'];
  $oPost->usersign = $row['signature'];
  $oPost->userrole = $row['role'];
  $oPost->issuedate = $now;
  $oPost->Show($oSEC,$oTopic,true,'',$_SESSION[QT]['skin_dir'],'1'); // use normal view

  echo '</div><div id="fadecontainer" onclick="HidePreviewContainer();"></div>',PHP_EOL;
}

// TOPIC (if inspection)

if ( $oTopic->type==='I' ) {
if ( $a=='re' || $a=='qu' ) {

  echo '<h2>',L('Inspection'),'</h2>',PHP_EOL;
  // ======
  $strState = 'p.*, u.role, u.location, u.photo, u.signature FROM '.TABPOST.' p, '.TABUSER.' u WHERE p.userid = u.id AND p.topic='.$oTopic->id.' ';
  $oDB->Query( LimitSQL($strState,'p.id ASC',0,1) );
  // ======
  $strAlt = 'r1';
  // ======
  $row=$oDB->Getrow();
  $oInspectionPost = new cPost($row);
  $strButton='';
  if ( !empty($oInspectionPost->modifuser) ) $strButton .= '<td class="post-modif"><span class="small">&nbsp;'.$L['Modified_by'].' <a href="'.Href('qti_user.php').'?id='.$oInspectionPost->modifuser.'" class="small">'.$oInspectionPost->modifname.'</a> ('.QTdatestr($oInspectionPost->modifdate,'$','$',true,true).')</span></td>'.PHP_EOL;
  if ( !empty($strButton) ) $strButton .= '<td>'.' '.'</td>'.PHP_EOL;
  if ( !empty($strButton) ) $strButton = '<table style="margin:10px 0 1px 0;"><tr>'.$strButton.'</tr></table>'.PHP_EOL;
  $oInspectionPost->text = QTcompact($oInspectionPost->text,0); // Pre processing data (compact, no button)
  $oInspectionPost->Show($oSEC,$oTopic,false,$strButton,$_SESSION[QT]['skin_dir'],$strAlt,false);
  if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
  // ======

}}

// FORM START

echo '<h2>',$oVIP->selfname,'</h2>',PHP_EOL;
if ( !empty($error) ) echo '<p><span class="error">',$error,'</span></p>';

echo '
<form id="form_edit" method="post" action="',Href(),'" enctype="multipart/form-data">
<input type="hidden" name="s" value="',$s,'" />
<input type="hidden" name="t" value="',$t,'" />
<input type="hidden" name="a" value="',$a,'" />
<input type="hidden" name="p" value="',$p,'" />
<input type="hidden" name="oldtype" value="',$oTopic->type,'" />
<input type="hidden" name="oldstatus" value="',$oTopic->status,'" />
';
if ( sUser::IsStaff() )
{
  echo '<div id="data-hd-r"><div id="optionsbar">',PHP_EOL;/*!!!!*/
  if ( $oPost->type=='P')
  {
  echo $L['Type'],' <select name="topictype" size="1" onchange="onchangetype(this.value);">',PHP_EOL;
  echo QTasTag(cTopic::Types(),$oTopic->type);
  echo '</select>&nbsp;',PHP_EOL;
  }
  echo $L['Status'],' <select name="topicstatus" size="1" id="topicstatus">',PHP_EOL;
  $arrS = sMem::Get('sys_statuses');
  switch($oTopic->type)
  {
  case 'T': $arr = $arrS; break; // use all statuses in $arr
  case 'I': $arr = array('A'=>L('I_running'),'Z'=>L('I_closed')); break;
  default:
    $strA = empty($arrS['A']['statusname']) ? L('Opened') : $arrS['A']['statusname'];
    $strZ = empty($arrS['Z']['statusname']) ? L('Closed') : $arrS['Z']['statusname'];
    $arr = array('A'=>$strA,'Z'=>$strZ);
    break;
  }
  echo QTasTag($arr,$oTopic->status);
  echo '</select>&nbsp;',PHP_EOL;
  echo $L['Send_on_behalf'],'&nbsp;<input type="text" name="behalf" id="behalf" size="14" maxlength="24" value="'.(empty($strBehalf) ? '' : $strBehalf).'" />',PHP_EOL;
  echo '</div></div>',PHP_EOL;
}
// End of rule for status and types

echo '<table class="t-data horiz">',PHP_EOL;

// PREFIX ICON

if ( !empty($oSEC->prefix) )
{
echo '<tr>',PHP_EOL;
echo '<th>',$L['Smiley'],'</th>',PHP_EOL;
echo '<td>',PHP_EOL;
for ($i=0;$i<10;++$i)
{
  if ( file_exists($_SESSION[QT]['skin_dir'].'/ico_prefix_'.$oSEC->prefix.'_0'.$i.'.gif') )
  {
  echo '<input type="radio" name="icon" id="0',$i,'" value="0',$i,'"',($oPost->icon=='0$i' ? QCHE : ''),' tabindex="',$i,'" /><label for="0',$i,'"><img src="',$_SESSION[QT]['skin_dir'],'/ico_prefix_',$oSEC->prefix,'_0',$i,'.gif" label="smile" title="',$L['Ico_prefix'][$oSEC->prefix.'_0'.$i],'" /></label>&nbsp; ',PHP_EOL;
  }
}
echo '<input type="radio" name="icon" id="00" value="00"',($oPost->icon=='00' ? QCHE : ''),' tabindex="10" /><label for="00">',$L['None'],'</label></td>',PHP_EOL;
echo '</tr>',PHP_EOL;
}

// TITLE

if ( $oTopic->type==='I' && $oPost->type!='P' )
{
  echo '<tr>',PHP_EOL;
  echo '<th>',L('Score'),'</th>',PHP_EOL;
  $strLevel = $oTopic->ReadOptions('Ilevel');
  $strSep = ' &nbsp; '; if ( $strLevel==='5' || $strLevel==='3' ) $strSep='<br/>';
  echo '<td>',HtmlScore($strLevel,$strSep,$oPost->title),'</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;
}
else
{
  if ( $oSEC->titlefield!=0 )
  {
  $str = ''; if ( $oSEC->titlefield==2 && $oPost->type==='P' ) $str=' required'; // required for topic in section having title required (but not for reply)
  echo '<tr>',PHP_EOL;
  echo '<th>',$L['Title'],'</th>',PHP_EOL;
  echo '<td><input'.$str.' type="text" id="title" name="title" size="80" maxlength="64" value="',QTstrh($oPost->title),'" tabindex="20" /></td>',PHP_EOL;
  echo '</tr>',PHP_EOL;
  }
}

// MESSAGE

echo '<tr>',PHP_EOL;
echo '<th>',$L['Message'],'</th>',PHP_EOL;
echo '<td>',PHP_EOL;

if ( QTI_BBC ) include 'qti_form_button.php';

echo PHP_EOL,'<a href="textarea"></a><textarea'.($oTopic->type!=='I' ? ' required': '').' id="text" name="text" ',(strlen($oPost->text)>500 ? 'rows="25"' : 'rows="10"' ),' tabindex="25" maxlength="'.(empty($_SESSION[QT]['chars_per_post']) ? '4000' : $_SESSION[QT]['chars_per_post']).'">',QTconv($oPost->text,'3',QTI_CONVERT_AMP,false),'</textarea>',PHP_EOL;
if ( CanPerform('upload',sUser::Role()) )
{
echo '<p class="attachment"><a href="javascript:void(0)" id="attachlink" onclick="document.getElementById(\'attachtr\').style.display=\'table-row\'; document.getElementById(\'attachlink\').style.display=\'none\';">',$L['Attachment'],'</a></p>';
}

echo '</td></tr>',PHP_EOL;

// ATTACHMENT

if ( CanPerform('upload',sUser::Role()) )
{
  $intMax = intval($_SESSION[QT]['upload_size'])*1024;
  echo '<tr id="attachtr">',PHP_EOL;
  echo '<th>',$L['Attachment'],'</th>',PHP_EOL;
  echo '<td>';
  if ( !empty($oPost->attach) )
  {
    if ( strstr($oPost->attach,'/') ) { $str = substr(strrchr($oPost->attach,'/'),1); } else { $str=$oPost->attach; }
    if ( substr($str,0,strlen($oPost->id.'_'))==($oPost->id).'_' ) $str = substr($str,strlen($oPost->id.'_'));
    echo AsImg($_SESSION[QT]['skin_dir'].'/ico_attachment.gif','-',$L['Attachment']),'&nbsp;',$str,'<input type="hidden" id="oldattach" name="oldattach" value="',$oPost->attach,'" />';
    echo ' &middot; <input type="checkbox" id="drop" name="drop[]" value="1" tabindex="26" /><label for="drop">&nbsp;',$L['Drop_attachment'],'</label>';
  }
  else
  {
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="',$intMax,'" />';
    echo '<input type="file" id="attach" name="attach" size="42" tabindex="3" />';
  }
  echo ' <a href="javascript:void(0)" id="attachhide" onclick="document.getElementById(\'attachtr\').style.display=\'none\'; document.getElementById(\'attachlink\').style.display=\'inline\';"><i class="close fa fa-times fa-lg"></i></a></td>',PHP_EOL,'</tr>',PHP_EOL;
}

// WISHEDDATE

if ( $oSEC->wisheddate!=0 ) {
if ( $oPost->type=='P' ) {

  $strValue = '';
  if ( $oSEC->wisheddflt>0 ) $strValue = ( $oSEC->wisheddflt==1 ? date('Y-m-d') : date('Y-m-d',strtotime('+'.($oSEC->wisheddflt-1).' day')) );
  if ( isset($_POST['wisheddate']) ) $strValue = $_POST['wisheddate'];
  if ( !empty($oTopic->wisheddate) ) $strValue = substr($oTopic->wisheddate,0,4).'-'.substr($oTopic->wisheddate,4,2).'-'.substr($oTopic->wisheddate,-2,2);

  echo '<tr>',PHP_EOL;
  echo '<th>',$L['Wisheddate'],'</th>',PHP_EOL;
  echo '<td><input type="date" id="wisheddate" name="wisheddate" size="20" maxlength="10" value="',$strValue,'" tabindex="30" min="',date('Y-m-d'),'" placeholder="',$L['yyyy-mm-dd'],'"/> ',PHP_EOL;
  echo '<i class="fa fa-calendar fa-lg" title="',$L['dateSQL']['Today'],'" onclick="document.getElementById(\'wisheddate\').value=\'',date('Y-m-d'),'\';"></i>',PHP_EOL;
  echo '&nbsp;<span class="small">',L('H_Wisheddate'),'</span></td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

}}

// NOTIFIED

if ( $oSEC->notify==1 ) {
if ( $oPost->type=='P' ) {
if ( $oSEC->notifycc!=0 ) {

  // default value
  $intValue = -1;
  $strValue = '';
  if ( $oSEC->notifycc==3 ) { $intValue = sUser::Id(); $strValue = sUser::Name(); }
  if ( $intNotified>=0 ) { $intValue = $intNotified; $strValue = $strNotified; }
  if ( $oTopic->notifiedid>=0 ) { $intValue = $oTopic->notifiedid; $strValue = $oTopic->notifiedname; }

  // extra java
  $oHtml->scripts_jq[] = '$(function() {
	  $( "#notifiedname" ).autocomplete({
	    minLength: 1,
	    source: function(request, response) {
	      $.ajax({
	        url: "bin/qti_j_name.php",
	        dataType: "json",
	        data: { term: request.term, e0: e0, e1: e1 },
	        success: function(data) { response(data); }
	      });
	    },
	    focus: function( event, ui ) {
	      $( "#notifiedname" ).val( ui.item.rItem );
	      return false;
	    },
	    select: function( event, ui ) {
	      $( "#notifiedname" ).val( ui.item.rItem );
	      return false;
	    }
	  })
	  .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
	    return $( "<li></li>" )
	      .data( "item.autocomplete", item )
	      .append( "<a class=\"jvalue\">" + item.rItem + "&nbsp;<span class=\"jinfo\">(" + (item.rItem=="" ? item.rInfo : Rolename(item.rInfo)) + ")</span></a>" )
	      .appendTo( ul );
	  };
	});';

  // row

  echo '<tr>',PHP_EOL;
  echo '<th>',$L['Notify_also'],'</th>',PHP_EOL;
  echo '<td><input type="hidden" id="notifiedid" name="notifiedid" value="',$intValue,'" tabindex="31" /><input type="text" id="notifiedname" name="notifiedname" size="20" maxlength="24" value="',$strValue,'" /></div></td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

}}}

// MAP coordinate field

if ( $oPost->type=='P' && $bMap )
{
  echo '<tr><th>',L('Coord'),'</th><td><input type="text" id="yx" name="coord" size="32" value="'.(!empty($oTopic->y) ? $oTopic->y.','.$oTopic->x : '').'" tabindex="32" /> <span class="small">',L('latlon'),'</span></td></tr>',PHP_EOL;
}

// SUBMIT

echo '<tr class="formsubmit">',PHP_EOL;
echo '<th>&nbsp;</th>',PHP_EOL;
echo '<td>',PHP_EOL;

if ( $oTopic->type!='I' && $oTopic->status!='Z' && $oTopic->firstpostuser==sUser::Id() )
{
  // topic status (from user)
  $bChecked = false;
  if ( isset($_POST['topicstatususer']) ) { if ( $_POST['topicstatususer'][0]=='Z' ) $bChecked=true; }
  echo '<input type="checkbox" id="topicstatususer" name="topicstatususer[]" value="Z"',($bChecked ? QCHE : ''),' tabindex="96" /><label for="topicstatususer">&nbsp;',L('Close_my_item'),' </label>';
}

echo '<input type="submit" id="dosend" name="dosend" value="',L('Send'),'" tabindex="97" onclick="return ValidateForm(this);" /> ',PHP_EOL;
echo '<input type="button" id="docancel" name="cancel" value="',L('Cancel'),'" tabindex="98" onclick="window.location=\''.$oVIP->exiturl.'\';" /> ',PHP_EOL;
echo '<input type="submit" id="dopreview" name="dopreview" value="',L('Preview'),'..." tabindex="99" onclick="return ValidateForm(this);" />';
echo '</td>',PHP_EOL;
echo '</tr>',PHP_EOL;
echo '</table>',PHP_EOL;

// map row

if ( $oPost->type=='P' && $bMap ) 
{  
  $oCanvas = new cCanvas();
  $str = L('map_cancreate');
  if ( isset($row) && !QTgemptycoord($row) )
  {
    $_SESSION[QT]['m_map_gcenter'] = $row['y'].','.$row['x'];
    $str = L('map_canmove');
  }
  $oCanvas->Header( array(), array($str,'add','del'), '', 'header right' );
  $oCanvas->Footer( 'find' ,'', 'footer right' );
  echo $oCanvas->Render(false,'','gmap edit'),PHP_EOL;
}

// FORM END

echo '</form>
';

// PREVIOUS POSTS (not for inspection)

if ( $oTopic->type!='I' ) {
if ( $a=='re' || $a=='qu' ) {

  echo '<div class="view-c">',PHP_EOL;
  echo '<h2>',$L['Previous_replies'],'</h2>',PHP_EOL;
  // ======
  $strState = 'p.*, u.role, u.location, u.photo, u.signature FROM '.TABPOST.' p, '.TABUSER.' u WHERE p.userid = u.id AND p.topic='.$oTopic->id.' ';
  $oDB->Query( LimitSQL($strState,'p.id DESC',0,5) );
  // ======
  $intPosts = 5;
  $iMsgNum = $oTopic->items + 2;
  $intWhile= 0;
  $strAlt = 'r1';
  $bButton = false;
  $bAvatar = false;
  // ======
  while($row=$oDB->Getrow())
  {
    $iMsgNum = $iMsgNum-1;
    $oPost = new cPost($row,$iMsgNum);
    $strButton='';
    if ( !empty($oPost->modifuser) ) $strButton .= '<td class="post-modif"><span class="small">&nbsp;'.$L['Modified_by'].' <a href="'.Href('qti_user.php').'?id='.$oPost->modifuser.'" class="small">'.$oPost->modifname.'</a> ('.QTdatestr($oPost->modifdate,'$','$',true,true).')</span></td>'.PHP_EOL;
    if ( !empty($strButton) ) $strButton .= '<td>'.' '.'</td>'.PHP_EOL;
    if ( !empty($strButton) ) $strButton = '<table style="margin:10px 0 1px 0;"><tr>'.$strButton.'</tr></table>'.PHP_EOL;
    $oPost->text = QTcompact($oPost->text,0); // Pre processing data (compact, no button)
    $oPost->Show($oSEC,$oTopic,false,$strButton,$_SESSION[QT]['skin_dir'],$strAlt);
    if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
    ++$intWhile;
  }
  // ======
  echo '</div>',PHP_EOL;

}}

// HTML END

$oHtml->scripts_end[] = '<script type="text/javascript">
if ( document.getElementById("attachlink") ) document.getElementById("attachlink").style.display="inline";
if ( document.getElementById("attachtr") ) document.getElementById("attachtr").style.display="none";
</script>
';


// MAP MODULE

if ( $bMap )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
	// add extra $oMapPoint properties (if defined in section settings)
	$oSettings = getMapSectionSettings($s);
	if ( is_object($oSettings) )
	{
	if ( property_exists($oSettings,'icon') ) $gmap_symbol = $oSettings->icon;
	if ( property_exists($oSettings,'shadow') ) $gmap_shadow = $oSettings->shadow;
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
      $gmap_markers[] = QTgmapMarker($oMapPoint->y.','.$oMapPoint->x, true, $strSymbol, $oMapPoint->title, $oMapPoint->info, $strShadow );
    }
  }

  $gmap_events[] = '
	google.maps.event.addListener(markers[0], "position_changed", function() {
		if (document.getElementById("yx")) {document.getElementById("yx").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(marker[0], "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  $gmap_functions[] = '
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("yx",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  function createMarker()
  {
    if ( !map ) return;
    if (infowindow) infowindow.close();
    deleteMarker();
    '.QTgmapMarker('map',true,$gmap_symbol).'
    gmapYXfield("yx",markers[0]);
    google.maps.event.addListener(markers[0], "position_changed", function() { gmapYXfield("yx",markers[0]); });
    google.maps.event.addListener(markers[0], "dragend", function() { map.panTo(markers[0].getPosition()); });
  }
  function deleteMarker()
  {
    if (infowindow) infowindow.close();
    for(var i=markers.length-1;i>=0;i--) markers[i].setMap(null);
    gmapYXfield("yx",null);
    markers=[];
  }
  ';

  include 'qtim_map_load.php';
}

include 'qti_inc_ft.php';
