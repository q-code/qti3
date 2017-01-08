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
if ( sUser::Role()!='A' ) die(Error(13));
require 'bin/class/qti_class_dom.php';
include Translate(APP.'_adm.php');

// INITIALISE

$d = -1; QThttpvar('d','int'); if ( $d<0 ) die('Missing argument d');

$oDOM = new cDomain($d);

$oVIP->selfurl = 'qti_adm_domain.php';
$oVIP->selfuri = 'qti_adm_domain.php?d='.$d;
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br />'.$L['Domain_upd'];
$oVIP->exiturl = 'qti_adm_sections.php';
$oVIP->exitname = '<i class="fa fa-chevron-circle-left fa-lg"></i> '.$L['Sections'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $str = trim($_POST['title']);
  if ( empty($str) ) { $str='Untitled'; $error = $L['Title'].' '.L('invalid'); }

  // Save

  if ( empty($error) )
  {
    $oDOM->Rename($str);

    cLang::Delete('domain','d'.$d);
    foreach($_POST as $key=>$str)
    {
      if ( substr($key,0,1)=='T' && !empty($str) ) cLang::Add('domain',substr($key,1),'d'.$d,$str);
    }

    // Exit
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  }
}

// --------
// HTML START
// --------

include 'qti_adm_inc_hd.php';

echo '
<form method="post" action="',$oVIP->selfuri,'">
<table class="t-data horiz">
<tr>
<th>',$L['Title'],'</th>
<td><input required type="text" id="title" name="title" size="32" maxlength="64" value="',QTstrh($oDOM->title),'" onchange="bEdited=true;" />',(strstr($str,'&amp;') ?  ' <span class="small">'.$oDOM->title.'</span>' : ''),'</td>
</tr>
';
echo '<tr>
<th>',$L['Translations'],'*</th>
<td>
<table>';
$arrTrans = cLang::Get('domain','*','d'.$d);
include 'bin/qti_lang.php'; // this creates $arrLang
foreach($arrLang as $strIso=>$arr)
{
  $str = empty($arrTrans[$strIso]) ? '' : $arrTrans[$strIso];
  echo '
  <tr>
  <td style="width:30px"><span title="',$arr[1],'">',$arr[0],'</span></td>
  <td><input class="small" title="',$L['Domain'],' (',$strIso,')" type="text" id="T',$strIso,'" name="T',$strIso,'" size="32" maxlength="64" value="',QTstrh($str,64),'" onchange="bEdited=true;" /></td>
  </tr>
  ';
}

echo '</table>
</td>
<tr>
<td class="blanko" colspan="2">* <span class="small">',sprintf($L['E_no_translation'],QTstrh($oDOM->title)),'</span></td>
</tr>

</tr>
</table>
<p class="submit"><input type="submit" id="ok" name="ok" value="',L('Save'),'" /><input type="hidden" name="d" value="',$d,'" /></p>
</form>
<p id="page-ft"><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>
';

// HTML END

include 'qti_adm_inc_ft.php';