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

if ( sUser::Role()!='A' ) die($L['E_admin']);

// INITIALISE

$oVIP->selfurl = 'qti_adm_skin.php';
$oVIP->selfname = '<span class="upper">'.L('Settings').'</span><br/>'.$L['Adm_layout'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check skin
  if ( empty($error) )
  {
    $_SESSION[QT]['skin_dir'] = $_POST['skin'];
    if ( !file_exists('skin/'.$_POST['skin'].'/qti_main.css') )
    {
    $error=$L['Section_skin'].' '.L('invalid').' (qti_main.css not found)';
    $_SESSION[QT]['skin_dir'] = 'default';
    }
  }

  // check banner/welcome/legend/home
  if ( empty($error) )
  {
    $_SESSION[QT]['skin_dir'] = 'skin/'.$_POST['skin'];
    $_SESSION[QT]['show_welcome'] = $_POST['show_welcome'];
    $_SESSION[QT]['show_legend'] = $_POST['legend'];
    $_SESSION[QT]['show_banner'] = $_POST['show_banner'];
    $_SESSION[QT]['home_menu'] = $_POST['home'];
    $_SESSION[QT]['section_desc'] = $_POST['section_desc'];
    $_SESSION[QT]['news_on_top'] = $_POST['news_on_top'];
    $_SESSION[QT]['items_per_page'] = substr($_POST['items_per_page'],1);
    $_SESSION[QT]['replies_per_page'] = substr($_POST['replies_per_page'],1);
    $_SESSION[QT]['show_quick_reply'] = $_POST['show_quick_reply'];
  }

  // check homename
  if ( $_SESSION[QT]['home_menu']=='1' )
  {
    if ( empty($error) )
    {
      if ( empty($_POST['homename']) ) { $_POST['homename'] = 'Home'; $error = $L['Home_website_name'].' '.L('invalid'); }
      $_SESSION[QT]['home_name'] = $_POST['homename'];
    }
    if ( empty($error) )
    {
      $str = substr(trim($_POST['homeurl']),0,255);
      if ( !empty($str) ) { $_SESSION[QT]['home_url'] = $str; } else { $error = $L['Site_url'].': '.L('invalid'); }
      if ( !preg_match('/^(http:\/\/|https:\/\/)/',$str) ) $warning = $L['Home_website_url'].': '.$L['E_missing_http'];
      $_SESSION[QT]['home_url'] = $str;
    }
  }

  // save value
  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['skin_dir'].'" WHERE param="skin_dir"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_welcome'].'" WHERE param="show_welcome"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_banner'].'" WHERE param="show_banner"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_legend'].'" WHERE param="show_legend"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['home_menu'].'" WHERE param="home_menu"');
    if ( $_SESSION[QT]['home_menu']=='1' )
    {
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($_SESSION[QT]['home_name']).'" WHERE param="home_name"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.QTstrd($_SESSION[QT]['home_url']).'" WHERE param="home_url"');
    }
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['items_per_page'].'" WHERE param="items_per_page"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['replies_per_page'].'" WHERE param="replies_per_page"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['section_desc'].'" WHERE param="section_desc"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['news_on_top'].'" WHERE param="news_on_top"');
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_quick_reply'].'" WHERE param="show_quick_reply"');
  }
  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

// WARNINGS

if ( !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['home_url']) ) $warning = $L['Home_website_url'].': '.$L['E_missing_http'];

$oHtml->scripts[] = '
<script type="text/javascript">
function homedisabled(str)
{
  if (str=="0")
  {
  document.getElementById("homename").disabled=true;
  document.getElementById("homeurl").disabled=true;
  }
  else
  {
  document.getElementById("homename").disabled=false;
  document.getElementById("homeurl").disabled=false;
  }
  return;
}
function ValidateForm(theForm)
{
  if (theForm.items_per_page.value.length < 1) { alert(qtHtmldecode("'.L('Missing').': '.$L['Items_per_section_page'].'")); return false; }
  return null;
}
</script>
';

include APP.'_adm_inc_hd.php';

// Read directory in language
$intHandle = opendir('skin');
$arrFiles = array();
while ( false!==($strFile = readdir($intHandle)) )
{
if ( $strFile!='.' && $strFile!='..' ) $arrFiles[$strFile]=ucfirst($strFile);
}
closedir($intHandle);
asort($arrFiles);

// Current skin
$strDfltskin = substr($_SESSION[QT]['skin_dir'],5);

// FORM

