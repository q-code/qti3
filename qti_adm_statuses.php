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
* @version    3 build:20160703
*/

session_start();
require 'bin/init.php';
if ( sUser::Role()!='A' ) die(Error(13));
include Translate(APP.'_adm.php');

// INITIALISE

$oVIP->selfurl = 'qti_adm_statuses.php';
$oVIP->exiturl = 'qti_adm_statuses.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br />'.$L['Statuses'];
$oVIP->exitname = $L['Statuses'];

$arrS = sMem::Get('sys_statuses');

// --------
// SUBMITTED for add
// --------

if ( isset($_POST['ok_add']) )
{
  // Check id, name and duplicate id

  $id = strtoupper(substr($_POST['id'],0,1));
  if ( !preg_match('/[B-Y]/',$id) ) $error="Id $id ".L('invalid')." (B-Y)";
  $name = trim($_POST['name']); if ( empty($name) ) { $name = 'Unknown'; $error = $L['Status'].' '.L('invalid'); }
  if ( array_key_exists($id,$arrS) ) $error = $L['Status'].' id ['.$id.'] '.strtolower($L['Already_used']);

  // Add

  if ( empty($error) )
  {
    $error = sStatus::Add($id,$name,'ico_status0.gif');
  }

  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_insert'] : 'E|'.$error);

  // Exit

  if ( empty($error) )
  {
    $oHtml->Redirect('qti_adm_status.php?id='.$id);
  }
}

// --------
// SUBMITTED for show
// --------

if ( isset($_POST['ok_show']) )
{
    $_SESSION[QT]['show_closed'] = $_POST['show_closed'];
    $oDB->Exec('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_closed'].'" WHERE param="show_closed"');
    // exit
    $strInfo = $L['S_save'];
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
function ValidateForm(theForm)
{
  if (theForm.id.value.length==0) { alert("Id ?"); return false; }
  if (theForm.name.value.length==0) { alert("'.$L['Status'].' ?"); return false; }
  return null;
}
</script>
';
include APP.'_adm_inc_hd.php';

echo '<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
<table class="t-item">
<tr>
<th style="width:30px;text-align:center">Id</th>
<th style="width:30px">&nbsp;</th>
<th>',$L['Status'],'</th>
<th>',$L['Email'],'</th>
<th>',$L['Action'],'</th>
<th style="width:50px">#</th>
</tr>
';

foreach($arrS as $id=>$arrStatus)
{
  echo '<tr class="t-item hover">',PHP_EOL;
  echo '<td class="center">',$id,'</td>',PHP_EOL;
  echo '<td class="center">',AsImg($_SESSION[QT]['skin_dir'].'/'.$arrStatus['icon'],'-',$arrStatus['statusname'],'ico'),'</td>',PHP_EOL;
  echo '<td><a href="qti_adm_status.php?id=',$id,'">',$arrStatus['statusname'],'</a></td>',PHP_EOL;
  echo '<td>',($arrStatus['mailto']!='' ? L('Y') : '<span class="disabled">'.$L['None'].'</span>'),'</td>',PHP_EOL;
  echo '<td><a href="qti_adm_status.php?id=',$id,'">',$L['Edit'],'</a>&nbsp;&middot;&nbsp;';
  if ( ($id=='A') || ($id=='Z') ) { echo '<span class="disabled">',L('Delete'); } else { echo '<a href="qti_adm_change.php?a=status_del&amp;v=',$id,'">',L('Delete'),'</a>'; }
  echo '</td>',PHP_EOL;
  echo '<td',( empty($arrStatus['color']) ? '' : ' style="background-color:'.$arrStatus['color'].'"'),'>&nbsp;</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;
}
echo '
<tr class="tr group hover">
<td><input required type="text" name="id" value="" size="1" maxlength="1" pattern="[A-Za-z]{1}" /></td>
<td>&nbsp;</td>
<td colspan="4"><input required type="text" name="name" value="" size="20" maxlength="24" /> <input class="inline" type="submit" name="ok_add" value="',$L['Add'],'" /></td>
</tr>
</table>
</form>
<br/>
';

echo '<h2 class="subtitle">',$L['Display_options'],'</h2>
<form method="post" action="',$oVIP->selfurl,'">
<table class="t-data horiz">
<tr>
<th style="width:150px"><label for="show_closed">',$L['Show_z'],'</label></th>
<td><select id="show_closed" name="show_closed">
<option value="0"',($_SESSION[QT]['show_closed']=='0' ? QSEL : ''),'>',L('N'),'</option>
<option value="1"',($_SESSION[QT]['show_closed']=='1' ? QSEL : ''),'>',L('Y'),'</option>
</select> <span class="small">',sprintf($L['H_Show_z'],$arrS['Z']['statusname']),'</span></td>
<td><input class="inline" type="submit" name="ok_show" value="',$L['Ok'],'" /></td>
</tr>
</table>
</form>
';

// --------
// HTML END
// --------

include APP.'_adm_inc_ft.php';