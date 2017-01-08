<?php // QuickTicket 3.0 build:20160703

class cTopic extends aQTcontainer
{

// -- Properties --

public $numid = -1;
public $statusdate = '0';
public $wisheddate = '0';
public $tags = '';
public $firstpostid = -1;
public $lastpostid = -1;
public $firstpostuser = -1;
public $lastpostuser = -1;
public $firstpostname;
public $lastpostname;
public $firstpostdate = '0';
public $lastpostdate = '0';
public $x;
public $y;
public $z;
public $actorid = -1;
public $actorname = '';
public $notifiedid = -1;
public $notifiedname = '';
public $views = 0;
public $modifdate = '0';
public $options = '';

public $youreply = '';
public $preview;
public $smile;
public $title = '';

function __construct($aTopic=null,$intUser=-1,$intPreviewsize=250)
{
  // Change aQTcontainer defaults
  $this->type = 'T';   // A=News P=Post. Attention, alphabetic order can be used as display order (i.e. "News on top")
  $this->status = 'A'; // A=submitted Z=closed (News 0=closed). Attention user can sort according to the status index.

  // Constructor accepts an array or an integer as parameter #1
  if ( isset($aTopic) )
  {
    if ( is_int($aTopic) )
    {
      if ( $aTopic<0 ) die('cTopic: Wrong id');
      global $oDB;
      $oDB->Query('SELECT * FROM '.TABTOPIC.' WHERE id='.$aTopic);
      $row = $oDB->Getrow();
      if ( $row===False ) die('No topic '.$aTopic);
      $this->MakeFromArray($row,$intPreviewsize);
      if ( $intUser>=0 )
      {
        // +1 when user is not the topic creator himself
        if ( $intUser != $this->firstpostuser )
        {
        $oDB->Exec('UPDATE '.TABTOPIC.' SET views = views+1 WHERE id='.$this->id);
        }
      }
    }
    elseif ( is_array($aTopic) )
    {
      $this->MakeFromArray($aTopic,$intPreviewsize);
    }
    else
    {
      die('Invalid constructor parameter #1 in the class cTopic');
    }
  }
}

// --------

private function DbFields()
{
  // relation dbfield=>property (exceptions: forum,replies,param)
  return array('id'=>'id','numid'=>'numid','forum'=>'parentid','type'=>'type',
  'status'=>'status','statusdate'=>'statusdate',
  'wisheddate'=>'wisheddate',  'tags'=>'tags',
  'firstpostid'=>'firstpostid','lastpostid'=>'lastpostid',
  'firstpostuser'=>'firstpostuser','lastpostuser'=>'lastpostuser',
  'firstpostname'=>'firstpostname', 'lastpostname'=>'lastpostname',
  'firstpostdate'=>'firstpostdate','lastpostdate'=>'lastpostdate',
  'x'=>'x','y'=>'y','z'=>'z',
  'actorid'=>'actorid','actorname'=>'actorname',
  'notifiedid'=>'notifiedid','notifiedname'=>'notifiedname',
  'replies'=>'items','views'=>'views',
  'modifdate'=>'modifdate','param'=>'options');
}

// --------

private function MakeFromArray($aTopic,$intPreview=100)
{
  foreach($aTopic as $strKey=>$oValue) {
  switch($strKey) {
    case 'textmsg':      $this->preview      = QTcompact(QTunbbc($oValue),$intPreview,' '); break;
    case 'id':           $this->id           = (int)$oValue; break;
    case 'numid':        $this->numid        = (int)$oValue; break;
    case 'forum':        $this->parentid     = (int)$oValue; break;
    case 'type':         $this->type         = $oValue; break;
    case 'status':       $this->status       = $oValue; break;
    case 'statusdate':   $this->statusdate   = $oValue; break;
    case 'wisheddate':   $this->wisheddate   = $oValue; break;
    case 'tags':         $this->tags         = $oValue; break;
    case 'firstpostid':  $this->firstpostid  = (int)$oValue; break;
    case 'lastpostid':   $this->lastpostid   = (int)$oValue; break;
    case 'firstpostuser':$this->firstpostuser= (int)$oValue; break;
    case 'lastpostuser': $this->lastpostuser = (int)$oValue; break;
    case 'firstpostname':$this->firstpostname= $oValue; break;
    case 'lastpostname': $this->lastpostname = $oValue; break;
    case 'firstpostdate':$this->firstpostdate= $oValue; break;
    case 'lastpostdate': $this->lastpostdate = $oValue; break;
    case 'actorid':      $this->actorid      = (int)$oValue; if ($this->actorid<=0) $this->actorid=-1; break;
    case 'actorname':    $this->actorname    = $oValue; break;
    case 'notifiedid':   $this->notifiedid   = (int)$oValue; if ($this->notifiedid<=0) $this->notifiedid=-1; break;
    case 'notifiedname': $this->notifiedname = $oValue; break;
    case 'replies':      $this->items        = (int)$oValue; break;
    case 'views':        $this->views        = (int)$oValue; break;
    case 'icon':         $this->smile        = $oValue; break;
    case 'title':        $this->title        = $oValue; break;
    case 'modifdate':    $this->modifdate    = $oValue; break;
    case 'x': if ( is_numeric($oValue) ) $this->x = (float)$oValue; break; // must be FLOAT (or NULL)
    case 'y': if ( is_numeric($oValue) ) $this->y = (float)$oValue; break; // must be FLOAT (or NULL)
    case 'z': if ( is_numeric($oValue) ) $this->z = (float)$oValue; break; // must be FLOAT (or NULL)
    case 'param':        $this->options      = $oValue; break;
  }}
}

// --------

public static function GetRef($numid=0,$format='',$na='&nbsp;')
{
  // This returns the formatted ref number (numid) of this item. Format can be defined by a string, a [int] section-id, or a [cSection] section.
  // In case of undefined format, this returns the numid (as '%03s' string), in case of 'N' format, return the $na string.
  if ( is_a($format,'cSection') ) $format=$format->numfield;
  if ( is_int($format) ) { $arr = sMem::Get('sys_sections'); if ( isset($arr[$format]['numfield']) ) $format = empty($arr[$format]['numfield']) ? '%03s' : $arr[$format]['numfield']; }
  if ( !is_string($format) ) $format = '%03s';
  if ( $format==='N' ) return $na;
  if ( empty($format) ) return (string)$numid;
  return sprintf($format,$numid);
}

// --------

public static function GetSections($ids)
{
  // Get sections from a list of topic ids (or a [string] csv id)
  if ( is_string($ids) ) $ids = explode(',',$ids);
  if ( !is_array($ids) ) die('cTopic::GetSections: wrong argument');
  global $oDB;
	$arrS = array();
	foreach ($ids as $id)
	{
		$oDB->Query( 'SELECT forum FROM '.TABTOPIC.' WHERE id='.$id );
		while( $row=$oDB->Getrow() )
		{
		if ( !in_array((int)$row['forum'],$arrS) ) $arrS[]=(int)$row['forum'];
		}
	}
	return $arrS;
}

// --------

public static function MakeIconSrc($type='T',$status='A',$skin='skin/default',$arrStatus=array(),$bCheckfile=true)
{
  // Build icon filename (with skin path) and check if file exists
  // In case of type "T", use arrStatus to get the icon filename (or sys_status when arrStatus is empty)
  // To build a filename without path, use $bCheckfile=false

  $alt = 'admin/ico_status.gif'; // alternate used if file check failed
  switch(strtoupper($type))
  {
  case 'T':
    if ( empty($arrStatus) ) $arrStatus = sMem::Get('sys_statuses');
    $file = isset($arrStatus[$status]['icon']) ? $arrStatus[$status]['icon'] : 'ico_topic_tZ.gif';
    if ( !$bCheckfile ) return $file;
    $file = $skin.'/'.$file;
    if ( file_exists($file) ) return $file;
    break;
  case 'I':
    $file = 'ico_topic_i_'.($status==='Z' ?  '1' : '0').'.gif';
    if ( !$bCheckfile ) return $file;
    $file = $skin.'/'.$file;
    if ( file_exists($file) ) return $file;
    break;
  case 'A':
    $file = 'ico_topic_a_'.($status==='Z' ?  '1' : '0').'.gif';
    if ( !$bCheckfile ) return $file;
    $file = $skin.'/'.$file;
    if ( file_exists($file) ) return $file;
    break;
  }
  return $alt;
}

public static function MakeIconName($type='T',$status='A',$name='Ticket',$arrStatus=array())
{
  switch(strtoupper($type))
  {
  case 'T':
    if ( empty($arrStatus) ) $arrStatus = sMem::Get('sys_statuses');
    $name = isset($arrStatus[$status]['statusname']) ? $arrStatus[$status]['statusname'] : 'status '.$status;
    break;
  case 'I': $name = L('Ico_item_i'.($status==='Z' ? 'Z' : '')); break;
  case 'A': $name = L('Ico_item_a'.($status==='Z' ? 'Z' : '')); break;
  }
  return $name;
}

public static function MakeIcon($type='T',$status='A',$name='Ticket',$id='',$skin='skin/default',$strHref='',$strTitleFormat='%s',$arrStatus=array())
{
  if ( $type==='T' && empty($arrStatus) ) $arrStatus = sMem::Get('sys_statuses');
  $src = cTopic::MakeIconSrc($type,$status,$skin,$arrStatus);
  if ( $name===false ) { $name=''; } else { $name = cTopic::MakeIconName($type,$status,$name,$arrStatus); $name = sprintf($strTitleFormat,$name); }
  return AsImg($src,$type,$name,'ico-'.strtolower($type),'',$strHref,$id);
}

// --------

public function GetIcon($skin='skin/default',$strHref='',$strTitleFormat='%s',$id='')
{
  return cTopic::MakeIcon($this->type,$this->status,$this->GetIconName(),$id,$skin,$strHref,$strTitleFormat);
}

public function GetIconName()
{
  return cTopic::MakeIconName($this->type,$this->status);
}

public function GetStatusName($str='unknown')
{
  $arrStatus = sMem::Get('sys_statuses');
  if ( isset($arrStatus[$this->status]['statusname']) ) return $arrStatus[$this->status]['statusname'];
  return $str;
}

public static function Types()
{
  return array('T'=>L('Item'), 'I'=>L('Inspection'), 'A'=>L('News'));
}
public static function Typename($type='T')
{
  $arr = cTopic::Types();
  return empty($arr[$type]) ? 'undefined' : $arr[$type];
}

public static function Statuses($type='T',$bOnlyNames=false)
{
  // Returns an array of [array] status attributes
  // For Inspections or News, returns a array of [string] statusnames
  // $bOnlyNames allow receiving only name (case type 'T')
  switch($type)
  {
  case 'T':
    $arr = sMem::Get('sys_statuses');
    if ( $bOnlyNames )
    {
    $arrName=array();
    foreach($arr as $key=>$attr) $arrName[$key] = empty($attr['statusname']) ? 'Status '.$key : $attr['statusname'];
    return $arrName;
    }
    return $arr;
    break;
  case 'I': return array('A'=>L('I_running'),'Z'=>L('I_closed')); break;
  default: return array('A'=>L('Submitted'),'Z'=>L('Closed')); break;
  }
}

public static function Statusname($type='T',$status='A')
{
  switch($type)
  {
  case 'T': $arr = sMem::Get('sys_statuses'); return (isset($arr[$status]['statusname']) ? $arr[$status]['statusname'] : 'unknown'); break;
  case 'I': return ($status==='Z' ? L('I_closed') : L('I_running')); break;
  default: return ($status==='Z' ? L('Closed') : L('Submitted')); break;
  }
}

// --------

public function GetTopicTitle()
{
  global $oDB;
  $oDB->Query('SELECT title FROM '.TABPOST.' WHERE id='.$this->firstpostid);
  $row = $oDB->Getrow();
  $this->title = $row['title'];
  return $this->title;
}

// --------

public function InsertTopic($bUserStat=true,$bCanNotify=true,$oPost=null,$oSEC=null)
{
  global $oDB;

  // Pass smile to cTopic
  $this->smile='00';
  if ( isset($oPost) && !empty($oPost->icon) &&  $oPost->icon!=='00') $this->smile = $oPost->icon;

  // In case of Topic type 'Inspection'

  if ( $this->type==='I' && isset($oPost) )
  {
  $this->status='Z'; // When creating a new inspection, ticket status is closed until creator setup parameters and turn it to 'submitted'.
  $this->options = 'Itype=0;Ilevel=3;Istat=mean'; // Initial parameters for an inspection
  $this->z=-1 ; // Inspection score < 0 means unknown
  }

  // Quote each db fieldvalues

  $arrValues = array();
  foreach($this->DbFields() as $strField=>$strProperty) $arrValues[$strField]=FieldQuote($this->$strProperty,TABTOPIC,$strProperty);

  // Insert

  $oDB->Exec('INSERT INTO '.TABTOPIC.' ('.implode(',',array_keys($arrValues)).') VALUES ('.implode(',',$arrValues).')');

  // Status notification

  if ( $bCanNotify && $this->type=='T' ) $this->NotifyStatus(-1,$oPost,$oSEC);

  // User stats

  if ( $bUserStat )
  {
  $oDB->Query('SELECT count(*) as countid FROM '.TABPOST.' WHERE userid='.$this->firstpostuser);
  $row = $oDB->Getrow();
  $oDB->Exec('UPDATE '.TABUSER.' SET lastdate="'.Date('Ymd His').'", numpost='.$row['countid'].', ip="'.$_SERVER['REMOTE_ADDR'].'" WHERE id='.$this->firstpostuser);
  $_SESSION[QT.'_usr_posts']=(int)$row['countid'];
  }

  // SSE

  sAppMem::Control( get_class().':'.__FUNCTION__, $this );

}

// --------

public function NotifyActor($intOldactorid=-1,$oSEC=null)
{
  if ( QTI_NOTIFY_NEWACTOR || QTI_NOTIFY_OLDACTOR ) {
  if ( $intOldactorid!=$this->actorid ) {

    if ( !isset($oSEC) ) $oSEC = new cSection($this->parentid);

    if ( $oSEC->notify==1 )
    {
      global $L;
      // prepare mail
      $strTopic = ''; if ( $oSEC->numfield!='N' ) $strTopic = sprintf($oSEC->numfield,$this->numid).' ';
      $strMails = '';
      if ( QTI_NOTIFY_NEWACTOR && $this->actorid>=0 ) $strMails .= GetUserInfo($this->actorid,'mail').',';
      if ( QTI_NOTIFY_OLDACTOR && $intOldactorid>=0 ) $strMails .= GetUserInfo($intOldactorid,'mail');
      $strMessage = sprintf("{$L['Topic']} %s ",$strTopic);
      $strMessage .= sprintf($L['Topic_forwarded'],$this->actorname);
      $strSubject = "{$_SESSION[QT]['site_name']}: {$L['Notification']} $strTopic";
      // send mail
      include 'bin/qt_lib_smtp.php';
      if ( !empty($strMails) ) QTmail($strMails,QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QT_HTML_CHAR);
    }

  }}
}

public function NotifyStatus($intOldactorid=-1,$oPost=null,$oSEC=null)
{
  global $L;
  $arrS = sMem::Get('sys_statuses');

  if ( !empty($arrS[$this->status]['mailto']) )
  {
    if ( !isset($oSEC) ) $oSEC = new cSection($this->parentid);

    if ( $oSEC->notify==1 )
    {
      // read message (and get it if not yet defined)
      if ( !isset($this->title) && isset($oPost) )
      {
        if ( is_integer($oPost) ) $oPost = new cPost($oPost); // $oPost can be an integer
        $this->title = $oPost->title;
        $this->preview = QTcompact(QTunbbc($oPost->text),100,' ');
      }
      if ( empty($this->title) ) $this->title = '';
      if ( empty($this->preview) ) $this->preview = '';
      $strTopic = ($oSEC->numfield!='N' ? sprintf($oSEC->numfield,$this->numid).' ' : '').$this->title."\r\n".$this->preview."\r\n".$_SESSION[QT]['site_url'].'/qti_topic.php?t='.$this->id;

      $strFile = GetLang().'mail_status.php';

      // notify list

      $lstMails = explode(',',$arrS[$this->status]['mailto']);
      $lstMails = array_unique($lstMails);

      // notify mails
      $arrMails = array();
      foreach($lstMails as $intUser)
      {
        switch($intUser)
        {
        case 'MF': $arrMails[] = GetUserInfo($oSEC->modid,'mail'); break;
        case 'MA': if ( $this->actorid>=0 ) $arrMails[] = GetUserInfo($this->actorid,'mail'); break;
        case 'U':
          $arrMails[] = GetUserInfo(intval($this->firstpostuser),'mail');
          if ( $this->notifiedid>=0 ) $arrMails[] = GetUserInfo(intval($this->notifiedid),'mail');
          break;
        case 'A': $arrMails = $arrMails + GetUserInfo('A','mail'); break;
        case 'S': $arrMails = $arrMails + GetUserInfo('S','mail'); break;
        default:  if ( $intUser>=0 ) $arrMails[] = GetUserInfo((int)$intUser,'mail'); break;
        }
      }
      $arrMails = array_unique($arrMails);
      $strMails = implode(', ',$arrMails);

      // message containing 2 parameters (the status, the topic preview)
      $strMessage = "{$L['Status']}: %s \r\n%s";
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$arrS[$this->status]['name'],$strTopic);

      $strSubject = $_SESSION[QT]['site_name'].': '.$L['Notification'].' '.$this->title;

      // send mail
      include 'bin/qt_lib_smtp.php';
      QTmail($strMails,QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QT_HTML_CHAR);

      // show send mails
      $strMails = '<br /><br />'.$L['Notification'].': '.$strMails;
    }
  }
}

