<?php // v3.0 build:20160703

ob_start();

if ( isset($_GET['view']) ) $_SESSION[QT]['viewmode'] = strtolower(substr($_GET['view'],0,1));

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

// check LangMenu condition

$arrLangMenu = array();
if ( $_SESSION[QT]['userlang']=='1' )
{
  if ( file_exists('bin/qti_lang.php') )
  {
    include 'bin/qti_lang.php';
    foreach ($arrLang as $strKey => $arrDef)
    {
    if ( QTiso()===$strKey ) { $str=' class="actif"'; } else { $str=''; }
    $arrLangMenu[] = '<a href="'.Href().'?'.GetURI('lx').'&amp;lx='.$strKey.'"'.(isset($arrDef[1]) ? ' title="'.$arrDef[1].'"' : '').$str.'>'.$arrDef[0].'</a>';
    }
  }
  else
  {
    $arrLangMenu[] = '<span class="missing">missing file:bin/qti_lang.php</span>';
  }
}

if ( sUser::Id()>0 )
{
  if ( empty($_SESSION[QT.'_usr_info']) ) $_SESSION[QT.'_usr_info'] = array('',L('Role_V'));
  $strLangMenu = '<i class="fa fa-user"></i>&nbsp;<a id="logname" href="'.Href('qti_user.php').'?id='.sUser::Id().'">'.sUser::Name().'</a>';
}
else
{
  $strLangMenu = '<i class="fa fa-user"></i>&nbsp;<span id="logname">'.L('Role_V').'</span>';
}

if ( count($arrLangMenu)>1 ) $strLangMenu .= ' | '.implode(' ',$arrLangMenu);

$strLangMenu = '<div id="menulang">'.$strLangMenu.'</div>'.PHP_EOL;

// check welcome
$bWelcome = true;
if ( in_array($oVIP->selfurl,array('qti_register.php','qti_form_reg.php','qti_change.php')) ) $bWelcome = false;
if ( $bWelcome && $_SESSION[QT]['show_welcome']=='0' ) $bWelcome = false;
if ( $bWelcome && $_SESSION[QT]['show_welcome']=='1' && sUser::Auth() ) $bWelcome = false;
if ( $bWelcome && !file_exists(Translate('sys_welcome.txt',false)) ) $bWelcome = false;
if ( $bWelcome && $_SESSION[QT]['board_offline']=='1' ) $bWelcome = false;

$oHtml->title = (empty($oVIP->selfname) ? '' : $oVIP->selfname.' - ').$oHtml->title;

// --------
// HTML START
// --------

echo $oHtml->Head();
echo $oHtml->Body(array('onload'=>(isset($strBodyAddOnload) ? $strBodyAddOnload : null),'onunload'=>(isset($strBodyAddOnunload) ? $strBodyAddOnunload : null)));

if ( $oVIP->selfurl!=='qti_index.php' && sUser::Role()==='A' && $_SESSION[QT]['board_offline']=='1' )
{
echo '<p style="margin:0 0 5px 0;padding:5px 10px;background-color:#C10505;color:#ffffff">Board is offline but Administrators can make some actions. You can <a style="color:white;text-decoration:underline" href="qti_adm_index.php">turn the board online here</a>.</p>';
}

echo cHtml::Page(START);

// MENU

$arrMenus = array();

// keys are:
// 'h' in header,
// 'f' in footer,
// 'n' name,
// 'u' url,
// 's' selected with url's,
// 'a' accesskey
// 'secondary' define if class secondary can be applied to header menu

