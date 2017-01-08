<?php

// QuickTicket 3.0 build:20160703

ob_start();

$bShowtoc = false;
if ( substr($oVIP->selfurl,0,7)=='qti_adm' || substr($oVIP->selfurl,0,5)=='qtim_' ) $bShowtoc=true;

$oHtml->links['icon'] = '<link rel="shortcut icon" href="admin/qti_icon.ico" />';
$oHtml->links['cssBase'] = '<link rel="stylesheet" type="text/css" href="admin/qt_base.css" />'; // attention qt_base
unset($oHtml->links['cssLayout']);
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="admin/qti_main.css" />';
$oHtml->scripts[] = '<script type="text/javascript">var e0 = '.(isset($L['E_editing']) ? '"'.$L['E_editing'].'"' : '0').';</script>';

// Page message

if ( !empty($_SESSION['pagedialog']) )
{
  $oHtml->scripts_jq[] = '$(function() {
    var dlg = document.getElementById("pagedialog");
    if ( dlg )
    {
      var dlgico = document.getElementById("pagedialog-ico");
      var dlgtxt = document.getElementById("pagedialog-txt");
      if ( dlgico ) dlgico.innerHTML = \''.cMsg::getTypeIcon().'\';
      var str = \''.cMsg::getText().'\';
      if ( dlgtxt ) { dlgtxt.innerHTML = str; } else { dlg.innerHTML= str; }
      $("#pagedialog").fadeIn(500).delay(2000).fadeOut(800);
    }
  });
  ';
  cMsg::Reset();
}

echo $oHtml->Head();
echo $oHtml->Body(array('onload'=>(isset($strBodyAddOnload) ? $strBodyAddOnload : null),'onunload'=>(isset($strBodyAddOnunload) ? $strBodyAddOnunload : null)));

echo '<div class="pg-admin">'.PHP_EOL;

if ( file_exists(Translate($oVIP->selfurl.'.txt')) )
{
  echo '<div class="hlp-cnt"><div class="hlp-box">';
  echo '<div class="hlp-head">',$L['Help'],'</div>';
  echo '<div class="hlp-body"><span id="helparea">';
  include Translate($oVIP->selfurl.'.txt');
  echo '</span></div></div></div>';
}

echo '
<div id="banner"><img id="logo" src="admin/'.APP.'_logo.gif" style="border-width:0" alt="QuickTicket" title="QuickTicket" /></div>

<!-- MENU/PAGE -->
<table class="pg-layout">
<tr>
<td class="',($bShowtoc ? 'pg-admin-menu' : 'hidden'),'">
';