// --------

public function SetStatus($strStatus='A',$bNotify=true,$oPost=null,$bSectionStats=true)
{
  if ( $this->status===$strStatus ) return false;
  if ( $strStatus!=='Z' && $this->status!=='Z' ) $bSectionStats=false; // only current Z or changing to Z are canditate to section-stats update

  global $oDB;

  $this->status=$strStatus;
  $this->statusdate=date('Ymd His');
  $oDB->Exec('UPDATE '.TABTOPIC.' SET status="'.$this->status.'", statusdate="'.$this->statusdate.'",modifdate="'.date('Ymd His').'" WHERE id='.$this->id);
  $this->status=$strStatus;
  // NOTIFY
  if ( $bNotify ) $this->NotifyStatus(-1,$oPost); // $oPost can be an integer

  // UPDATE section stats if required (only if currently Z or changing to Z)
  if ( $bSectionStats ) { $voidSEC = new cSection(); $voidSEC->uid=$this->parentid; $voidSEC->UpdateStats(); }

  // SSE
  sAppMem::Control( get_class().':'.__FUNCTION__, array('section'=>$this->parentid,'topic'=>$this->id,'type'=>$this->type,'status'=>$this->status) ); // statusdate is added by sMem::Control

  return true;

}

// --------

public static function SetType($section=-1,$topic=-1,$type='T',$status='A')
{
  // Check

  if ( $topic<0 ) die('cTopic::SetType() Wrong id');
  if ( !is_string($status) || empty($status) ) $status='A';
  if ( !is_int($section) ) $section=-1;

  // Process

  global $oDB;
  $oDB->Exec('UPDATE '.TABTOPIC.' SET type="'.$type.'",modifdate="'.date('Ymd His').'" WHERE id='.$topic );

  // SSE
  sAppMem::Control(get_class().':'.__FUNCTION__, compact('section','topic','type','status') );
}

