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
include Translate('qtim_import.php');
if ( sUser::Role()!='A' ) die(Error(13));

// FUNCTIONS

function startElement($parser, $strTag, $arrTagAttr)
{
  $strTag = strtolower($strTag);
  global $arrTopic,$arrPosts;
  global $t,$p,$L;

  switch($strTag)
  {
  case 'topic':
    $arrTopic = array();
    $arrPosts = array();
    if ( isset($arrTagAttr['ID']) ) { $t=intval($arrTagAttr['ID']); } else { $t=0; }
    $arrTopic['id'] = $t;
    $arrTopic['type'] = (isset($arrTagAttr['TYPE']) ? $arrTagAttr['TYPE'] : 'T');
    break;
  case 'post':
    if ( isset($arrTagAttr['ID']) ) { $p=intval($arrTagAttr['ID']); } else { $p=0; }
    $arrPosts[$p] = array();
    $arrPosts[$p]['id'] = $p;
    $arrPosts[$p]['type'] = (isset($arrTagAttr['TYPE']) ? $arrTagAttr['TYPE'] : 'P');
    break;
  }
}
function characterData($parser, $data)
{
  global $strValue;
  $strValue = trim($data);
}
function endElement($parser, $strTag)
{
  $strTag = strtolower($strTag);

  global $arrTopic,$arrPosts;
  global $t,$p,$intTopicInsertId,$intPostInsertId;
  global $strValue;
  global $oDB, $arrCounts;

  switch($strTag)
  {
  case 'x':         $arrTopic['x']=$strValue; break;
  case 'y':         $arrTopic['y']=$strValue; break;
  case 'z':         $arrTopic['z']=$strValue; break;
  case 'tags':      if ( !$_SESSION['m_import_xml']['droptags'] ) { $arrTopic['tags']=$strValue; } break;
  case 'eventdate': $arrTopic['eventdate']=$strValue; break;
  //case 'wisheddate':$arrTopic['wisheddate']=$strValue; break;
  case 'firstpostdate': if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrTopic['firstpostdate']=date('Ymd His'); } else { $arrTopic['firstpostdate']=$strValue; } break;
  case 'lastpostdate': if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrTopic['lastpostdate']=date('Ymd His'); } else { $arrTopic['lastpostdate']=$strValue; } break;
  //case 'param':     $arrTopic['param']=$strValue; break;

  case 'icon':     $arrPosts[$p]['icon']=$strValue; break;
  case 'title':    $arrPosts[$p]['title']=$strValue; break;
  case 'userid':   $arrPosts[$p]['userid']=0; break; //userid must be reset to 0
  case 'username': $arrPosts[$p]['username']=$strValue; break;
  case 'issuedate':if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrPosts[$p]['issuedate']=date('Ymd His'); } else { $arrPosts[$p]['issuedate']=$strValue; } break;
  case 'modifdate':$arrPosts[$p]['modifdate']=$strValue; break;
  case 'modifuser':$arrPosts[$p]['modifuser']=0; break; //userid must be reset to 0
  case 'modifname':$arrPosts[$p]['modifname']=$strValue; break;
  case 'textmsg':  $arrPosts[$p]['textmsg']=$strValue; break;
  case 'posts':    $arrTopic['posts']=$arrPosts; break;

  case 'topic':

    // Process topic

    $oTopic = new cTopic($arrTopic);
    $oTopic->section = $_SESSION['m_import_xml']['dest'];
    $oTopic->id = $intTopicInsertId; ++$intTopicInsertId;
    $oTopic->status = $_SESSION['m_import_xml']['status'];

    $oTopic->InsertTopic(false);
    ++$arrCounts['topic'];

    // Process posts

    foreach($arrTopic['posts'] as $arrPost)
    {
      $oPost = new cPost($arrPost); if ( $_SESSION['m_import_xml']['dropreply'] && $oPost->type!='P' ) break;
      $oPost->id = $intPostInsertId; ++$intPostInsertId;
      $oPost->topic = $oTopic->id;
      $oPost->section = $_SESSION['m_import_xml']['dest'];
      if ( $_SESSION['m_import_xml']['dropbbc'] ) $oPost->text = QTbbc($oPost->text,'drop');

      $oPost->InsertPost(false,false);
      if ( $oPost->type!='P' ) ++$arrCounts['reply']; // count only the replies
    }

    // Topic stats

    $oTopic->UpdateTopicStats($oTopic->id,0); // This update firstpost/lastpost (and do not perform close-topic check)

    break;

  default:
    if ( trim($strValue)!='' ) $arrTopic[$strTag]=$strValue;
    break;
  }
}

// INITIALISE

$intDest   = -1;
$strStatus = '1';
$bDropbbc  = false;
$bDropreply= false;
$bDroptags = false;
$bDropdate = false;
$arrCounts = array('topic'=>0,'reply'=>0);

