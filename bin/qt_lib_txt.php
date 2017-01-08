<?php
/*
qt_lib_txt.php
------------
version: 5.3 build:20160703
This is a library of public functions
-------------
Requires a function GetSetting() that can provide an application settings
------------
QTstrd
QTstrh
QTtrunc
QTtruncarray
QTdateclean
QTdatestr -- since version 5.3 arguments order changed (for the 2 last arguments)
QTbbc
QTconv
QTfile
QTislogin
QTispassword
QTismail
QTisbetween
QTisvaliddate
QTisvalidtime
QTunbbc
QTcompact
QThttpvar
*/

// Convert input text into valid db text.
// Attention, default max length is 255 (use false to not truncate)

function QTstrd($str,$max=255)
{
	if ( !is_string($str) ) die('QTstrd: invalid argument #1');
	$str = addslashes($str);
  if ( is_int($max) && isset($str[$max]) ) return substr($str,0,$max);
	return $str;
}

// Convert text into valid html content (e.i. attribute value) nulll or 0 are returned unchanged).

function QTstrh($str,$max=false)
{
  if ( empty($str) ) return $str;
	if ( !is_string($str) ) die('QTstrh: invalid argument #1');
	$str = str_replace('"','&quot;',$str);
	$str = str_replace("'",'&#039;',$str);
	$str = str_replace('<','&lt;',$str);
	$str = str_replace('>','&gt;',$str);
	if ( is_int($max) && isset($str[$max]) ) return substr($str,0,$max);
	return $str;
}

// Truncates long text (to 255 and includes '...' by default)

function QTtrunc($str,$max=255,$end='...')
{
  if ( is_string($str) && is_int($max) && isset($str[$max]) && $max>strlen($end) ) return substr($str,0,$max-strlen($end)).$end;
  if ( is_array($str) ) return QTtruncarray($str,$max,$end);
  return $str;
}

// Truncate each long text in an array. Only array of string are processed (an array in $arr, or an int in $arr is unchanged)

function QTtruncarray($arr,$max=255,$end='...')
{
  if ( is_array($arr) && is_int($max) && $max>strlen($end) ) foreach($arr as $key=>$str) if ( is_string($str) && isset($str[$max]) ) $arr[$key]=substr($str,0,$max-strlen($end)).$end;
  return $arr;
}

// Format datetime to YYYYMMDD[HHMM[SS]] from a numerical [int or string] of 8 (to maximum 14) digits.
// $s can be: QTdatabase format, 'now', integer or a string like 'YYYY-MM-DD HH:MM:SS' (with trailing 0!)
// Returns '' when format is not supported

function QTdateclean($s='now')
{
  if ( $s==='now' ) return date('YmdHi');
  if ( is_int($s) ) $s = (string)$s;
  if ( !is_string($s) || empty($s) ) return '';
  if ( is_numeric($s) ) return substr($s,0,14);
  $s = substr(str_replace(array(' ','-','.','/',':'),'',$s),0,14);
  if ( is_numeric($s) ) return substr($s,0,14);
  return '';
}

// QTdatestr
//
// Convert a date [string] to a formatted date [string] and translate it.
//
// @$sDate        The date string, can be 'YYYYMMDD[HH][MM][SS]' or 'now'. It can include [.][/][-][ ]
// @$sOutDate     The output format for the date (or '$' to use the system format)
// @$sOutTime     The output format for the time (or '$' to use the system format). If not empty, it is added to the date format (or to the friendlydate)
// @$bFriendly    Replace date by 'Today','Yesterday'
// @$bDropOldTime Don't show time for date > 2 days.
//
// When $sDate is '0' or empty, or when the input date format is unsupported the function returns $e ('?')
// The translation uses $L['dateSQL']. If not existing, the php words remains (english).
// Also accept $sOutDate='RFC-3339' (this will ignore other parametres)