// --------

public function SetActor($intActor=-1,$bCanNotify=true,$bInsertForwardMessage=true)
{
  $intOldactorid = $this->actorid;
  if ( $intActor<0 ) die('Topic->SetActor: Wrong actor id');

  global $oDB;

  // change actor
  $this->actorid = intval($intActor);
  $this->actorname = GetUserInfo($this->actorid,'name');
  $oDB->Exec('UPDATE '.TABTOPIC.' SET actorid='.$this->actorid.', actorname="'.$this->actorname.'",modifdate="'.date('Ymd His').'" WHERE id='.$this->id);

  // posting a forward messsage
  if ( $bInsertForwardMessage )
  {
  $oPost = new cPost();
  $oPost->id = $oDB->Nextid(TABPOST);
  $oPost->section = $this->parentid;
  $oPost->topic = $this->id;
  $oPost->type = 'F';
  $oPost->title = L('Item_handled').' '.L('by').' '.$this->actorname;
  $oPost->text = sprintf(L('Item_forwarded'),$this->actorname);
  $oPost->userid = $this->actorid;
  $oPost->username = $this->actorname;
  $oPost->issuedate = date('Ymd His');
  $oPost->modifdate = '';
  $oPost->modifuser = '';
  $oPost->InsertPost(true,false); // Update topic stat, not user's stat
  }

  // email
  if ( $bCanNotify ) $this->NotifyActor($intOldactorid);

  // SSE
  $arr = array('topic'=>$this->id,'actorid'=>$this->actorid,'actorname'=>$this->actorname);
  if ( $bInsertForwardMessage ) { $arr['replies']='+1'; $arr['lastpostid']=$oPost->id; $arr['lastpostuser']=$oPost->userid; $arr['lastpostname']=$oPost->username; $arr['lastpostdate']=date('H:i'); }
  sAppMem::Control( get_class().':'.__FUNCTION__, $arr );

}

