<?php

/*
qt_lib_sys.php
------------
version: 4.7 build:20160703
This is a library of public functions
------------
Error
Href
GetLang
Translate
L
QTcheckL
QTiso
QTargs
QTasTag
QTarradd
QTexplodeIni
QTimplodeIni
QTexplodeUri
QTimplodeUri
QTuritoform

: v4.6 changes
: APPCST is removed
: QTargexplode is depreciated, use QTexplodeUri instead
: QTargimplode is depreciated, use QTimplodeUri instead
*/

// --------

function CanPerform($strParam,$strRole='V')
{
  // valid parameter are: upload, show_calendar, show_stats
  if ( empty($strParam) || !isset($_SESSION[QT][$strParam]) ) return false;
  if ( $_SESSION[QT][$strParam]=='A' && $strRole=='A' ) return true;
  if ( $_SESSION[QT][$strParam]=='M' && ($strRole=='A' || $strRole=='M') ) return true;
  if ( $_SESSION[QT][$strParam]=='U' && $strRole!='V' ) return true;
  if ( $_SESSION[QT][$strParam]=='V' ) return true;
  return false;
}

// --------

function Error($i=0)
{
  include Translate(APP.'_error.php');
  if ( isset($e[$i]) ) return $e[$i];
  return 'Error '.$i;
}

// --------

function Href($str='',$ext='.html')
{
  // When urlrewriting is active, the url can be displayed in html format (they will be converted by the server's rewrite rule).
  // This function transforms a php url into a html like url (the url can have arguments): 'qnm_login.php' is displayed as 'login.html'.
  // Note: Don't worry, server's rewriting has NO effect when the url is in php format (i.e. when this function is not used or when QTx_URLREWRITE is FALSE)
  if ( empty($str) ) { global $oVIP; $str=$oVIP->selfurl; }
  if ( empty($str) ) $str=$_SERVER['PHP_SELF'];
  if ( constant(strtoupper(APP).'_URLREWRITE') && substr($str,0,4)==APP && strstr($str,'.php') )
  {
    $str = substr($str,4);
    $str = str_replace('.php',$ext,$str);
  }
  return $str;
}

// ---------

function GetLang($str='')
{
  if ( empty($str) ) $str = GetSetting('language','english',true);
  return 'language/'.$str.'/';
}

// --------

function GetSettings($where='',$register=false)
{
  // Returns settings [array] matching with $where condition (use '' to get ALL settings)
  // Can also register key-value in $_SESSION[QT]
  global $oDB;
  $arr = array();
  $oDB->Query( 'SELECT param,setting FROM '.TABSETTING.(empty($where) ? '' : ' WHERE '.$where) );
  while ($row = $oDB->Getrow())
  {
  $arr[$row['param']]=strval($row['setting']);
  if ( $register ) $_SESSION[QT][$row['param']]=strval($row['setting']);
  }
  return $arr;
}

// ---------

function GetSetting($key='',$dflt='',$db=false)
{
  // This returns a string (can be empty string if key not found and no default)
  if ( empty($key) || !is_string($key) || !is_string($dflt) ) die('GetSetting: wrong type for key or default');

  // Read from session
  if ( isset($_SESSION[QT][$key]) ) return $_SESSION[QT][$key];

  // Read from database
  if ( $db )
  {
  global $oDB;
  $oDB->Query('SELECT setting FROM '.TABSETTING.' WHERE param="'.$key.'"');
  $row=$oDB->Getrow();
  if ( isset($row['setting']) ) { $_SESSION[QT][$key] = (string)$row['setting']; return $_SESSION[QT][$key]; }
  }

  // Uses default
  return $dflt;
}

// --------

function Translate($strFile)
{
  if ( file_exists(GetLang().$strFile) ) Return GetLang().$strFile;
  Return 'language/english/'.$strFile;
}

// --------

function UseModule($strName=null)
{
  if ( !is_string($strName) ) die('UseModule: arg #1 must be a string');
  if ( isset($_SESSION[QT]['module_'.$strName]) ) return TRUE;
  return FALSE;
}

// --------