function QTdatestr($sDate='now',$sOutDate='$',$sOutTime='$',$bFriendly=false,$bDropOldTime=false,$title=false,$titleid=false,$e='?')
{
  $sDate = QTdateclean($sDate); if ( empty($sDate) ) return $e; // Clean $sDate (this is a numeric string YYYYMMDD[HHMMSS] max 14 char)

  if ( strlen($sDate)===4 ) return $sDate; // Stop if input is a year only

  // Analyse date time: returns $e when input is a invalid date otherwhise detect if recent date

  $intDate = FALSE;
  switch(strlen($sDate))
  {
  case 6:  $intDate = mktime(0,0,0,substr($sDate,4,2),1,substr($sDate,0,4)); break;
  case 8:  $intDate = mktime(0,0,0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  case 10: $intDate = mktime(substr($sDate,-2,2),0,0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  case 12: $intDate = mktime(substr($sDate,-4,2),substr($sDate,-2,2),0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  case 14: $intDate = mktime(substr($sDate,-6,2),substr($sDate,-4,2),substr($sDate,-2,2),substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  default: return $e;
  }
  if ( $intDate===FALSE ) return $e;

  // Exceptions (used by rss xml)

  if ( $sOutDate==='RFC-3339' )
  {
    $sDate = date('Y-m-d\TH:i:s',$intDate);
    $sGMT = date('O',$intDate);
    $sGMT = substr($sGMT,0,3).':'.substr($sGMT,-2,2);
    return $sDate.$sGMT;
  }

  // Check requested formats (and if recent)

  QTargs( 'QTdatestr',array($sOutDate,$sOutTime,$bFriendly,$bDropOldTime),array('str','str','boo','boo') );
  $bRecent = ( date('Y-m-d')==date('Y-m-d',$intDate) || date('Y-m-d')==date('Y-m-d',$intDate+86400) );
  if ( $sOutDate==='$' ) $sOutDate=GetSetting('formatdate','Ymd',false);
  if ( $sOutTime==='$' ) $sOutTime=GetSetting('formattime','',false);
  if ( empty($sOutDate) ) $sOutDate='Ymd'; // system date format cannot be empty
  if ( empty($sOutTime) ) $sOutTime=''; // system time format can be empty
  if ( !$bRecent && $bDropOldTime ) $sOutTime='';

  // Apply output format. In case of friendly date, Today/Yesterday will replace the date (and time can be added)

  $stamp = '';
  if ( $bRecent && $bFriendly )
  {
  if ( date('Y-m-d')==date('Y-m-d',$intDate) )       { $stamp = 'Today '; $sOutDate=''; }
  if ( date('Y-m-d')==date('Y-m-d',$intDate+86400) ) { $stamp = 'Yesterday '; $sOutDate=''; }
  }
  $format = trim($sOutDate.' '.$sOutTime);
  $sDate = $stamp.(empty($format) ?  '' : date($format,$intDate));
  if ( empty($sDate) )  return $e;
  $sDateFull = date('j F Y'.(empty($sOutTime) ? '' : ', '.$sOutTime),$intDate);

  // Translating

  global $L;
  if ( isset($L['dateSQL']) && is_array($L['dateSQL']) )
  {
    $sDate = str_replace(array_keys($L['dateSQL']),array_values($L['dateSQL']),$sDate);
    $sDateFull = str_replace(array_keys($L['dateSQL']),array_values($L['dateSQL']),$sDateFull);
  }

  // Exit

  if ( $title===false ) return $sDate;
  return '<span'.(empty($titleid) ? '' : ' id="'.$titleid.'" ').' title="'.QTstrh($sDateFull).'">'.QTstrh($sDate).'</span>';
}

// ============
// QTbbc
// ------------
// Convert bbc to html
// ------------
// $str       : [mandatory] a string than can contains bbc tags
// $nl        : convert \r\n, \r or \n to $nl. Use FALSE to not convert.
// $beforediv : (optional) tag to use before a bloc ([quote] or [code])
// $afterdiv  : (optional) tag to use after a bloc ([quote] or [code])
// ------------
// Examples
// QTbbc( '[b]Text[/b]')        -->   <b>Text</b>
// QTbbc( '[i]<b>Text<b>[/i]')  -->   <i>&lt;b&gt;Text&lt;/b&gt;</i>
// ============

function QTbbc($str,$nl='<br />',$beforediv='',$afterdiv='')
{
  // check

  if ( !is_string($str) ) die('QTbbc: arg #1 must be a string');
  if ( !is_string($nl) ) die('QTbbc: arg #3 must be a string');

  // process

  $arrSearch = array (
  '/</',
  '/>/',
  '/\[b\](.*?)\[\/b\]/',
  '/\[i\](.*?)\[\/i\]/',
  '/\[u\](.*?)\[\/u\]/',
  '/\[\*\]/',
  '/\[img\](.*?)\[\/img\]/',
  '/\[url\](.*?)\[\/url\]/',
  '/\[url\=(.*?)\](.*?)\[\/url\]/',
  '/\[mail\](.*?)\[\/mail\]/',
  '/\[mail\=(.*?)\](.*?)\[\/mail\]/',
  '/\[color\=(.*?)\](.*?)\[\/color\]/',
  '/\[size=(.*?)\](.*?)\[\/size\]/',
  '/\[quote\]/',
  '/\[quote\=(.*?)\]/',
  '/\[\/quote\]/',
  '/\[code\]/',
  '/\[\/code\]/');

  $arrReplace = array (
  '&lt;',
  '&gt;',
  '<b>$1</b>',
  '<i>$1</i>',
  '<span class="u">$1</span>',
  '&bull;',
  '<div class="imgmsg"><img class="imgmsg" src="$1" alt="[image]" title=""/></div>',
  '<a class="msgbody" href="http://$1" target="_blank">$1</a>',
  '<a class="msgbody" href="http://$2" target="_blank">$1</a>',
  '<a class="msgbody" href="mailto:$1">$1</a>',
  '<a class="msgbody" href="mailto:$2">$1</a>',
  '<font color="$1">$2</font>',
  '<span style="font-size:$1pt">$2</span>',
  $beforediv.'<div class="quotetitle">Quotation:</div><div class="quote">',
  '<div class="quotetitle">Quotation by $1:</div><div class="quote">',
  '</div>'.$afterdiv,
  $beforediv.'<div class="codetitle">Code:</div><div class="code">',
  '</div>'.$afterdiv);

  $str = preg_replace( $arrSearch, $arrReplace, $str );
  $str = str_replace( array('http://http','http://ftp:','http://mailto:','mailto:mailto:'), array('http','ftp:','mailto:','mailto'), $str ); // special check for the href error
  if ( is_string($nl) ) $str = str_replace( array("\r\n","\r","\n"), $nl, $str );

  return $str;
}

// --------

function QTconv($str,$to='1',$bConvAmp=false,$bDroptags=true)
{
  if ( empty($str) ) return $str;
  if ( !is_string($str) ) die('QTconv: arg #1 must be a string');
  if ( !is_string($to) ) die('QTconv: arg #2 must be a string');
  if ( !is_bool($bConvAmp) ) die('QTconv: arg #3 must be a boolean');
  if ( !is_bool($bDroptags) ) die('QTconv: arg #4 must be a boolean');

  // optional drop tags and &

  if ( $bDroptags ) $str = strip_tags($str);
  if ( $to=='3' && $bConvAmp ) $to='4';

  // U special for username and password
  // I special for input form: convert & alone to &amp;
  // 1 converts "          // -1 converts &quot;
  // 2 converts " '        // -2 converts &quot; &#039;
  // 3 converts " ' < >    // -3 converts &quot; &#039; &lt; &gt;
  // 4 converts " ' < > &  // -4 converts &quot; &#039; &lt; &gt; &amp;
  // 5 converts to htmlentities but restore the &amp; > &
  // 6 converts to htmlentities

  switch($to)
  {
  case 'U':
    return substr(htmlspecialchars(trim($str),ENT_QUOTES),0,24);
    break;
  case 'I':
    if ( strstr($str,'&') )
    {
    $str = str_replace('&','&amp;',$str);
    $str = str_replace('&amp;quot;','&quot;',$str);
    $str = str_replace('&amp;#039;','&#039;',$str);
    }
    break;
  case '1':
    $str = str_replace('"','&quot;',$str);
    break;
  case '2':
    $str = str_replace('"','&quot;',$str);
    $str = str_replace("'",'&#039;',$str);
    break;
  case '3':
    $str = str_replace('"','&quot;',$str);
    $str = str_replace("'",'&#039;',$str);
    $str = str_replace('<','&lt;',$str);
    $str = str_replace('>','&gt;',$str);
    break;
  case '4':
    $str = htmlspecialchars($str,ENT_QUOTES);
    break;
  case '5':
    $str = htmlentities($str,ENT_QUOTES);
    if ( strstr($str,'&') ) $str = str_replace('&amp;','&', $str);
    break;
  case '6':
    $str = htmlentities($str,ENT_QUOTES);
    break;
  case '-1':
    $str = str_replace('&quot;','"',$str);
    break;
  case '-2':
    $str = str_replace('&quot;','"',$str);
    $str = str_replace('&#039;',"'",$str);
    break;
  case '-3':
    $str = str_replace('&quot;','"',$str);
    $str = str_replace('&#039;',"'",$str);
    $str = str_replace('&lt;','<',$str);
    $str = str_replace('&gt;','>',$str);
    break;
  case '-4':
    $str = str_replace('&quot;','"', $str);
    $str = str_replace('&#039;',"'", $str);
    if ( strstr($str,'&') )
    {
    $str = str_replace('&amp;','&', $str);
    $str = str_replace('&#39;',"'", $str);
    $str = str_replace('&lt;','<', $str);
    $str = str_replace('&gt;','>', $str);
    }
    break;
  }
  if ( isset($str[4000]) ) $str = substr($str,0,4000);
  return trim($str);
}

// ------------
// QTispassword / islogin / ismail /isbetween / isvaliddate
// ------------
// These functions shows an error message when the principal argument(s) is not of the correct type.
// About login/password:
//   Return FALSE if the text is not trimmed
//   Return FALSE when text includes unacceptable characters: the characters < > " ' must be converted to html entities before.
//   All other characters are valid. Note that & is also valid, but is always converted to &amp; before.
// About validdate:
//   This function will check date like YYYYMMDD (as string or as number). Options allow also to rejet past/futur year.
// ------------

function QTislogin($str,$intMin=4,$intMax=64,$trim=false)
{
  if ( !is_string($str) || !is_int($intMin) || !is_int($intMax) || !is_bool($trim) ) die('QTislogin: invalid arguments');
  if ( $trim && $str!=trim($str) ) return false;
  if ( isset($str[$intMax]) ) return false; //length > $intMax
  if ( !isset($str[$intMin-1]) ) return false; //length < $intMin
  return true;
}
function QTispassword($str,$intMin=4,$intMax=40)
{
  if ( !is_string($str) ) die('QTispassword: arg #1 must be a string');
  if ( !is_int($intMin) ) die('QTispassword: arg #2 must be an int');
  if ( !is_int($intMax) ) die('QTispassword: arg #3 must be an int');
  // uses QTislogin
  if ( !QTislogin($str,$intMin,$intMax) ) return false;
  return true;
}
function QTismail($str)
{
  if ( !is_string($str) ) die('QTismail: arg #1 must be a string');

  if ( $str!=trim($str) ) return false;
  if ( $str!=strip_tags($str) ) return false;
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$str) ) return false;
  return true;
}
function QTisbetween($intValue,$intMin=0,$intMax=99999)
{
  if ( $intValue==='') return false;
  if ( !is_numeric($intValue) ) return false;
  if ( !is_numeric($intMin) ) die('QTisbetween: arg #2 must be a numeric (or a number as string)');
  if ( !is_numeric($intMax) ) die('QTisbetween: arg #3 must be a numeric (or a number as string)');
  if ( $intValue<$intMin ) return false;
  if ( $intValue>$intMax ) return false;
  return true;
}
function QTisvaliddate($d,$bPast=true,$bFutur=false) // allow past year, disallow futur year
{
  if ( is_string($d) ) { if ( substr($d,0,6)=='Cannot' ) return false; }
  if ( !is_numeric($d) ) return false;
  if ( !is_bool($bPast) ) die('QTisvaliddate: arg #2 must be a bolean');
  if ( !is_bool($bFutur) ) die('QTisvaliddate: arg #3 must be a bolean');

  $str = strval($d);
  if ( strlen($str)!=8 ) return false;
  $intY = intval(substr($str,0,4));
  $intM = intval(substr($str,4,2));
  $intD = intval(substr($str,-2,2));
  if ( $intY<1900 ) return false;
  if ( $intM<1 || $intM>12 ) return false;
  if ( $intD<1 || $intD>31 ) return false;
  if ( !$bPast ) { if ( $intY<date('Y') ) return false; }
  if ( !$bFutur ) { if ( $intY>date('Y') ) return false; }
  if ( !checkdate($intM,$intD,$intY) ) return false;
  return true;
}
function QTisvalidtime($d)
{
  if ( is_string($d) ) { if ( substr($d,0,6)=='Cannot' ) return false; }
  if ( !is_numeric($d) ) return false;

  $d = strval($d);
  if ( strlen($d)!=4 && strlen($d)!=6 ) return false;
  if ( !QTisbetween(substr($d,0,2),0,23) ) return false;
  if ( !QTisbetween(substr($d,2,2),0,59) ) return false;
  if ( strlen($d)==6 ) { if ( !QTisbetween(substr($d,4,2),0,59) ) return false; }
  return true;
}

// --------

function QTunbbc($str,$bDeep=true)
{
  if ( !is_string($str) ) die('QTunbbc: arg #1 must be a string');
  if ( empty($str) ) return $str;
  return preg_replace( array('/\[b\](.*?)\[\/b\]/','/\[i\](.*?)\[\/i\]/', '/\[u\](.*?)\[\/u\]/', '/\[\*\]/', '/\[img\](.*?)\[\/img\]/', '/\[url\](.*?)\[\/url\]/', '/\[url\=(.*?)\](.*?)\[\/url\]/', '/\[mail\](.*?)\[\/mail\]/', '/\[mail\=(.*?)\](.*?)\[\/mail\]/', '/\[color\=(.*?)\](.*?)\[\/color\]/', '/\[size=(.*?)\](.*?)\[\/size\]/', '/\[quote\]/', '/\[quote\=(.*?)\]/', '/\[\/quote\]/', '/\[code\]/', '/\[\/code\]/') , array('$1','$1','$1','$1','$1','$1','$1','$1','$1','$1','$1',($bDeep ? '' : 'Quotation: '),($bDeep ? '' : 'Quotation by $1'),'',($bDeep ? '' : 'Code: '),'') , $str );
}

// --------

function QTcompact($str,$max=200,$nl="\r\n")
{
  if ( !is_string($str) ) die('QTcompact: arg #1 must be a string');
  if ( empty($str) ) return $str;
  if ( $max>5 ) $str= QTtrunc($str,$max);
  $str = str_replace("\r\n\r\n\r\n",$nl,$str);
  $str = str_replace("\r\n\r\n",$nl,$str);
  if ( strpos($str,'[')!==FALSE )
  {
  $str = str_replace("[/quote]\r\n",'[/quote]',$str);
  $str = str_replace("[/code]\r\n",'[/code]',$str);
  }
  return $str;
}

// --------

function QThttpvar($arrV,$arrT,$bStriptags=true,$bGet=true,$bPost=true)
{
  // Assign values Http GET or POST to the variables. The values are assigned with the specific type.
  // $arrV      is the list of variables to create from the http get/post [can be a string of names separated by space]
  // $arrT      is the list of desired variable types: 'int','str','boo' or 'flo' [can be a string of names separated by space]
  // $bStiptags strip the tags when the type string is requested (to avoid injection)
  // $bGet      accept/reject variables send by Http GET method
  // $bPost     accept/reject variables send by Http POST method
  // Ex: QThttpvar('a b c','str boo int');
  // Note #1: When a user try to inject new variables, they will not be created (only variables in the list are parsed).
  // Note #2: It's recommended to initialise the variables before using this assigment function.
  // Note #3: When values are not send by Http get/post, the initial variable remains unchanged (can be a new variable with NULL value if the variable was not initialised).
  // Note #4: When you request the type 'boo' (boolean), the variable is set to TRUE when http get/post is '1', for all other values the variable is set to FALSE.
  if ( is_string($arrV) ) $arrV=explode(' ',$arrV);
  if ( is_string($arrT) ) $arrT=explode(' ',$arrT);
  if ( !is_array($arrV) || !is_array($arrT)) die('QThttpvar: arrV and arrT must be arrays.');
  if ( count($arrV)!=count($arrT) ) die('QThttpvar: arrV and arrT must be the same size.');

  $i=0;
  foreach($arrV as $strV)
  {
    $strT = $arrT[$i];
    global $$strV;
    if ( $bGet && isset($_GET[$strV]) )
    {
      $_GET[$strV]=trim($_GET[$strV]);
      switch($strT)
      {
      case 'int': $$strV=intval($_GET[$strV]); break;
      case 'str': $$strV=($bStriptags ? strip_tags($_GET[$strV]) : $_GET[$strV]); break;
      case 'boo': $$strV=($_GET[$strV]==='1' ? true : false); break;
      case 'flo': $$strV=floatval($_GET[$strV]); break;
      default: die('QThttpvar: Invalid data type ['.$strT.']');
      }
    }
    if ( $bPost && isset($_POST[$strV]) )
    {
      $_POST[$strV]=trim($_POST[$strV]);
      switch($strT)
      {
      case 'int': $$strV=intval($_POST[$strV]); break;
      case 'str': $$strV=($bStriptags ? strip_tags($_POST[$strV]) : $_POST[$strV]); break;
      case 'boo': $$strV=($_POST[$strV]==='1' ? true : false); break;
      case 'flo': $$strV=floatval($_POST[$strV]); break;
      default: die('QThttpvar: Invalid data type ['.$strT.']');
      }
    }
    ++$i;
  }
}