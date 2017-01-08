<?php

// QT re-usable component 1.2 build:20160703

// Memcache usage - This mechanism put in memory frequently used objects (i.e. list of domains, sections, statuses...)
// sMem::CreateCache() Initialize and open connection (if MEMCACHE_HOST is defined). Can return false with a warning message.
// sMem::Get() ::Set() ::Clear() Use memcache library OR session variables (i.e. when memcache is disable or when memcache daemon faill to respond)
// sMem::memcacheGet()
// sMem::memcacheSet()
// sMem::memcacheAppend()
// sMem::memcacheClear() Use memcache only
//
// Note - These functions return FALSE in case of failure (when memcache not enabled or daemon not responding). It's thus not recommended to store FALSE in shared memory

// About sMem::Get()
// As memcache has a timeout (or daemon may be not responding),
// sMem::Get() is designed to REGENERATE the values when they cannot be fecthed from memory.
// Using sMem::Get() to initialize memory is thus recommended, for these frequently used objects values.
// BUT if you use sMem::Get() with other keys, these may be regenerated to $default value ! (at your own risk)

class sMem {

public static function CreateCache(&$warning)
{
  $memcache=false;
  if ( MEMCACHE_HOST )
  {
    if ( class_exists('Memcache') )
    {
    $memcache = new Memcache;
    if ( !$memcache->connect(MEMCACHE_HOST,MEMCACHE_PORT) ) { $warning='Unable to contact memcache daemon ['.MEMCACHE_HOST.' port '.MEMCACHE_PORT.']. Turn this option to false in bin/config_web.php...'; $m=false; }
    }
    else
    {
    $warning='Memcache library not found. Turn this option to false in bin/config_web.php...';
    $memcache=false;
    }
  }
  return $memcache;
}

public static function Get($key,$default=false)
{
  // Fetch (and reset) sys-object in shared memory: sys_domains,sys_sections,...
  // Attention:
  // Fetching OTHER data MAY put and return the $default value in shared memory (i.e. when shared memory is timeout)

  $obj = sMem::memcacheGet($key);
  if ( $obj===false )
  {
    // Check session if not in memcache
    if ( MEMCACHE_FAILOVER && isset($_SESSION[QT][$key]) ) return $_SESSION[QT][$key];
    // Regenerate when not in memory
    $obj = cVIP::SysInit($key,$default); // When $key is not the name of a sys-object, SysInit() just returns the $default value
    sMem::Set($key,$obj);
  }
  return $obj;
}

public static function Set($key,$obj,$timeout=MEMCACHE_LIVETIME)
{
  if ( sMem::memcacheSet($key,$obj,$timeout)===false && MEMCACHE_FAILOVER ) $_SESSION[QT][$key]=$obj;
}

public static function memcacheGet($key)
{
  global $memcache; return $memcache ? $memcache->get($key) : false;
}

public static function memcacheSet($key,$obj,$timeout=MEMCACHE_LIVETIME)
{
  global $memcache; return $memcache ? $memcache->set($key,$obj,0,$timeout) : false;
}

public static function memcacheAppend($key,$obj,$timeout=MEMCACHE_LIVETIME)
{
  // Attention, this works only if value in cache and new value are string, otherwise the new value REPLACES the value in cache.
  // Using append will also extend (i.e. reset) the timeout.
  if ( !is_string($obj) ) return false;
  global $memcache;
  $str = memcacheGet($key);
  if ( is_string($str) ) $obj = $str.$obj;
  return $memcache->set($key,$obj,0,$timeout);
}

public static function Query($key,$sql,$timeout=MEMCACHE_LIVETIME)
{
  // Caching a sql query a few minutes
  // Note this uses memcache only and NOT $_SESSION
  if ( empty($key) ) $key = md5(APP.$sql);

  // Get the cache from memcache
  if ( ($cache=sMem::memcacheGet($key))===false )
  {
    // If no cache response, runs the query to populate $cache
    $cache = false;
    global $oDB;
    if ( $oDB->Query($sql) )
    {
    $i = 0;
    while( $row=$oDB->Getrow() ) { $cache[$i]=$row; ++$i; }
    // Save $cache into the memcache. Attention if memcache daemon not running or not responding, this will failled (setCache just returns false)
    sMem::memcacheSet($key,$cache,$timeout);
    }
  }
  return $cache;
}
public static function QueryCount($key,$sql,$timeout=MEMCACHE_LIVETIME,$field=false)
{
  // This returns a db count [int] from memcache, or put the value in cache if not yet cached.
  // When running the query, this function uses the first column (as int).
  // An other column can be fetched ($field). Note that if $field cannot be found, the first column is used.
  if ( empty($key) ) $key = md5(APP.$sql);
  $cache = sMem::memcacheGet($key);
  if ( $cache===false )
  {
    global $oDB;
    if ( $oDB->Query($sql) )
    {
      $arr = $oDB->Getrow();
      $cache = (int)reset($arr); // first column
      if ( $field && isset($arr[$field]) ) $cache = (int)$arr[$field];
      sMem::memcacheSet($key,$cache,$timeout);
    }
  }
  return $cache;
}
public static function Clear($key)
{
   sMem::memcacheClear($key); if ( isset($_SESSION[QT][$key]) ) unset($_SESSION[QT][$key]);
}
public static function memcacheClear($key)
{
  global $memcache; return $memcache ? $memcache->delete($key) : false;
}

}
// ========

