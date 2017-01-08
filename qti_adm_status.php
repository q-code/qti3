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

$id = ''; QThttpvar('id','str'); if ( empty($id) ) die('Missing status id...');

$oVIP->selfurl = 'qti_adm_status.php';
$oVIP->selfuri = 'qti_adm_status.php?id='.$id;
$oVIP->exiturl = 'qti_adm_statuses.php';
$oVIP->selfname = '<span class="upper">'.L('Adm_content').'</span><br />'.L('Statuses');
$oVIP->exitname = '<i class="fa fa-chevron-circle-left fa-lg"></i> '.L('Statuses');

$arrS = sMem::Get('sys_statuses');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check id
  if (!preg_match('/[A-Z]/',$id)) $error="Id $id ".L('invalid').' (B-Y)'; //A and Z can be edited (hidden input)


  // change id
  if ( empty($error) )
  {
    if ( $_POST['oldid']!=$id )
    {
    $error = sStatus::ChangeId($_POST['oldid'],$id);
    }
  }

  // check name
  if ( empty($error) )
  {
    $name = strip_tags(trim($_POST['name']));
    if ( empty($name) ) { $name='Unknown'; $error='Status name '.' '.L('invalid'); }
  }

  // check unic name
  if ( empty($error) )
  {
    if ( $_POST['oldname']!=$name )
    {
    $oDB->Query('SELECT count(id) as countid FROM '.TABSTATUS.' WHERE name="'.QTstrd($name,24).'"');
    $row = $oDB->Getrow();
    if ($row['countid']>0) $warning = 'Name ['.$name.'] '.$L['Already_used'];
    }
  }

  // check color
  if ( empty($error) )
  {
    $color = strip_tags(trim($_POST['color']));
    if ( $color==='#' ) $color='';
  }

  // check icon
  if ( empty($error) )
  {
    $icon = strip_tags(trim($_POST['icon']));
    $icon = htmlspecialchars($icon,ENT_QUOTES);
    if ( $icon!=trim($_POST['icon']) ) $error = $L['Icon'].' '.L('invalid');
  }

  // check notified
  if ( empty($error) )
  {
    $lst_mail = array();
    if (isset($_POST['mailto'])) $lst_mail = $_POST['mailto'];
    $lst_others = QTexplodeStr(',; ',$_POST['others']);
    $lst_saved = array();

    $i=array_search('U',$lst_mail);
    if ( ($i===false) || (is_null($i)) ) { $bolUser=false; } else { $bolUser=true; $lst_saved[] = 'U'; }
    $i=array_search('MA',$lst_mail);
    if ( ($i===false) || (is_null($i)) ) { $bolOper=false; } else { $bolOper=true; $lst_saved[] = 'MA'; }
    $i=array_search('MF',$lst_mail);
    if ( ($i===false) || (is_null($i)) ) { $bolMode=false; } else { $bolMode=true; $lst_saved[] = 'MF'; }
    $i=array_search('1',$lst_mail);
    if ( ($i===false) || (is_null($i)) ) { $bolAdmi=false; } else { $bolAdmi=true; $lst_saved[] = '1'; }
    $lst_saved = array_merge($lst_saved,$lst_others);
    $lst_saved = array_unique($lst_saved);
    $saved = implode(",",$lst_saved);
  }

  // save

  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABSTATUS.' SET name="'.QTstrd($name,24).'",color="'.$color.'",mailto="'.$saved.'",icon="'.$icon.'" WHERE id="'.$id.'"');

    //  save translation

    cLang::Delete(array('status','statusdesc'),$id);
    foreach($_POST as $key=>$str)
    {
      if ( substr($key,0,1)=='T' && !empty($str) ) cLang::Add('status',substr($key,1),$id,$_POST[$key]);
      if ( substr($key,0,1)=='D' && !empty($str) ) cLang::Add('statusdesc',substr($key,1),$id,$_POST[$key]);
    }

    //exit
    sMem::Clear('sys_statuses');
    $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
    $oHtml->Redirect($oVIP->exiturl);
  }
  else
  {
    $id = $_POST['oldid'];
  }
}

// --------
// HTML START
// --------

include APP.'_adm_inc_hd.php';

// ANALYSE NOTIFY

$lst_mail = explode(',',$arrS[$id]['mailto']);
$lst_mail = array_unique($lst_mail);
$others = '';

$i=array_search('U',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolUser=false; } else { unset($lst_mail[$i]); $bolUser=true; }
$i=array_search('MA',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolOper=false; } else { unset($lst_mail[$i]); $bolOper=true; }
$i=array_search('MF',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolMode=false; } else { unset($lst_mail[$i]); $bolMode=true; }
$i=array_search('1',$lst_mail);
if ( ($i===false) || (is_null($i)) ) { $bolAdmi=false; } else { unset($lst_mail[$i]); $bolAdmi=true; }

$others = implode(',',$lst_mail);

// DISPLAY RESULT

echo '<table>
<tr>
<td style="width:25px">',$id,'</td>
<td style="width:30px">',AsImg($_SESSION[QT]['skin_dir'].'/'.$arrS[$id]['icon'],'-',$arrS[$id]['statusdesc'],'ico'),'</td>
<td style="width:100px;padding:3px 10px 3px 10px;text-align:center;background-color:',(empty($arrS[$id]['color']) ? 'transparent' : $arrS[$id]['color']),'; border-style:solid; border-color:#dddddd; border-width:1px">',$arrS[$id]['statusname'],'</td>
<td>&nbsp;</td>
</tr>
</table>
<br />',PHP_EOL;
echo '<form method="POST" action="',$oVIP->selfuri,'">',PHP_EOL;
echo '<h2 class="subtitle">',$L['Definition'],'</h2>',PHP_EOL;
echo '<table class="t-data horiz">',PHP_EOL;
echo '<tr>';
echo '<th style="width:150px;">Id</th>';
echo '<td>';
if ( ($id=='A') || ($id=='Z') )
{
  echo $id.'&nbsp;<input type="hidden" name="id" value="',$id,'" />';
}
else
{
  echo '<input type="text" id="id" name="id" size="1" maxlength="1" value="',$id,'" />';
}
echo '</td>';

