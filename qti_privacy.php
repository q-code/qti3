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
* @package    qt-cute.org
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2015 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';

// INITIALISE

$oVIP->selfurl = APP.'_privacy.php';
$oVIP->selfname = L('Legal');

// --------
// HTML START
// --------

$oHtml->DropJquery();
$oHtml->scripts = array();

include APP.'_inc_hd.php';

$oHtml->Msgbox($oVIP->selfname);

include Translate('sys_rules.txt',false);

$oHtml->Msgbox(END);

$oHtml->Msgbox('About QuickTicket');

$strFile = Translate('sys_about.php');
if ( file_exists($strFile) ) { include $strFile; } else { echo 'Missing file:<br />'.$strFile; }

echo '<p>
<img src="bin/css/vhtml5.png" alt="HTML 5" height="64" width="64"/>&nbsp;
<a href="http://jigsaw.w3.org/css-validator/"><img src="bin/css/vcss.png" alt="Valid CSS" height="31" width="88"/></a>&nbsp;
<a href="http://www.w3.org/WAI/WCAG1AAA-Conformance" title="Explanation of Level Triple-A Conformance"><img height="31" width="88" src="bin/css/wcag1aaa.png" alt="Level Triple-A conformance icon, W3C-WAI Web Content Accessibility Guidelines 1.0"/></a>
';

// ----------
// module rss
if ( UseModule('rss') )
{
echo '<img height="31" width="88" src="admin/valid-rss-rogers.png" alt="[Valid RSS]" title="Valid RSS feed" /> 
<img height="31" width="88" src="admin/valid-atom.png" alt="[Valid RSS]" title="Valid RSS feed" />
';
}
// ----------

echo '</p>
';

$oHtml->Msgbox(END);

// HTML END

include APP.'_inc_ft.php';