class cLang
{

  public static function Add($strType='',$strLang='en',$strId='',$strName='',$bCheck=false)
  {
    QTargs( 'cLang::Add',array($strType,$strLang,$strId,$strName,$bCheck),array('str','str','str','str','boo') );
    QTargs( 'cLang::Add',array($strType,$strLang,$strId,$strName),'empty' );

    // Process
    $prefix = strtoupper(substr(constant('QT'),0,3));
    if ( !defined($prefix.'_CONVERT_AMP') ) define($prefix.'_CONVERT_AMP',false);

    global $oDB;
    if ( $bCheck )
    {
    $oDB->Query('SELECT count(objid) AS countid FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objlang="'.strtolower($strLang).'" AND objid="'.$strId.'"');
    $row=$oDB->Getrow();
    if ( $row['countid']!=0 ) return False;
    }
    return $oDB->Exec('INSERT INTO '.TABLANG.' (objtype,objlang,objid,objname) VALUES ("'.$strType.'","'.strtolower($strLang).'","'.$strId.'","'.QTstrd($strName,4000).'")');
  }
  public static function Delete($strType='',$strId='')
  {
    if ( is_array($strType) ) $strType = implode('" OR objtype="',$strType);
    QTargs( 'cLang::Delete',array($strType,$strId) );
    QTargs( 'cLang::Delete',array($strType,$strId),'empty' );

    // Process

    global $oDB;
    return $oDB->Exec('DELETE FROM '.TABLANG.' WHERE (objtype="'.$strType.'") AND objid="'.$strId.'"');
  }
  public static function Get($strType='',$strLang='en',$strId='*')
  {
    // Return the object name (translated)
    // Can return an array of object names (in this language) when $strId is '*'
    // Can return an array of object translation when $strLang is '*'

    QTargs('cLang::Get',array($strType,$strLang,$strId));
    QTargs('cLang::Get',array($strType,$strLang,$strId),'empty');
    if ( $strId==='*' && $strLang==='*' ) Die('cLang::Get: Arg 2 and 3 cannot be *');

    // Process

    global $oDB;
    if ( $strId==='*' )
    {
      $arr = array();
      $oDB->Query('SELECT objid,objname FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objlang="'.strtolower($strLang).'"');
      while($row=$oDB->Getrow())
      {
        if ( !empty($row['objname']) ) $arr[$row['objid']]=$row['objname'];
      }
      return $arr;
    }
    elseif ( $strLang==='*' )
    {
      $arr = array();
      $oDB->Query('SELECT objlang,objname FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objid="'.$strId.'"');
      while($row=$oDB->Getrow())
      {
        $arr[$row['objlang']]=$row['objname'];
      }
      return $arr;
    }
    else
    {
      $oDB->Query('SELECT objname FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objlang="'.strtolower($strLang).'" AND objid="'.$strId.'"');
      $row=$oDB->Getrow();
      return (empty($row['objname']) ? '' : $row['objname']);
    }
  }
  public static function ObjectName($type='index',$id='i',$alt=true,$max=false,$end='...',$asHtml=true)
  {
    // This function returns the name of a system object using current session variable (thus in current language)
    // When translation is not defined, returns a default name or $alt when $alt is a string
    // Use $alt=false to return '' on missing translation
    // When $max>1, the text is truncated to $max characters and the string $trunc is added.
    // Note: type/id/name uses following storage format:
    // 'index'   'i'  >> index name
    // 'domain'  'd1' >> domain 1 name
    // 'sec'     's1' >> section 1 name
    // 'secdesc' 's1' >> section 1 description

    if ( !is_string($type) && !is_string($id) ) die('cLang::ObjectName: Arg #1 and #2 must be string');

    $str = '';
    if ( isset($_SESSION['L'][$type][$id]) ) $str = $_SESSION['L'][$type][$id];
    if ( empty($str) && $alt )
    {
      switch($type)
      {
      case 'sec': $str = (is_string($alt) ? $alt : '(section '.$id.')'); break;
      case 'domain': $str = (is_string($alt) ? $alt : '(domain '.$id.')'); break;
      case 'field': $str = ucfirst(str_replace('_',' ',$id)); break;
      case 'tab': $str = ucfirst(str_replace('_',' ',$id)); break;
      case 'index': $str = (empty($_SESSION[QT]['index_name']) ? 'Index' : $_SESSION[QT]['index_name']); break;
      case 'secdesc':
      case 'tabdesc':
      case 'ffield': $str = (is_string($alt) ? $alt : ''); break;
      default: return '(unknown object '.$type.')';
      }
    }
    if ( $asHtml===true ) $str = QTstrh($str);
    if ( is_int($max) && isset($str[$max]) ) $str = QTtrunc($str,$max,$end);
    return $str;
  }
}