function L($key='',$int=false,$bInclude=true)
{
  // Returns the corresponding word or the lowercase version of the word from the $L dictionnary [array] in the language file
  // Examples for a french language file:
  // L('Password') returns 'Mot de passe'
  // L('password') returns 'mot de passe' (Note: if a lowercase version is defined that word is returned)

  // Also searches the plural word if necessary: i.e. when $int>1 (plural version of a word can be define in the language file by $key+'s')
  // In case of $int is set (<>false) and $bInclude is true, the $int value is added before the word. ($int can be negative or 0)
  // L('Domain',0) returns '0 Domaine'
  // L('domain',1) returns '1 domaine'
  // L('Domain',2) returns '2 Domaines'
  // Note: when the plural version (key+'s') is not defined in the language file the function returns the singular word.

  // Fallback:
  // If the requested word (key) is not defined in the language file, the function returns the key itself (without '_')
  // L('Unknown_key') returns 'Unknown key'
  // A key like 'E_aaa' (used to describe an error code) will be converted to 'Error aaa' when not defined in the language file.

  // Debug:
  // If you define a session variable 'QTdebuglang' set to '1', the function will show in red the key not defined in the language file.

  global $L;
  if ( isset($L[$key]) )
  {
    $str = $L[$key];
    if ( $int!==false ) { if ( $int>1 && isset($L[$key.'s']) ) $str = $L[$key.'s']; }
  }
  elseif ( isset($L[ucfirst($key)]) )
  {
    $str = strtolower($L[ucfirst($key)]);
    if ( $int!==false ) { if ( $int>1 && isset($L[ucfirst($key.'s')]) ) $str = strtolower($L[ucfirst($key.'s')]); }
  }
  else
  {
  $str = str_replace('_',' ',$key); // When word is missing, returns the key code without _
  if ( substr($key,0,2)==='E_' ) $str = 'error: '.substr($str,2);
  if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang']==='1' ) $str = '<span style="color:red">'.$str.'</span>';
  }
  return ($int!==false && $bInclude ? $int.' ' : '').$str; // When $int<>false (and $bInclude is true) the value is merged with the word
}

// --------

function QTcheckL($arr)
{
  if ( is_string($arr) ) $arr=explode(';',$arr);
  if ( !is_array($arr) ) die('QTcheckL: arg #1 be an array');
  foreach($arr as $str) if ( !isset($_SESSION['L'][$str]) ) $_SESSION['L'][$str] = cLang::Get($str,QTiso(),'*');
}

// --------

function QTiso($str='')
{
  if ( empty($str) ) $str = GetSetting('language','english',true);
  switch(strtolower($str))
  {
  case 'english': return 'en'; break;
  case 'francais': return 'fr'; break;
  case 'nederlands': return 'nl'; break;
  case 'italiano': return 'it'; break;
  case 'espanol': return 'es'; break;
  default: include 'bin/'.APP.'_lang.php'; $arr=array_flip(QTarrget($arrLang,2)); if ( isset($arr[$str]) ) return $arr[$str]; break;
  }
  return 'en';
}

// --------

// This function allow cheching argument types: The value in $arrArgs must be of type specified in $arrTypes
// Application stops when the value is not of the specified type.
// Note 1: The type 'empty' means that the application stops if the value IS empty.
// Note 2: When $arrTypes is one type, this type is converted to a list of types

