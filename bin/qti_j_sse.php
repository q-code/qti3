<?php
header('Content-Type: text/event-stream');
header('Cache-control: no-cache');

// Library

function sse_echo($msg='data: ping',$retry=10000)
{
  if ( empty($msg) ) return;
  if ( !is_int($retry) ) $retry=10000;
  echo ($retry>0 ? 'retry: '.$retry.PHP_EOL : '').$msg;
  ob_flush();
  flush();
}


// Check and open memcache connection

$memcache = class_exists('Memcache') ? new Memcache : false;
if ( !$memcache )
{
sse_echo( 'event: error'.PHP_EOL.'data: Memcache library not found on the webserver'.PHP_EOL.PHP_EOL,0 );
exit;
}

require 'config.php';
if ( isset($qti_install) ) { define('QT','qti'.substr($qti_install,-1)); } else { define('qti'); }
require 'config_web.php';

if ( !$memcache->connect(MEMCACHE_HOST,MEMCACHE_PORT) )
{
sse_echo( 'event: error'.PHP_EOL.'data: Unable to contact Memcache daemon ['.MEMCACHE_HOST.'] port ['.MEMCACHE_PORT.']'.PHP_EOL.PHP_EOL,0 );
exit;
}

// Broadcast 3 shared memories (if they contain values) on each connection request comming from the client.
// The default timelap to retry connection (15 seconds) is included in the broadcasted message.

$sseConnect = defined('SSE_CONNECT') ? SSE_CONNECT : 15000; if ( $sseConnect===true ) $sseConnect = 15000; // 15 seconds

$b = false;
foreach(array('section','topic','reply') as $memory)
{
  $m = $memcache->get(QT.'_sse_'.$memory); // read last actions

  if ( $m!==false )
  {
    if ( substr($m,0,1)!=='[' ) $m = '['.$m.']';

    $jd = json_decode($m,true);
    if ( count($jd) > 9 ) $memcache->delete(QT.'_sse_'.$memory); // garbadge collector released
    foreach($jd as $j)
    {
      $event = isset($j['event']) ? $j['event'] : '';
      $data = isset($j['data']) ? json_encode($j['data']) : '';
      if ( empty($event) && empty($data) )
      {
        $msg = 'event: error'.PHP_EOL.'data: memcache structure unknown in key '.QT.'_sse_'.$memory.PHP_EOL.PHP_EOL;
      }
      else
      {
        $msg = (empty($event) ? '' : 'event: '.$event.PHP_EOL).'data: '.$data.PHP_EOL.PHP_EOL;
      }
      $b=true;
      sse_echo($msg,$sseConnect);
      $memcache->delete(QT.'_sse_noevent');
    }
  }
}

// When there is nothing in the shared memories, we send a simple message 'no event'

if ( !$b )
{
  // Retry timelaps extended after 20 times without events
  if ( $sseConnect<15000 )
  {
  $m = $memcache->get(QT.'_sse_noevent');
  if ( $m===false ) $m = 0;
  if ( $m>19 ) { $sseConnect=25000; }
  $memcache->set(QT.'_sse_noevent',++$m,0,60);
  }
  // log no event
  sse_echo('data: nothing, retry in '.($sseConnect/1000).'s'.PHP_EOL.PHP_EOL,$sseConnect);
}
