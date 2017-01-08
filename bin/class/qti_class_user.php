<?php

// QTI 3.0 build:20160703

class sUser
{

public static function Auth() { return (isset($_SESSION[QT.'_usr_auth']) ? $_SESSION[QT.'_usr_auth'] : FALSE); }
public static function Role() { return (isset($_SESSION[QT.'_usr_role']) ? $_SESSION[QT.'_usr_role'] : 'V'); }
public static function Id()   { return (isset($_SESSION[QT.'_usr_id'])   ? $_SESSION[QT.'_usr_id']   : 0); }
public static function Name() { return (isset($_SESSION[QT.'_usr_name']) ? $_SESSION[QT.'_usr_name'] : 'Guest'); }

// --------

public static function Login($username='',$password='',$bRemember=FALSE)
{

  if ( empty($username) || empty($password) ) return false;
  
  // Check profile exists for this username/password (auth is true if only one user exists)

  $_SESSION[QT.'_usr_auth'] = ( 1===cVIP::SysCount('members',' AND pwd="'.sha1($password).'" AND name="'.$username.'"') ? true : false );

  // External login: even if profile is not found (sUser::Auth() is false) external login may be able to create a new profile
  // Note 'Admin' MUST ALWAYS BYPASS external login:
  // When ldap config/server changes, Admin (at least) MUST be able to login to change the settings!

  if ( isset($_SESSION[QT]['login_addon']) && $_SESSION[QT]['login_addon']!=='0' && $username!=='Admin' )
  {
    $sModuleKey = $_SESSION[QT]['login_addon'];
    $prefix = strtolower(substr(constant('QT'),0,3));
    if ( isset($_SESSION[QT][$sModuleKey]) && $_SESSION[QT][$sModuleKey]!=='0' )
    {
      if ( file_exists($prefix.$sModuleKey.'_login.php') )
      {
        include $prefix.$sModuleKey.'_login.php';
      } else {
        $_SESSION[QT.'_usr_auth'] = false;
        echo 'Access denied (missing addon controler)';
      }
    }
  }

  // Register and get extra user info, if authentication is successfull

  if ( $_SESSION[QT.'_usr_auth'] )
  {
    sUser::RegisterUser($username,$password,true); // get extra user info and register user's info

    global $oDB;
    $oDB->Exec('UPDATE '.TABUSER.' SET ip="'.$_SERVER['REMOTE_ADDR'].'" WHERE id='.sUser::Id());

    if ( $bRemember )
    {
    setcookie(QT.'_cookname', htmlspecialchars($username,ENT_QUOTES), time()+3600*24*100, '/');
    setcookie(QT.'_cookpass', sha1($password), time()+3600*24*100, '/');
    setcookie(QT.'_cooklang', $_SESSION[QT]['language'], time()+3600*24*100, '/');
    }

    // Reset parameters (because the Role can impact the lists)
    sMem::Clear('sys_domains');
    sMem::Clear('sys_sections');
  }

  return $_SESSION[QT.'_usr_auth'];

}

// --------

public static function IsStaff()
{
  return (sUser::Role()==='M' || sUser::Role()==='A');
}

// --------

public static function CanView($level='V5',$offlinestop=true)
{
  if ( !isset($_SESSION[QT]['visitor_right']) ) return FALSE;
  if ( !isset($_SESSION[QT]['board_offline']) ) return FALSE;

  // $level user role that can access the page: U, M, A or Vi(where i=public access level)
  // $offlinestop stop when application off-line

  if ( sUser::Role()==='A' ) return true;

  if ( $level=='U' && sUser::Role()==='V') return false;
  if ( $level=='M' && !sUser::IsStaff() ) return false;
  if ( $level=='A' && sUser::Role()!='A' ) return false;
  $strPAL='5';
  if ( isset($level[1]) ) $strPAL=$level[1]; // use second char, otherwize 5
  if ( sUser::Role()==='V' && $_SESSION[QT]['visitor_right']<$strPAL ) return false;
  if ( $_SESSION[QT]['board_offline']=='1' && $offlinestop ) return false;
  return true;
}

// --------

public static function CanViewCalendar()
{
  if ( !isset($_SESSION[QT]['show_calendar']) ) return true;
  if ( $_SESSION[QT]['show_calendar']=='V' ) return true;
  if ( $_SESSION[QT]['show_calendar']=='U' && sUser::Role()!='V' ) return true;
  return sUser::IsStaff();
}

// --------

public static function IsPrivate($targetprivacy,$targetid)
{
  if ( sUser::IsStaff() ) return false;

  // Check the privacy setting. $str is the user's privacy level (can be integer !)
  // Returns true/false if current user can see the private info
  if ( $targetprivacy==2 || sUser::Id()==$targetid ) return false;
  if ( $targetprivacy==1 && sUser::Role()!='V') return false;
  return true;
}

// --------

public static function RegisterUser($name='',$password='',$bSha1=true)
{
  // Read and Set user in session variable $_SESSION[QT.'_usr']
  // Main info are set in auth,id,name,role others are in info
  global $oDB;
  $oDB->Query('SELECT * FROM '.TABUSER.' WHERE pwd="'.($bSha1===false ? $password : sha1($password)).'" AND name="'.$name.'"'); // when checking from coockies, password is already hashed
  if ( $row=$oDB->Getrow() )
  {
    $_SESSION[QT.'_usr_auth'] = true;
    $_SESSION[QT.'_usr_id']   = (int)$row['id']; unset($row['id']); // unset in orther to not include this in the $_SESSION[QT.'_usr_info']
    $_SESSION[QT.'_usr_name'] = $row['name'];    unset($row['name']);
    $_SESSION[QT.'_usr_role'] = $row['role'];    unset($row['role']);
    $_SESSION[QT.'_usr_info'] = $row;
  }
}

// --------

public static function SessionUnset()
{
  // User's properties as in CURRENT SESSION
  $_SESSION[QT.'_usr_auth'] = false;
  $_SESSION[QT.'_usr_id']   = 0;
  $_SESSION[QT.'_usr_name'] = 'Guest';
  $_SESSION[QT.'_usr_role'] = 'V';
  $_SESSION[QT.'_usr_info'] = array();
}

// --------

public static function GetUserInfo($key,$not=null,$bToInt=false)
{
  // User's property as in CURRENT SESSION or $not if property not found
  // Existing value can be converted to int ($bToInt=true)
  if ( empty($key) ) die('sUser::GetUserInfo: invalid key');
  if ( !isset($_SESSION[QT.'_usr_info'][$key]) ) return $not;
  return ($bToInt ? (int)$_SESSION[QT.'_usr_info'][$key] : $_SESSION[QT.'_usr_info'][$key]);
}

// --------

public static function GetLastMember($strWhere='')
{
  global $oDB;
  $arr = array();
  $oDB->Query('SELECT max(id) as countid FROM '.TABUSER.' WHERE id>=0'.$strWhere);
  if ( $row=$oDB->Getrow() )
  {
  $arr['newuserid'] = (int)$row['countid'];
  $oDB->Query('SELECT name,firstdate FROM '.TABUSER.' WHERE id='.$row['countid'] );
  $row = $oDB->Getrow();
  $arr['newusername'] = $row['name'];
  $arr['newuserdate'] = (empty($row['firstdate']) ? '0' : substr($row['firstdate'],0,8)); // date only
  }
  return $arr;
}

// --------

public static function SetUserInfo($key,$value)
{
  if ( sUser::GetUserInfo($key,null)===null ) return;
  $_SESSION[QT.'_usr_info'][$key] = $value;
}

// --------

public static function GetCoppa()
{
  // No need to add query: coppa values are available in the $_SESSION[QT.'_usr_info']
  $children = sUser::GetUserInfo('children',0);
  $parentmail = sUser::GetUserInfo('parentmail',0);
  return array('children'=>(int)$children,'parentmail'=>$parentmail);
}

// --------

public static function LoginPostProc()
{
  if ( !sUser::Auth() ) return 'User is not authenticated';

  global $oVIP,$oHtml,$oDB;

  // check ban

  $ban = sUser::GetUserInfo('closed',0,true);
  if ( $ban>0 )
  {
    $items = sUser::GetUserInfo('numpost',0,true);
    // protection against hacking of admin/moderator
    if ( sUser::Id()<2 || sUser::IsStaff() || $items==0 )
    {
      $oDB->Exec('UPDATE '.TABUSER.' SET closed="0" WHERE id='.sUser::Id() );
      $oVIP->exiturl = 'qti_login.php?dfltname='.sUser::Name();
      $oVIP->exitname = L('Login');
      sUser::SessionUnset();
      $oHtml->PageMsg( NULL, '<p>'.L('Is_banned_nomore').'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '500px' );
    }

    // end ban control
    $last = sUser::GetUserInfo('lastdate','20000101');
    $intDays = 1;
    if ( $ban==2 ) $intDays = 10;
    if ( $ban==3 ) $intDays = 20;
    if ( $ban==4 ) $intDays = 30;
    $endban = DateAdd(substr($last,0,8),$intDays,'day');

    if ( date('Ymd')>$endban )
    {
      $oDB->Exec('UPDATE '.TABUSER.' SET closed="0" WHERE id='.sUser::Id() );
      $oVIP->exiturl = 'qti_login.php?dfltname='.sUser::Name();
      $oVIP->exitname = L('Login');
      sUser::SessionUnset();
      $oHtml->PageMsg( NULL, '<p>'.L('Is_banned_nomore').'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '500px' );
    }
    else
    {
      sUser::SessionUnset();
      $oHtml->PageMsg( NULL, '<h2>'.sUser::Name().' '.strtolower(L('Is_banned')).'</h2><p>'.Error(10).'</p><p>'.L('Retry_tomorrow').'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '500px' );
    }
  }

  // upgrade profile if new user (secrect question)

  $oDB->Query('SELECT secret_a FROM '.TABUSER.' WHERE id='.sUser::Id() );
  $row = $oDB->Getrow();
  if ( empty($row['secret_a']) )
  {
    $oVIP->exiturl = 'qti_userquestion.php?id='.sUser::Id();
    $oVIP->exitname = L('Secret_question');
    $oHtml->PageMsg(NULL,'<h2>'.L('Welcome').' '.sUser::Name().'</h2><br/><p/>'.L('Update_secret_question').'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '400px' );
  }
}

// --------

public static function AddUser($username='',$password='',$mail='',$role='U',$child='0',$parentmail='')
{
  if ( empty($username) ) return false;
  if ( empty($password) ) return false;
  global $oDB;
  $oDB->BeginTransac();
    $id = $oDB->Nextid(TABUSER);
    $b = $oDB->Exec('INSERT INTO '.TABUSER.' (id,name,pwd,closed,role,mail,privacy,firstdate,lastdate,numpost,children,parentmail,photo) VALUES ('.$id.',"'.QTstrd($username,64).'","'.sha1($password).'","0","'.$role.'","'.$mail.'","1","'.date('Ymd His').'","'.date('Ymd His').'",0,"'.$child.'","'.$parentmail.'","0")' );
  $oDB->CommitTransac();
  if ( $b===false ) return false;
  return $id;
}

// --------

public static function GetUserId($username='')
{
  // Returns FALSE when user does not exist OR when argument is wrong
  // Caution when testing returned value: userid 0 is visitor!
  if ( empty($username) || !is_string($username) ) return FALSE;
  global $oDB;
  $oDB->Query('SELECT id FROM '.TABUSER.' WHERE name="'.QTstrd($username).'"');
  if ( $row=$oDB->Getrow() ) return (int)$row['id'];
  return false;
}

// -------

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
  $oDB->Exec('UPDATE '.TABUSER.' SET y='.(isset($y) ? $y : 'NULL').',x='.(isset($x) ? $x : 'NULL').' WHERE id='.$id);
}

}