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
require 'bin/qti_fn_tags.php';
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die($L['E_admin']);


// DEFINE LANG SET TO EDIT

$tt='en';
if ( isset($_GET['tt']) ) $tt = strip_tags($_GET['tt']);

// INITIALISE

$oVIP->selfurl = 'qti_adm_tags.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Tags'];

// --------
// SUBMITTED
// --------

$strAction = '';
$strFile = '';
$strSection = '*';

if ( isset($_GET['s']) ) $strSection = strip_tags($_GET['s']);
if ( isset($_GET['a']) ) $strAction = strip_tags($_GET['a']);

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';

if ( $_SESSION[QT]['tags']=='0' )
{
  $oHtml->Msgbox('!','msgbox message');
  echo $L['R_security'];
  echo '<p><a href="qti_adm_secu.php">&laquo; ',L('Security'),'</a></p>';
  $oHtml->Msgbox(END);
  include APP.'_adm_inc_ft.php';
  exit;
}

$arrDomains = GetDomains();
$arrTabs = array();

if ( file_exists('bin/qti_lang.php') )
{
  include 'bin/qti_lang.php';
  foreach($arrLang as $strKey=>$arrDef)
  {
    $arrTabs[$strKey]=$arrDef[1];
  }
}
else
{
  $arrTabs = array('*'=>'No language file');
}

// DISPLAY TABS

echo HtmlTabs($arrTabs,$oVIP->selfurl,$tt,6,L('E_editing'));

// DISPLAY TAB PANEL

echo '<div class="pan">
<div class="pan-top">',$L['Edit'],': ',$arrTabs[$tt],'</div>
';

echo '<table class="t-sec">
<tr class="t-sec">
<th colspan="2">',$L['Domain'],'/',$L['Section'],'</th>
<th class="c-file">',$L['File'],'</th>
<th class="c-action">',$L['Action'],'</th>
</tr>
';

  // common tags

  $strSectionTags = '('.L('none').')';
  $bFile = false;
  if ( file_exists('upload/tags_'.$tt.'.csv') )
  {
    $strSectionTags = 'tags_'.$tt.'.csv';
    $bFile = true;
  }

  echo '<tr class="t-sec">';
  echo '<td class="c-section group" colspan="2">',$L['Common_all_sections'],'</td>';
  echo '<td class="c-file group">',$strSectionTags,'</td>';
  echo '<td class="c-action group">';
  if ( $bFile )
  {
  echo '<a class="small" href="'.$oVIP->selfurl.'?tt=',$tt,'&amp;s=*&amp;a=view">',$L['Preview'],'</a> &middot; <a class="small" href="upload/',$strSectionTags,'">',$L['Download'],'</a> &middot; <a class="small" href="qti_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'.csv">',$L['Upload'],'</a> &middot; <a class="small" href="qti_adm_change.php?tt=',$tt,'&amp;a=tags_del&amp;v=',$strSectionTags,'">',L('Delete'),'</a>';
  }
  else
  {
  echo '<span class="disabled">',$L['Preview'],'</span> &middot; <span class="disabled">',$L['Download'],'</span> &middot; <a class="small" href="qti_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'.csv">',$L['Upload'],'</a> &middot; <span class="disabled">',L('Delete'),'</span>';
  }
  echo '</td></tr>',PHP_EOL;

$i=0;
foreach($arrDomains as $intDomid=>$strDomtitle)
{
  // GET SECTIONS (with hidden)

  $arrSections = QTarrget(GetSections('A',$intDomid));

  // DISPLAY

  echo '<tr class="t-sec">',PHP_EOL;
  echo '<td class="c-section group" colspan="2">',$strDomtitle,'</td>',PHP_EOL;
  echo '<td class="c-file group">&nbsp;</td>',PHP_EOL;
  echo '<td class="c-action group">&nbsp;</td>',PHP_EOL;
  echo '</tr>';

  // tags per section

  foreach($arrSections as $intSecid=>$strSectitle)
  {
    // GET SECTIONS
    $oSEC = new cSection($intSecid);

    $strSectionTags = $strSectionTags = '('.L('none').')';
    $bFile = false;
    if ( file_exists( 'upload/tags_'.$tt.'_'.$intSecid.'.csv' ) )
    {
      $strSectionTags = 'tags_'.$tt.'_'.$intSecid.'.csv';
      $bFile = true;
    }

    echo '<tr class="t-sec">';
    echo '<td class="c-icon">',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'ico ico-s','','qti_adm_section.php?d='.$intDomid.'&amp;s='.$oSEC->uid),'</td>';
    echo '<td><span class="bold">',$oSEC->name,'</span><br/><span class="small">id ',$intSecid,'</span> &middot; ';
    if ( cSection::CountItems($intSecid,'tags')>0 )
    {
    echo '<a class="small" href="qti_adm_tags.php?tt=',$tt,'&amp;s=',$intSecid,'&amp;a=used">',$L['Find_used_tags'],'</a>';
    }
    else
    {
    echo '<span class="disabled">',$L['E_no_tag'],'</span>';
    }
    echo '</td>';
    echo '<td class="c-file">',$strSectionTags,'&nbsp;</td>';
    echo '<td class="c-action">';
    if ( $bFile )
    {
    echo '<a class="small" href="'.$oVIP->selfurl.'?tt=',$tt,'&amp;s=',$oSEC->uid,'&amp;a=view">',$L['Preview'],'</a> &middot; <a class="small" href="upload/',$strSectionTags,'">',$L['Download'],'</a> &middot; <a class="small" href="qti_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'_',$intSecid.'.csv">',$L['Upload'],'</a> &middot; <a class="small" href="qti_adm_change.php?tt=',$tt,'&amp;a=tags_del&amp;v=',$strSectionTags,'">',L('Delete'),'</a>';
    }
    else
    {
    echo '<span class="disabled">',$L['Preview'],'</span> &middot; <span class="disabled">',$L['Download'],'</span> &middot; <a class="small" href="qti_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'_',$intSecid.'.csv">',$L['Upload'],'</a> &middot; <span class="disabled">',L('Delete'),'</span>';
    }
    echo '</td></tr>',PHP_EOL;
  }
}
echo '</table>
';

