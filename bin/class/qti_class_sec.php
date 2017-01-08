<?php

// QuickTicket 3.0 build:20160703

class cSection extends aQTcontainer
{

// aQTcontainer's extra properties
public $domname;       // Domain name
public $title;         // Section name (original)
public $name;          // Section name (translation of the title)
public $notify;        // Notify: 0=disable, 1=enabled
public $modid;         // Moderator id
public $modname;       // Moderaotr name
public $numfield;      // Format of the ref number ('N' means no ref number)
public $titlefield;    // Topic title: 0=None, 1=Optional, 2=Mandatory
public $wisheddate=0;  // Topic wisheddate: 0=None, 1=Optional, 2=Mandatory
public $wisheddflt=0;  // Topic wisheddate[2] default: 0=None, 1=Today, 2=Day+1, 3=Day+2
public $notifycc;      // Topic alternate notify: 0=None, 1=Optional, 2=Mandatory
public $prefix;        // Serie of the Prefix icons
public $options='';    // Several options (csv)
public $stats='';      // Several stats (not used)

// stats
// public $items=0;  // Total topics (including news) //already defined in aQTcontainer
public $itemsZ=0;    // Total topics closed (including news)
public $replies=0;   // Total replies (type R and F), not D
public $repliesZ=0;  // Total replies in topics closed (type R,F or  D)
public $tags=0;      // >0 means tags exists (may be several time the same!)

// computed values
public $lastpostid;
public $lastposttopic;
public $lastpostdate;
public $lastpostuser;
public $lastpostname;

// --------

function __construct($aSection=null,$bLast=false)
{
  // Constructor accepts an id or an array as parameter

  if ( isset($aSection) )
  {
    if ( is_int($aSection) )
    {
      if ( $aSection<0 ) die('No section '.$aSection);
      global $oDB;
      $oDB->Query('SELECT * FROM '.TABSECTION.' WHERE id='.$aSection);
      $row = $oDB->Getrow();
      if ( $row===False ) die('No section '.$aSection);
      $this->MakeFromArray($row);
    }
    elseif ( is_array($aSection) )
    {
      $this->MakeFromArray($aSection);
    }
    else
    {
      die('Invalid constructor parameter #1 for the class cSection');
    }
  }

  // Read options/stats

  $this->ReadOptions(); // initialise options properties !!!
  $this->ReadStats();   // initialise stats properties

  // Find last post

  if ( $bLast ) $this->GetSectionLastPost($bLast); // NOTE $bLast can be an id (int) to bypass the search step and get directly the lastpost info

}

// --------

private function MakeFromArray($aSection)
{
  foreach($aSection as $strKey=>$oValue) {
  switch($strKey) {
    case 'domainid':     $this->pid = (int)$oValue; break;
    case 'id':           $this->uid = (int)$oValue; break;
    case 'title':        $this->title     = $oValue; if (empty($this->title) ) $this->title='(section s'.$this->uid.')';  break;
    case 'type':         $this->type      = (int)$oValue; break;
    case 'status':       $this->status    = (int)$oValue; break;
    case 'notify':       $this->notify    = (int)$oValue; break;
    case 'moderator':    $this->modid     = (int)$oValue; break;
    case 'moderatorname':$this->modname   = $oValue; break;
    case 'stats':        $this->stats     = $oValue; break;
    case 'options':      $this->options   = $oValue; break;
    case 'numfield':     $this->numfield  = $oValue; break;
    case 'titlefield':   $this->titlefield= (int)$oValue; break;
    case 'wisheddate':
      $this->wisheddate = (int)$oValue;
      if ( $this->wisheddate>2 ) { $this->wisheddflt=$this->wisheddate-2; $this->wisheddate=2;  }
      break;
    case 'alternate':    $this->notifycc  = (int)$oValue; break;
    case 'prefix':       $this->prefix    = $oValue; break;
  }}
  $this->name = cLang::ObjectName('sec','s'.$this->uid,$this->title);// use title if no translation
  $this->descr = cLang::ObjectName('secdesc','s'.$this->uid,false);
  $this->domname= cLang::ObjectName('domain','d'.$this->pid);
}

// --------

public function GetSectionLastPost($id=-1)
{
  // Searches last post and assign values to $this->lastpost* properties
  // Returns true in case of success. Returns false (and $this->lastpost* properties reset to null) when the section is empty (no items) or the provided $id is wrong/deleted
  // When providing an $id (integer>=0) the function skips the search part and directly assigns that post-id as being the lastpost

  // Initialize and check
  $this->lastpostid = null;
  $this->lastposttopic = null;
  $this->lastpostdate = null;
  $this->lastpostuser = null;
  $this->lastpostname = null;
  if ( $this->uid<0 ) return false;
  if ( $this->items==0 ) return false;
  if ( !is_integer($id) ) $id=-1; //Note: Only $id being a integer>=0 can be used to skip the search part. With any other value (true,false,-1,...) the search is applied.

  // Query - attention: Subqueries requires mysql 4.1. We use two queries
  global $oDB;

  // Search (maxid)
  if ( $id<0 )
  {
    $oDB->Query( 'SELECT MAX(id) as maxid FROM '.TABPOST.' WHERE forum='.$this->uid );
    $row = $oDB->Getrow();
    $id = (int)$row['maxid'];
  }

  // Get lastpost informations
  if ( $id>=0 )
  {
    $oDB->Query( 'SELECT id,topic,issuedate,userid,username FROM '.TABPOST.' WHERE id='.$id );
    if ( $row=$oDB->Getrow() )
    {
    $this->lastpostid = intval($row['id']);
    $this->lastposttopic = intval($row['topic']);
    $this->lastpostdate = $row['issuedate'];
    $this->lastpostuser = intval($row['userid']);
    $this->lastpostname = $row['username'];
    return true;
    }
  }
  return false;
}

// --------

public function GetLogo()
{
  $str = $this->ReadOption('logo');
  if ( !empty($str) )
  {
  if ( file_exists('upload/section/'.$str) ) return 'upload/section/'.$str;
  }
  return $_SESSION[QT]['skin_dir'].'/ico_section_'.$this->type.'_'.$this->status.'.gif';
}

// --------

public static function GetTagsUsed($id=-1,$intMax=50)
{
  // -1 to compute on all sections
  // $intMax is the maximum of distinct tags returned

  // Check

  if ( !is_int($id) ) die('cSection->GetTagsUsed: Argument #1 must be integer');

  // Process

  $arrTags = array();
  global $oDB;
  $oDB->Query('SELECT DISTINCT tags FROM '.TABTOPIC.($id==-1 ? '' : ' WHERE forum='.$id) );
  $i=0;
  while($row=$oDB->Getrow()) {
  if ( !empty($row['tags']) ) {
    $arr = explode(';',$row['tags']);
    foreach($arr as $str)
    {
      if ( !empty($str) ) {
      if ( !in_array($str,$arrTags) ) {
        $arrTags[$str] = $str;
        ++$i;
        if ( $i>$intMax ) break;
      }}
    }
    if ( $i>$intMax ) break;
  }}
  if ( count($arrTags)>2 ) asort($arrTags);
  return $arrTags;
}

// --------

public function LastColumn()
{
  $str = $this->ReadOption('last');
  if ( empty($str) || $str==='no' || $str==='none' ) return '';
  // exception
  if ( $str==='wisheddate' && $this->wisheddate==0 ) return '';
  if ( $str==='notifiedname' && $this->notifycc==0 ) return '';
  return $str;
}

// --------

public function MoveTopics($intD=0,$intRenum=1,$intTopic=-1,$bClosedOnly=false,$strYear='')
{
  if ( !is_int($intD) ) die('cSection->MoveTopics: Argument #2 must be integer');
  if ( $this->uid<0 ) die('cSection->MoveTopics: Wrong argument #1 (id<0)');
  if ( $intD<0 ) die('cSection->MoveTopics: Wrong argument #2 (d<1)');
  if ( $this->uid==$intD ) die('cSection->MoveTopics: Wrong argument, source=destination');
  if ( !is_string($strYear) ) $strYear = intval($strYear); // $strYear can be a integer or "old"
  if ( strlen($strYear)>4 ) die('cSection->MoveTopics: Argument #2 must be a string');

  global $oDB;

  $strWhere = (empty($strYear) ? '' : ' AND '.SqlDateCondition($strYear));
  $strTopic = '';
  $strPost = '';
  $strField = ''; if ( $intRenum==0 ) $strField = ', numid = 0';

  // Move only one topic

  if ( $intTopic>=0 )
  {
    $strTopic=" AND id=$intTopic";
    $strPost=" AND topic=$intTopic";
    if ( $intRenum==2 )
    {
    $nextnumid = $oDB->Nextid(TABTOPIC,'numid','WHERE forum='.$intD);
    $strField = ', numid='.intval($nextnumid);
    }
    $bClosedOnly=false;
  }
  else
  {
    if ( $intRenum==2 )
    {
    $nextnumid = $oDB->Nextid(TABTOPIC,'numid','WHERE forum='.$intD);
    $oDB->Query('SELECT MIN(numid) as minnumid FROM '.TABTOPIC.' WHERE forum='.$this->uid);
    $row = $oDB->Getrow();
    $minnumid = $row['minnumid'];
    $strField = ", numid = $nextnumid + (numid - $minnumid)";
    }
  }

  // Check if prefix system are the same. If not, remove the prefix.

  $strSetPrefix = '';
  if ( !empty($this->prefix) )
  {
    $oDB->Query('SELECT prefix FROM '.TABSECTION.' WHERE id='.$intD);
    $row = $oDB->Getrow();
    if ( $row['prefix']!=$this->prefix ) $strSetPrefix = ',icon="00"';
  }

  // Update topics and posts

  if ( $bClosedOnly )
  {
    $arr = array();
    $oDB->Query('SELECT id FROM '.TABTOPIC.' WHERE forum='.$this->uid.' AND status="Z" AND type="T"'.$strWhere);
    while($row=$oDB->Getrow()) $arr[] = intval($row['id']);
    foreach($arr as $intVal)
    {
    $oDB->Exec('UPDATE '.TABTOPIC.' SET forum='.$intD.$strField.',modifdate="'.date('Ymd His').'" WHERE id='.$intVal );
    $oDB->Exec('UPDATE '.TABPOST.' SET forum='.$intD.$strSetPrefix.' WHERE topic='.$intVal );
    }
  }
  else
  {
    $oDB->Exec('UPDATE '.TABTOPIC.' SET forum='.$intD.$strField.',modifdate="'.date('Ymd His').'" WHERE forum='.$this->uid.$strTopic.$strWhere );
    $oDB->Exec('UPDATE '.TABPOST.' SET forum='.$intD.$strSetPrefix.' WHERE forum='.$this->uid.$strPost.str_replace('firstpostdate','issuedate',$strWhere) );
  }

  // Update stats of this section and stats of destination section

  $this->UpdateStats(array('tags'=>$this->tags),true,true);
  $voidSEC = new cSection(); $voidSEC->uid=$intD; $voidSEC->UpdateStats(array(),true,true);
}

// --------

public function DeleteItems($types='*',$bClosedOnly=false,$strYear='',$bUpdateStats=true)
{
  if ( $this->uid<0 ) die('cSection->DeleteItems: Wrong argument #1 (id<0)');
  if ( !is_string($strYear) ) $strYear = intval($strYear); // $strYear can be a integer or the string 'old'
  if ( strlen($strYear)>4 ) die('cSection->DeleteItems: Argument #2 must be a string');
  if ( !is_string($types) ) die('cSection->DeleteItems: Wrong argument #0, must be a string');

  // Process - delete topics and posts

  global $oDB;

  $strStatus = $bClosedOnly ? ' AND status="Z"'  : '';
  $strYear = empty($strYear) ? '' : ' AND '.SqlDateCondition($strYear);
  $strTypes = '';
  if ( $types!=='*' )
  {
    $types=explode(',',$types);
    foreach($types as $str) $strTypes .= (empty($strTypes) ? '' : ' OR ').'type="'.$str.'"';
  }
  if ( !empty($strTypes) ) $strTypes = ' AND ('.$strTypes.')';

  $oDB->Exec( 'DELETE FROM '.TABPOST.' WHERE forum='.$this->uid.' AND topic IN (SELECT id FROM '.TABTOPIC.' WHERE forum='.$this->uid.$strStatus.$strTypes.$strYear.')' );
  $oDB->Exec( 'DELETE FROM '.TABTOPIC.' WHERE forum='.$this->uid.$strStatus.$strTypes.$strYear );

  if ( $bUpdateStats ) $this->UpdateStats(array('tags'=>$this->tags));

}

// --------

public static function UpdateLastPostDate($id)
{
  if ( !is_int($id) ) die(get_class().'::'.__FUNCTION__.' Argument #1 must be integer');

  global $oDB;
  switch($oDB->type)
  {
  case 'pdo.sqlite':
  case 'pdo.pg':
  case 'pdo.sqlsrv':
  case 'sqlite':
  case 'pg':
  case 'sqlsrv':
  case 'db2':
    $oDB->Exec('UPDATE '.TABTOPIC.' SET lastpostdate=(SELECT MAX(issuedate) FROM '.TABPOST.' p, '.TABTOPIC.' t WHERE t.id=p.topic) WHERE forum='.$id ); break;
  default:
    $oDB->Exec('UPDATE '.TABTOPIC.' t SET t.lastpostdate=(SELECT MAX(p.issuedate) FROM '.TABPOST.' p WHERE t.id=p.topic) WHERE t.forum='.$id );
  }
}

public static function UpdateReplies($id)
{
  if ( !is_int($id) ) die(get_class().'::'.__FUNCTION__.' Argument #1 must be integer');

  global $oDB;
  switch($oDB->type)
  {
  case 'mysql':
  case 'pdo.mysql':
  case 'sqlsrv':
  case 'pdo.sqlsrv':
    $oDB->Exec( 'UPDATE '.TABTOPIC.' SET '.TABTOPIC.'.replies=(SELECT count(*) FROM '.TABPOST.' WHERE '.TABPOST.'.type<>"P" AND '.TABPOST.'.topic='.TABTOPIC.'.id) WHERE '.TABTOPIC.'.forum='.$id.' AND '.TABTOPIC.'.replies=0' );
    break;
  default:
    //use loop as inner join update is not supported
    $oDB->Query( 'SELECT id FROM '.TABTOPIC.' WHERE forum='.$id.' AND replies=0' );
    while($row = $oDB->Getrow()) { $arr[] = (int)$row['id']; if (count($arr)>1000) break; }
    foreach($arr as $i)
    {
    $oDB->Exec( 'UPDATE '.TABTOPIC.' SET replies=(SELECT COUNT(*) FROM '.TABPOST.' WHERE topic='.$i.' AND type<>"P") WHERE id='.$i );
    }
  }
}

// --------

public function UpdateStats($arrValues=array(),$bLastPostDate=false,$bReplies=false)
{
  if ( $this->uid<0 ) die('UpdateSectionStats: Wrong id');

  // Process (provided values are not recomputed)

  if ( !isset($arrValues['topics']) )   $arrValues['topics']  = cSection::CountItems($this->uid,'topics');
  if ( !isset($arrValues['replies']) )  $arrValues['replies'] = cSection::CountItems($this->uid,'replies');
  if ( !isset($arrValues['tags']) )     $arrValues['tags']    = cSection::CountItems($this->uid,'tags');
  if ( !isset($arrValues['topicsZ']) )  $arrValues['topicsZ'] = cSection::CountItems($this->uid,'topicsZ');
  if ( !isset($arrValues['repliesZ']) ) $arrValues['repliesZ']= cSection::CountItems($this->uid,'repliesZ');

  $this->stats = QTimplodeIni($arrValues);
  $this->WriteStats(); // also send sse
  // Update lastpostdate or replies of EACH item in this section
  if ( $bLastPostDate ) cSection::UpdateLastPostDate($this->uid); // used after import or prune
  if ( $bReplies ) cSection::UpdateReplies($this->uid);  // used after import

  return $this->stats;
}

public static function baseUpdateStats($uid,$arrValues=array(),$bLastPostDate=false,$bReplies=false)
{
  if ( !is_int($uid) || $uid<0 ) die('UpdateSectionStats: Wrong id');

  // Process (provided values are not recomputed)

  if ( !isset($arrValues['topics']) )   $arrValues['topics']  = cSection::CountItems($uid,'topics');
  if ( !isset($arrValues['replies']) )  $arrValues['replies'] = cSection::CountItems($uid,'replies');
  if ( !isset($arrValues['tags']) )     $arrValues['tags']    = cSection::CountItems($uid,'tags');
  if ( !isset($arrValues['topicsZ']) )  $arrValues['topicsZ'] = cSection::CountItems($uid,'topicsZ');
  if ( !isset($arrValues['repliesZ']) ) $arrValues['repliesZ']= cSection::CountItems($uid,'repliesZ');

  $str = QTimplodeIni($arrValues);
  cSection::baseWriteStats($uid,$str);

  // Update lastpostdate or replies of EACH item in this section
  if ( $bLastPostDate ) cSection::UpdateLastPostDate($uid); // used after import or prune
  if ( $bReplies ) cSection::UpdateReplies($uid);  // used after import

  return $str;
}

// --------
// aQTcontainer implementations
// --------

public static function Create($title,$parentid)
{
  QTargs( 'cSection->Create',array($title,$parentid),array('str','int') );
  if ( empty($title) ) die('cSection->Create: Argument #1 must be a string');
  $title = substr($title,0,64);

  global $oDB;
  $oDB->BeginTransac();
    $id = $oDB->Nextid(TABSECTION);
    $oDB->Exec(
    'INSERT INTO '.TABSECTION.' (domainid,id,titleorder,moderator,type,status,notify,titlefield,wisheddate,alternate,title,stats,options,moderatorname,numfield,prefix) VALUES ('.$parentid.','.$id.',0,1,"0","0","0","0","0","0",:title,"","coord=0;order=0;last=0;logo=0","Admin","%03s","a")',
    array(':title'=>$title)
    );
  $oDB->CommitTransac();

  // Impact on globals
  sMem::Clear('sys_sections');
  return $id;
}

public static function Drop($id)
{
  if ( $id<1 ) die('cSection->Drop: Cannot delete section 0');
  global $oDB;
  $oDB->BeginTransac();
    $oDB->Exec('DELETE FROM '.TABSECTION.' WHERE id='.$id);
    cLang::Delete(array('sec','secdesc'),'s'.$id);
  $oDB->CommitTransac();

  // Impact on globals
  sMem::Clear('sys_sections');
}

public static function MoveItems($id,$destination)
{
  // See MoveTopics
}

public static function CountItems($id,$status,$intD=10,$strYear='')
{
  if ( !is_int($intD) ) die('cSection->Count: Argument #2 must be an int');
  if ( $id<0 ) die('cSection->Count: Wrong argument (id<0)');
  if ( $intD<1 ) die('cSection->Count: Wrong argument #2 (d<1)');
  if ( !is_string($strYear) ) $strYear = intval($strYear); // $strYear can be a integer or "old"
  if ( strlen($strYear)>4 ) die('cSection->Count: Argument #3 must be a string');

  global $oDB;

  $strWhere = (empty($strYear) ? '' : ' AND '.SqlDateCondition($strYear));

  // Process

  switch($status)
  {
  case 'topics':
  case 'items': $oDB->Query('SELECT count(*) as countid FROM '.TABTOPIC.' WHERE forum='.$id.$strWhere ); break;
  case 'topicsZ':
  case 'itemsZ': $oDB->Query('SELECT count(*) as countid FROM '.TABTOPIC.' WHERE status="Z" AND forum='.$id.$strWhere ); break;
  case 'news': $oDB->Query('SELECT count(*) as countid FROM '.TABTOPIC.' WHERE forum='.$id.' AND type="A"'.$strWhere ); break;
  case 'inspections': $oDB->Query('SELECT count(*) as countid FROM '.TABTOPIC.' WHERE forum='.$id.' AND type="I"'.$strWhere ); break;
  case 'replies': $oDB->Query('SELECT count(*) as countid FROM '.TABPOST.' WHERE forum='.$id.' AND (type="R" OR type="F")'.str_replace('firstpostdate','issuedate',$strWhere) ); break;
  case 'repliesZ': $oDB->Query('SELECT count(*) as countid FROM '.TABPOST.' p INNER JOIN '.TABTOPIC.' t ON p.topic=t.id WHERE p.forum='.$id.' AND p.type<>"P" AND t.status="Z"'.$strWhere ); break;
  case 'unreplied': $oDB->Query('SELECT count(*) as countid FROM '.TABTOPIC.' WHERE forum='.$id.' AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere ); break;
  case 'unrepliedT': $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE forum='.$id.' AND type="T" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere ); break;
  case 'unrepliedA': $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE forum='.$id.' AND type="A" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere ); break;
  case 'unrepliedI': $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE forum='.$id.' AND type="I" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere ); break;
  case 'tags': $oDB->Query('SELECT count(*) as countid FROM '.TABTOPIC.' WHERE tags<>"" AND forum='.$id.$strWhere ); break;
  case 'messages': $oDB->Query('SELECT count(*) as countid FROM '.TABPOST.' WHERE forum='.$id.$strWhere ); break;
  case 'unrepliednews': $oDB->Query('SELECT count(id) as countid FROM '.TABTOPIC.' WHERE forum='.$id.' AND type="A" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere ); break; // (unrepliedA) stays for backbward compatibility
  default: die('cSection->Count: Wrong argument #1 '.$status);
  }
  $row = $oDB->Getrow();
  return (int)$row['countid'];
}

