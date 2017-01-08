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
include Translate('qtim_export.php');
if ( sUser::Role()!='A' ) die(Error(13));
if ( !defined('QTI_XML_CHAR') ) define('QTI_XML_CHAR','utf-8');

function ToXml($str)
{
  $str = html_entity_decode($str,ENT_QUOTES);
  if ( strstr($str,'&') ) $str = Xencode($str,'-A -Q -L -R -&');
  $str = str_replace(chr(160),' ',$str); // required for xml
  $str = Xencode($str,'& L R'); // required for xml
  return $str;
}
function Xencode($str='',$symbols='Q A L R')
{
  // This will encode (or decode) special characters: quote, apostrophe, open, close, amp
  // $arrSymbols is the list of symbols to encode (noted Q A L R or &). Use - to decode
  // Note: $arrSymbols can be a string with space separated values
  // Note: If you want to convert &, you must make it first.

  if ( empty($str) ) return $str;
  if ( is_string($symbols) ) $symbols = explode(' ',$symbols);
  if ( empty($symbols) ) return $str;
  if ( !is_array($symbols) ) return $str;

  foreach($symbols as $symbol) {
  switch($symbol) {
  case '&': $str = str_replace('&','&amp;',$str); break;
  case 'A': $str = str_replace("'",'&apos;',$str); break;
  case 'Q': $str = str_replace('"','&quot;',$str); break;
  case 'L': $str = str_replace('<','&lt;',$str); break;
  case 'R': $str = str_replace('>','&gt;',$str); break;
  case '-A': $str = str_replace(array('&apos;','&#039;','&#39;'),"'",$str); break;
  case '-Q': $str = str_replace(array('&quot;','&#034;','&#34;'),'"',$str); break;
  case '-L': $str = str_replace(array('&lt;','&#060;','&#60;'),'<',$str); break;
  case '-R': $str = str_replace(array('&gt;','&#062;','&#62;'),'>',$str); break;
  case '-&': $str = str_replace(array('&amp;','&#038;','&#38;'),'&',$str); break;
  }}
  return $str;
}

// INITIALISE

$intTopics = 0;
$arrYears = array(
  strval(date('Y'))=>strval(date('Y')),
  strval(date('Y')-1)=>strval(date('Y')-1),
  'old'=>'&lt; '.strval(date('Y')-1)
  );

if ( !isset($_SESSION['m_export_xml']) )
{
  $_SESSION['m_export_xml'] = array(
  'title'   => 'export_'.date('Ymd').'.xml',
  'dropbbc' => 'Y');
}

$oVIP->selfurl = 'qtim_export_adm.php';
$oVIP->selfname = $L['Export_Admin'];
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;
$strPageversion = $L['Export_Version'].' 3.0';

// --------
// SUBMITTED
// --------

