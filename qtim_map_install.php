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
if ( sUser::Role()!='A' ) die($L['E_admin']);

// INITIALISE

$strVersion='v3.0';
$oVIP->selfurl = 'qtim_map_install.php';
$oVIP->selfname = 'Installation module MAP '.$strVersion;

$bStep1 = true;
$bStepZ = true;

// STEP 1

$strFile = 'qtim_map_uninstall.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
$strFile = 'qtim_map_adm.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
$strFile = 'qtim_map/config_map.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
if ( !empty($error) ) $bStep1 = false;

// STEP Z
if ( empty($error) )
{
  $oDB->Exec('DELETE FROM '.TABSETTING.' WHERE param="module_map" OR param="m_map_gkey" OR param="m_map_gcenter" OR param="m_map_gzoom" OR param="m_map_gbuttons" OR param="m_map_gsymbol"');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("module_map","Map","0")');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("m_map_gkey","","0")');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("m_map_gcenter","50.8468142558,4.35238838196","0")');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("m_map_gzoom","10","0")');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("m_map_gbuttons","P10100","0")');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("m_map_gfind","Brussels, Belgium","0")');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("m_map_gsymbol","0","0")');
  $_SESSION[QT]['module_map'] = 'Map';
  $_SESSION[QT]['m_map_gkey'] = '';
  $_SESSION[QT]['m_map_gcenter'] = '50.8468142558,4.35238838196';
  $_SESSION[QT]['m_map_gzoom'] = '10';
  $_SESSION[QT]['m_map_gbuttons'] = 'P10100';
  $_SESSION[QT]['m_map_gfind'] = 'Brussels, Belgium';
  $_SESSION[QT]['m_map_gsymbol'] = '0'; // Default symbol
}

// --------
// Html start
// --------
include APP.'_adm_inc_hd.php';

echo '<h2>Checking components</h2>';
if ( !$bStep1 )
{
  echo '<p class="error">',$error,'</p>';
  include APP.'_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Database settings</h2>';
if ( !$bStepZ )
{
  echo '<p class="error">',$error,'</p>';
  include APP.'_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Installation completed</h2>';

if ( $_SESSION[QT]['version']=='1.8' || $_SESSION[QT]['version']=='1.9' )
{
  echo '<p class="error">Your database version is <2.0. We recommand you to upgrade to 3.0 (use the installation wizard of QuickTicket).</p>';
}