if ( $bShowtoc )
{

echo '
<!-- TOC -->
<div id="menu">
';

$arrLangMenu = array();
if ( file_exists('bin/'.APP.'_lang.php') )
{
  include 'bin/'.APP.'_lang.php';
  $strURI = GetURI('lx');
  foreach($arrLang as $strKey=>$arrDef)
  {
  $arrLangMenu[] = '<a href="'.Href().'?'.$strURI.'&amp;lx='.$strKey.'" title="'.$arrDef[1].'" onclick="return qtEdited(bEdited,e0);">'.$arrDef[0].'</a>';
  }
}
else
{
  $arrLangMenu[] = '<span class="small">missing file:bin/qti_lang.php</span>';
}

echo '<p id="menulang">',implode(' &middot; ',$arrLangMenu),'</p>
';

$str = 'return qtEdited(bEdited,e0);';
echo '
<div class="group">
<p class="group">',L('Info'),'</p>
<a class="item'.($oVIP->selfurl==APP.'_adm_index.php' ? ' actif' : '').'" href="'.APP.'_adm_index.php" onclick="'.($oVIP->selfurl==APP.'_adm_index.php' ? 'return false;' : $str).'">',$L['Adm_status'],'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_site.php' ? ' actif' : '').'" href="'.APP.'_adm_site.php" onclick="'.($oVIP->selfurl==APP.'_adm_site.php' ? 'return false;' : $str).'">',$L['Adm_general'],'</a>
</div>
<div class="group">
<p class="group">',L('Settings'),'</p>
<a class="item'.($oVIP->selfurl==APP.'_adm_region.php' ? ' actif' : '').'" href="'.APP.'_adm_region.php" onclick="'.($oVIP->selfurl==APP.'_adm_region.php' ? 'return false;' : $str).'">',$L['Adm_region'],'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_skin.php' ? ' actif' : '').'" href="'.APP.'_adm_skin.php" onclick="'.($oVIP->selfurl==APP.'_adm_skin.php' ? 'return false;' : $str).'">',$L['Adm_layout'],'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_secu.php' ? ' actif' : '').'" href="'.APP.'_adm_secu.php" onclick="'.($oVIP->selfurl==APP.'_adm_secu.php' ? 'return false;' : $str).'">',L('Security'),'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_sse.php' ? ' actif' : '').'" href="'.APP.'_adm_sse.php" onclick="'.($oVIP->selfurl==APP.'_adm_sse.php' ? 'return false;' : $str).'">SSE</a>
</div>
<div class="group">
<p class="group">',L('Adm_content'),'</p>
<a class="item'.($oVIP->selfurl==APP.'_adm_sections.php' ? ' actif' : '').'" href="'.APP.'_adm_sections.php" onclick="'.($oVIP->selfurl==APP.'_adm_sections.php' ? 'return false;' : $str).'">',$L['Sections'],'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_topic.php' ? ' actif' : '').'" href="'.APP.'_adm_topic.php" onclick="'.($oVIP->selfurl==APP.'_adm_topic.php' ? 'return false;' : $str).'">',$L['Items'],'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_statuses.php' ? ' actif' : '').'" href="'.APP.'_adm_statuses.php" onclick="'.($oVIP->selfurl==APP.'_adm_statuses.php' ? 'return false;' : $str).'">',$L['Statuses'],'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_tags.php' ? ' actif' : '').'" href="'.APP.'_adm_tags.php" onclick="'.($oVIP->selfurl==APP.'_adm_tags.php' ? 'return false;' : $str).'">',$L['Tags'],'</a>
<a class="item'.($oVIP->selfurl==APP.'_adm_users.php' ? ' actif' : '').'" href="'.APP.'_adm_users.php" onclick="'.($oVIP->selfurl==APP.'_adm_users.php' ? 'return false;' : $str).'">',$L['Users'],'</a>
</div>
<div class="group">
<p class="group">',L('Adm_modules'),'</p>
';

// search modules
$arrModules = GetSettings('param LIKE "module%"');
if ( count($arrModules)>0 )
{
  foreach($arrModules as $strKey=>$strValue)
  {
  $strKey = str_replace('module_','',$strKey);
  echo '<a class="item'.($oVIP->selfurl=='qtim_'.$strKey.'_adm.php' ? ' actif' : '').'" href="qtim_',$strKey,'_adm.php" onclick="return qtEdited(bEdited,e0);">',$strValue,'</a>',PHP_EOL;
  }
}
echo '<p class="item"><a href="'.APP.'_adm_module.php?a=add" onclick="return qtEdited(bEdited,e0);">[',L('Add'),']</a> &middot; <a href="'.APP.'_adm_module.php?a=rem" onclick="return warningedited(bEdited,e0);">[',$L['Remove'],']</a></p>
</div>
<div id="footer"><a id="exit" href="'.APP.'_index.php" target="_top" onclick="return qtEdited(bEdited,e0);">',L('Exit'),'</a></div>
</div>
';
}

echo '</td>
';
// --------------
// END TABLE OF CONTENT
// --------------

echo '
<td>
<!-- END MENU/PAGE -->
';

echo cHtml::Page(START);

// Title (and help frame)

echo '<div style="width:300px; margin-bottom:20px"><h1>',$oVIP->selfname,'</h1>';
if ( isset($strPageversion) ) echo '<p class="small">',$strPageversion,'</p>';
if ( !empty($error) ) echo '<p id="infomessage" class="error">',$error,'</p>';
if ( empty($error) && !empty($warning) ) echo '<p id="warningmessage" class="warning">',$warning,'</p>';
if ( empty($error) && isset($strInfo) ) echo '<p id="infomessage" style="color:#007F11"><b>',$strInfo,'</b></p>';
echo '</div>
';
