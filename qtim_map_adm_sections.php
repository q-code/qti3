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
include Translate(APP.'_adm.php');
if ( sUser::Role()!='A' ) die(Error(13));

include Translate('qtim_map.php');
include Translate('qtim_map_adm.php');
include 'qtim_map_lib.php';

// INITIALISE

$oVIP->selfurl = 'qtim_map_adm_sections.php';
$oVIP->selfname = 'Map';
$oVIP->exiturl = 'qtim_map_adm.php';
$oVIP->exitname = '<i class="fa fa-chevron-circle-left fa-lg"></i> '.$oVIP->selfname;
$strPageversion = $L['map_Version'].' 3.0';

$arrSections = QTarrget(GetSections('A'));

// Read png in directory

$intHandle = opendir('qtim_map');
$arrFiles = array();
while ( false!==($strFile = readdir($intHandle)) )
{
  if ( $strFile!='.' && $strFile!='..' ) {
  if ( substr($strFile,-4,4)=='.png' ) {
  if ( !strstr($strFile,'shadow') ) {
    $arrFiles[substr($strFile,0,-4)]=ucfirst(substr(str_replace('_',' ',$strFile),0,-4));
  }}}
}
closedir($intHandle);
asort($arrFiles);

// --------
// SUBMITTED for changes
// --------

if ( isset($_POST['ok']) && !empty($_SESSION[QT]['m_map_gkey']) )
{
  // save setting files
  $strFilename = 'qtim_map/config_map.php';

  $arrData = array();
  $arrData['U'] = array('section'=>'U','enabled'=>(isset($_POST['sec_U']) ? 1 : 0));
  if ( $arrData['U']['enabled']==1 && isset($_POST['list_U']) ) $arrData['U']['list']=(int)$_POST['list_U'];
  if ( $arrData['U']['enabled']==1 && isset($_POST['mark_U']) ) $arrData['U']['icon']=$_POST['mark_U'];
  $arrData['S'] = array('section'=>'S','enabled'=>(isset($_POST['sec_S']) ? 1 : 0));
  if ( $arrData['S']['enabled']==1 && isset($_POST['list_S']) ) $arrData['S']['list']=(int)$_POST['list_S'];
  if ( $arrData['S']['enabled']==1 && isset($_POST['mark_S']) ) $arrData['S']['icon']=$_POST['mark_S'];
  foreach($arrSections as $id=>$strSectitle)
  {
  $arrData[$id] = array('section'=>$id,'enabled'=>(isset($_POST['sec_'.$id]) ? 1 : 0));
  if ( $arrData[$id]['enabled']==1 && isset($_POST['list_'.$id]) ) $arrData[$id]['list']=(int)$_POST['list_'.$id];
  if ( $arrData[$id]['enabled']==1 && isset($_POST['mark_'.$id]) ) $arrData[$id]['icon']=$_POST['mark_'.$id];
  }
  $content = '<?php'.PHP_EOL;
  $content .= '$jMapSections = \''.PHP_EOL;
  $content .= json_encode($arrData).PHP_EOL;
  $content .= '\';';
  
  if (!is_writable($strFilename)) $error="Impossible to write into the file [$strFilename].";
  if ( empty($error) )
  {
  if (!$handle = fopen($strFilename, 'w')) $error="Impossible to open the file [$strFilename].";
  }
  if ( empty($error) )
  {
  if ( fwrite($handle, $content)===FALSE ) $error="Impossible to write into the file [$strFilename].";
  fclose($handle);
  }

  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

// prepare section settings

if ( file_exists('qtim_map/config_map.php') ) { include 'qtim_map/config_map.php'; } else { $jMapSections = '[{"section":0,"enabled":0}]'; }

$arrConfig = json_decode($jMapSections,true); // decode as an array
$arrSections = sMem::Get('sys_sections');

$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qti_base.js"></script>
<script type="text/javascript">
function mapsection(section)
{
  if (document.getElementById("sec_"+section).checked)
  {
  document.getElementById("mark_"+section).style.visibility="visible";
  document.getElementById("list_"+section).style.visibility="visible";
  }
  else
  {
  document.getElementById("mark_"+section).style.visibility="hidden";
  document.getElementById("list_"+section).style.visibility="hidden";
  }
  return null;
}
</script>
';

// DISPLAY

include APP.'_adm_inc_hd.php';

echo '<form method="post" action="',$oVIP->selfurl,'">
<h2 class="subtitle">',L('Sections'),'</h2>
<div class="pan">
<p>',$L['map_Allowed'],'</p>
<table class="subtable">
<tr>
<th style="width:25px;text-align:center">&nbsp;</th>
<th>',$L['Sections'],'</th>
<th>',$L['map_symbols'],'</th>
<th>',$L['map_Main_list'],'</th>
</tr>
';

foreach($arrSections as $id=>$strSectitle)
{
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled']=0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list']=0;
echo '<tr class="hover">
<td style="background-color:#c3d9ff;width:25px;text-align:center"><input type="checkbox" id="sec_',$id,'" name="sec_',$id,'"'.($arrConfig[$id]['enabled']==0 ? '' : QCHE).' style="vertical-align: middle" onclick="mapsection(\'',$id,'\')" /></td>
<td><label for="sec_',$id,'">',(isset($arrSections[$id]['title']) ? $arrSections[$id]['title'] : '[section '.$id.']'),'</label></td>
<td>
<select class="small" id="mark_',$id,'" name="mark_',$id,'" size="1" style="',($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : ''),'">
<option value="0">',$L['map_Default'],'</option>
<option value="-" disabled="disabled">&nbsp;</option>
',QTasTag($arrFiles,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)),'
</select>
</td>
<td>
<select class="small" id="list_',$id,'" name="list_',$id,'" size="1" style="',($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : ''),'">
',QTasTag($L['map_List'],$arrConfig[$id]['list']),'
</select>
</td>
</tr>
';
}