$oVIP->selfurl = 'qtim_import_adm.php';
$oVIP->selfname = $L['Import_Admin'];
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;
$strPageversion = $L['Import_Version'].' 3.0';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check file

  if (!is_uploaded_file($_FILES['title']['tmp_name'])) $error = $L['Import_E_nofile'];

  // check form value

  if ( empty($error) )
  {
    if ( isset($_POST['dropbbc']) ) $bDropbbc=true;
    if ( isset($_POST['dropreply']) ) $bDropreply=true;
    if ( isset($_POST['droptags']) ) $bDroptags=true;
    if ( isset($_POST['dropdate']) ) $bDropdate=true;
    $intDest = intval($_POST['section']);
    $strStatus = $_POST['status'];
    $_SESSION['m_import_xml']=array('dest'=>$intDest,'status'=>$strStatus,'dropbbc'=>$bDropbbc,'dropreply'=>$bDropreply,'droptags'=>$bDroptags,'dropdate'=>$bDropdate);
  }

  // check format

  if ( empty($error) )
  {
    if ( $_FILES['title']['type']!='text/xml' )
    {
    $error = $L['Import_E_format'];
    unlink($_FILES['title']['tmp_name']);
    }
  }

  // import xml

  if ( empty($error) )
  {
    $arrTopic = array();
    $arrPosts = array();
    $t = 0;
    $p = 0;
    $strValue = '';

    $intTopicInsertId = $oDB->Nextid(TABTOPIC);
    $intPostInsertId = $oDB->Nextid(TABPOST);

    $xml_parser = xml_parser_create();
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
    xml_set_element_handler($xml_parser, 'startElement', 'endElement'); // SQL is precessed in the endElement function
    xml_set_character_data_handler($xml_parser, 'characterData');
    if ( !($fp = fopen($_FILES['title']['tmp_name'],'r')) ) die('could not open XML input');
    while ($data = fread($fp,4096))
    {
      if ( !xml_parse($xml_parser, $data, feof($fp)) ) die(sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
    }
    xml_parser_free($xml_parser);

  }

  if ( empty($error) )
  {
    // Clean file

    unlink($_FILES['title']['tmp_name']);

    // Update section stats and system stats

    cSection::UpdateLastPostDate($intDest);
    cSection::UpdateReplies($intDest);

    // End message (pause)

    $oHtml->PageMsgAdm( NULL, '<p>'.L('Item',$arrCounts['topic']).'<br/>'.L('Reply',$arrCounts['reply']).'</p><br/>'.$L['Import_S_import'].'<p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '350px');
  }
}

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';

if ( isset($_SESSION['m_import_xml']['dest']) )      $intDest   = $_SESSION['m_import_xml']['dest'];
if ( isset($_SESSION['m_import_xml']['status']) )    $strStatus = $_SESSION['m_import_xml']['status'];
if ( isset($_SESSION['m_import_xml']['dropbbc']) )   $bDropbbc  = $_SESSION['m_import_xml']['dropbbc'];
if ( isset($_SESSION['m_import_xml']['dropreply']) ) $bDropreply= $_SESSION['m_import_xml']['dropreply'];
if ( isset($_SESSION['m_import_xml']['droptags']) )  $bDroptags = $_SESSION['m_import_xml']['droptags'];
if ( isset($_SESSION['m_import_xml']['dropdate']) )  $bDropdate = $_SESSION['m_import_xml']['dropdate'];

echo '<form method="post" action="',$oVIP->selfurl,'" enctype="multipart/form-data">
<input type="hidden" name="maxsize" value="5242880"/>

<h2 class="subtitle">',$L['Import_File'],'</h2>
<table class="t-data horiz">
<tr>
<th>',$L['Import_File'],'</th>
<td><input type="file" id="title" name="title" required/></td>
</tr>
';
if ( $_SESSION[QT]['board_offline']=='0' )
{
echo '
<tr>
<th><i class="fa fa-exclamation-triangle fa-lg"></i></th>
<td>It is recommanded to turn the board off-line while importing. <a href="qti_adm_index.php">Board status...</a></td>
</tr>
';
}
echo '</table>
';

echo '
<h2 class="subtitle">',$L['Import_Content'],'</h2>
<table class="t-data horiz">
<tr>
<th>',$L['Import_Drop_tags'],'</th>
<td><input type="checkbox" id="droptags" name="droptags"',($bDroptags ? QCHE : ''),'/> <label for="droptags">',$L['Import_HDrop_tags'],'</label></td>
</tr>
<tr>
<th>',$L['Import_Drop_reply'],'</th>
<td><input type="checkbox" id="dropreply" name="dropreply"',($bDropreply ? QCHE : ''),'/> <label for="dropreply">',$L['Import_HDrop_reply'],'</label></td>
</tr>
<tr>
<th>',$L['Import_Drop_bbc'],'</th>
<td><input type="checkbox" id="dropbbc" name="dropbbc"',($bDropbbc ? QCHE : ''),'/> <label for="dropbbc">',$L['Import_HDrop_bbc'],'</label></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',$L['Destination'],'</h2>
<table class="t-data horiz">
<tr>
<th style="width:200px"><label for="section">',$L['Import_Destination'],'</label></td>
<td><select id="section" name="section">',Sectionlist(),'</select> <a href="qti_adm_sections.php">',$L['Section_add'],'</a></td>
</tr>
<tr>
<th><label for="status">',$L['Status'],'</label></th>
<td><select id="status" name="status">
',QTasTag(cTopic::Statuses(),$strStatus),'</select></td>
</tr>
<tr>
<th>',$L['Import_Drodate'],'</th>
<td><input type="checkbox" id="dropdate" name="dropdate"',($bDropdate ? QCHE : ''),'/> <label for="dropdate">',$L['Import_HDrodate'],'</label></td>
</tr>
</table>
';
echo '<p class="submit"><input type="submit" name="ok" value="',$L['Ok'],'"/></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';