// ========

class cStats
{
  // This uses dynamic properties
  // Any properties can be created (i.e. to create a property 'items' just set $oStats->items=0)
  // The properties are stored in a session variable (i.e. $_SESSION[QT]['sys_stat_items'])
  // It's USELESS to create several object cStats (there is only one storage per session)
  // NOTE: when properties is not defined, __get generate an error
  function __get($prop)
  {
    if ( !isset($this->$prop) && isset($_SESSION[QT]['sys_stat_'.$prop]) ) $this->$prop = $_SESSION[QT]['sys_stat_'.$prop];
    if ( isset($this->$prop) ) return $this->$prop;
    echo 'cStats: undefined properties '.$prop;
  }
  function __set($prop,$value)
  {
    $this->$prop = $value;
    $_SESSION[QT]['sys_stat_'.$prop] = $this->$prop;
  }
  public function RemoveProperty($prop)
  {
    if ( isset($_SESSION[QT]['sys_stat_'.$prop]) ) unset($_SESSION[QT]['sys_stat_'.$prop]);
    if ( isset($this->$prop) ) unset($this->$prop);
  }
}

// ========

class cMsg
{
  // This class handles a message comming from a previous page (session variable 'pagedialog')
  // The message is displayed thanks to a jquery function from *_page_header.
  // The session variable must be destroyed after display width cMsg::Reset()
  // cMsg::getType()
  // cMsg::getFulltext()
  // cMsg::getItems()
  // cMsg::getText()
  // The class can also be instanciated for backward compatibility (see after)

  public static function getType()
  {
    if ( !empty($_SESSION['pagedialog']) )
    {
    $arr = explode('|',$_SESSION['pagedialog']);
    return strtolower(substr($arr[0],0,1));
    }
    return 'o'; //i=info, e=error, w=warning, o=ok (default)
  }
  public static function getTypeIcon()
  {
    $type = cMsg::getType();
    switch($type)
    {
    case 'o': return '<i class="fa fa-check fa-3x" style="color:green"></i>'; break;
    case 'e': return '<i class="fa fa-times-circle fa-3x" style="color:red"></i>'; break;
    case 'w': return '<i class="fa fa-exclamation-triangle fa-3x" style="color:orange"></i>'; break;
    case 'u': return '<i class="fa fa-sign-out fa-3x" style="color:red"></i>'; break;
    case 'l': return '<i class="fa fa-sign-in fa-3x" style="color:green"></i>'; break;
    default: return '<i class="fa fa-exclamation-circle fa-3x" style="color:orange"></i>';
    }
  }

  public static function getFulltext()
  {
    if ( !empty($_SESSION['pagedialog']) )
    {
    $arr = explode('|',$_SESSION['pagedialog']);
    $i = isset($arr[2]) ? (int)$arr[2] : 0;
    if ( isset($arr[1]) ) return str_replace('"','',$arr[1]).($i>1 ? ' ('.$i.')' : '');
    }
    return '';
  }
  public static function getItems()
  {
    if ( !empty($_SESSION['pagedialog']) )
    {
    $arr = explode('|',$_SESSION['pagedialog']);
    if ( isset($arr[2]) ) return (int)$arr[2];
    }
    return 0;
  }
  public static function getText()
  {
    if ( !empty($_SESSION['pagedialog']) )
    {
    $str = cMsg::getFulltext();
    return (isset($str{65}) ? substr($str,0,60).'...' : $str);
    }
    return '';
  }
  public static function Reset()
  {
    $_SESSION['pagedialog']=null;
  }

  // For backward compatiblity, the non-static class

  public $text; // '' means nothing (no display)
  public $fulltext;
  public $type; // i=info, e=error, w=warning, o=ok (default)
  public $items; // affected items
  public function __construct()
  {
    if ( !empty($_SESSION['pagedialog']) ) $this->FromString($_SESSION['pagedialog']);
  }
  public function Clear()
  {
    $_SESSION['pagedialog']=null;
  }
  public function FromString($arr)
  {
    if ( is_string($arr) ) $arr=explode('|',$arr);
    if ( is_array($arr) )
    {
      if ( isset($arr[0]) ) $this->type = strtolower(substr($arr[0],0,1));
      if ( isset($arr[1]) ) $this->fulltext = str_replace('"','',$arr[1]);
      if ( isset($arr[2]) ) $this->items = (int)$arr[2];
      if ( $this->items>1 ) $this->fulltext .= ' ('.$this->items.')';
      $this->text = QTtrunc($this->fulltext,64);
    }
    return $this->text;
  }
}