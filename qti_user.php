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
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qti_profile.css" />';
if ( !sUser::CanView('V4') ) { $oHtml->PageMsg(11); return; }

$id = -1;
if ( isset($_GET['id']) ) $id = (int)$_GET['id'];
if ( isset($_POST['id']) ) $id = (int)$_POST['id'];
if ( isset($_GET['edit']) ) $_SESSION[QT]['editing']=($_GET['edit']=='1' ? true : false);
if ( isset($_POST['edit']) ) $_SESSION[QT]['editing']=($_POST['edit']=='1' ? true : false);
if ( $id<0 ) die('Wrong id');

// --------
// FUNCTION
// --------

function show_ban($strRole='V',$intBan=0,$name='')
{
  if ( $intBan<1 ) return '';
  if ( $strRole=='A' || $strRole=='M' )
  {
    if ( $intBan>1 ) $intBan=($intBan-1)*10;
    Return '<p class="small error">'.$name.' '.strtolower(L('Is_banned').' '.L('Day',$intBan).' '.L('Since').' '.L('Last_message')).'</p>';
  }
}

// --------
// INITIALISE
// --------

include 'bin/class/qt_class_smtp.php';
include Translate(APP.'_reg.php');

$bCanEdit = false;
if ( sUser::Id()==$id || sUser::Role()==='A' ) $bCanEdit=true;
if ( !$bCanEdit && sUser::Role()==='M' && defined('QTI_STAFFEDITPROFILES') && QTI_STAFFEDITPROFILES ) $bCanEdit=true; // apply setting if staff member can edit profile
if ( $id==0 ) $bCanEdit=false;
if ( !isset($_SESSION[QT]['editing']) || !$bCanEdit) $_SESSION[QT]['editing']=false;

$oVIP->selfurl = 'qti_user.php';
$oVIP->selfname = $L['Profile'];

// MAP MODULE