if ( $_SESSION[QT]['home_menu']=='1' && !empty($_SESSION[QT]['home_url']) )
{
$arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>QTstrh($_SESSION[QT]['home_name']), 'u'=>$_SESSION[QT]['home_url']);
}
$arrMenus[]=array('h'=>false,'f'=>true, 'n'=>L('Legal'), 'u'=>'qti_privacy.php');
$arrMenus[]=array('h'=>false,'f'=>true, 'n'=>L('FAQ'), 'u'=>'qti_faq.php');
$arrMenus[]=array('h'=>true, 'f'=>false,'n'=>cLang::ObjectName(), 'u'=>'qti_index.php', 's'=>'qti_index.php qti_items.php qti_item.php qti_calendar.php qti_form_edit.php qti_form_del.php qti_privacy.php', 'secondary'=>true, 'a'=>'i');
if ( QTI_SIMPLESEARCH ) { $str='s_'; } else { $str=''; }
if ( $oVIP->selfurl==='qti_s_search.php' ) $str=''; // when on Simple search, Search go to Advanced search
$arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>L('Search'), 'u'=>( $_SESSION[QT]['board_offline']=='1' || (sUser::Role()==='V' && $_SESSION[QT]['visitor_right']<5) ? '' : 'qti_'.$str.'search.php'),'s'=>'qti_s_search.php qti_search.php', 'a'=>'s');
$arrMenus[]=array('h'=>true, 'f'=>false,'n'=>L('Memberlist'), 'u'=>( $_SESSION[QT]['board_offline']=='1' || (sUser::Role()==='V' && $_SESSION[QT]['visitor_right']<4) ? '' : 'qti_users.php'));
if ( CanPerform('show_stats',sUser::Role()) )
{
$arrMenus[]=array('h'=>false,'f'=>true,'n'=>L('Statistics'), 'u'=>($_SESSION[QT]['board_offline']=='1' ? '' : 'qti_stats.php'));
}
if ( sUser::Auth() )
{
$arrMenus[]=array('h'=>true, 'f'=>true,'n'=>L('Profile'), 'u'=>($_SESSION[QT]['board_offline']=='1' ? '' : 'qti_user.php?id='.sUser::Id()), 's'=>'qti_user.php qti_userimage.php.php qti_usersign.php qti_userpwd.php', 'secondary'=>true);
$arrMenus[]=array('h'=>true, 'f'=>true,'n'=>L('Logout'), 'u'=>'qti_login.php?a=out');
}
else
{
$arrMenus[]=array('h'=>true, 'f'=>true,'n'=>L('Register'),'u'=>($_SESSION[QT]['board_offline']=='1' ? '' : 'qti_register.php'), 's'=>'qti_register.php qti_form_reg.php', 'secondary'=>true);
$arrMenus[]=array('h'=>true, 'f'=>true,'n'=>L('Login'),'u'=>'qti_login.php', 's'=>'qti_login.php qti_reset_pwd.php');
}

$strMenus = '<div id="menuapp"'.($bWelcome ? ' class="withwelcome"' : '').'><p>';
foreach($arrMenus as $arrMenu) {
  if ( $arrMenu['h'] ) {
    if ( !isset($arrMenu['s']) ) $arrMenu['s']=' '.$arrMenu['u'];
    if ( !isset($arrMenu['i']) ) $arrMenu['i']=' '.$arrMenu['u'];
    if ( empty($arrMenu['u']) )
    {
      $strMenus .= '<span'.(isset($arrMenu['secondary']) ? ' class="secondary"' : '').'>'.$arrMenu['n'].'</span>';
    }
    else
    {
      $strMenus .= '<a'.(isset($arrMenu['secondary']) ? ' class="secondary"' : '').(strstr($arrMenu['s'],$oVIP->selfurl) ? ' id="menuactif"' : '').' href="'.Href($arrMenu['u']).'"'.(strstr($arrMenu['i'],$oVIP->selfurl) ? ' onclick="return false;"' : '').(empty($arrMenu['a']) ? '' : ' accesskey="'.$arrMenu['a'].'"').'>'.$arrMenu['n'].'</a>';
    }
  }
}
$strMenus .='</p></div>
';

// show banner and menu

echo '<!-- banner -->',PHP_EOL;
if ( !isset($_SESSION[QT]['show_banner']) ) $_SESSION[QT]['show_banner']='0';
switch($_SESSION[QT]['show_banner'])
{
  case '0': EchoBanner('nobanner','',$strLangMenu); echo $strMenus; break;
  case '1': EchoBanner('banner',$_SESSION[QT]['skin_dir'].'/qti_logo.gif',$strLangMenu); echo $strMenus; break;
  case '2': EchoBanner('banner',$_SESSION[QT]['skin_dir'].'/qti_logo.gif',$strLangMenu,$strMenus); break;
}

// WELCOME

