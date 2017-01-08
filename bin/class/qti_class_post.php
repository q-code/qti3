<?php // QuickTicket 3.0 build:20160703

class cPost
{

// --------

public $id = -1;
public $topic = -1;
public $section = -1;
public $type = 'P';
public $icon = '00';
public $title = '';
public $issuedate = '0';
public $text = '';
public $modifdate = '0';
public $modifuser;
public $modifname;
public $attach;
public $userid;
public $username;
public $userrole;
public $userloca;
public $useravat;
public $usersign;
public $num; // optional renumbering (used while displaying item-replies)

// --------

function cPost($aPost=null,$intNum=null)
{
  if ( isset($aPost) )
  {
    if ( is_int($aPost) )
    {
      if ( $aPost<0 ) die('No post '.$aPost);
      global $oDB;
      $oDB->Query('SELECT p.*,u.role,u.location,u.photo,u.signature FROM '.TABPOST.' p LEFT JOIN '.TABUSER.' u ON p.userid=u.id WHERE p.id='.$aPost);
      $row = $oDB->Getrow();
      $this->MakeFromArray($row);
    }
    elseif ( is_array($aPost) )
    {
      $this->MakeFromArray($aPost);
    }
    elseif ( strtolower(get_class($aPost))=='cpost' )
    {
      $this->id       = $aPost->id;
      $this->topic    = $aPost->topic;
      $this->section  = $aPost->section;
      $this->type     = $aPost->type;
      $this->icon     = $aPost->icon;
      $this->title    = $aPost->title;
      $this->text     = $aPost->text;
      $this->issuedate= $aPost->issuedate;
      $this->userid   = $aPost->userid;
      $this->username = $aPost->username;
      $this->userrole = $aPost->userrole;
      $this->userloca = $aPost->userloca;
      $this->useravat = $aPost->useravat;
      $this->usersign = $aPost->usersign;
      $this->modifdate= $aPost->modifdate;
      $this->modifuser= $aPost->modifuser;
      $this->modifname= $aPost->modifname;
      $this->attach   = $aPost->attach;
    }
    else
    {
      die('Invalid constructor parameter#1 for class cPost');
    }

    if ( $this->type=='D' ) $this->title = '&nbsp;';

  }
  if ( isset($intNum) ) $this->num = (int)$intNum;
  if ( isset($this->title[64]) ) $this->title = substr($this->title,0,64);
}

// --------

function MakeFromArray($aPost)
{
  if ( !is_array($aPost) ) die('cPost->MakeFromArray: aPost is not an array');
  foreach($aPost as $strKey=>$oValue) {
  switch($strKey) {
    case 'id':       $this->id     = (int)$oValue; break;
    case 'forum':    $this->section= (int)$oValue; break;
    case 'topic':    $this->topic  = (int)$oValue; break;
    case 'type':     $this->type   = $oValue; break;
    case 'icon':     $this->icon   = $oValue; break;
    case 'title':    $this->title  = $oValue; break;
    case 'textmsg':  $this->text   = $oValue; break;
    case 'issuedate':$this->issuedate= $oValue; break;
    case 'userid':   $this->userid   = (int)$oValue; break;
    case 'username': $this->username = $oValue; break;
    case 'role':     $this->userrole = $oValue; break;
    case 'location': $this->userloca = $oValue; break;
    case 'photo':    $this->useravat = $oValue; break;
    case 'signature':$this->usersign = $oValue; break;
    case 'modifdate':$this->modifdate= $oValue; break;
    case 'modifuser':$this->modifuser= (int)$oValue; break;
    case 'modifname':$this->modifname= $oValue; break;
    case 'attach':   $this->attach   = $oValue; break;
  }}
}

// --------

function CanEdit()
{
  // By default, Staff can edit post of other staff (even Admin post)
  global $oVIP;
  if ( !sUser::Auth() ) return false;
  if ( $this->userid==sUser::Id() || sUser::Role()==='A' ) return true;
  if ( sUser::Role()==='M' )
  {
    if ( $this->userrole==='A' && !QTI_STAFFEDITADMIN ) return false;
    if ( $this->userrole==='M' && !QTI_STAFFEDITSTAFF ) return false;
    return true;
  }
  return false;
}

// --------

function InsertPost($bTopicStat=false,$bUserStat=false,$bCheckUserPostsToday=true)
{
  global $oDB;

  // Insert

  $oDB->Exec(
  'INSERT INTO '.TABPOST.' (id,forum,topic,title,type,icon,userid,username,issuedate,textmsg'.( !empty($this->attach) ? ',attach' : '').') VALUES ( '.$this->id.','.$this->section.','.$this->topic.',:title,"'.$this->type.'","'.$this->icon.'",'.$this->userid.',:username,"'.$this->issuedate.'",:textmsg'.( !empty($this->attach) ? ',"'.$this->attach.'"' : '').')',
  array(':username'=>$this->username,
        ':title'=>$this->title,
        ':textmsg'=>$this->text)
  );

  // added for db2
  if ( $oDB->type==='db2') $oDB->Exec( 'UPDATE '.TABPOST.' SET textmsg2=:textmsg WHERE id='.$this->id, array(':textmsg'=>substr($this->text,0,255)) );

  // Update Topic's replies and lastpost (inserting a post does NOT change de modifdate of the topic!)

  if ( $bTopicStat )
  {
  $oDB->Exec('UPDATE '.TABTOPIC.' SET replies=replies+1,lastpostid='.$this->id.',lastpostuser='.$this->userid.',lastpostname="'.$this->username.'",lastpostdate="'.$this->issuedate.'" WHERE id='.$this->topic);
  }

  // Lastpost delay control

  $_SESSION[QT.'_usr_lastpost'] = time();

  // Number of posts today control

  if ( isset($_SESSION[QT.'_usr_posts_today']) && $bCheckUserPostsToday )
  {
    if ( $this->type==='P' || $this->type==='R' ) ++$_SESSION[QT.'_usr_posts_today'];
  }

  // User stat

  if ( $bUserStat )
  {
  $oDB->Exec('UPDATE '.TABUSER.' SET lastdate="'.Date('Ymd His').'", numpost=numpost+1, ip="'.$_SERVER['REMOTE_ADDR'].'" WHERE id='.$this->userid);
  if ( !isset($_SESSION[QT.'_usr_posts']) ) $_SESSION[QT.'_usr_posts']=0;
  ++$_SESSION[QT.'_usr_posts'];
  }


  // broadcast only for reply (new topic is broadcasted by cTopic)
  if ( $this->type==='R' )
  {
  sAppMem::Control( get_class().':'.__FUNCTION__, $this );
  }
}

// --------

public static function baseDelete($id)
{
  if ( !is_int($id) || $id<0 ) die('Post::baseDelete: Wrong id');
  global $oDB;
  $oDB->Exec('DELETE FROM '.TABPOST.' WHERE id='.$id);
}

// --------

function Dropattach()
{
  if ( $this->id<0 ) die('Post->Dropattach: Wrong post id');
  cPost::baseDropattach($this->id,$this->attach);
  $this->attach = NULL;
  if ( strstr($this->text,'[img]@[/img]') ) $this->text = str_replace('[img]@[/img]','',$this->text);
}

public static function baseDropattach($id,$doc)
{
  if ( !is_int($id) || $id<0 ) die('Post::baseDropattach: Wrong argument');
  if ( empty($doc) || !is_string($doc) ) return false;
  if ( file_exists(QTI_DIR_DOC.$doc) ) unlink(QTI_DIR_DOC.$doc);
  global $oDB;
  $oDB->Exec('UPDATE '.TABPOST.' SET attach="" WHERE id='.$id);
  return true;
}

// --------

public static function GetPrefix($serie='a',$icon='00',$skin='skin/default',$class='')
{
  $key='';
  if ( is_a($serie,'cSection') ) $key = $serie->prefix;  // serie can be a section id [int] or a cSection
  if ( is_int($serie) ) { $arr = sMem::Get('sys_sections'); $key = ( empty($arr[$serie]['prefix']) ? '' : $arr[$serie]['prefix'] ); }
  if ( is_string($serie) ) $key = $serie;
  if ( empty($icon) || $icon==='00' || empty($key) || !is_string($key) ) return '';

  $src = cPost::GetPrefixSrc($key,$icon,$skin);
  if ( empty($src) ) return '';
  $label = $key.'_'.$icon;
  return '<img'.(empty($class) ? '' : ' '.$class).' src="'.$src.'" alt="[o]" title="'.(empty($L['Ico_prefix'][$label]) ? $label : $L['Ico_prefix'][$label]).'"/>';
}
public static function GetPrefixSrc($serie='a',$icon='00',$skin='skin/default')
{
  $key='';
  if ( is_a($serie,'cSection') ) $key = $serie->prefix;  // serie can be a section id [int] or a cSection
  if ( is_int($serie) ) { $arr = sMem::Get('sys_sections'); $key = ( empty($arr[$serie]['prefix']) ? '' : $arr[$serie]['prefix'] ); }
  if ( is_string($serie) ) $key = $serie;
  if ( empty($icon) || $icon==='00' || empty($key) || !is_string($key) ) return '';

  return $skin.'/ico_prefix_'.$key.'_'.$icon.'.gif';
}

function GetIcon($strSkin='skin/default',$strHref='')
{
  switch(strtolower($this->type))
  {
  case 'r': return '<i class="fa fa-reply" title="'.QTstrh(L('Ico_post_r')).'"></i>';
  case 'f': return '<i class="fa fa-forward" title="'.QTstrh(L('Ico_post_f')).'"></i>';
  case 'd': return '<i class="fa fa-times-circle" title="'.QTstrh(L('Ico_post_d')).'"></i>';
  default: return '<i class="fa fa-question-circle" title="(unknown type)"></i>';
  }
}

// --------

function GetScore($oTopic)
{
  if ( $oTopic->type=='I' )
  {
    $i = strtolower(trim($this->title));
    if ( $i==='' || is_null($i) || $i==='null' ) $i=-1;
    if ( strlen($i)>4 ) $i = substr($i,0,4);
    if ( is_numeric($i) ) { $i=floatval($i); } else { $i=-1; }
    return $i;
  }
  return -1;
}
function GetScoreImage($oTopic,$bName=true)
{
  $i = $this->GetScore($oTopic);
  return ($i<0 ? S : ValueScalebar($i,$oTopic->ReadOptions('Ilevel')).($bName ? ' '.ValueName($i,$oTopic->ReadOptions('Ilevel')) : ''));
}
function GetScoreName($oTopic)
{
  $i = $this->GetScore($oTopic);
  return ($i<0 ? '' : ValueName($i,$oTopic->ReadOptions('Ilevel')));
}

// --------

function Show($oSEC,$oTopic,$bAvatar=true,$strEndLine='',$strSkin='skin/default',$strAlt='r1',$bCompact=-1)
{
  if ( !isset($oSEC) ) die('oPost->Show: Missing $oSEC');
  if ( !isset($oTopic) ) die('oPost->Show: Missing $oTopic');
  if ( $bCompact===-1 ) $bCompact = ($_SESSION[QT]['viewmode']==='c');

  // Process

  global $L,$bMap;

  // Prepare icon

  $strIcon = ($this->type=='P' ? $oTopic->GetIcon($strSkin) : $this->GetIcon($strSkin));

  // Prepare title

  $strTitle = $this->title;
  switch($this->type)
  {
  case 'P';
    if ( !empty($oSEC->wisheddate) && !empty($oTopic->wisheddate) )
    {
      if ( sUser::CanViewCalendar() ) { $strLink = '<a href="'.Href('qti_calendar.php').'?s='.$oTopic->parentid.'&amp;v=wisheddate&amp;y='.substr($oTopic->wisheddate,0,4).'&amp;m='.substr($oTopic->wisheddate,4,2).'" title="'.$L['Ico_view_f_c'].': '.QTdatestr($oTopic->wisheddate,'Y-m-d','',true).'">%s</a>'; } else { $strLink = '<a title="'.QTdatestr($oTopic->wisheddate,'Y-m-d','',true).'">%s</a>'; }
      $strTitle .= '<span class="wisheddate"> &middot; '.L('Wisheddate').': '.sprintf($strLink,QTdatestr($oTopic->wisheddate,'d M','',true)).'</span>';
    }
    break;
  case 'D': $strTitle = L('Message_deleted'); break;
  case 'F': $strTitle = L('Item_handled').' '.L('by').' '.$oTopic->actorname; break;
  default:
    if ( $oTopic->type=='I' ) $strTitle = $this->GetScore($oTopic);
    if ( empty($strTitle) ) $strTitle = L('Reply');
  }

  // Message container

  echo '
  <a id="p',$this->id,'"></a>
  <div class="post post-',$this->type,'">
  <table class="post">
  ';

  // message title

  echo '<tr>',PHP_EOL;
  echo '<th class="c-ico ',$strAlt,'">',$strIcon,'</th>';
  echo '<th class="c-msg ',$strAlt,'">';
  echo '<p class="post-attr">',($bCompact || $oTopic->type=='I' ? '<a href="qti_user.php?id='.$this->userid.'" class="a_post_title">'.$this->username.'</a>,' : ''),' ',QTdatestr($this->issuedate,'$','$',true),( isset($this->num) ? ' <span class="post-num">'.$this->num.'</span>' : ''),'</p>';
  echo '<p class="post-title">',$strTitle,'</p>';
  echo '</th>';
  echo '</tr>',PHP_EOL;

  // message body

  if ( $this->type!='D' )
  {
    echo '<tr>',PHP_EOL;
    echo '<td class="c-ico ',$strAlt,'">',cPost::GetPrefix($oSEC->prefix,$this->icon,$strSkin),'</td>',PHP_EOL;
    echo '<td class="c-msg ',$strAlt,'">',PHP_EOL;

    // userimage
    $strUserImage = ''; if ( is_string($bAvatar) ) $strUserImage = $bAvatar; // avatar can be replaced by something else (if string is provided)
    if ( !$bCompact && $bAvatar===true )
    {
      $img = empty($this->useravat) ? '' : AsUserImg(QTI_DIR_PIC.$this->useravat,'','message'); // title is in caption
      $caption = '<a href="'.Href('qti_user.php?id='.$this->userid).'">'.QTtrunc($this->username,24).'</a><br />'.($this->userrole!='U' ? L('Role_'.$this->userrole) : '').'<br />'.(empty($this->userloca) ? '' : '<span class="post-location">'.QTtrunc($this->userloca,24).'</span>' );
      $strUserImage = AsImgBox($img,$caption,'picboxmsg');
    }
    echo $strUserImage,PHP_EOL;

    // message attachment and signature
    echo '<p class="msgbody">';
      // format the text
      $str = QTbbc($this->text,'<br />','</p>','<p class="msgbody">');
      // show the image (if any)
      if ( !$bCompact ) {
      if ( !empty($this->attach) ) {
      if ( in_array(substr($this->attach,-4,4),array('.gif','.jpg','jpeg','.png')) ) {
      if ( strstr($str, 'src="@"') ) {
        $str = str_replace('src="@"','src="'.QTI_DIR_DOC.$this->attach.'"',$str);
      }}}}
    echo $str,'</p>',PHP_EOL;

    if ( !empty($this->attach) )
    {
    echo '<p class="attachment">'.AsImg($strSkin.'/ico_attachment.gif','A',$L['Attachment'],'ico i-user');
    if ( strstr($this->attach,'/') ) { $str = substr(strrchr($this->attach,'/'),1); } else { $str=$this->attach; }
    if ( substr($str,0,strlen($this->id.'_'))==($this->id).'_' ) $str = substr($str,strlen($this->id.'_'));
    echo '&nbsp;<a href="'.QTI_DIR_DOC.$this->attach.'" class="a_attachment" target="_blank">'.$str.'</a></p>',PHP_EOL;
    }
    if ( !$bCompact && $this->type!='F' && !empty($this->usersign) ) echo '<p class="post-sign">',QTbbc($this->usersign),'</p>',PHP_EOL;

    // command line
    echo $strEndLine;
    echo '</td>',PHP_EOL,'</tr>',PHP_EOL;
  }

  // end message container

  echo '
  </table>
  </div>
  ';

}

// --------

function SetFromPost($bNew=true)
{
  $error='';
  global $oVIP,$strBehalf;

  // Identify the user (can be onbehalf)

  $this->modifuser = sUser::Id();
  $this->modifname = sUser::Name();
  if ( isset($_POST['behalf']) )
  {
    $strBehalf = trim($_POST['behalf']); if ( get_magic_quotes_gpc() ) $strBehalf = stripslashes($strBehalf);
    if ( !is_null($strBehalf) && $strBehalf!=='' )
    {
      // Find behalf id
      $strBehalf = htmlspecialchars($strBehalf,ENT_QUOTES);
      $intBehalf = current(array_keys(GetUsers('name',$strBehalf) )); // can be FALSE when not found
      if ( is_int($intBehalf) ) { $this->modifuser = $intBehalf; $this->modifname = $strBehalf; } else { $error=L('Send_on_behalf').' '.Error(1); }
    }
  }

  // Identify creator as being the user. When editing existing message ($bNew=false) then autor remains unchanged

  if ( $bNew )
  {
  $this->userid = $this->modifuser;
  $this->username = $this->modifname;
  }

  // Read message values

  if ( isset($_POST['icon']) ) $this->icon = $_POST['icon'];
  if ( isset($_POST['title']) ) $this->title = QTunbbc(trim($_POST['title']));
  if ( isset($_POST['text']) ) $this->text = trim($_POST['text']);
  if ( !empty($_POST['wisheddate']) )
  {
  $str = QTdatestr(trim($_POST['wisheddate']),'Ymd','');
  if ( !is_string($str) ) $error = L('Wisheddate').' '.Error(1);
  if ( substr($str,0,6)=='Cannot' ) $error = L('Wisheddate').' '.Error(1);
  if ( substr($str,0,4)=='1970' ) $error = L('Wisheddate').' '.Error(1);
  if ( empty($error) ) $this->wisheddate = $str;
  }
  if ( isset($_POST['oldattach']) ) { $this->attach = $_POST['oldattach']; }

  return $error;
}

// --------

public static function FillEmptyTitle($title,$str,$default='untitled')
{
  if ( !is_string($title) || !is_string($str) ) die('FillEmptyTitle: arguments must be string');
  if ( empty($title) )
  {
  if ( empty($str) ) $str=$default;
  $i=strpos($str,"\r\n"); if ( $i>5 ) $str=substr($str,0,$i);
  $title = QTunbbc(QTcompact($str,100,' '));
  }
  return QTtrunc($title,64);
}

}