$bMap=false;
if ( UseModule('map') )
{
  include Translate('qtim_map.php');
  include 'qtim_map_lib.php';
  if ( QTgcanmap('U') ) $bMap=true;
  if ( $bMap )
  {
  $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qtim_map.css" />';
  if ( !isset($_SESSION[QT]['m_map_symbols']) ) $_SESSION[QT]['m_map_symbols']='0';
  $arrSymbolByRole = ( empty($_SESSION[QT]['m_map_symbols']) ? array() : QTexplodeIni($_SESSION[QT]['m_map_symbols']) );
  if ( !isset($_SESSION[QT]['m_map_hidelist']) ) $_SESSION[QT]['m_map_hidelist']=false;
  }
}

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check form

  $strLoca = trim($_POST['location']);

  if ( empty($error) )
  {
    $arrMails = QTexplodeStr(';, ',$_POST['mail']);
    foreach($arrMails as $strMail) if ( !QTismail($strMail) ) { $error='Email '.$strMail.' '.L('invalid'); break; }
    $strMail = implode(';',$arrMails);
  }

  if ( empty($error) )
  {
    $strWww = QTconv($_POST['www'],'2');
    if ( !empty($strWww) && substr($strWww,0,4)!='http' ) $error=$L['Website'].' '.L('invalid');
    if ( $strWww=='http://' || $strWww=='https://' ) $strWww='';
  }

  // Save

  if ( empty($error) )
  {
    $oDB->Exec('UPDATE '.TABUSER.' SET location="'.QTstrd($strLoca).'", mail="'.$strMail.'", www="'.QTstrd($strWww).'", privacy="'.$_POST['privacy'].'", children="'.$strChild.'", parentmail="'.$strParentmail.'" WHERE id='.$id);
    if ( isset($_POST['coord']) )
    {
      if ( empty($_POST['coord']) )
      {
      sUser::SetCoord($id,null);
      }
      else
      {
      sUser::SetCoord($id,$_POST['coord']); //z is not used
      }
    }

    // exit (if no error)

    $oVIP->exiturl = 'qti_user.php?id='.$id;
    $oVIP->exitname = $L['Profile'];
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
    $oHtml->Redirect($oVIP->exiturl);
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// --------
// STATS AND USER
// --------

// COUNT TOPICS

$oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE firstpostuser='.$id);
$row = $oDB->Getrow();
$items = $row['countid'];

// COUNT MESSAGES

$oDB->Query('SELECT count(id) as countid FROM '.TABPOST.' WHERE userid='.$id);
$row = $oDB->Getrow();
$countmessages = $row['countid'];

// QUERY USER

$oDB->Query('SELECT * FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();
$row['name'] = QTconv($row['name'],'5');
$row['privacy']= (int)$row['privacy'];

  // check privacy
  if ( sUser::IsPrivate($row['privacy'],$id) ) { $row['y']=null; $row['x']=null; }

  // staff cannot edit admin
  if ( $row['role']==='A' && sUser::Role()==='M' ) { $bCanEdit=false; $_SESSION[QT]['editing']=false; }

  // map settings
  if ( $bMap && !QTgempty($row['x']) && !QTgempty($row['y']) )
  {
    $y = (float)$row['y']; $x = (float)$row['x'];
    $strPname = QTconv($row['name'],'U');
    $oMapPoint = new cMapPoint($y,$x,$strPname);
			// add extra $oMapPoint properties (if defined in section settings)
			$oSettings = getMapSectionSettings('U');
			if ( is_object($oSettings) ) foreach(array('icon','shadow','printicon','printshadow') as $prop) if ( property_exists($oSettings,$prop) ) $oMapPoint->$prop = $oSettings->$prop;
    $arrExtData[$id] = $oMapPoint;
  }

// --------
// HTML START
// --------

include 'qti_inc_hd.php';

if ( $id<0 )  die('Wrong id in qti_user.php');

// -- PARTICIPATION INFO --

$strParticip = '';
if ( $items>0 )
{
$strParticip .= '<a href="qti_items.php?q=user&amp;v='.$id.'&amp;v2='.urlencode($row['name']).'">'.L('Item',$items).'</a>, ';
}
if ( $countmessages>0 )
{
  $strParticip .= L('Message',$countmessages).', '.strtolower($L['Last_message']).' '.QTdatestr($row['lastdate'],'$','$',true);
  $oDB->Query('SELECT p.id,p.topic,p.forum FROM '.TABPOST.' p WHERE p.userid='.$id.' ORDER BY p.issuedate DESC' );
  $row2 = $oDB->Getrow();
  $strParticip .= ' <a class="lastitem" href="qti_item.php?t='.$row2['topic'].'&amp;tt=messages#p'.$row2['id'].'" title="'.L('Goto_message').'">'.QTI_GOTOBUTTON.'</a>';
}

// -- DISPLAY PROFILE --

$strMail = '';  if ( !empty($row['mail']) && !sUser::IsPrivate($row['privacy'],$id) ) $strMail = AsEmailsTxt($row['mail'],' ','mail'.$id,true,QTI_JAVA_MAIL);
$strLocation = ''; if ( !empty($row['location']) && !sUser::IsPrivate($row['privacy'],$id) ) $strLocation = $row['location'];
$strPriv = '<i class="fa fa-lock" title="'.L('Privacy_1').'"></i>';
if ( $row['privacy']==0 ) $strPriv = '<i class="fa fa-lock" title="'.L('Privacy_1').'"></i>';
if ( $row['privacy']==0 && (sUser::IsStaff() || sUser::Id()==$row['id']) ) $strPriv = '<i class="fa fa-lock private" title="'.L('Privacy_0').'"></i>';
if ( $row['privacy']==2 ) $strPriv = '<i class="fa fa-unlock" title="'.L('Privacy_2').'"></i>';

if ( isset($bCanEdit) && $bCanEdit ) echo '<div class="profilecmd">',( $_SESSION[QT]['editing'] ? '<a class="button" href="'.Href($oVIP->selfurl).'?id='.$id.'&amp;edit=0">'.$L['Edit_stop'].'</a>' : '<a class="button" href="'.Href($oVIP->selfurl).'?id='.$id.'&amp;edit=1">'.$L['Edit_start'].'</a>'),'</div>';
echo '<h1 class="secondary">',$row['name'],'</h1>
<table class="profile">
<tr>
<td class="profileleft">
';

echo AsUserImg( (empty($row['photo']) ?  '' : QTI_DIR_PIC.$row['photo']),'','','','imguser' ),PHP_EOL;

if ( $bCanEdit )
{
  if ( $_SESSION[QT]['avatar']!='0' )
  {
  echo '<p><a href="',Href('qti_userimage.php'),'?id=',$id,'">',$L['Change_picture'],'</a></p>';
  }
  echo '<p><a href="',Href('qti_usersign.php'),'?id=',$id,'">',$L['Change_signature'],'</a></p>';
  if ( sUser::Id()===$id )
  {
  echo '<p><a href="',Href('qti_userpwd.php'),'?id=',$id,'">',$L['Change_password'],'</a></p>';
  echo '<p><a href="',Href('qti_userquestion.php'),'?id=',$id,'">',$L['Secret_question'],'</a></p>';
  }
  if ( sUser::Id()===$id && $id>1 ) echo '<p><a href="'.Href('qti_unregister.php').'?id=',$id,'">',L('Unregister'),'</a></p>';
  if ( sUser::IsStaff() )
  {
    echo '<hr/>';
    echo '<p class="disabled secondary">'.L('Role_'.sUser::Role()).':</p>';
    echo '<p><a href="'.Href('qti_change.php').'?a=pwdreset&amp;s='.$id.'">'.L('Reset_pwd').'</a></p>';
    if ( $id>1 )
    {
    echo '<p><a href="'.Href('qti_adm_users_edit.php').'?a=usersrole&amp;ids='.$id.'&amp;v2='.$row['role'].'">'.L('Change_role').'</a></p>';
    echo '<p><a href="'.Href('qti_adm_users_edit.php').'?a=usersdel&amp;ids='.$id.'">'.L('Delete').' '.L('user').'</a></p>';
    }
  }
}
if ( !empty($row['closed']) ) echo '<hr/>',show_ban(sUser::Role(),$row['closed'],$row['name']);

echo '</td>
<td class="profileright">
<div class="userprofile">
';

// -- EDIT PROFILE --
if ( $_SESSION[QT]['editing'] ) {
// -- EDIT PROFILE --

echo '<form method="post" action="',Href('qti_user.php'),'?id=',$id,'">
<table class="t-data">
<tr><th>',$L['Username'],'</th><td><span class="bold">',$row['name'],'</span> ';
if ( sUser::Role()==='A' || (sUser::Id()==$id && QTI_CHANGE_USERNAME) ) {
if ( $id>1 ) {
  echo ' &middot; <a class="small" href="qti_username.php?id=',$id,'">',$L['Change_name'],'</a>';
}}
echo '</td></tr>
<tr><th>',$L['Role'],'</th><td>',L('Role_'.$row['role']),'</td></tr>
<tr><th>',$L['Location'],'</th><td><input type="text" name="location" size="35" maxlength="24" value="',$row['location'],'"/></td></tr>
<tr><th>',$L['Email'],'</th><td><input type="text" name="mail" size="35" maxlength="64" value="',$row['mail'],'"/></td></tr>
<tr><th>',$L['Website'],'</th><td><input type="text" name="www" pattern="^(http://|https://).*" size="35" maxlength="64" value="',( !empty($row['www']) ? $row['www'] : 'http://' ),'" title="',$L['H_Website'],'"/></td></tr>
<tr>
<th>',$L['Privacy'],'</th>
<td>',$L['Email'],'/',$L['Location'],($bMap ? '/'.$L['map_position'] : ''),' <select size="1" name="privacy">
<option value="2"',($row['privacy']=='2' ? QSEL : ''),'>',L('Privacy_2'),'</option>
<option value="1"',($row['privacy']=='1' ? QSEL : ''),'>',L('Privacy_1'),'</option>
<option value="0"',($row['privacy']=='0' ? QSEL : ''),'>',L('Privacy_0'),'</option>
</select></td>
</tr>
';

if ( $bMap )
{
  $oCanvas = new cCanvas();
  $str = L('map_cancreate');
  if ( !empty($row['x']) && !empty($row['y']) )
  {
    $_SESSION[QT]['m_map_gcenter'] = $row['y'].','.$row['x'];
    $str = L('map_canmove');
  }
  $oCanvas->Header( array(), array($str,'add','del') );
  $oCanvas->Footer( 'find' ,'', 'gmap commands right' );
  $strPosition = $oCanvas->Render(false,'','');

  echo '<tr>
  <th>',$L['Coord'],'</th>
  <td><input type="text" id="yx" name="coord" size="32" value="'.(!empty($row['y']) ? $row['y'].','.$row['x'] : '').'"/> <span class="small">',L('latlon'),'</span></td>
  </tr>
  ';
}

echo '<tr>
<th>&nbsp;</th>
<td><input type="hidden" name="id" value="',$id,'"/><input type="hidden" name="name" value="',$row['name'],'"/><input type="submit" name="ok" value="',$L['Save'],'"/>',( !empty($error) ? ' <span class="error">'.$error.'</span>' : '' ),'</td>
</tr>
';

if ( $bMap )
{
echo '<tr>
<td colspan="2">',$strPosition,'</td>
</tr>
';
}

echo '
</table>
</form>
';

// ------
} else {
// ------

echo '
<table class="t-data">
<tr><th>',$L['Username'],'</th><td><span class="bold">',$row['name'],'</span>';
if ( sUser::Role()==='A' || (sUser::Id()==$id && QTI_CHANGE_USERNAME) ) {
if ( $id>1 ) {
  echo ' &middot; <a class="small" href="qti_username.php?id=',$id,'">',$L['Change_name'],'</a>';
}}
echo '</td></td><td class="info">&nbsp;</td></tr>
<tr><th>',$L['Role'],'</th><td>',$L['Role_'.$row['role']],'</td><td class="info">&nbsp;</td></tr>
<tr><th>',$L['Location'],'</th><td>',$strLocation,'</td><td class="info">',$strPriv,'</td></tr>
<tr><th>',$L['Email'],'</th><td>',$strMail,'</td><td class="info">',$strPriv,'</td></tr>
<tr><th>',$L['Website'],'</th><td>',( empty($row['www']) ? '&nbsp;' : '<a href="'.$row['www'].'" target="_blank">'.$row['www'].'</a>' ),'</td><td class="info">&nbsp;</td></tr>
<tr><th>',$L['Joined'],'</th><td>',QTdatestr($row['firstdate'],'$','$',true),'</td><td class="info">&nbsp;</td></tr>
<tr><th>',$L['Messages'],'</th><td>',$strParticip,'</td><td class="info">&nbsp;</td></tr>
';

if ( sUser::Id()==$id || sUser::IsStaff() ) {
  echo '<tr><th>',$L['Privacy'],'</th><td>',$strPriv,' ',$L['Email'],'/',$L['Location'],($bMap ? '/'.$L['map_position'] : ''),' ',L('Privacy_'.$row['privacy']),'</td><td class="info">&nbsp;</td></tr>';
}

if ( $bMap && !QTgemptycoord($row) )
{
  $strCoord = ' ';
  if ( !sUser::IsPrivate($row['privacy'],$id) ) $strCoord = '<a class="gmappoint" href="javascript:void(0)"'.(!$_SESSION[QT]['m_map_hidelist'] ? ' onclick="gmapPan(\''.$row['y'].','.$row['x'].'\');return false;"' : '').' title="'.L('map_Center').'"><i class="fa fa-map-marker"></i></a> '.QTdd2dms(floatval($row['y'])).', '.QTdd2dms(floatval($row['x'])).' '.L('latlon').' <span class="small disabled">DD '.round(floatval($row['y']),8).','.round(floatval($row['x']),8).'</span>';
  echo '<tr><th>',$L['Coord'],'</th><td>',$strCoord,'</td><td class="info">',$strPriv,'</td></tr>',PHP_EOL;
  echo '<tr><td colspan="3"><div id="map_canvas" style="width:100%; height:350px;"></div></td></tr>',PHP_EOL;
}

echo '</table>
';

// ------
}
// ------

echo '
</div>
</td>
</tr>
</table>
';

// --------
// HTML END
// --------

// MAP MODULE

if ( $bMap )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_map_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_map_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_map_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_map_gcenter']));

  // First item is the user's location and symbol
  if ( isset($arrExtData[$id]) )
  {
    // symbol by role
    $oMapPoint = $arrExtData[$id];
    if ( !empty($oMapPoint->icon) ) $gmap_symbol = $oMapPoint->icon;
    if ( !empty($oMapPoint->shadow) ) $gmap_shadow = $oMapPoint->shadow;

    // center on user
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    }
  }

  // update center
  $_SESSION[QT]['m_map_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  $gmap_markers[] = QTgmapMarker($_SESSION[QT]['m_map_gcenter'],$_SESSION[QT]['editing'],$gmap_symbol,$row['name'],'',$gmap_shadow);
  if ( $_SESSION[QT]['editing'] )
  {
  $gmap_events[] = '
	google.maps.event.addListener(markers[0], "position_changed", function() {
		if (document.getElementById("yx")) {document.getElementById("yx").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(marker[0], "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  $gmap_functions[] = '
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("yx",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  function createMarker()
  {
    if ( !map ) return;
    if (infowindow) infowindow.close();
    deleteMarker();
    '.QTgmapMarker('map',true,$gmap_symbol).'
    gmapYXfield("yx",markers[0]);
    google.maps.event.addListener(markers[0], "position_changed", function() { gmapYXfield("yx",markers[0]); });
    google.maps.event.addListener(markers[0], "dragend", function() { map.panTo(markers[0].getPosition()); });
  }
  function deleteMarker()
  {
    if (infowindow) infowindow.close();
    for(var i=markers.length-1;i>=0;i--)
    {
      markers[i].setMap(null);
    }
    gmapYXfield("yx",null);
    markers=[];
  }
  ';
  }
  include 'qtim_map_load.php';
}

include 'qti_inc_ft.php';