// --------

public static function TagsClear($str,$bDropDuplicate=true)
{
  if ( is_array($str) ) $str = implode(';',$str);
  if ( !is_string($str) ) die('TagsClear: wrong argument #1');

  // Returns a string 'tag1;tag2;tag3' (trimed, no empty entry, no-accent). Note: 0,'0','',' ' are also removed
  // Returns '' when $str is empty
  // Dropping duplicate is case INsensitive (keeping the first). Exemple: 'Info;info;DATE;date;INFO;Date' returns 'Info;DATE'

  $str = trim($str);
  if ( $str==='*' ) return '*'; // used in case of delete all tags
  if ( empty($str) ) return '';
  $str = strtr($str,'éèêëÉÈÊËáàâäÁÀÂÄÅåíìîïÍÌÎÏóòôöÓÒÔÖõÕúùûüÚÙÛÜ','eeeeeeeeaaaaaaaaaaiiiiiiiioooooooooouuuuuuuu');

  $str = str_replace(',',';',trim($str));
  $arr = explode(';',$str);
  $arrClear = array();
  $arrClearLC = array();
  foreach($arr as $str)
  {
    $str=trim($str);
    if ( empty($str) || $str==='*' ) continue; // '*' can be alone, but not inside other tags
    if ($bDropDuplicate && in_array(strtolower($str),$arrClearLC)) continue;
    $arrClear[]=$str;
    $arrClearLC[]=strtolower($str);
  }
  return implode(';',$arrClear);
}