echo '<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
<h2 class="subtitle">',$L['Skin'],'</h2>
<table class="t-data horiz">
<tr title="',$L['H_Board_skin'],'">
<th><label for="skin">',$L['Board_skin'],'</label></th>
<td><select id="skin" name="skin" onchange="bEdited=true;">',QTasTag($arrFiles,$strDfltskin),'</select></td>
</tr>
<tr title="',$L['H_Show_banner'],'">
<th><label for="show-banner">',$L['Show_banner'],'</label></th>
<td><select id="show-banner" name="show_banner" onchange="bEdited=true;">'.QTasTag(array(L('Show_banner0'),L('Show_banner1'),L('Show_banner2')),(int)$_SESSION[QT]['show_banner']).'</select></td>
</tr>
<tr title="',$L['H_Show_welcome'],'">
<th><label for="show_welcome">',L('Show_welcome'),'</label></th>
<td><select id="show_welcome" name="show_welcome" onchange="bEdited=true;">
<option value="2"',($_SESSION[QT]['show_welcome']=='2' ? QSEL : ''),'>',L('Y'),'</option>
<option value="0"',($_SESSION[QT]['show_welcome']=='0' ? QSEL : ''),'>',L('N'),'</option>
<option value="1"',($_SESSION[QT]['show_welcome']=='1' ? QSEL : ''),'>',L('While_unlogged'),'</option>
</select></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',$L['Layout'],'</h2>
<table class="t-data horiz">
';
$arr = array('n10'=>'10','n25'=>'25','n50'=>'50','n100'=>'100'); if ( $_SESSION[QT]['items_per_page']==='20' || $_SESSION[QT]['items_per_page']==='30' || $_SESSION[QT]['items_per_page']==='40' ) $_SESSION[QT]['items_per_page']='25'; //upgrade version 2.x to 3.0 
echo '<tr title="',$L['H_Items_per_section_page'],'">
<th><label for="items_per_page">',$L['Items_per_section_page'],'</label></th>
<td><select id="items_per_page" name="items_per_page" onchange="bEdited=true;">
',QTasTag($arr,'n'.$_SESSION[QT]['items_per_page'],array('format'=>'%s / '.strtolower($L['Page']))),'
</select></td>
</tr>
<tr title="',$L['H_Replies_per_item_page'],'">
<th><label for="replies_per_page">',$L['Replies_per_item_page'],'</label></th>
<td><select id="replies_per_page" name="replies_per_page" onchange="bEdited=true;">
',QTasTag($arr,'n'.$_SESSION[QT]['replies_per_page'],array('format'=>'%s / '.strtolower($L['Page']))),'
</select></td>
</tr>
';
echo '<tr title="',$L['H_Show_legend'],'">
<th><label for="legend">',$L['Show_legend'],'</label></th>
<td>
<select id="legend" name="legend" onchange="bEdited=true;">',QTasTag(array(L('N'),L('Y')),(int)$_SESSION[QT]['show_legend']),'</select>
</td>
</tr>
</table>
';

echo '<h2 class="subtitle">',L('Your_website'),'</h2>
<table class="t-data horiz">
';
echo '<tr title="',$L['H_Home_website_name'],'">
<th><label for="home">',L('Add_home'),'</label></th>
<td>
<select id="home" name="home" onchange="homedisabled(this.value); bEdited=true;">',QTasTag(array(L('N'),L('Y')),(int)$_SESSION[QT]['home_menu']),'</select>
&nbsp;<input type="text" id="homename" name="homename" size="15" maxlength="255" value="',QTstrh($_SESSION[QT]['home_name']),'"',($_SESSION[QT]['home_menu']=='0' ? QDIS : ''),' onchange="bEdited=true;"/></td>
</tr>
<tr title="',$L['H_Website'],'">
<th><label for="homeurl">',$L['Home_website_url'],'</label></th>
<td><input type="text" id="homeurl" name="homeurl" pattern="^(http://|https://).*" size="30" maxlength="255" value="',QTstrh($_SESSION[QT]['home_url'],255),'"',($_SESSION[QT]['home_menu']=='0' ? QDIS : ''),' onchange="bEdited=true;"/></td>
</tr>
</table>
';

echo '<h2 class="subtitle">',L('Display_options'),'</h2>
<table class="t-data horiz">
<tr title="',L('H_Repeat_section_description'),'">
<th><label for="section_desc">',L('Repeat_section_description'),'</label></th>
<td><select id="section_desc" name="section_desc" onchange="bEdited=true;">',QTasTag(array(L('N'),L('Y'),L('By_section')),(int)$_SESSION[QT]['section_desc']),'</select></td>
</tr>
<tr title="',L('H_Show_news_on_top'),'">
<th><label for="news_on_top">',L('Show_news_on_top'),'</label></th>
<td><select id="news_on_top" name="news_on_top" onchange="bEdited=true;">',QTasTag(array(L('N'),L('Y'),L('By_section')),(int)$_SESSION[QT]['news_on_top']),'</select>
'.(empty($L['if_not_closed']) ? '' : '<span class="small disabled">'.$L['if_not_closed'].'</span>').'</td>
</tr>
<tr title="',L('H_Show_quick_reply'),'">
<th><label for="show_quick_reply">',L('Show_quick_reply'),'</label></th>
<td><select id="show_quick_reply" name="show_quick_reply" onchange="bEdited=true;">',QTasTag(array(L('N'),L('Y'),L('By_section')),(int)$_SESSION[QT]['show_quick_reply']),'</select></td>
</tr>
</table>
';
echo '<p style="margin:0 0 5px 0;text-align:center"><input type="submit" name="ok" value="',L('Save'),'"/></p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';