<?php
$bMap=true;
if ( empty($_SESSION[QT]['m_map_gkey']) ) $bMap=false;
if ( $bMap ) { require 'qtim_map_lib.php'; if ( !QTgcanmap($strCheck,sUser::Role()) ) $bMap=false; }
if ( $bMap ) 
{
  include Translate('qtim_map.php');
  $bMapGoogle=true;
  $bMapSitework=false;
  if ( !empty($_SESSION[QT]['m_sitework']) ) { $bMapSitework=true; $bMapGoogle=false; }
  if ( $bMapGoogle ) $oHtml->links[] = '<link rel="stylesheet" type="text/css" href="qtim_map.css" />';
  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_map_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_map_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_map_hidelist']) ) $_SESSION[QT]['m_map_hidelist']=false;
}