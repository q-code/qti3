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
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die(Error(13));

// INITIALISE

$a = 'add';
$id = '';
$ok = '';
QThttpvar('a id ok','str str str');

$oVIP->selfurl = 'qti_adm_module.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_modules'].'</span><br />'.($a=='add' ? $L['Add'] : $L['Remove']);

// --------
// SUBMITTED
// --------

if ( !empty($ok) )
{
  // check form
  $strName = strtolower(strip_tags($id));
  $strName = str_replace(' ','_',$strName);
  $strFile = 'qtim_'.$strName.'_'.($a=='rem' ? 'un' : '').'install.php';

  if ( file_exists($strFile) )
  {
  // exit
  $oVIP->selfname = $L['Adm_modules'];
  $oVIP->exiturl = 'qti_adm_module.php?a='.$a;
  $oVIP->exitname = L('Exit');
  $oHtml->PageMsgAdm(NULL, '<p>'.$L['Module_name'].': '.$strName.'</p><p><a class="button" href="'.$strFile.'">'.$L['Module_'.$a].' !</a></p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '500px');

  }
  else
  {
  $error = 'Module not found... ('.$strFile.')<br /><br />Possible cause: components of this module are not uploaded.';
  $_SESSION['pagedialog'] = 'E|Module not found...';
  }
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
function fill(id)
{
  var str = id.innerHTML;
  var doc = document.getElementById("id");
  if ( doc ) doc.value=str;
}
</script>';

include APP.'_adm_inc_hd.php';

// Browse qtim file

$intHandle = opendir(getcwd());
$arrFiles = array();

while (false !== ($file = readdir($intHandle)))
{
  $file=strtolower($file);
  if ( $file!='.' && $file!='..' ) {
    if ( substr($file,0,5)==='qtim_' && substr($file,-12)==='_install.php')
    {
    $arrFiles[] = '<a href="javascript:void(0)" onclick="fill(this);">'.str_replace('_install.php','',str_replace('qtim_','',$file)).'</a>';
    }
  }
}
closedir($intHandle);
sort($arrFiles);

echo '<form method="post" action="',$oVIP->selfurl,'">
<table class="t-data horiz">
<tr>
<th style="width:150px;">',L('Module'),'</th>
<td>',implode(', ',$arrFiles),'</td>
</tr>
<tr>
<th>',L('Search'),'</th>
<td><input required type="text" id="id" name="id" size="12" maxlength="24" value="" />&nbsp;',L('Module_name'),'</td>
</tr>
<tr>
<th>&nbsp;</th>
<td><input type="hidden" name="a" value="',$a,'" /><input type="submit" id="ok" name="ok" value="',$L['Ok'],'" /></td>
</tr>
</table>
</form>
<script type="text/javascript">doc.getElementById("id").focus();</script>
';

// HTML END

include APP.'_adm_inc_ft.php';