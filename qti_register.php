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
* @copyright  2016 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
if ( $_SESSION[QT]['board_offline']=='1' ) { $oHtml->PageMsg(99); return; }
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_profile.css" />';
$oHtml->scripts = array();

// INITIALISE

include GetLang().'qti_reg.php';

$c='2'; // coppa default
// QThttpvar('c','str');

$oVIP->selfurl = 'qti_register.php';
$oVIP->selfname = L('Register');
$oVIP->exitname = L('Register');

// --------
// EXECUTE FORM (the not-agreed message can also be triggered by a timeout from the registration form)
// --------

if ( isset($_POST['ok']) || isset($_GET['timeout']) )
{
  if ( !isset($_POST['agreed']) )
  {
    include APP.'_inc_hd.php';

    $oHtml->MsgBox($oVIP->selfname);
    $strFile=GetLang().'sys_not_agree.txt';
    if ( file_exists($strFile) ) { include $strFile; } else { echo 'Rules not agreed...'; }
    echo '<p><a href="',Href(),'?c='.$c.'">',$L['Register'],'</a></p>';
    $oHtml->Msgbox(-1);

    include APP.'_inc_ft.php';
    exit;
  }
  $_SESSION[QT]['regv'] = time()+120; // validity of the aggrement 2 minutes (will be extended in the registration form)
  $oHtml->Redirect(APP.'_form_reg.php?c='.$c,$L['Register']);
}

// --------
// HTML START
// --------

include 'qti_inc_hd.php';

echo '<div class="scrollmessage">
';

$strFile = GetLang().'sys_rules.txt';
if ( file_exists($strFile) ) { include $strFile; } else { echo "Missing file:<br />$strFile"; }

echo '
</div>
';

echo '<form method="post" action="',Href(),'">
';

$oHtml->MsgBox($oVIP->selfname);
  echo '<p><input type="checkbox" id="agreed" name="agreed"/>&nbsp;<label for="agreed">',$L['Agree'],'</label></p><p><input type="submit" name="ok" value="',L('Proceed'),'..." /></p>';
$oHtml->Msgbox(-1);

echo '</form>
';

// HTML END

include 'qti_inc_ft.php';