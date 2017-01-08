<?php

// QuickTicket 3.0 build:20160703
// Script added in qti_form_edit.php when rss is activated.

// ----------

if ( $_SESSION[QT]['m_rss']==='1' ) MakeRss($oSEC); // make rss if module is enabled. This can return a false

// ----------

function ToXml($str)
{
  $str = html_entity_decode($str,ENT_QUOTES);
  if ( strstr($str,'&') ) $str = Xencode($str,'-A -Q -L -R -&');
  $str = str_replace(chr(160),' ',$str); // required for xml
  $str = Xencode($str,'& L R'); // required for xml
  return $str;
}
function Xencode($str='',$symbols='Q A L R')
{
  // This will encode (or decode) special characters: quote, apostrophe, open, close, amp
  // $arrSymbols is the list of symbols to encode (noted Q A L R or &). Use - to decode
  // Note: $arrSymbols can be a string with space separated values
  // Note: If you want to convert &, you must make it first.

  if ( empty($str) ) return $str;
  if ( is_string($symbols) ) $symbols = explode(' ',$symbols);
  if ( empty($symbols) ) return $str;
  if ( !is_array($symbols) ) return $str;

  foreach($symbols as $symbol) {
  switch($symbol) {
  case '&': $str = str_replace('&','&amp;',$str); break;
  case 'A': $str = str_replace("'",'&apos;',$str); break;
  case 'Q': $str = str_replace('"','&quot;',$str); break;
  case 'L': $str = str_replace('<','&lt;',$str); break;
  case 'R': $str = str_replace('>','&gt;',$str); break;
  case '-A': $str = str_replace(array('&apos;','&#039;','&#39;'),"'",$str); break;
  case '-Q': $str = str_replace(array('&quot;','&#034;','&#34;'),'"',$str); break;
  case '-L': $str = str_replace(array('&lt;','&#060;','&#60;'),'<',$str); break;
  case '-R': $str = str_replace(array('&gt;','&#062;','&#62;'),'>',$str); break;
  case '-&': $str = str_replace(array('&amp;','&#038;','&#38;'),'&',$str); break;
  }}
  return $str;
}
function MakeRss($oSEC=-1)
{

// check
if ( $_SESSION[QT]['m_rss']!='1' ) return false;
if ( is_int($oSEC) && $oSEC>=0 ) $oSEC = new cSection($oSEC);
if ( !is_a($oSEC,'cSection') ) {echo 'section undefined'; return false;}
if ( $oSEC->type===0 ) return false; // no rss for hidden section
$s=$oSEC->uid;

global $oDB,$L;

$arr = explode(' ',$_SESSION[QT]['m_rss_conf']);
$top = $arr[2];

// search new topics
$strOrder = 't.lastpostdate DESC';
$oDB->Query( LimitSQL( 't.*,p.title,p.textmsg FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid = p.id WHERE t.forum = '.$s, $strOrder, 0, $top) );
$i=0;
while ($row = $oDB->Getrow())
{
  $item[$i]['title'] = ToXml($row['title']);
  $item[$i]['link'] = $_SESSION[QT]['site_url']."/qti_item.php?t=".$row['id']."&amp;p=".$row['firstpostid'];
  // format the RSS text
  $item[$i]['description'] = ToXml(QTcompact(QTunbbc($row['textmsg']),400,' '));
  $item[$i]['pubDate'] = $row['lastpostdate'];
  $item[$i]['author'] = $row['firstpostname'];
  ++$i;
}

// write rss 2.0

$strFilename = 'rss/qti_2_'.$s.'.xml';
if ( file_exists($strFilename) )
{
  if ( !is_writable($strFilename) )
  {
  echo $strFilename,' not writable<br />';
  return false;
  }
}
$handle = fopen('rss/qti_2_'.$s.'.xml','w');
fwrite($handle,'<?xml version="1.0" encoding="'.QT_HTML_CHAR.'" ?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>'.PHP_EOL);
fwrite($handle,'<title>'.ToXml($_SESSION[QT]['site_name'].' - '.$oSEC->name).'</title>');
fwrite($handle,'<link>'.$_SESSION[QT]['site_url'].'/qti_items.php?s='.$s.'</link>');
fwrite($handle,'<description>'.ToXml($oSEC->descr).'</description>');
fwrite($handle,"<language>".QT_HTML_LANG."</language>\n");
fwrite($handle,"<generator>QuickTicket ".substr(QTIVERSION,0,3)."</generator>\n");
fwrite($handle,"<managingEditor>{$_SESSION[QT]['admin_email']} (webmaster)</managingEditor>\n");
fwrite($handle,"<category>Troubleticket</category>\n");
fwrite($handle,"<image><url>{$_SESSION[QT]['site_url']}/rss/qti_logo.gif</url><title>".$_SESSION[QT]['site_name'].' - '.$oSEC->name."</title><link>{$_SESSION[QT]['site_url']}/qti_items.php?s=$s</link><width>110</width><height>50</height></image>\n");
fwrite($handle,'<atom:link href="'.$_SESSION[QT]['site_url'].'/rss/qti_2_'.$s.'.xml" rel="self" type="application/rss+xml" />'."\n");
for ($n=0; $n<$i; ++$n)
{
fwrite($handle,"<item>\n");
fwrite($handle,"<title>{$item[$n]['title']}</title>\n");
fwrite($handle,"<link>{$item[$n]['link']}</link>\n");
fwrite($handle,"<description>{$item[$n]['description']}</description>\n");
fwrite($handle,"<pubDate>".QTdatestr($item[$n]['pubDate'],'D, d M Y H:i:00 O','')."</pubDate>\n");
fwrite($handle,"<guid>{$item[$n]['link']}</guid>\n");
fwrite($handle,"</item>\n");
}
fwrite($handle,'</channel></rss>');
fclose($handle);

// write atom 1.0

$strFilename = 'rss/qti_atom_'.$s.'.xml';
if ( file_exists($strFilename) )
{
  if ( !is_writable($strFilename) )
  {
  echo $strFilename,' not writable<br />';
  return false;
  }
}
$handle = fopen('rss/qti_atom_'.$s.'.xml','w');
fwrite($handle,'<?xml version="1.0" encoding="'.QT_HTML_CHAR.'" ?><feed xmlns="http://www.w3.org/2005/Atom">'.PHP_EOL);
fwrite($handle,'<title>'.ToXml($_SESSION[QT]['site_name'].' - '.$oSEC->name).'</title>');
fwrite($handle,'<link href="'.$_SESSION[QT]['site_url'].'/qti_items.php?s='.$s.'" />');
fwrite($handle,'<link href="'.$_SESSION[QT]['site_url'].'/rss/qti_atom_'.$s.'.xml" rel="self" />');
fwrite($handle,"<id>{$_SESSION[QT]['site_url']}/qti_items.php?s=$s</id>\n");
fwrite($handle,"<updated>".QTdatestr(date('Y-m-d H:i:s'),'RFC-3339')."</updated>\n");
fwrite($handle,"<author><name>Webmaster</name><email>{$_SESSION[QT]['admin_email']}</email></author>\n");
fwrite($handle,'<category term="Troubleticket" />');
fwrite($handle,"<generator>QuickTicket ".substr(QTIVERSION,0,3)."</generator>\n");
fwrite($handle,"<icon>{$_SESSION[QT]['site_url']}/rss/qti_icon.gif</icon>\n");
fwrite($handle,"<logo>{$_SESSION[QT]['site_url']}/rss/qti_logo.gif</logo>\n");
for ($n=0; $n<$i; ++$n)
{
fwrite($handle,"<entry>\n");
fwrite($handle,"<id>{$item[$n]['link']}</id>\n");
fwrite($handle,"<title>{$item[$n]['title']}</title>\n");
fwrite($handle,"<updated>".QTdatestr($item[$n]['pubDate'],'RFC-3339')."</updated>\n");
fwrite($handle,"<author><name>{$item[$n]['author']}</name></author>\n");
fwrite($handle,"<content>{$item[$n]['description']}</content>\n");
fwrite($handle,'<link href="'.$item[$n]['link'].'" />');
fwrite($handle,"</entry>\n");
}
fwrite($handle,'</feed>');
fclose($handle);

}