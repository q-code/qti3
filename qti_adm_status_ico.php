<?php

// QuickTicket 2.4 build:20091114

session_start();
require 'bin/init.php';
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qti_adm_status_ico.php';
$oVIP->exiturl = 'qti_adm_statuses.php';
$oVIP->selfname = 'Icons';
$oVIP->exitname = $L['Statuses'];

$arrFiles=array();
$arrStatuses=array();

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';
include 'qti_adm_p_title.php';

// Browse image file

$intHandle = opendir($_SESSION[QT]['skin_dir']);

$i=0;
while (false !== ($file = readdir($intHandle)))
{
  $file=strtolower($file);
  if ( $file!='.' && $file!='..' ) {
    if ( substr($file,0,6)=='status' )
    {
    $arrStatuses[] = $file;
    }
    else
    {
    if ( substr($file,0,3)!='bg_' && substr($file,0,10)!='background' ) $arrFiles[] = $file;
    }
    ++$i;
  }
}
closedir($intHandle);
sort($arrStatuses);
sort($arrFiles);

echo $_SESSION[QT]['skin_dir'],', ',$i,' files<br/><br/>';

echo '
<table>
<tr>
<td style="width:250px;vertical-align:top">
';

echo '<table style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>',N;
foreach($arrStatuses as $key=>$val)
{
  if (strtolower(substr($val,-4,4))=='.gif')
  {
  echo '<tr><td style="padding-left:4px"><img src="',$_SESSION[QT]['skin_dir'],'/',$val,'"/></td><td class="td_icon">',$val,'</td></tr>',N;
  }
}
echo '</table>
';
echo '
</td>
<td style="width:20px;">
<td style="width:250px;vertical-align:top">
';
echo '<table style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>',N;
foreach($arrFiles as $key=>$val)
{
  if (strtolower(substr($val,-4,4))=='.gif')
  {
  echo '<tr><td style="padding-left:4px"><img src="',$_SESSION[QT]['skin_dir'],'/',$val,'"/></td><td class="td_icon">',$val,'</td></tr>',N;
  }
}
echo '</table>
';
echo '
</td>
<td>&nbsp;</td>
</tr>
</table>
';

// HTML END

include APP.'_adm_inc_ft.php';

?>