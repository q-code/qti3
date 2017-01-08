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

$strVersion='v2.5';
$oVIP->selfurl = 'qtim_export_install.php';
$oVIP->selfname = 'Installation module EXPORT '.$strVersion;

$bStep1 = true;
$bStepZ = true;

// STEP 1

$strFile = 'qtim_export_uninstall.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
$strFile = 'qtim_export_adm.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br />This module cannot be used.';
if ( !empty($error) ) $bStep1 = false;

// STEP Z
if ( empty($error) )
{
  $oDB->Exec('DELETE FROM '.TABSETTING.' WHERE param="module_export" OR param="m_export_conf"');
  $oDB->Exec('INSERT INTO '.TABSETTING.' (param,setting,loaded) VALUES ("module_export","Export","0")');
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

// --------
// Html end
// --------
