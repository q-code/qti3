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

if ( sUser::Role()!='A' ) die(Error(13));

// INITIALISE

$d = empty($_SESSION[QT]['unreplied_days']) ? 10 : (int)$_SESSION[QT]['unreplied_days']; // days
$ok = '';
QThttpvar('d ok','int str');

$oVIP->selfurl = 'qti_adm_topic.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Items'];
$oVIP->exitname = '&laquo; '.$L['Items'];

// --------
// SUBMITTED
// --------

if ( !empty($ok) )
{
  if ( !QTisbetween($d,1,99) ) { $error=$L['Days'].' '.Error(1).' (1-99)'; $d=10; }
  if ( empty($error) && $_SESSION[QT]['unreplied_days']!=$d )
  {
    $_SESSION[QT]['unreplied_days']=$d;
    $oDB->Exec('DELETE FROM '.TABSETTING.' WHERE param="unreplied_days"');
    $oDB->Exec('INSERT INTO '.TABSETTING.' VALUES ("unreplied_days","'.$d.'","1")');
  }
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
function ToggleForms()
{
doc.getElementById("unrepliedoption").style.display = ( doc.getElementById("unrepliedoption").style.display=="none" ? "block" : "none" );
doc.getElementById("toggleforms-arrow").setAttribute("class", (doc.getElementById("unrepliedoption").style.display=="none" ? "fa fa-caret-down" : "fa fa-caret-up"));
}
</script>
';

include APP.'_adm_inc_hd.php';

$arrDomains = sMem::Get('sys_domains');
$arrSections = SectionsByDomain('A');
$arrI = cSection::CountItemsBySection();
$arrR = cSection::CountItemsBySection('replies');
//$arrN = cSection::CountItemsBySection('news');
$arrU = cSection::CountItemsBySection('unreplied',$d);

echo '<p id="page-ft"><a id="toggleforms" href="javascript:void(0)" onclick="ToggleForms(); return false;"><i class="fa fa-gear fa-lg"></i> ',L('Unreplied'),' ',L('option'),'&nbsp;<i id="toggleforms-arrow" class="fa fa-caret-down"></i></a></p>
<div id="unrepliedoption">
<form method="post" action="qti_adm_topic.php">
<p>',L('Unreplied'),': ',L('H_Unreplied'),' <input required type="number" name="d" maxlength="2" value="'.$d.'" min="1" max="99"/> ',L('days'),' <input class="inline" type="submit" name="ok" value="',$L['Ok'],'"/></p>
</form>
</div>
<script type="text/javascript">ToggleForms();</script>
';

echo '<table class="t-sec">
<tr>
<th class="c-section" colspan="2">',$L['Domain'],'/',$L['Section'],'</th>
<th class="c-items">',$L['Items'],'</th>
<th class="c-replies">',$L['Replys'],'</th>
<th class="c-unreplies">',$L['Unreplied'],'</th>
<th class="c-action">',$L['Action'],'</th>
</tr>',PHP_EOL;

foreach($arrDomains as $intDomid=>$strDomtitle)
{

  echo '<tr><td class="c-section group" colspan="6">',$strDomtitle,'</td></tr>',PHP_EOL;

  if ( isset($arrSections[$intDomid]) ) {
  foreach($arrSections[$intDomid] as $s=>$arrSection)
  {
    $oSEC = new cSection($arrSection); //$oSEC = new cSection($s);
    //$intN = isset($arrN[$s]) ? $arrN[$s] : '?';
    $intI= isset($arrI[$s]) ? $arrI[$s] : '?';
    $intR = isset($arrR[$s]) ? $arrR[$s] : '?';
    $intU = isset($arrU[$s]) ? $arrU[$s] : '?';

    echo '<tr>';
    echo '<td class="c-icon">',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'ico i-sec'),'</td>';
    echo '<td class="c-name"><span class="sectionname">',$oSEC->name,'</span><br/><span class="small">',$L['Section_type'][$oSEC->type],($oSEC->status=='1' ? '('.$L['Section_status'][1].')' : ''),'</span></td>';
    echo '<td class="c-items">',$intI,'</td>';
    echo '<td class="c-replies">',$intR,'</td>';
    echo '<td class="c-unreplies">',$intU,'</td>';
    echo '<td class="c-action">';
    if ( $intI>0 )
    {
    echo '<a href="qti_adm_change.php?a=topicmoveall&amp;s=',$s,'&amp;d=',$d,'">',$L['Move'],'</a> &middot; ';
    echo '<a href="qti_adm_change.php?a=topicdeleteall&amp;s=',$s,'&amp;d=',$d,'">',L('Delete'),'</a> &middot; ';
    }
    else
    {
    echo '<span class="disabled">',$L['Move'],'</span> &middot; ';
    echo '<span class="disabled">',L('Delete'),'</span> &middot; ';
    }
    if ( $intU>0 )
    {
    echo '<a href="qti_adm_change.php?a=topicprune&amp;s=',$s,'&amp;d=',$d,'">',$L['Prune'],'</a>';
    }
    else
    {
    echo '<span class="disabled">',$L['Prune'],'</span>';
    }
    echo '</td></tr>',PHP_EOL;
  }}
}
echo '</table>
';

// HTML END

include APP.'_adm_inc_ft.php';