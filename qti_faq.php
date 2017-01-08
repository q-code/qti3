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

// INITIALISE

$oVIP->selfurl = 'qti_faq.php';
$oVIP->selfname = $L['FAQ'];

// --------
// HTML START
// --------

$oHtml->scripts = array();
include 'qti_inc_hd.php';

echo '<a id="top"></a><h1>',$oVIP->selfname,'</h1>',PHP_EOL;
include Translate(APP.'_faq.php');

// HTML END

include 'qti_inc_ft.php';