// --------

public function TagsAdd($str,$oSEC=false)
{
  // Check and format
  $str = cTopic::TagsClear($str); // returns ssv distinct tags [string] (can return '' or '*')
  if ( empty($str) || $str==='*' ) return false;

  // Append to current and clear (to remove duplicate)
  $this->tags = cTopic::TagsClear($this->tags.';'.$str);

  // Save
  global $oDB;
  $oDB->Exec( 'UPDATE '.TABTOPIC.' SET tags=:tags,modifdate=:modifdate WHERE id='.$this->id, array(':tags'=>$this->tags,':modifdate'=>date('Ymd His')) );

  // Update section stats (if tags added)
  if ( is_int($oSEC) ) $oSEC = new cSection($oSEC);
  if ( is_a($oSEC,'cSection') )
  {
    $count = count(explode(';',$this->tags));
    if ( $count>0 )
    {
    $oSEC->stats = QTimplodeIni(QTarradd(QTexplodeIni($oSEC->stats),'tags',cSection::CountItems($oSEC->uid,'tags')));
    $oSEC->WriteStats();
    }
  }
}

// --------

public function TagsDel($str,$oSEC=false)
{
  if  ( empty($this->tags) || empty($str) ) return false;

  // Check and format
  $str = cTopic::TagsClear($str); // returns ssv distinct tags [string] (can return '' or '*')
  if ( empty($str) ) return false;

  // Build new tags list
  if ( $str==='*' )
  {
    $this->tags='';
  }
  else
  {
    $arrTag = explode(';',$this->tags); // Current tags
    $arrDel = explode(';',strtolower($str)); // Tag to delete
    $arr = array(); // new tags
    foreach($arrTag as $tag) if ( !in_array(strtolower($tag),$arrDel) ) $arr[]=$tag; // keep not deleted tags
    $this->tags = implode(';',$arr);
  }

  // Save
  global $oDB;
  $oDB->Exec( 'UPDATE '.TABTOPIC.' SET tags=:tags,modifdate=:modifdate WHERE id='.$this->id, array(':tags'=>$this->tags,':modifdate'=>date('Ymd His')) );

  // Update section stats
  if ( is_int($oSEC) ) $oSEC = new cSection($oSEC);
  if ( is_a($oSEC,'cSection') )
  {
    $oSEC->stats = QTimplodeIni(QTarradd(QTexplodeIni($oSEC->stats),'tags',cSection::CountItems($oSEC->uid,'tags')));
    $oSEC->WriteStats();
  }
}

