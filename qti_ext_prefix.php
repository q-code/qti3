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

$oVIP->selfurl = 'qti_ext_prefix.php';
$oVIP->exiturl = 'qti_adm_sections.php';
$oVIP->selfname = $L['Item_prefix'];
$oVIP->exitname = $L['Sections'];

// --------
// HTML START
// --------

$oHtml->scripts=array();

include APP.'_adm_inc_hd.php';

echo '<table>
<tr>
<td style="width:250px;padding-right:15px;vertical-align:top">
<div style="margin-top:10px;border:1px solid #AAAAAA;padding:4px">
<p class="small" style="margin:0">To create a new serie, upload your images in each skin subfolders.<br /><br />
Images filenames must be <b>ico_prefix_{serie}_{id}.gif</b> where {serie} is a character and {id} a two digit value.<br /><br />
Example: ico_prefix_e_01.gif.<br /><br />
To give names to your serie and to your icons, edit the file qti_icon.php in each language subfolders.<br /><br />
The id 00 cannot be created: 00, meaning "no prefix", is automatically available in each serie.</p>
</div>
</td>
<td>
';

// Browse image file

foreach($L['Prefix_serie'] as $strKey=>$strName)
{
echo '<table>
<tr>
<td style="width:200px; border-bottom:solid 1px #aaaaaa; vertical-align:top"><h2>',$strName,'</h2></td>
<td style=" border-bottom:solid 1px #aaaaaa">
';
echo '<table>
<tr><td style="width:50px;">#</td><td style="width:50px;">Icon</td><td>Name</td></tr>
';
for ($i=1;$i<10;++$i)
{
  if ( file_exists($_SESSION[QT]['skin_dir'].'/ico_prefix_'.$strKey.'_0'.$i.'.gif') )
  {
  echo '<tr><td>',$strKey,' 0',$i,'</td><td><img src="',$_SESSION[QT]['skin_dir'],'/ico_prefix_',$strKey,'_0',$i,'.gif" /></td><td>',$L['Ico_prefix'][$strKey.'_0'.$i],'</td></tr>',PHP_EOL;
  }
}
echo '
</table>
</td>
</tr>
</table>
';
}

echo '
</td>
</tr>
</table>';

// HTML END

include APP.'_adm_inc_ft.php';