function QTargs($str='Error',$arrArgs,$arrTypes='str')
{
  if ( !is_string($str) ) die('QTargs: Argument #1 must be a string');
  if ( !is_array($arrArgs) ) die('QTargs: Argument #2 must be an array');
  // last argument can be one string meaning: an array of n time this string is created
  if ( is_string($arrTypes) ) { $s=$arrTypes; $arrTypes=array(); foreach($arrArgs as $a) $arrTypes[]=$s; }
  if ( !is_array($arrTypes) ) die('QTargs: Argument #3 must be an array');
  if ( count($arrTypes)!=count($arrArgs) ) die('QTargs: Argument #2 and #3 are not the same size');

  // Process

  for($i=0;$i<count($arrArgs);++$i) {
  switch($arrTypes[$i]) {
  case 'str': if ( !is_string($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be a string'); break;
  case 'int': if ( !is_int($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be an int'); break;
  case 'arr': if ( !is_array($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be an array'); break;
  case 'flo': if ( !is_float($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be a float'); break;
  case 'boo': if ( !is_bool($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be a boolean'); break;
  case 'empty': if ( empty($arrArgs[$i]) ) die($str.': Argument #'.$i.' is empty'); break;
  }}
}

// --------

// arrAttr can includes (S means selected & C current):
// format,name,endline,current,class,classS,classC,style,styleS,styleC

function QTasOption($arr,$valSelected='',$arrAttr=array(),$arrDisabled=array()) { return QTasTag($arr,$valSelected,$arrAttr,'option',$arrDisabled); }
function QTasHidden($arr,$valSelected='',$arrAttr=array()) { return QTasTag($arr,$valSelected,$arrAttr,'hidden'); }
function QTasCheckbox($arr,$valSelected='',$arrAttr=array(),$arrDisabled=array()) { return QTasTag($arr,$valSelected,$arrAttr,'checkbox',$arrDisabled); }
function QTasSpan($arr,$valSelected='',$arrAttr=array()) { return QTasTag($arr,$valSelected,$arrAttr,'span'); }
function QTasTag($arr,$valSelected='',$arrAttr=array(),$strTag='option',$arrDisabled=array(),$eol='',$asHtml=true)
{
  QTargs( 'QTasTag',array($arr,$arrAttr,$strTag),array('arr','arr','str') ); // valSelected can be str or int
  $strReturn = '';
  foreach($arr as $strKey=>$strValue)
  {
    // format the value
    if ( !is_string($strKey) ) $strKey = (string)$strKey;
    if ( is_array($strValue) ) $strValue = reset($strValue);
    if ( isset($arrAttr['format']) ) $strValue = sprintf($arrAttr['format'],$strValue);
    if ( !is_string($strValue) ) $strValue = (string)$strValue;
    if ( $asHtml===true ) $strValue = QTstrh($strValue);

    $strName='';
      if ( isset($arrAttr['name']) ) $strName=$arrAttr['name'];
    $strClass='';
      if ( isset($arrAttr['class']) ) $strClass=$arrAttr['class'];
      if ( isset($arrAttr['classS']) ) { if ( strlen($valSelected)>0 && $valSelected==$strKey ) $strClass=$arrAttr['classS']; }
      if ( isset($arrAttr['current']) && isset($arrAttr['classC']) ) { if ( $arrAttr['current']==$strKey ) $strClass=$arrAttr['classC']; }
    $strStyle='';
      if ( isset($arrAttr['style']) ) $strStyle=$arrAttr['style'];
      if ( isset($arrAttr['styleS']) ) { if ( strlen($valSelected)>0 && $valSelected==$strKey ) $strStyle=$arrAttr['styleS']; }
      if ( isset($arrAttr['current']) && isset($arrAttr['styleC']) ) { if ( $arrAttr['current']==$strKey ) $strStyle=$arrAttr['styleC']; }
    switch($strTag)
    {
    // attention: $valSelected==$strKey (not ===)
    case 'option': $strReturn .= '<option value="'.QTstrh($strKey).'"'.(empty($strClass) ? '' : ' class="'.$strClass.'"').(empty($strStyle) ? '' : ' style="'.$strStyle.'"').($valSelected==$strKey ? ' selected="selected"' : '').(in_array($strKey,$arrDisabled,true) ? ' disabled="disabled" ': '').'>'.QTstrh($strValue).'</option>'; break;
    case 'checkbox': $strReturn .= '<input type="checkbox" value="'.QTstrh($strKey).'"'.(empty($strClass) ? '' : ' class="'.$strClass.'"').(empty($strStyle) ? '' : ' style="'.$strStyle.'"').(empty($strName) ? '' : ' name="'.$strName.'"').(in_array($strKey,$arrDisabled,true) ? ' disabled="disabled" ': '').'/>'.QTstrh($strValue); break;
    case 'hidden': $strReturn .= '<input type="hidden" name="'.QTstrh($strKey).'" value="'.QTstrh($strValue).'"/>'; break;
    case 'span': $strReturn .= '<span'.(empty($strClass) ? '' : ' class="'.$strClass.'"').'>'.QTstrh($strValue).'</span>'.(isset($arrAttr['endline']) ? $arrAttr['endline'] : '' ); break;
    default: die('HtmlTags: Invalid argument #2');
    }
    if ( !empty($eol) ) $strReturn .= $eol;
  }
  return $strReturn;
}

// --------

function QTexplodeStr($sep=';,',$str,$max=null,$bClean=true,$bTrim=true)
{
  // Same as explode but using several separators [array] $sep (or each char in the [string] $sep)
  // Note: Each exploded string is NOT trimmed, $bTrim only trims the source $str.
  //       When using long separators, put separators in an [array] $sep having keys [int] 0..i
  //       $bClean allow removing double separators in the source $str.
  if ( is_string($sep) ) $sep = str_split($sep);
  if ( !is_array($sep) || !is_string($str) ) die('QTexplodechars: invalid argument');
  if ( $bTrim ) $str = trim($str);
  if ( count($sep)>1 )
  {
  $str = str_replace($sep, $sep[0], $str); // all separators are translated to primary separator
  if ( $bClean ) while ( strpos($str,$sep[0].$sep[0])!==false ) $str = str_replace($sep[0].$sep[0],$sep[0],$str); // remove duplicate separator
  }
  if ( is_int($max) ) return explode($sep[0],$str,$max);
  return explode($sep[0],$str);
}

// --------

function QTarradd($arr,$strKey,$strValue=null)
{
  // Add (or remove) a key+value to the array.
  // When $strValue is null, the key is not set (or removed if existing)
  if ( !is_array($arr) ) die('QTarradd: arg #1 must be an array');
  if ( !is_string($strKey) ) die('QTarradd: arg #2 must be a string');
  if ( isset($arr[$strKey]) ) unset($arr[$strKey]);
  if ( is_null($strValue) ) return $arr;
  $arr[$strKey] = $strValue;
  return $arr;
}

// --------

function QTarrget($arr,$key='title')
{
  // Converts an array of arrays into a simple array where the values are the [$key]element of each array (indexes are preserved).
  // When the [$key]element doesn't existing, the result will include a NULL.
  // If one element of $arr is not an array, it REMAINS in the result. $key can be integer or string.
  if ( !is_array($arr) ) die('QTarrget: arg #1 must be an array');
  foreach($arr as $k=>$a) {
  if ( is_array($a) ) {
    if ( isset($a[$key]) ) { $arr[$k]=$a[$key]; } else { $arr[$k]=null; }
  }}
  return $arr;
}

// --------

function QTexplode($str,$sep=';',$function='') { return QTexplodeIni($str,$sep,$function); } // Obsolete since v3.0, use QTexplodeIni instead
function QTexplodeIni($str,$sep=';',$function='')
{
  // From a string "key1=value1;key2=value2" returns an array of key=>value.
  // When $str is empty or when there is no "=" the function returns an empty array
  // When duplicate keys exist, the last value overwrites previous values
  // A $function can be applied to each value (ex "urldecode" or "strtolower")

  if ( empty($str) ) return array();
  if ( !empty($function) && !function_exists($function) ) die('QTexplode: requested function ['.$function.'] is unknown');
  $arr = explode($sep,$str);
  $arrArgs = array();
  foreach($arr as $str)
  {
    if ( strstr($str,'=') )
    {
    $arrPart = explode('=',$str);
    $arrArgs[$arrPart[0]]= (empty($function) ? $arrPart[1] : $function($arrPart[1]));
    }
  }
  return $arrArgs;
}


// --------

function QTimplode($arr,$sep=';',$fx='') { return QTimplodeIni($arr,$sep,$fx); } // Obsolete since v3.0, use QTimplodeIni instead
function QTimplodeIni($arr,$sep=';',$fx='')
{
  // Build a string "key1=value1;key2=value2" from the array. Returns '' when the array is empty.
  // A $function can be applied to each value (ex "urlencode" or "strtolower")

  if ( !is_array($arr) ) die('QTimplode: arg #1 must be an array');
  if ( !is_string($sep) ) die('QTimplode: arg #2 must be a string');
  if ( !empty($fx) && !function_exists($fx) ) die('QTimplode: requested function ['.$fx.'] is unknown');

  if ( count($arr)==0 ) return '';
  $str = '';
  foreach($arr as $key=>$value)
  {
  if ( !empty($fx) ) $value = $fx($value);
  $str .= ($str==='' ? '' : $sep).$key.'='.$value;
  }
  return $str;
}

// --------

function QTexplodeUri($str='',$urldecode=false)
{
  // Same as QTexplodeIni() but for url arguments (separated by & or by &amp;)
  // If $str is empty, the current URI is used. If URI contains full URL, the ? right part is used.
  // By default each argument is not urldecoded.

  if ( empty($str) )
  {
    $arr = parse_url($_SERVER['REQUEST_URI']);
    if ( !isset($arr['query']) ) return array();
    $str = $arr['query'];
    if ( empty($str) ) return array();
  }
  else
  {
    // drop url part to keep uri part
    if ( strstr($str,'?') )
    {
    $arr = explode('?',$str); if ( empty($arr[1]) ) return array();
    $str = $arr[1];
    }
  }
  $str = str_replace('&amp;','&',$str);
  return QTexplodeIni($str,'&',($urldecode ? 'urldecode' : ''));
}

// --------

function QTimplodeUri($arr,$urlencode=false,$sep='&amp;')
{
  // Same as QTimplodeIni() but for url arguments. By default each argument is NOT urlencoded.
  return QTimplodeIni($arr,$sep,($urlencode ? 'urlencode' : ''));
}

// --------

function QTuritoform($uri,$bDropNullString=true,$reject=array())
{
  // Convert an uri to a serie of hidden-input
  // $uri can be an array or an url string
  // $reject is the list of arguments that must be dropped (can be an array of a csv-string)

  if ( is_string($uri) ) $uri = QTexplodeUri($uri,true); // values are urldecoded
  if ( !is_array($uri) ) die('QTuritoform: invalid argument #1');

  // $reject to remove parametres from the URI (can be csv)
  if ( is_string($reject) ) $reject=explode(',',$reject);
  if ( !is_array($reject) ) die('QTuritoform: invalid argument #3');

  $str = '';
  foreach($uri as $key=>$value)
  {
    if ( !empty($key) && !in_array($key,$reject) )
    {
    if ( $bDropNullString && $value=='' ) continue;
    $str .= '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
    }
  }
  return $str;
}

// --- DEPRECIATED ---

function QTargexplode($str='') { return QTexplodeUri($str,false); }
function QTargimplode($arr,$sep='&amp;') { return QTimplodeUri($arr,false,$sep); }