// --------

public function UpdateStats($intMax=100,$bInspectionUpdateScore=true)
{
  if ( $this->id<0 ) die('Topic->UpdateStats: Wrong id');

  // Count

  global $oDB;
  $arr = array();
  $this->items = 0;
  $oDB->Query('SELECT id,userid,username,issuedate,type FROM '.TABPOST.' WHERE topic='.$this->id.' ORDER BY issuedate');
  while($row=$oDB->Getrow())
  {
    $arr[]=$row;
    if ( $row['type']!=='P' ) ++$this->items;
  }

  // save stats

  $oDB->Exec('UPDATE '.TABTOPIC.' SET replies='.$this->items.',firstpostid='.$arr[0]['id'].',firstpostuser='.$arr[0]['userid'].',firstpostname="'.$arr[0]['username'].'",firstpostdate="'.$arr[0]['issuedate'].'",lastpostid='.$arr[count($arr)-1]['id'].',lastpostuser='.$arr[count($arr)-1]['userid'].',lastpostname="'.$arr[count($arr)-1]['username'].'",lastpostdate="'.$arr[count($arr)-1]['issuedate'].'" WHERE id='.$this->id );

  // close topic if full

  if ( $intMax>1 ) {
  if ( $this->items>$intMax ) {
    $oDB->Exec('UPDATE '.TABTOPIC.' SET status="Z" WHERE id='.$this->id );
  }}

  // update inspection stats

  if ( $this->type==='I' && $bInspectionUpdateScore ) $this->InspectionUpdateScore();
}

// --------

public static function SetCoord($id,$coord)
{
  // Coordinates must be a string 'y,x'.
  // '0,0' can be use to remove a coordinates.
  // z is not used here
  if ( empty($coord) ) $coord='0,0';
  $y=null;
  $x=null;
  $coord = explode(',',$coord);
  if ( isset($coord[0]) ) $y = (float)$coord[0];
  if ( isset($coord[1]) ) $x = (float)$coord[1];
  if ( EmptyFloat($y) && EmptyFloat($x) ) { $y=null; $x=null; }
  global $oDB;
  $oDB->Exec('UPDATE '.TABTOPIC.' SET y='.(isset($y) ? $y : 'NULL').',x='.(isset($x) ? $x : 'NULL').' WHERE id='.$id);
}

// --- INSPECTION ---

public function InspectionUpdateScore()
{
  $this->z = $this->InspectionAggregate();
  global $oDB;
  $oDB->Exec('UPDATE '.TABTOPIC.' SET z='.$this->z.' WHERE id='.$this->id );
}