if ( $bWelcome )
{
echo '
<!-- welcome -->
<div id="welcome">';
include Translate('sys_welcome.txt');
echo '</div>
';
}

// MAIN

echo '
<!-- MAIN CONTENT -->
<div id="body">
';

echo '
<!-- top bar -->
<div id="body-hd">
<div id="body-hd-l"><a class="body_hd" href="',Href('qti_index.php'),'"',($oVIP->selfurl=='qti_index.php' ? ' onclick="return false;"' : ''),'>',cLang::ObjectName(),'</a>';
if ( isset($s) ) {
if ( is_int($s) ) {
if ( $s>=0 ) {
  $arr = sMem::Get('sys_sections');
  $str = isset($arr[$s]['title']) ? $arr[$s]['title'] : 'section '.$s;
  echo QTI_CRUMBTRAIL,'<a class="body_hd" href="',Href('qti_items.php'),'?s=',$s,'"',($oVIP->selfurl==='qti_items.php' && $q==='s' ? ' onclick="return false;"' : ''),'>',$str,'</a>';
  if ( isset($oTopic) && isset($oSEC) )
  {
    if ( $oTopic->numid>=0 && $oSEC->numfield!='N' ) echo QTI_CRUMBTRAIL,sprintf($oSEC->numfield,$oTopic->numid);
  }
}}}

if ($oVIP->selfurl==='qti_user.php') echo QTI_CRUMBTRAIL,L('Profile');

echo '</div>
<div id="body-hd-r">';

switch($oVIP->selfurl)
{
case 'qti_items.php':
  $bodyctId = 's'.($s>=0 ? $s : 'null'); if ( !empty($q) ) $bodyctId = 'q-'.$q;
  if ( CanPerform('show_calendar',sUser::Role()) && $q==='s' )
  {
  echo '<a href="',Href('qti_calendar.php'),'?s=',$s,'"><i class="fa fa-calendar fa-lg" title="',L('Ico_view_f_c'),'"></i></a>';
  }
  break;
case 'qti_calendar.php':
  if ( CanPerform('show_calendar',sUser::Role()) )
  {
  echo '<a href="',Href('qti_items.php'),'?s=',$s,'"><i class="fa fa-th-list fa-lg" title="',L('Ico_view_f_n'),'"></i></a>';
  }
  break;
case 'qti_stats.php':
  $strURI = QTimplodeUri(QTarradd(QTexplodeUri(),'view'));
  break;
case 'qti_item.php':
  $strURI = QTimplodeUri(QTarradd(QTexplodeUri(),'view'));

  if ( $_SESSION[QT]['viewmode']=='c' )
  {
  echo '<a href="',Href($oVIP->selfurl),'?',$strURI,'&amp;view=N"><img src="',$_SESSION[QT]['skin_dir'],'/ico_view_n.gif" title="',$L['Ico_view_n'],'" alt="N"/></a>';
  }
  else
  {
  echo '<a href="',Href($oVIP->selfurl),'?',$strURI,'&amp;view=C"><img src="',$_SESSION[QT]['skin_dir'],'/ico_view_c.gif" title="',$L['Ico_view_c'],'" alt="C"/></a>';
  }
  break;
case 'qti_users.php':
  if ( !empty($_SESSION[QT]['avatar']) )
  {
    if ( $_SESSION[QT]['viewmode']=='c' )
    {
    echo '<a href="',Href('qti_users.php'),'?view=N"><img src="',$_SESSION[QT]['skin_dir'],'/ico_view_n.gif" title="',$L['Ico_view_n'],'" alt="N"/></a>';
    }
    else
    {
    echo '<a href="',Href('qti_users.php'),'?view=C"><img src="',$_SESSION[QT]['skin_dir'],'/ico_view_c.gif" title="',$L['Ico_view_c'],'" alt="C"/></a>';
    }
  }
  break;
}

echo '</div>
</div>
';

// MAIN CONTENT

echo '
<!-- main content -->
<div id="body-ct" class="pg-'.cVIP::PageCode($oVIP->selfurl).' view-'.$_SESSION[QT]['viewmode'].'">
';
if ( isset($bodyctId) ) echo '<div id="pg-',$bodyctId,'"></div>',PHP_EOL;