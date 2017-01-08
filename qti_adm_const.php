<?php

// QT 3.0 build:20160703

session_start();
require 'bin/init.php';
if ( sUser::Role()!='A' ) die('Access denied');
$oVIP->selfurl=APP.'_adm_const.php';
$oVIP->selfname='PHP constants';

function ConstantToString($str)
{
  if ( is_string($str) ) return '"'.htmlentities($str).'"';
  if ( is_bool($str) ) return ($str ? 'TRUE' : 'FALSE');
  if ( is_array($str) ) return 'array of '.count($str).' values';
  if ( is_null($str) ) return '(null)';
  return $str;
}

// HTML start

include Translate(APP.'_adm.php');
include APP.'_adm_inc_hd.php';

// CONSTANT

$arr = get_defined_constants(true); if ( isset($arr['user']) ) $arr = $arr['user']; // userdefined constants

// Show constants

echo '<p>Here are the major constants. To have a full list of constants see the file /bin/config_web.php.</p>';

echo '<table class="t-data horiz">',PHP_EOL;
foreach($arr as $key=>$str)
{
  if ( substr($key,0,3)==strtoupper(APP) ) echo '<tr><th>',$key,'</th><td>',ConstantToString($str),'</td></tr>',PHP_EOL;
}
echo '</table>',PHP_EOL;

// Show DB parameters

echo '<p>Here are the database connection parameters (except passwords)</p>';

echo '<table class="t-data horiz">',PHP_EOL;
foreach(array('dbsystem','host','database','prefix','user','port','install') as $str)
{
  $str = APP.'_'.$str;
  echo '<tr><th>$'.$str,'</th><td>',(isset($$str) ? ConstantToString($$str) : '&nbsp;'),'</td></tr>',PHP_EOL;
}
echo '</table>',PHP_EOL;

include APP.'_adm_inc_ft.php';