if ( isset($_POST['submit']) )
{
  // read and check mandatory
  if ( isset($_POST['dropbbc']) ) { $_SESSION['m_export_xml']['dropbbc']='Y'; } else { $_SESSION['m_export_xml']['dropbbc']='N'; }
  if ( empty($_POST['title']) ) $error='Filename '.Error(1);
  if ( substr($_POST['title'],-4,4)!='.xml' ) $_POST['title'] .= '.xml';
  if ( $_POST['section']=='-' ) $error='No data found';
  if ( $_POST['year']=='-' ) $error='No data found';

  // EXPORT COUNT
  if ( empty($error) )
  {
    $strWhere = '';
    if ( $_POST['section']!='*' ) { $strWhere .= 'forum='.$_POST['section']; } else { $strWhere .= 'forum>=0'; }
    if ( $_POST['year']!='*' ) $strWhere .= ' AND '.SqlDateCondition($_POST['year'],'firstpostdate');
    $oDB->Query('SELECT count(*) as countid FROM '.TABTOPIC.' WHERE '.$strWhere );
    $row=$oDB->Getrow();
    if ( $row['countid']==0 ) $error='No data found';
  }

  // ------
  // EXPORT XML
  // ------

  if ( empty($error) )
  {
    $oDB2 = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);

    // start export

    if (!headers_sent())
    {
      header('Content-Type: text/xml; charset='.QTI_XML_CHAR);
      header('Content-Disposition: attachment; filename="'.$_POST['title'].'"');
    }

    echo '<?xml version="1.0" encoding="'.QTI_XML_CHAR.'"?>',PHP_EOL;
    echo '<quicktalk version="3.0">',PHP_EOL;

    // export topic
    $oDB->Query('SELECT * FROM '.TABTOPIC.' WHERE '.$strWhere );
    while($row=$oDB->Getrow())
    {
      $oTopic = new cTopic($row);

      echo '<topic id="',$oTopic->id,'" type="',$oTopic->type,'" forum="',$oTopic->section,'">',PHP_EOL;
      echo '<numid>',$oTopic->numid,'</numid>',PHP_EOL;
      echo '<status>',$oTopic->status,'</status>',PHP_EOL;
      if ( !empty($oTopic->statusdate) )    echo '<statusdate>',$oTopic->statusdate,'</statusdate>',PHP_EOL;
      //if ( !empty($oTopic->eventdate) )     echo '<eventdate>',$oTopic->eventdate,'</eventdate>',PHP_EOL;
      //if ( !empty($oTopic->wisheddate) )    echo '<wisheddate>',$oTopic->wisheddate,'</wisheddate>',PHP_EOL;
      if ( !empty($oTopic->firstpostid) )   echo '<firstpostid>',$oTopic->firstpostid,'</firstpostid>',PHP_EOL;
      if ( !empty($oTopic->lastpostid) )    echo '<lastpostid>',$oTopic->lastpostid,'</lastpostid>',PHP_EOL;
      if ( !empty($oTopic->firstpostuser) ) echo '<firstpostuser>',$oTopic->firstpostuser,'</firstpostuser>',PHP_EOL;
      if ( !empty($oTopic->lastpostuser) )  echo '<lastpostuser>',$oTopic->lastpostuser,'</lastpostuser>',PHP_EOL;
      if ( !empty($oTopic->firstpostname) ) echo '<firstpostname>',$oTopic->firstpostname,'</firstpostname>',PHP_EOL;
      if ( !empty($oTopic->lastpostname) )  echo '<lastpostname>',$oTopic->lastpostname,'</lastpostname>',PHP_EOL;
      if ( !empty($oTopic->firstpostdate) ) echo '<firstpostdate>',$oTopic->firstpostdate,'</firstpostdate>',PHP_EOL;
      if ( !empty($oTopic->lastpostdate) )  echo '<lastpostdate>',$oTopic->lastpostdate,'</lastpostdate>',PHP_EOL;
      if ( !empty($oTopic->x) )             echo '<x>',$oTopic->x,'</x>',PHP_EOL;
      if ( !empty($oTopic->y) )             echo '<y>',$oTopic->y,'</y>',PHP_EOL;
      if ( !empty($oTopic->z) )             echo '<z>',$oTopic->z,'</z>',PHP_EOL;
      if ( !empty($oTopic->tags) )          echo '<tags>',$oTopic->tags,'</tags>',PHP_EOL;
      if ( !empty($oTopic->param) )         echo '<param>',$oTopic->param,'</param>',PHP_EOL;

      echo '<posts>',PHP_EOL;

        $oDB2->Query('SELECT * FROM '.TABPOST.' WHERE topic='.$oTopic->id );
        while($row2=$oDB2->Getrow())
        {
          $oPost = new cPost($row2);
          echo '<post id="',$oPost->id,'" type="',$oPost->type,'">',PHP_EOL;
          echo '<icon>',$oPost->icon,'</icon>',PHP_EOL;
          echo '<title>',ToXml($oPost->title),'</title>',PHP_EOL;
          echo '<userid>',$oPost->userid,'</userid>',PHP_EOL;
          echo '<username>',$oPost->username,'</username>',PHP_EOL;
          echo '<issuedate>',$oPost->issuedate,'</issuedate>',PHP_EOL;
          if ( !empty($oPost->modifdate) ) echo '<modifdate>',$oPost->modifdate,'</modifdate>',PHP_EOL;
          if ( !empty($oPost->modifuser) ) echo '<modifuser>',$oPost->modifuser,'</modifuser>',PHP_EOL;
          if ( !empty($oPost->modifname) ) echo '<modifname>',$oPost->modifname,'</modifname>',PHP_EOL;
          echo '<textmsg>',ToXml($oPost->text),'</textmsg>',PHP_EOL;
          echo '</post>',PHP_EOL;  // doc is not exported
        }

      echo '</posts>',PHP_EOL;
      echo '</topic>',PHP_EOL;
    }

    // end export

    echo '</quicktalk>';
    exit;
  }

}

// --------
// HTML START
// --------

$oHtml->scripts[] = '
<script type="text/javascript">
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert("'.L('Missing').': File"); return false; }
  return null;
}
</script>
';

include APP.'_adm_inc_hd.php';

echo '<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
<h2 class="subtitle">',$L['Export_Content'],'</h2>
<table class="t-data horiz">
<tr>
<th><label for="section">',$L['Section'],'</label></th>
<td>
<select id="section" name="section" size="1">
<option value="*">[ ',$L['All'],' ]</option>
',Sectionlist(),'</select>
</td>
</tr>
<tr><th><label for="year">',$L['Items'],'</label></th>
<td><select id="year" name="year" size="1">
<option value="*">[ ',$L['All'],' ]</option>
',QTastag($arrYears),'
</select></td>
</tr>
<tr>
<th><label for="dropbbc">',$L['Export_Drop_bbc'],'</label></th>
<td><input type="checkbox" id="dropbbc" name="dropbbc"',($_SESSION['m_export_xml']['dropbbc']=='Y' ? QCHE : ''),'/> <label for="dropbbc">',$L['Export_H_Drop_bbc'],'</label></td>
</tr>
</table>
';

echo '<h2 class="subtitle">',$L['Destination'],'</h2>
<table class="t-data horiz">
<tr>
<th><label for="title">',$L['Export_Filename'],'</label></th>
<td><input type="text" id="title" name="title" size="32" maxlength="32" value="',$_SESSION['m_export_xml']['title'],'"/></td>
</tr>
</table>
';
echo '<p class="submit"><input type="submit" name="ok" value="',$L['Ok'],'"/></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';