// END TABS

echo '</div>
';

// PREVIEW FILE

if ( empty($strAction) )
{
  echo '<h2>',$L['Preview'],'</h2>';
  echo '<p class="disabled">',$L['E_nothing_selected'],'</p>';
}

if ( $strAction=='view' )
{
  $strFile = 'tags_'.$tt.($strSection=='*' ? '' : '_'.$strSection).'.csv';

  if ( !empty($strFile) ) { if ( !file_exists('upload/'.$strFile) ) $strFile=''; }

  echo '<h2>',$L['Preview'],(empty($strFile) ? '' : ': '.$L['Proposed_tags'].' ['.$strFile.']'),'</h2>
  ';

  if ( empty($strFile) )
  {
    echo '<p class="disabled">',$L['E_nothing_selected'],'</p>';
  }
  else
  {
    if ( file_exists('upload/'.$strFile) )
    {
      $intSection = -1; if ( $strSection!='*' ) $intSection = intval($strSection);

      // read csv

      $arrTags = TagsRead($tt,$strSection,false,'upload/');

      // display
      echo '<div class="scrollmessage">';
      echo '<table class="tags">',PHP_EOL;
      foreach($arrTags as $strKey=>$strValue)
      {
      echo '<tr class="hover">',PHP_EOL;
      echo '<td>',$strKey,'</td>',PHP_EOL;
      echo '<td>',$strValue,'</td>',PHP_EOL;
      echo '<td><a class="small" href="qti_items.php?q=tst&amp;s=',$intSection,'&amp;v2=*&amp;v=',urlencode($strKey),'" title="',QTstrh(L('Find_item_tag')),'">',L('Search'),'</a></td>',PHP_EOL;
      echo '</tr>';
      }
      echo '</table>',PHP_EOL;
      echo '</div>';

    }
    else
    {
      echo '<p class="disabled">File not found...</p>';
    }
  }
}

// PREVIEW FIND

if ( $strAction=='used' && $strSection!='*' )
{
  $intSection = intval($strSection);

  // search used tags

  $arrUsed = cSection::GetTagsUsed($intSection,100);
  if ( count($arrUsed)>=100 ) $arrUsed[]='...';

  // display

  echo '<h2>',$L['Preview'],': ',$L['Used_tags'],' ',L('in_section'),' ',$intSection,'</h2>
  ';

  if ( count($arrUsed)==0 )
  {
    echo '<p class="disabled">',$L['No_result'],'</p>';
  }
  else
  {
    // search proposed tags

    $arrTags = TagsRead($tt,'*');
    $arrTags2 = TagsRead($tt,$intSection);
    foreach($arrTags2 as $strKey=>$strValue)
    {
      if ( !isset($arrTags[$strKey]) ) $arrTags[$strKey]=$strValue;
    }

    // display

    echo '<div class="scrollmessage">';
    echo '<table class="tags">',PHP_EOL;
    foreach($arrUsed as $strValue)
    {
    echo '<tr class="hover">',PHP_EOL;
    echo '<td>',$strValue,'</td>',PHP_EOL;
    echo '<td>',(isset($arrTags[$strValue]) ? $arrTags[$strValue] : '&nbsp;'),'</td>',PHP_EOL;
    echo '<td><a class="small" href="qti_items.php?q=tst&amp;s=',$strSection,'&amp;v2=*&amp;v=',$strValue,'" title="',$L['Find_item_tag'],'">',$L['Search'],'</a></td>',PHP_EOL;
    echo '</tr>';
    }
    echo '</table>',PHP_EOL;
    echo '</div>';
  }
}

// HTML END

include APP.'_adm_inc_ft.php';