public static function CountItemsBySection($status='items',$intD=10,$strYear='',$bMissingAsZero=true)
{
  // Same as CountItems but performed for all sections at once. Returns an array where key is the section id.
  // Attention:
  // Some section MAY BE ABSENT in the result array (if there is no matching items belonging to a section, these section-items cannot be counted).
  // To force having a result for each sections use $bMissingAsZero=true
  if ( !is_int($intD) ) die('cSection->Count: Argument #2 must be an int');
  if ( $intD<1 ) die('cSection->Count: Wrong argument #2 (d<1)');
  if ( !is_string($strYear) ) $strYear = intval($strYear); // $strYear can be a integer or "old"
  if ( strlen($strYear)>4 ) die('cSection->Count: Argument #3 must be a string');

  global $oDB;

  $strWhere = (empty($strYear) ? '' : ' AND '.SqlDateCondition($strYear));

  // Process

  switch($status)
  {
  case 'topics':
  case 'items': $oDB->Query('SELECT forum,count(*) as countid FROM '.TABTOPIC.' WHERE forum>=0'.$strWhere.' GROUP BY forum' ); break;
  case 'topicsZ':
  case 'itemsZ':      $oDB->Query('SELECT forum,count(*) as countid FROM '.TABTOPIC.' WHERE status="Z" '.$strWhere.' GROUP BY forum' ); break;
  case 'news':        $oDB->Query('SELECT forum,count(*) as countid FROM '.TABTOPIC.' WHERE type="A"'.$strWhere.' GROUP BY forum' ); break;
  case 'inspections': $oDB->Query('SELECT forum,count(*) as countid FROM '.TABTOPIC.' WHERE type="I"'.$strWhere.' GROUP BY forum' ); break;
  case 'replies':     $oDB->Query('SELECT forum,count(*) as countid FROM '.TABPOST.' WHERE (type="R" OR type="F")'.str_replace('firstpostdate','issuedate',$strWhere).' GROUP BY forum' ); break;
  case 'repliesZ':    $oDB->Query('SELECT forum,count(*) as countid FROM '.TABPOST.' p INNER JOIN '.TABTOPIC.' t ON p.topic=t.id WHERE p.type<>"P" AND t.status="Z"'.$strWhere.' GROUP BY forum' ); break;
  case 'unreplied':   $oDB->Query('SELECT forum,count(*) as countid FROM '.TABTOPIC.' WHERE replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere.' GROUP BY forum' ); break;
  case 'unrepliedT':  $oDB->Query('SELECT forum,count(id) as countid FROM '.TABTOPIC.' WHERE type="T" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere.' GROUP BY forum' ); break;
  case 'unrepliedA':  $oDB->Query('SELECT forum,count(id) as countid FROM '.TABTOPIC.' WHERE type="A" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere.' GROUP BY forum' ); break;
  case 'unrepliedI':  $oDB->Query('SELECT forum,count(id) as countid FROM '.TABTOPIC.' WHERE AND type="I" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere.' GROUP BY forum' ); break;
  case 'tags':        $oDB->Query('SELECT forum,count(*) as countid FROM '.TABTOPIC.' WHERE tags<>"" '.$strWhere.' GROUP BY forum' ); break;
  case 'messages':    $oDB->Query('SELECT forum,count(*) as countid FROM '.TABPOST.' WHERE forum>=0'.$strWhere.' GROUP BY forum' ); break;
  case 'unrepliednews': $oDB->Query('SELECT forum,count(id) as countid FROM '.TABTOPIC.' WHERE type="A" AND replies=0 AND firstpostdate<"'.DateAdd(date('Ymd His'),-$intD,'day').'"'.$strWhere.' GROUP BY forum' ); break; // (unrepliedA) stays for backbward compatibility
  default: die('cSection->Count: Wrong argument #1 '.$status);
  }

  $rows = array();
  while($row=$oDB->Getrow())
  {
  $rows[(int)$row['forum']] = (int)$row['countid'];
  }
  // add missing section with 0
  if ($bMissingAsZero) foreach(array_keys(sMem::Get('sys_sections')) as $s) if ( !isset($rows[$s]) ) $rows[$s]=0;

  return $rows;
}

// --------
// IOptions, IStats implementations
// --------

public function ChangeOption($key,$str)
{
  // Writes the options and returns the options [array]
  if ( !is_string($key) || !is_string($str) ) die(get_class().'::'.__FUNCTION__.' wrong argument type');
  if ( empty($key) ) die(get_class().'::'.__FUNCTION__.' missing key'); // $strKey can be ''

  $arr = QTarradd(QTexplodeIni($this->options),$key,$str);
  $this->options = QTimplodeIni($arr);
  cSection::WriteOptions($this->uid,$this->options);
  return $arr;
}
public function ChangeStat($key,$str)
{
  // Writes the stats and returns the stats [array]
  if ( !is_string($key) || !is_string($str) ) die(get_class().'::'.__FUNCTION__.' wrong argument type');
  if ( empty($key) ) die(get_class().'::'.__FUNCTION__.' missing key'); // $strKey can be ''

  $arr = QTarradd(QTexplodeIni($this->stats),$key,$str);
  $this->stats = QTimplodeIni($arr);
  cSection::WriteStats($this->uid,$this->stats);
  return $arr;
}
public function ReadOptions()
{
  return QTexplodeIni($this->options);
}
public function ReadOption($name)
{
  $arr = QTexplodeIni($this->options);
  if ( isset($arr[$name]) ) return $arr[$name];
  return null;
}
public function ReadStats()
{
  $arr = QTexplodeIni($this->stats);
  if ( isset($arr['topics']) ) $this->items = intval($arr['topics']);
  if ( isset($arr['replies']) ) $this->replies = intval($arr['replies']);
  if ( isset($arr['tags']) ) $this->tags = intval($arr['tags']);
  if ( isset($arr['topicsZ']) ) $this->itemsZ = intval($arr['topicsZ']);
  if ( isset($arr['repliesZ']) ) $this->repliesZ = intval($arr['repliesZ']);
  return $arr;
}
public function WriteOptions()
{
  cSection::baseWriteOptions($this->uid,$this->options);
}
public function WriteStats()
{
  cSection::baseWriteStats($this->uid,$this->stats);
  sAppMem::Control(get_class().':'.__FUNCTION__, array('section'=>$this->uid,'stats'=>$this->stats)); //System update
}
public static function baseWriteOptions($id,$str='')
{
  if ( !is_int($id) || !is_string($str) ) die(get_class().'::'.__FUNCTION__.' wrong argument type');
  global $oDB; $oDB->Exec( 'UPDATE '.TABSECTION.' SET options=:options WHERE id='.$id, array(':options'=>$str) );
}
public static function baseWriteStats($id,$str='')
{
  if ( !is_int($id) || !is_string($str) ) die(get_class().'::'.__FUNCTION__.' wrong argument type');
  global $oDB; $oDB->Exec( 'UPDATE '.TABSECTION.' SET stats=:stats WHERE id='.$id, array(':stats'=>$str) );
  sMem::Clear('sys_sections');
}

// --------

}