echo '<tr>';
echo '<th><label for="name">',L('Name'),'</label></th>';
echo '<td><input type="text" id="name" name="name" size="24" maxlength="24" value="',$arrS[$id]['name'],'" style="background-color:#FFFF99" /></td>';
echo '</tr>',PHP_EOL;
echo '<tr>';
echo '<th><label for="icon">Icon</label></th>';
echo '<td><input type="text" id="icon" name="icon" size="24" maxlength="64" value="',$arrS[$id]['icon'],'" />&nbsp;',AsImg($_SESSION[QT]['skin_dir'].'/'.$arrS[$id]['icon'],'-',$arrS[$id]['statusdesc'],'ico'),'&nbsp;&nbsp;<a href="qti_ext_statusico.php" target="_blank">show icons</a></td>';
echo '</tr>',PHP_EOL;
echo '<tr>';
echo '<th><label for="color">',L('Status_background'),'</label></th>';
echo '<td>
<input type="text" class="colortext" id="color" name="color" size="10" maxlength="24" value="',(empty($arrS[$id]['color']) ? '#' : $arrS[$id]['color']),'" onchange="bEdited=true;" />
<input type="color" id="colorpicker" value="'.(empty($arrS[$id]['color']) ? '#ffffff' : $arrS[$id]['color']).'" onchange="document.getElementById(\'color\').value=this.value;"/>
&nbsp;<span class="small">',$L['H_Status_background'],'</span>
</td>';
echo '</tr>',PHP_EOL,'</table>',PHP_EOL;

echo '<h2 class="subtitle">',L('Options'),'</h2>',PHP_EOL;
echo '<table class="t-data horiz">',PHP_EOL;
echo '<tr>
<th>',L('Notification'),'</span></th>
<td>
  <table>
  <tr>
  <td width="200">
  <input id="mailtoU" type="checkbox" name="mailto[]" value="U"',($bolUser ? QCHE : ''),'/> <label for="mailtoU">',$L['Role_U'],'</label><br />
  <input id="mailtoMA" type="checkbox" name="mailto[]" value="MA"',($bolOper ? QCHE : ''),'/> <label for="mailtoMA">',L('Actor'),'</label><br />
  <input id="mailtoMF" type="checkbox" name="mailto[]" value="MF"',($bolMode ? QCHE : ''),'/> <label for="mailtoMF">',L('Coordinator'),'</label><br />
  <input id="mailto1" type="checkbox" name="mailto[]" value="1"',($bolAdmi ? QCHE : ''),'/> <label for="mailto1">',$L['Role_A'],'</label><br /></td>
  <td>',L('Notify_also'),':<br />
  <textarea id="others" name="others" cols="40" rows="2">',$others,'</textarea><br />
  <span class="small">',$L['H_Status_notify'],'</span>
  </td>
  </tr>
  </table>
</td>
';
echo '</tr>',PHP_EOL,'</table>',PHP_EOL;

echo '<h2 class="subtitle">',L('Translations'),'</h2>',PHP_EOL;

$arrTrans = cLang::Get('status','*',$id);
$arrDescTrans = cLang::Get('statusdesc','*',$id);
include 'bin/qti_lang.php'; // this creates $arrLang

echo '<table class="t-data horiz">
<tr><td colspan="2" style="background-color:white;text-align:right"><p class="small" style="margin:0">',sprintf($L['E_no_translation'],ucfirst(str_replace('_',' ',$arrS[$id]['name']))),'</p></td></tr>
<tr>
<th>',L('Name'),'</th>
<td>
<table>
';
foreach($arrLang as $strIso=>$arr)
{
  echo '
  <tr>
  <td style="width:30px"><span title="',$arr[1],'">',$arr[0],'</span></td>
  <td>
  <input class="small" title="',$L['Status'],' (',$strIso,')" type="text" id="T',$strIso,'" name="T',$strIso,'" size="20" maxlength="64" value="',(isset($arrTrans[$strIso]) ? $arrTrans[$strIso] : ''),'" />
  </td>
  </tr>
  ';
}
echo '</table>
</td>
</tr>
<tr>
<th>',L('Description'),'</th>
<td>
<table>
';
foreach($arrLang as $strIso=>$arr)
{
  echo '
  <tr>
  <td style="width:30px"><span title="',$arr[1],'">',$arr[0],'</span></td>
  <td>
  <input class="small" title="',$L['Description'],' (',$strIso,')" type="text" id="D',$strIso,'" name="D',$strIso,'" size="55" maxlength="255" value="',(isset($arrDescTrans[$strIso]) ? $arrDescTrans[$strIso] : ''),'" />
  </td>
  </tr>
  ';
}
echo '</table>
</td>
</tr>
</table>
';

echo '<p class="submit">
<input type="hidden" name="oldid" value="',$id,'" />
<input type="hidden" name="oldname" value="',$arrS[$id]['name'],'" />
<input type="submit" name="ok" value="',L('Save'),'"/>
</p>
</form>
<p id="page-ft"><a href="',$oVIP->exiturl,'" onclick="return qtEdited(bEdited,\'',$L['E_editing'],'\');">',$oVIP->exitname,'</a></p>
';

// HTML END

include APP.'_adm_inc_ft.php';