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
if ( !isset($_SESSION[QT]['m_rss']) ) die(Error(10));

$arrConf = explode(' ',$_SESSION[QT]['m_rss_conf']);
$strUser = $arrConf[0];
$strForm = $arrConf[1];
$strSize = $arrConf[2];

if ( !sUser::CanView(($strUser=='V' ? 'V' : 'U')) ) die('Access denied');

// INITIALISE

include Translate('qtim_rss.php');

$oVIP->selfurl = 'qtim_rss.php';
$oVIP->selfname = $L['rss']['Rss'];

$strRssUrl = $_SESSION[QT]['site_url'].'/rss';

$arrSections = SectionsByDomain('U'); // Get all sections at once (grouped by domain)

// --------
// HTML START
// --------

foreach($arrSections as $d=>$arr)
{
  foreach($arr as $s=>$arrSection) $oHtml->links[] = '<link rel="alternate" type="application/rss+xml" title="'.QTconv($arrSection['title'],'1').'" href="'.$strRssUrl.'/qti_'.$strForm.'_'.$arrSection['id'].'.xml"/>';
}

$oVIP->arrJava=null;
include 'qti_inc_hd.php';

  // END IF NO DOMAIN
  if (count($arrSections)==0)
  {
  echo '<h2>',$oVIP->selfname,'</h2>',PHP_EOL;
  echo '<p>',$L['rss']['E_nosection'],'</p>';
  include 'qti_inc_ft.php';
  exit;
  }

// TITLE & version
$str = '2.0';
if ( $strForm=='atom' ) $str='Atom';

echo '<h2>',$oVIP->selfname,'</h2>
<p>RSS ',$str,'</p>
';

// --------
// DOMAIN / SECTIONS
// --------

$intSec = 0;

foreach(sMem::Get('sys_domains') as $intDomid=>$strDomain)
{
  if ( isset($arrSections[$intDomid]) ) {
  if ( count($arrSections[$intDomid])>0 ) {

    echo '<table class="t-sec">',PHP_EOL;
    echo '<tr>',PHP_EOL;
    echo '<th>&nbsp;</th>';
    echo '<th>',$strDomain,'</th>';
    echo '<th>',L('Open'),'</th>';
    echo '<th>Url</th>';
    echo '</tr>',PHP_EOL;

    $strAlt='r1';
		foreach($arrSections[$intDomid] as $s=>$arrSection)
		{
			++$intSec;
			$oSEC = new cSection($arrSection);
      echo '<tr class="t-sec ',$strAlt,'">',PHP_EOL;
      echo '<td class="c-icon">'.AsImg($_SESSION[QT]['skin_dir'].'/ico_section_'.$oSEC->type.'_'.$oSEC->status.'.gif','F',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'ico ico-s').'</td>';
      echo '<td style="width:200px"><span class="section">',$oSEC->name,'</span><br/><span class="sectiondesc">',$oSEC->descr,'</span></td>';
      echo '<td style="width:50px"><a class="rss" href="',$strRssUrl,'/qti_',$strForm,'_',$s,'.xml"><i class="fa fa-rss-square fa-2x"/></i></a></td>';
      echo '<td>',$strRssUrl,'/qti_',$strForm,'_',$s,'.xml</td>';
      echo '</tr>',PHP_EOL;
      if ( $strAlt==='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
    }

		echo '</table>',PHP_EOL;
		echo '<div class="separator"></div>',PHP_EOL;

  }}
}

if (  sUser::IsStaff() )
{
if ( $intSec < sMem::Get('sys_sections') ) echo '<p class="disabled"><i class="fa fa-exclamation-triangle fa-lg"></i>  For security reason, a hidden section cannot provide RSS feeds.</p>';
}

// No public section

if ( $intSec==0 )
{
  echo '<p>',( sUser::Role()==='V' ? $L['E_no_public_section'] : $L['E_no_visible_section'] ),'</p>';
}

// HTML END

if (isset($oSEC)) unset($oSEC);
include 'qti_inc_ft.php';