function InspectionAggregate()
{
  if ( $this->id<0 || $this->items<1 ) return -1; // -1 means no results or unknown

  $strIstat = strtolower($this->ReadOptions('Istat')); if ( empty($strIstat) ) $strIstat='mean';
  global $oDB;
  $i=-1;

  switch($strIstat)
  {
  case 'mean':
    $oDB->Query('SELECT title FROM '.TABPOST.' WHERE topic='.$this->id.' AND type="R" AND title<>""');
    $arr = array();
    $i=0;
    while($row=$oDB->Getrow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      $arr[] = floatval($str);
      ++$i;
    }
    if ( empty($arr) ) return -1;
    $i=(array_sum($arr))/$i;
    break;
  case 'min':
    $oDB->Query('SELECT title FROM '.TABPOST.' WHERE topic='.$this->id.' AND type="R" AND title<>""');
    $i=999;
    while($row=$oDB->Getrow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      if ( floatval($str)<$i ) $i=floatval($str);
    }
    if ( $i==999 ) return -1;
    break;
  case 'max':
    $oDB->Query('SELECT title FROM '.TABPOST.' WHERE topic='.$this->id.' AND type="R" AND title<>""');
    while($row=$oDB->Getrow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      if ( floatval($str)>$i ) $i=floatval($str);
    }
    break;
  case 'first':
    $oDB->Query('SELECT title FROM '.TABPOST.' WHERE topic='.$this->id.' AND type="R" AND title<>"" ORDER BY issuedate');
    while($row=$oDB->Getrow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      return round(floatval($str),1);
    }
    break;
  case 'last':
    $oDB->Query('SELECT title FROM '.TABPOST.' WHERE topic='.$this->id.' AND type="R" AND title<>"" ORDER BY issuedate DESC');
    while($row=$oDB->Getrow())
    {
      $str = strtolower(trim($row['title']));
      if ( $str==='' || is_null($str) || $str==='null' ) continue;
      if ( strlen($str)>4 ) $str = substr($str,0,4);
      if ( !is_numeric($str) ) continue;
      return round(floatval($str),1);
    }
    break;
  default: die('Unknown aggregation function ['.$strIstat.']');
  }
  return round($i,1);
}

// --------
// aQTcontainer implementations
// --------

public static function Drop($id)
{
  if ( !is_int($id) ) die(get_class().'::'.__FUNCTION__.' Argument #1 must be integer');
  if ( $id<0 ) die(get_class().'::'.__FUNCTION__.' Wrong argument #1 (id<0)');

  global $oDB;
  $oDB->Exec('DELETE FROM '.TABPOST.' WHERE topic='.$id);
  $oDB->Exec('DELETE FROM '.TABTOPIC.' WHERE id='.$id);
}

public static function Create($title,$parentid) {} // Not used, see InsertTopic

public static function MoveItems($id,$destination) {} // Not used

public static function CountItems($id,$status)
{
  // count number of 'reply'-posts
  // post-status is not used
  global $oDB;
  $oDB->Query('SELECT count(id) as countid FROM '.TABPOST.' WHERE type<>"P"');
  $row = $oDB->Getrow();
  return (int)$row['countid'];
}

// --------
// IOptions implementations
// --------

public function ChangeOption($strKey,$strValue)
{
  QTargs('cTopic->ChangeOption',array($strKey,$strValue));
  if ( $strKey==='' ) die ('cTopic->ChangeOption: Missing key'); // $strKey can be ''

  $arr = QTarradd(QTexplodeIni($this->options),$strKey,$strValue);
  $this->options = QTimplodeIni($arr);
  $this->WriteOptions();
  return $arr;
}

public function ReadOptions($str='*')
{
  // Returns an array of {parameter=>value} when $str is *
  // Returns the value when $str is an existing key. Returns '' when $str is not defined or when $str in not an existing key
  if ( $str==='*' ) return (empty($this->options) ? array() : QTexplodeIni($this->options));
  if ( empty($str) || empty($this->options) ) return '';
  $arr = QTexplodeIni($this->options);
  if ( isset($arr[$str]) ) return $arr[$str];
  return '';
}

public function WriteOptions()
{
  global $oDB;
  $oDB->Exec('UPDATE '.TABTOPIC.' SET param="'.addslashes($this->options).'" WHERE id='.$this->id );
}

// --------

}

// ========

