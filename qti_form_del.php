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
* @copyright  20013 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
if ( !sUser::CanView('V6') ) die(Error(11));

// INITIALISE

$s = -1; // section
$t = -1; // topic
$p = -1; // post
$ok = '';// submitted
QThttpvar('s t p ok', 'int int int str',true,true,true);
if ( $s<0 ) die('Missing parameters s');
if ( $t<0 ) die('Missing parameters t');
if ( $p<0 ) die('Missing parameters p');

$oSEC = new cSection($s);
$oTopic = new cTopic($t);
$oPost = new cPost($p);

$bReason = true;
$strDisabled = '';

$oVIP->selfurl = 'qti_form_del.php';
$oVIP->selfname = $L['Message'];
$oVIP->exiturl = 'qti_item.php?t='.$t;
$oVIP->exitname = $L['Items'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{

  // Check user is creator (or Staff)

  if ( sUser::Role()==='V' ) die(Error(11));
  if ( sUser::Role()==='U' && $oPost->userid!=sUser::Id() ) die(Error(11));

  // Drop attachement

  cPost::baseDropattach($p,$oPost->attach);

  // Topic delete if only one post (no topic broacasting)

  if ( $oTopic->lastpostid==$p && $oTopic->firstpostid==$p )
  {
    cTopic::Drop($t); // delete post and topic
    $oSEC->UpdateStats(array('tags'=>$oSEC->tags));
    $oVIP->exiturl = 'qti_items.php?s='.$s;
  }
  else
  {
    if ( $oTopic->lastpostid==$p || $oTopic->type==='I' )
    {
      // physical delete if last post (or in case of inspection) and reply broacasting
      cPost::baseDelete($p);
      $oTopic->UpdateStats(0); // This update firstpost/lastpost (and do not perform close-topic check)
      sAppMem::Control( 'cTopic:DeletePost', $oTopic );
    }
    else
    {
      // logical delete if not last post
      $str = trim($_POST['text']);
      if ( $oDB->type==='db2' )
      {
      $oDB->Exec('UPDATE '.TABPOST.' SET type="D",title="'.$L['Message_deleted'].'", textmsg="'.QTstrd($str,255).'",textmsg2="'.QTstrd($str,255).'", modifdate="'.Date('Ymd His').'", modifuser='.sUser::Id().' WHERE id='.$p);
      }
      else
      {
      $oDB->Exec('UPDATE '.TABPOST.' SET type="D",title="'.$L['Message_deleted'].'", textmsg="'.QTstrd($str,255).'", modifdate="'.Date('Ymd His').'", modifuser='.sUser::Id().' WHERE id='.$p);
      }
    }
    $oVIP->exiturl = 'qti_item.php?t='.$t;
  }

  // exit

  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// HTML START
// --------

$oVIP->selfname = L('Delete');
if ( $oTopic->lastpostid==$p || $oTopic->type=='I' || $oPost->type=='P' ) $bReason=false;

$str = '<form method="post" action="'.Href().'">
<input type="hidden" name="s" value="'.$s.'" />
<input type="hidden" name="t" value="'.$t.'" />
<input type="hidden" name="p" value="'.$p.'" />
<table class="t-data horiz">
<tr><th>'.L('Author').'</th><td>'.$oPost->username.' ('.QTdatestr($oPost->issuedate,'$','$',true).')</td></tr>
<tr class="t-data">';
if ( $oTopic->type==='I' && $oPost->type!='P' )
{
$str .= '<th>'.L('Score').'</th><td>'.$oPost->GetScoreImage($oTopic).'</td>';
}
else
{
if ( $oSEC->titlefield!=0 ) $str .= '<th>'.L('Title').'</th><td>'.$oPost->title.'</td>';
}
$str .= '</tr>'.PHP_EOL;
$str .= '<tr><th>'.L('Message').'</th><td>'.cPost::GetPrefix($oSEC->prefix,$oPost->icon,$_SESSION[QT]['skin_dir']).QTbbc($oPost->text).'</td></tr>'.PHP_EOL;

if ( $_SESSION[QT]['upload']!='0' && !empty($oPost->attach) )
{
$str .= '<tr><th>'.L('Attachment').'</th><td><input'.QDIS.' type="text" size="75" value="'.$oPost->attach.'" /><input type="hidden" id="attach" name="attach" value="'.$oPost->attach.'" /></td></tr>'.PHP_EOL;
}
if ( $bReason )
{
$str .= '<tr><th>'.L('Reason').'</th><td><textarea id="text" name="text" rows="2" wrap="virtual" style="width:90%" maxlength="255"></textarea></td></tr>'.PHP_EOL;
}
$str .= '</table>'.PHP_EOL;
$str .= '<p class="submit">'.(empty($error) ? '' : '<span class="error">'.$error.'</span> ').'<input type="submit" name="ok" value="'.L('Delete').'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.Href($oVIP->exiturl).'\';"/></p>
<p class="exit"><a href="'.Href($oVIP->exiturl).'">'.L('Exit').'</a></p>
</form>
';

$oHtml->PageMsg
(
	NULL,
	$str,
	0,
	'600px'
);

/*
// CONTENT

include 'qti_inc_hd.php';

echo '<h2>'.$oVIP->selfname.'</h2><br />',PHP_EOL;
echo '<form method="post" action="',Href(),'">',PHP_EOL;
echo '<input type="hidden" name="s" value="',$s,'" />';
echo '<input type="hidden" name="t" value="',$t,'" />';
echo '<input type="hidden" name="p" value="',$p,'" />';
echo '<table class="t-data horiz">',PHP_EOL;
echo '<tr>';
echo '<th>',$L['Author'],'</th>';
echo '<td>',$oPost->username,' (',QTdatestr($oPost->issuedate,'$','$',true),')</td>';
echo '</tr>',PHP_EOL;

echo '<tr class="t-data">';

// TITLE

if ( $oTopic->type==='I' && $oPost->type!='P' )
{
  echo '<th>',L('Score'),'</th>';
  echo '<td>',$oPost->GetScoreImage($oTopic),'</td>';
}
else
{
  if ( $oSEC->titlefield!=0 )
  {
  echo '<th>',$L['Title'],'</th>';
  echo '<td>',$oPost->title,'</td>';
  }
}
echo '</tr>',PHP_EOL;
echo '<tr>';
echo '<th>'.$L['Message'].'</th>';
echo '<td>',cPost::GetPrefix($oSEC->prefix,$oPost->icon,$_SESSION[QT]['skin_dir']),QTbbc($oPost->text),'</td>';
echo '</tr>',PHP_EOL;
if ( $_SESSION[QT]['upload']!='0' ) {
if ( !empty($oPost->attach) ) {
echo '<tr>';
echo '<th>',$L['Attachment'],'</th>';
echo '<td><input'.QDIS.' type="text" size="75" value="',$oPost->attach,'" /><input type="hidden" id="attach" name="attach" value="'.$oPost->attach.'" /></td>';
echo '</tr>',PHP_EOL;
}}
if ( $bReason )
{
echo '<tr>';
echo '<th>',$L['Reason'],'</th>';
echo '<td>';
echo '<textarea id="text" name="text" rows="2" wrap="virtual" cols="80" maxlength="255"></textarea></td>';
echo '</tr>',PHP_EOL;
}
echo '</table>',PHP_EOL;
echo '<p class="submit"><input type="submit" name="ok" value="',L('Delete'),'" />&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.Href($oVIP->exiturl).'\';"/>';
if ( !empty($error) ) echo '<span class="error">',$error,'</span>';
echo '</p>
<p class="exit"><a href="',Href($oVIP->exiturl),'">',L('Exit'),'</a></p>
</form>
';

// HTML END

include 'qti_inc_ft.php';
*/