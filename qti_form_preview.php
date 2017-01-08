<?php

// 3.0 build:20160703

// This page is used in case of attachement in the forms to preview.
// Because the form input "file" cannot be filled in, the form must remains opened during the preview.

session_start();
require 'bin/init.php';
if ( !sUser::CanView('V6') ) { $oHtml->PageMsg(11); return; }
if ( !isset($_POST['a']) ) die('Missing parameters: action');
if ( !in_array($_POST['a'],array('nt','re','ed','qu','de')) ) die('Missing parameters A');

// INITIALISE

$s = -1; QThttpvar('s','int'); if ( $s<0 ) die('Missing parameters: section id');

$intBehalf=-1;
$strBehalf='';
$intNotified=-1;
$strNotified='';
$wisheddate='0';
$now = date('Y-m-d H:i:s');
$oVIP->selfname = $L['Post_reply'];

$oSEC = new cSection($s);
$oTopic = new cTopic();
$oPost = new cPost();
$oPost->userid = sUser::Id();
$oPost->username = sUser::Name();
$oPost->type = 'R';
$oPost->issuedate = $now;

// CHECK SUBMITTED

  if ( isset($_POST['post']) ) { $oPost->id = intval($_POST['post']); }
  if ( isset($_POST['icon']) ) { $oPost->icon = $_POST['icon']; }
  if ( isset($_POST['title']) ) { $oPost->title = trim($_POST['title']); if ( get_magic_quotes_gpc() ) $oPost->title = stripslashes($oPost->title); }
  if ( isset($_POST['text']) ) { $oPost->text = trim($_POST['text']); if ( get_magic_quotes_gpc() ) $oPost->text = stripslashes($oPost->text); }
  if ( isset($_POST['oldattach']) ) { $oPost->attach = $_POST['oldattach']; }
  if ( isset($_POST['behalf']) ) { $strBehalf = trim($_POST['behalf']); if ( get_magic_quotes_gpc() ) $strBehalf = stripslashes($strBehalf); }
    // complete if missing behalf name
    if ( $strBehalf!='' )
    {
      $arrNames = GetUsers('name',$strBehalf);
      if ( !empty($arrNames) ) { foreach($arrNames as $intKey=>$strValue ) { $intBehalf = $intKey; } }
      if ( $intBehalf<0 ) $error = $L['Send_on_behalf'].' '.L('invalid');
      $oPost->userid = $intBehalf;
      $oPost->username = $strBehalf;
    }
  if ( isset($_POST['notifiedname']) ) { $strNotified = trim($_POST['notifiedname']); if ( get_magic_quotes_gpc() ) $strNotified = stripslashes($strNotified); }
    // complete if missing behalf name
    if ( $strNotified!='' )
    {
    $arrNames = GetUsers('name',$strNotified);
    if ( !empty($arrNames) ) { foreach($arrNames as $intKey=>$strValue ) { $intNotified = $intKey; } }
    }
    if ( $intNotified<0 && $strNotified!='' ) $error = $L['Notify_also'].' '.L('invalid');

  if ( $oSEC->notifycc=='2' && $intNotified<0 && $_POST['a']=='nt' ) $error = $L['Notify_also'].': '.L('Missing');
  if ( $oSEC->wisheddate=='2' && empty($_POST['wisheddate']) && $_POST['a']=='nt' ) $error = $L['Wisheddate'].': '.L('Missing');
  $oPost->title = QTcompact(QTunbbc($oPost->title),50,' ');
  if ( $_POST['a']=='nt' && $oPost->title=='' && $oSEC->titlefield==2 ) $error = $L['E_no_title'];
  if ( $_POST['a']=='nt' && $oPost->title=='' && $oSEC->titlefield!=2 ) $oPost->title = QTunbbc(QTcompact($oPost->text,50,' '));
  if ( strlen($oPost->text)>$_SESSION[QT]['chars_per_post'] ) $error = $L['E_too_long'].S.sprintf($L['E_char_max'], $_SESSION[QT]['chars_per_post']);
  if ( substr_count($oPost->text,"\n")>$_SESSION[QT]['lines_per_post'] ) $error = $L['E_too_long'].S.sprintf($L['E_line_max'], $_SESSION[QT]['lines_per_post']);
  $oPost->text = substr( QTconv($oPost->text,'3',QTI_CONVERT_AMP), 0, $_SESSION[QT]['chars_per_post'] );
  if ($oPost->text=='') $error = $L['Message'].S.L('invalid');

// PREPARE DISPLAY

if ( $_POST['a']=='nt' ) { $oVIP->selfname = $L['Post_new_topic']; $oPost->type = 'P'; }
if ( $_POST['a']=='ed' ) $oVIP->selfname = $L['Edit'];

// get user info
$oDB->Query('SELECT signature,photo,location,role FROM '.TABUSER.' WHERE id='.$oPost->userid);
$row = $oDB->Getrow();
$oPost->userloca = $row['location'];
$oPost->useravat = $row['photo'];
$oPost->usersign = $row['signature'];
$oPost->userrole = $row['role'];

// --------
// HTML START
// --------

echo $oHtml->Head();
echo $oHtml->Body();

echo cHtml::Page(START);

echo '
<div class="msgboxpreview">

<h2>',$L['Preview'],'</h2>
';
if ( !empty($error) ) echo '<p><span class="error">',$error,'</span></p>';

$oPost->Show($oSEC,$oTopic,true,'',$_SESSION[QT]['skin_dir'],'1',false);

echo '
<script type="text/javascript">
<!--
document.write(\'<p><a href="#" onclick="window.close();">',$L['Close'],' [x]</a></p>\')
-->
</script>
</div>
';

echo cHtml::Page(END);

echo $oHtml->End();