$id='S';
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled']=0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list']=0;
echo '<tr class="hover">
<td style="background-color:#c3d9ff;border-top:solid 1px #c3d9ff;width:25px;text-align:center"><input type="checkbox" id="sec_',$id,'" name="sec_',$id,'"'.($arrConfig[$id]['enabled']==0 ? '' : QCHE).' style="vertical-align: middle" onclick="mapsection(\'',$id,'\')" /></td>
<td style="border-top:solid 1px #c3d9ff"><label for="sec_',$id,'">',L('Search_result'),'</label></td>
<td style="border-top:solid 1px #c3d9ff">
<select class="small" id="mark_',$id,'" name="mark_',$id,'" size="1" style="',($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : ''),'">
<option value="S">',$L['map_From_section'],'</option>
<option value="0">',$L['map_Default'],'</option>
<option value="-" disabled="disabled">&nbsp;</option>
',QTasTag($arrFiles,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)),'
</select>
</td>
<td style="border-top:solid 1px #c3d9ff">
<select class="small" id="list_',$id,'" name="list_',$id,'" size="1" style="',($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : ''),'">
',QTasTag($L['map_List'],$arrConfig[$id]['list']),'
</select>
</td>
</tr>
';

$id='U';
if ( !isset($arrConfig[$id]['enabled']) ) $arrConfig[$id]['enabled']=0;
if ( !isset($arrConfig[$id]['list']) ) $arrConfig[$id]['list']=0;
echo '<tr class="hover">
<td style="background-color:#c3d9ff;width:25px;text-align:center"><input type="checkbox" id="sec_',$id,'" name="sec_',$id,'"'.($arrConfig[$id]['enabled']==0 ? '' : QCHE).' style="vertical-align: middle" onclick="mapsection(\'',$id,'\')" /></td>
<td><label for="sec_',$id,'">',L('Users'),'</label></td>
<td>
<select class="small" id="mark_',$id,'" name="mark_',$id,'" size="1" style="',($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : ''),'">
<option value="0">',$L['map_Default'],'</option>
<option value="-" disabled="disabled">&nbsp;</option>
',QTasTag($arrFiles,(isset($arrConfig[$id]['icon']) ? $arrConfig[$id]['icon'] : null)),'
</select>
</td>
<td>
<select class="small" id="list_',$id,'" name="list_',$id,'" size="1" style="',($arrConfig[$id]['enabled']==0 ? 'visibility:hidden' : ''),'">
',QTasTag($L['map_List'],$arrConfig[$id]['list']),'
</select>
</td>
</tr>
</table>
';

echo '<p class="submit"><input type="submit" name="ok" value="',L('Save'),'"/></p>
</div>
</form>
';

// show table symbols

echo '<br/>
<h2 class="subtitle">',L('map_symbols'),'</h2>
<table class="t-data horiz">
<tr>
<td class="center"><img alt="i" class="marker" src="bin/css/gmap_marker.png"/><br/><span class="small">Default</span></td>
';
$i=0;
foreach ($arrFiles as $strFile=>$strName)
{
echo '<td class="center"><img alt="i" class="marker" src="qtim_map/'.$strFile.'.png"/><br/><span class="small">'.$strName.'</span></td>
';
++$i;
if ( $i>=9 ) { echo '</tr><tr>'; $i=0; }
}
echo '</tr>
</table>
<p id="page-ft"><a href="',$oVIP->exiturl,'" onclick="return qtEdited(bEdited,\'',$L['E_editing'],'\');">',$oVIP->exitname,'</a></p>
';

// HTML END

include APP.'_adm_inc_ft.php';