class sStatus
{

public static function GetAll()
{
  $arr = array();

  global $oDB;  $oDB->Query('SELECT * FROM '.TABSTATUS.' ORDER BY id');
  while($row=$oDB->Getrow())
  {
    $arr[$row['id']]['statusname'] = ucfirst(str_replace('_',' ',$row['name']));
    $arr[$row['id']]['statusdesc'] = '';
    $arr[$row['id']]['name'] = $row['name'];
    $arr[$row['id']]['icon'] = $row['icon'];
    $arr[$row['id']]['mailto'] = $row['mailto'];
    $arr[$row['id']]['color'] = $row['color'];
  }

  // find translations

  $arrL = cLang::Get('status',QTiso(),'*');
  foreach ($arrL as $id=>$str)
  {
    if ( !empty($str) ) $arr[$id]['statusname'] = $str;
  }
  $arrL = cLang::Get('statusdesc',QTiso(),'*');
  foreach ($arrL as $id=>$str)
  {
    if ( !empty($str) ) $arr[$id]['statusdesc'] = $str;
  }

  return $arr;
}

// --------

public static function Add($id='',$name='',$icon='',$color='',$mailto='')
{
  // Check

  if ( !is_string($id) || empty($id) ) die('cVIP->StatusAdd: Argument #1 must be a string');
  if ( !is_string($name) || empty($name) ) die('cVIP->StatusAdd: Argument #2 must be a string');
  if ( !is_string($icon) ) die('cVIP->AddStatusAdd Argument #3 must be a string');
  if ( !is_string($color) ) die('cVIP->StatusAdd: Argument #4 must be a string');
  if ( !is_string($mailto) ) die('cVIP->StatusAdd: Argument #5 must be a string');

  // Process

  global $oDB;
  $error = '';

  $id = strtoupper(substr(trim($id),0,1));
  $name = QTconv($name,'3',QTI_CONVERT_AMP);

  // unique id and name

  $oDB->Query('SELECT count(*) AS countid FROM '.TABSTATUS.' WHERE id="'.$id.'"');
  $row=$oDB->Getrow();
  if ( $row['countid']>0 ) $error = "Status id [$id] already used";
  $oDB->Query('SELECT count(*) AS countid FROM '.TABSTATUS.' WHERE name="'.QTstrd($name,24).'"');
  $row=$oDB->Getrow();
  if ( $row['countid']>0 ) $error = "Status name [$name] already used";

  // Save

  if ( empty($error) )
  {
    $oDB->Exec('INSERT INTO '.TABSTATUS.' (id,name,color,mailto,icon) VALUES ("'.$id.'","'.QTstrd($name,24).'","'.$color.'","'.$mailto.'","'.$icon.'")');
  }

  // Exit

  sMem::Clear('sys_statuses');
  return $error;
}

// --------

public static function Delete($id='',$to='A')
{
  // Check

  if ( !is_string($id) || empty($id) ) die('sStatus::Delete: Argument #1 must be a string');
  if ( !is_string($to) || empty($to) ) die('sStatus::Delete: Argument #2 must be a string');
  $id = strtoupper(substr(trim($id),0,1));
  $to = strtoupper(substr(trim($to),0,1));
  if ( $id=='A' || $id=='A' ) die('sStatus::Delete: Argument #1 cannot be A nor Z');
  if ( $id==$to ) die('sStatus::Delete: Argument #1 equal #2');

  // Process - status id > to and delete id

  global $oDB;

  $oDB->Exec('UPDATE '.TABTOPIC.' SET status="'.$to.'" WHERE status="'.$id.'"');
  $oDB->Exec('DELETE FROM '.TABSTATUS.' WHERE id="'.$id.'"');
  $oDB->Exec('DELETE FROM '.TABLANG.' WHERE (objtype="status" OR objtype="statusdesc") AND objid="'.$id.'"');

  // Exit

  sMem::Clear('sys_statuses');
}

// --------

public function ChangeId($id='',$to='')
{
  // Check

  if ( !is_string($id) || empty($id) ) die('sStatus::ChangeId: Argument #1 must be a string');
  if ( !is_string($to) || empty($to) ) die('sStatus::ChangeId: Argument #2 must be a string');
  $id = strtoupper(substr(trim($id),0,1));
  $to = strtoupper(substr(trim($to),0,1));
  if ( $id=='A' || $id=='A' ) die('sStatus::ChangeId: Argument #1 cannot be A nor Z');
  if ( $to=='A' || $to=='A' ) die('sStatus::ChangeId: Argument #2 cannot be A nor Z');

  // Process

  global $oDB;
  $error = '';

  // Unique name

  if ( array_key_exists($to,$this->statuses) ) return L('Status').' id ['.$to.'] '.strtolower(L('Already_used'));

  // Save changes

  if ( empty($error) )
  {
  $oDB->Exec('UPDATE '.TABTOPIC.' SET status="'.$to.'" WHERE status="'.$id.'"');
  $oDB->Exec('UPDATE '.TABSTATUS.' SET id="'.$to.'" WHERE id="'.$id.'"');
  }

  // Exit

  sMem::Clear('sys_statuses');
  return $error;
}

}