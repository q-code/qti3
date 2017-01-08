<?php // v 3.0 build:20160703

function array_get_or($arr,$key,$na=null)
{
  return (isset($arr[$key]) ? $arr[$key] : $na); // return an array value, or $na if not existing
}

// --------

function array_prefix_keys($str,$arrSource)
{
  // add the prefix $str to the keys in an array.
  if ( empty($str) || !is_string($str) ) die('array_prefix_keys: arg #1 must be a string');
  if ( !is_array($arrSource) ) die('array_prefix_keys: arg #2 must be an array');
  $arr = array();
  foreach($arrSource as $key=>$value) $arr[$str.$key]=$value;
  return $arr;
}

// --------

function EmptyFloat($i)
{
  // Return true when $i is empty or a value starting with '0.000000'
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('EmptyFloat: Invalid argument #1, must be a float, int or string');
  if ( substr((string)$i,0,8)==='0.000000' ) return true;
  return false;
}

// --------

function LimitSQL($strState,$strOrder,$intStart=0,$intLength=50)
{
  global $oDB;
  $strOrder = trim($strOrder); if ( strtolower(substr($strOrder,-3,3))!='asc' && strtolower(substr($strOrder,-4,4))!='desc' ) $strOrder .= ' asc';
  switch($oDB->type)
  {
    case 'pdo.mysql': return 'SELECT '.$strState.' ORDER BY '.$strOrder.' LIMIT '.$intStart.','.$intLength; break;
    case 'pdo.sqlsrv':
    case 'sqlsrv':
      if ($intStart==0 ) return 'SELECT TOP '.$intLength.' '.$strState.' ORDER BY '.$strOrder;
      return 'SELECT * FROM (SELECT ROW_NUMBER() OVER (ORDER BY '.$strOrder.') AS rownum, '.$strState.') AS orderrows WHERE rownum BETWEEN '.($intStart+1).' AND '.($intStart+$intLength).' ORDER BY rownum )'; break;
    case 'pdo.pg':
    case 'pg': return "SELECT $strState ORDER BY $strOrder LIMIT $intLength OFFSET $intStart"; break;
    case 'pdo.firebird':
    case 'ibase': return "SELECT FIRST $intLength SKIP $intStart $strState ORDER BY $strOrder"; break;
    case 'pdo.sqlite':
    case 'sqlite': return "SELECT $strState ORDER BY $strOrder LIMIT $intLength OFFSET $intStart"; break;
    case 'db2': return ($intStart==0 ? "SELECT $strState ORDER BY $strOrder FETCH FIRST $intLength ROWS ONLY" : "SELECT * FROM (SELECT ROW_NUMBER() OVER() AS RN, $strState) AS cols WHERE RN BETWEEN ($intStart+1) AND ($intStart+1+$intLength)"); break;
    case 'pdo.oci':
    case 'oci': return ($intStart==0 ? "SELECT * FROM (SELECT $strState ORDER BY $strOrder) WHERE ROWNUM<$intLength" : "SELECT * FROM (SELECT a.*, rownum RN FROM (SELECT $strState ORDER BY $strOrder) a WHERE rownum<$intStart+1+$intLength) WHERE rn>=$intStart"); break;
    default: return 'SELECT '.$strState.' ORDER BY '.$strOrder.' LIMIT '.$intStart.','.$intLength; break;
  }
}

// --------

function FirstCharCase($strField,$strCase='u',$len=1)
{
  global $oDB;
  switch($oDB->type)
  {
    case 'pdo.sqlsrv':
    case 'sqlsrv':
      if ( $strCase=='u' ) return "UPPER(LEFT($strField,$len))";
      if ( $strCase=='l' ) return "LOWER(LEFT($strField,$len))";
      if ( $strCase=='a-z' ) return "(ASCII(UPPER(LEFT($strField,1)))<65 OR ASCII(UPPER(LEFT($strField,1)))>90)";
      break;
    case 'pdo.pg':
    case 'pg':
      if ( $strCase=='u' ) return "UPPER(SUBSTRING($strField,1,$len))";
      if ( $strCase=='l' ) return "LOWER(SUBSTRING($strField,1,$len))";
      if ( $strCase=='a-z' ) return "UPPER($strField) !~ '^[A-Z]'";
      break;
    case 'pdo.firebird':
    case 'ibase':
      if ( $strCase=='u' ) return "UPPER(SUBSTRING($strField FROM 1 FOR $len))";
      if ( $strCase=='l' ) return "LOWER(SUBSTRING($strField FROM 1 FOR $len))";
      if ( $strCase=='a-z' ) return "(UPPER(SUBSTRING($strField FROM 1 FOR 1))<'A' OR UPPER(SUBSTRING($strField FROM 1 FOR 1))>'Z')";
      break;
    case 'pdo.sqlite':
    case 'sqlite':
      if ( $strCase=='u' ) return "UPPER(SUBSTR($strField,1,$len))";
      if ( $strCase=='l' ) return "LOWER(SUBSTR($strField,1,$len))";
      if ( $strCase=='a-z' ) return "(UPPER(SUBSTR($strField,1,1))<'A' OR UPPER(SUBSTR($strField,1,1))>'Z')";
      break;
    case 'pdo.oci':
    case 'oci':
    case 'db2':
      if ( $strCase=='u' ) return "UPPER(SUBSTR($strField,1,$len))";
      if ( $strCase=='l' ) return "LOWER(SUBSTR($strField,1,$len))";
      if ( $strCase=='a-z' ) return "(ASCII(UPPER(SUBSTR($strField,1,1)))<65 OR ASCII(UPPER(SUBSTR($strField,1,1)))>90)";
      break;
    default:
      if ( $strCase=='u' ) return "UPPER(LEFT($strField,$len))";
      if ( $strCase=='l' ) return "LOWER(LEFT($strField,$len))";
      if ( $strCase=='a-z' ) return "UPPER($strField) NOT REGEXP '^[A-Z]'";
      break;
  }
}

// --------

function SqlDateCondition($strDate='',$strField='firstpostdate',$intLength=4,$strComp='=')
{
  // Creates a where close for a date field. strDate can be an integer or the string 'old' (5 years or more)
  global $oDB;
  if ( $strDate==='old' ) { $strDate = '<"'.(Date('Y')-3).'"'; } else { $strDate = $strComp.'"'.$strDate.'"'; }
  switch($oDB->type)
  {
  case 'pdo.mysql': return 'LEFT('.$strField.','.$intLength.')'.$strDate; break;
  case 'pdo.pg':
  case 'pg': return 'SUBSTRING('.$strField.',1,'.$intLength.')'.$strDate; break;
  case 'pdo.firebird':
  case 'ibase': return 'SUBSTRING('.$strField.' FROM 1 FOR '.$intLength.')'.$strDate; break;
  case 'pdo.sqlite':
  case 'sqlite':
  case 'db2':
  case 'pdo.oci':
  case 'oci': return 'SUBSTR('.$strField.',1,'.$intLength.')'.$strDate; break;
  default: return 'LEFT('.$strField.','.$intLength.')'.$strDate;
  }
}

// --------
// COMMON FUNCTIONS
// --------

function AsFilename($str,$max=255)
{
  if ( !is_string($str) || !is_int($intMax) || $intMax<1) die('AsFilename: invalid arguments');
  $str = trim($str);
  $str=strtr($str,'éèêëÉÈÊËáàâäÁÀÂÄÅåíìîïÍÌÎÏóòôöÓÒÔÖõÕúùûüÚÙÛÜ','eeeeeeeeaaaaaaaaaaiiiiiiiioooooooooouuuuuuuu');
  $str=strtolower($str);
  $str=preg_replace('/[^a-z0-9_\-]/', '_', $str); // replace symbol by '_' (but keep the '.' and '-')
  if ( isset($str[$max]) ) $str = substr($str,0,$max);
  return $str;
}

function AsEmailText($str,$strId='mail-',$strIdN='',$bLink=true,$bHash=true,$arrProp=array())
{
  QTargs('AsEmailText',array($str,$strId,$strIdN,$bLink,$bHash,$arrProp),array('str','str','str','boo','boo','arr'));
  // arrProp can includes class, style, title
  if ( !QTismail($str) ) return $str;

  if ( $bLink )
  {
    if ( $bHash )
    {
    $arr = explode('@',$str,2);
    $strJava='<script type="text/javascript">document.getElementById("'.$strId.$strIdN.'").href="mailto:'.$arr[0].'"+"@"+"'.$arr[1].'";document.getElementById("'.$strId.$strIdN.'").innerHTML="'.$arr[0].'"+"@"+"'.$arr[1].'";</script>';
    $str = '';
    }
    return '<a id="'.$strId.$strIdN.'" href="mailto:'.$str.'"'.(isset($arrProp['title']) ? ' title="'.$arrProp['title'].'"' : '').(isset($arrProp['class']) ? ' class="'.$arrProp['class'].'"' : '').(isset($arrProp['style']) ? ' style="'.$arrProp['style'].'"' : '').(isset($arrProp['target']) ? ' target="'.$arrProp['target'].'"' : '').'>'.$str.'</a>'.(isset($strJava) ? $strJava: '');
  }
  else
  {
    if ( $bHash )
    {
    $arr = explode('@',$str,2);
    $strJava='<script type="text/javascript">document.getElementById("'.$strId.$strIdN.'").innerHTML="'.$arr[0].'"+"@"+"'.$arr[1].'";</script>';
    $str = '';
    }
    return '<span id="'.$strId.$strIdN.'"'.(isset($arrProp['title']) ? ' title="'.$arrProp['title'].'"' : '').(isset($arrProp['class']) ? ' class="'.$arrProp['class'].'"' : '').(isset($arrProp['style']) ? ' style="'.$arrProp['style'].'"' : '').(isset($arrProp['target']) ? ' target="'.$arrProp['target'].'"' : '').'>'.$str.'</span>'.(isset($strJava) ? $strJava: '');
  }
}
function AsEmailImg($str,$strId='mail-',$strIdN='',$bLink=true,$bHash=true,$arrProp=array(),$root='')
{
  QTargs('AsEmailImg',array($str,$strId,$strIdN,$bLink,$bHash,$arrProp,$root),array('str','str','str','boo','boo','arr','str'));
  // arrProp can includes class, style, title

  if ( $bLink )
  {
    if ( $bHash )
    {
    $arr = explode('@',$str,2);
    $strJava='<script type="text/javascript">document.getElementById("'.$strId.$strIdN.'").href="mailto:'.$arr[0].'"+"@"+"'.$arr[1].'";if (document.getElementById("img'.$strId.$strIdN.'")) document.getElementById("img'.$strId.$strIdN.'").title="'.$arr[0].'"+"@"+"'.$arr[1].'";</script>';
    $str = '';
    }
    return '<a id="'.$strId.$strIdN.'" href="mailto:'.$str.'"'.(isset($arrProp['title']) ? ' title="'.$arrProp['title'].'"' : '').(isset($arrProp['class']) ? ' class="'.$arrProp['class'].'"' : '').(isset($arrProp['style']) ? ' style="'.$arrProp['style'].'"' : '').(isset($arrProp['target']) ? ' target="'.$arrProp['target'].'"' : '').'><i id="img'.$strId.$strIdN.'" class="fa fa-envelope fa-lg" title="'.QTstrh($str).'"></i></a>'.(isset($strJava) ? $strJava: '');
  }
  else
  {
    if ( $bHash )
    {
    $arr = explode('@',$str,2);
    $strJava='<script type="text/javascript">document.getElementById("'.$strId.$strIdN.'").href="mailto:'.$arr[0].'"+"@"+"'.$arr[1].'";if (document.getElementById("img'.$strId.$strIdN.'")) document.getElementById("img'.$strId.'").title="'.$arr[0].'"+"@"+"'.$arr[1].'";</script>';
    $str = '';
    }
    //return '<img id="img'.$strId.$strIdN.'" src="'.$root.$_SESSION[QT]['skin_dir'].'/ico_user_e_1.gif" alt="email" title="'.$str.'" />';
    return '<i id="img'.$strId.$strIdN.'" class="fa fa-envelope" title="'.$str.'"></i>';
  }
}
function AsEmailsTxt($strEmails,$sep=' ',$strId='mail-',$bLink=true,$bHash=true,$intMax=0,$strEmpty='&nbsp;',$arrProp=array())
{
  if ( empty($strEmails) || !is_string($strEmails) ) return $strEmpty;
  // get list of Emails (and remove duplicate mails)
  $arrEmails = array_unique(QTexplodeStr(';, ',$strEmails));
  if ( $intMax>0 ) $arrEmails = array_slice($arrEmails,0,$intMax);
  // render emails
  $arr = array();
  foreach ($arrEmails as $i=>$str)
  {
    $arr[]= AsEmailText($str,(string)$strId,(string)$i,$bLink,$bHash,$arrProp);
  }
  return implode($sep,$arr);
}
function AsEmailsImg($strEmails,$sep='',$strId='mail-',$bLink=true,$bHash=true,$intMax=0,$strEmpty='&nbsp;',$arrProp=array(),$root='')
{
  if ( empty($strEmails) || !is_string($strEmails) ) return $strEmpty;
  // get list of Emails (and remove duplicate mails)
  $arrEmails = array_unique(QTexplodeStr(';, ',$strEmails));
  if ( $intMax>0 ) $arrEmails = array_slice($arrEmails,0,$intMax);
  // render emails
  $arr = array();
  foreach ($arrEmails as $i=>$str)
  {
    $arr[]= AsEmailImg($str,(string)$strId,(string)$i,$bLink,$bHash,$arrProp,$root);
  }
  return implode($sep,$arr);
}

// --------

function AsFormat($arr='',$strFormat='',$strSep='<br/>')
{
  if ( !is_array($arr) ) $arr = array($arr);
  if ( empty($strFormat) || $strFormat==='%s' ) return implode($strSep,$arr);

  foreach($arr as $strKey=>$strValue)
  {
  if ( $strValue==='' ) continue;
  if ( strstr($strFormat,',') ) continue;
  if ( strstr($strFormat,' ; ') ) continue;
  $arr[$strKey] = sprintf($strFormat,$strValue);
  }

  return implode($strSep,$arr);
}

// --------

function AsUserImg($src='',$title='',$class='picbox',$alt='',$id='',$style='')
{
  QTargs( 'AsUserImg',array($src,$class,$title,$alt,$id,$style) );
  if ( empty($src) ) $src = $_SESSION[QT]['skin_dir'].'/user.gif';
  return '<img src="'.$src.'" alt="'.QTconv($alt).'" title="'.QTstrh($title).'" class="'.$class.'"'.($style==='' ? '' : ' style="'.$style.'"').($id==='' ? '' : ' id="'.$id.'"').'/>';
}

function AsImgBox($img='',$caption='',$class='picbox',$style='',$href='')
{
  QTargs('AsImgBox',array($img,$class,$style,$caption,$href));
  if ( !empty($caption) && !empty($href) ) $caption = '<a href="'.Href($href).'">'.$caption.'</a>';
  return '<div class="'.$class.'"'.(empty($style) ? '' : ' style="'.$style.'"').'>'.$img.(empty($caption) ? '' : '<p>'.$caption.'</p>').'</div>';
}

function AsImg($strSrc='',$strAlt='',$strTitle='',$strClass='',$strStyle='',$strHref='',$strId='',$strHrefClass='')
{
  QTargs( 'AsImg',array($strSrc,$strAlt,$strClass,$strStyle,$strHref,$strId) );

  if ( empty($strSrc) ) return '';
  $strSrc = '<img src="'.$strSrc.'" alt="'.(empty($strAlt) ? '' : QTconv($strAlt)).'" title="'.(empty($strTitle) ? '' : QTconv($strTitle)).'"'.(!empty($strClass) ? ' class="'.$strClass.'"' : '').(empty($strStyle) ? '' : ' style="'.$strStyle.'"').(empty($strId) ? '' : ' id="'.$strId.'"').'/>';
  if ( empty($strHref) ) { return $strSrc; } else { return '<a'.(!empty($strHrefClass) ? ' class="'.$strHrefClass.'"' : '').' href="'.$strHref.'">'.$strSrc.'</a>' ; }
}

function AsImgPopup($strId='',$strSrc='',$strAlt='',$strTitle='',$strClass='',$strStyle='',$strHref='')
{
  QTargs('AsImg',array($strId,$strSrc,$strAlt,$strClass,$strStyle,$strHref));
  if ( empty($strSrc) ) return '';
  return '<img class="popup clickable" id="popup_'.$strId.'" src="'.$strSrc.'" style="display:none" onclick="qtHide(this.id);" alt="(image not found)"/><img'.(empty($strId) ? '' : ' id="'.$strId.'"').' src="'.$strSrc.'" alt="'.(empty($strAlt) ? '' : QTconv($strAlt)).'" title="'.(empty($strTitle) ? '' : QTconv($strTitle)).'" class="clickable'.(empty($strClass) ? '' : ' '.$strClass).'"'.(empty($strStyle) ? '' : ' style="'.$strStyle.'"').' onclick="qtPopupImage(this,\''.(empty($strHref) ? '' : $strHref).'\');"/>';
}


// --------

function DateAdd($d='0',$i=-1,$str='year')
{
   if ( $d=='0' ) die('DateAdd: Argument #1 must be a string');
   QTargs( 'DateAdd',array($d,$i,$str),array('str','int','str') );

   $intY = intval(substr($d,0,4));
   $intM = intval(substr($d,4,2));
   $intD = intval(substr($d,6,2));
   switch($str)
   {
   case 'year': $intY += $i; break;
   case 'month': $intM += $i; break;
   case 'day': $intD += $i; break;
   }
   if ( in_array($intM,array(1,3,5,7,8,10,12)) && $intD>31 ) { ++$intM; $intD -= 31; }
   if ( in_array($intM,array(4,6,9,11)) && $intD>30 ) { ++$intM; $intD -= 30; }
   if ( $intD<1 ) { --$intM; $intD += 30; }
   if ( $intM>12 ) { ++$intY; $intM -= 12; }
   if ( $intM<1 ) { --$intY; $intM += 12; }
   if ( $intM==2 && $intD>28 ) { ++$intM; $intD -= 28; }
   return strval($intY*10000+$intM*100+$intD).(strlen($d)>8 ? substr($d,8) : '');
}

// --------

function FieldQuote($strValue,$strTable,$strField)
{
  // Returns a quoted value, except for these fields:
  if ( $strTable===TABTOPIC && in_array($strField,array('id','numid','forum','firstpostid','lastpostid','firstpostuser','lastpostuser','x','y','z','actorid','notifiedid','replies','views'),$strField) ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( $strTable===TABPOST && in_array($strField,array('id','forum','topic','userid','modifuser')) ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( $strTable===TABUSER && $strField=='id' ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( $strTable===TABSECTION && in_array($strField,array('id','domainid','titleorder','moderator')) ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( $strTable===TABDOMAIN && in_array($strField,array('id','titleorder')) ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( $strTable===TABSTATUS && $strField=='id' ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( empty($strValue) ) {
  if ( in_array($strField,array('birthday','docdate','eventdate','fielddate','firstdate','firstpostdate','issuedate','lastdate','lastpostdate','modifdate','statusdate','wisheddate')) ) {
    return '"0"';
  }}
  return '"'.$strValue.'"';
}

// --------

function GetDomains()
{
  global $oDB;
  $arr = array();
  $oDB->Query('SELECT id,title FROM '.TABDOMAIN.' ORDER BY titleorder');
  while( $row=$oDB->Getrow() ) $arr[(int)$row['id']] = $row['title'];
  // search translation
  $arrL = cLang::Get('domain',QTiso(),'*');
  if ( count($arrL)>0 )
  {
  foreach($arr as $id=>$str) if ( !empty($arrL['d'.$id]) ) $arr[$id]=$arrL['d'.$id];
  }
  return $arr;
}

// --------

function GetSections($strRole='V',$intDomain=-1,$arrReject=array(),$bTranslateTitle=true,$strExtra='',$strOrder='d.titleorder,s.titleorder')
{
  // Returns an array of sections. The format is $arrSection[sectionid] = array of section info (all fields from the database)
  // Use $intDomain to get sections in this domain only.
  // $intDomain=-1 returns sections in all domains.
  // $intDomain=-2 returns sections grouped by domain (see definition in SectionsByDomain)

  if ( is_int($arrReject) || is_string($arrReject) ) $arrReject = array((int)$arrReject);
  QTargs( 'GetSections',array($strRole,$intDomain,$arrReject,$strExtra,$strOrder),array('str','int','arr','str','str') );
  if ( $intDomain>=0 ) { $strWhere = 's.domainid='.$intDomain; } else { $strWhere = 's.domainid>=0'; }
  if ( $strRole==='V' || $strRole==='U' ) $strWhere .= ' AND s.type<>"1"';
  if ( !empty($strExtra) ) $strWhere .= ' AND '.$strExtra;
  $arr = array();
  global $oDB;
  $oDB->Query('SELECT s.* FROM '.TABSECTION.' s INNER JOIN '.TABDOMAIN.' d ON s.domainid=d.id WHERE '.$strWhere.' ORDER BY '.$strOrder);
  while($row=$oDB->Getrow())
  {
    $id = (int)$row['id'];
    // if reject
    if ( in_array($id,$arrReject,true) ) continue;

    // add name (translation of title)
    if ( empty($row['title']) ) $row['title']='(section '.$id.')';
    if ( $bTranslateTitle )
    {
      $str = cLang::ObjectName('sec','s'.$id,false); // {false} to return '' when translation not defined
      if ( !empty($str) ) $row['title']=$str;
    }
    $arr[$id] = $row;
  }
  if ( $intDomain==-2 ) $arr = SectionsByDomain($strRole,$arr);
  return $arr;
}

// --------

function SectionsByDomain($role='',$arrSections=array())
{
  // Returns an array of domains+sections. The format is $arr[domainid][sectionid] = array of section info
  // This can work also with on a subset of sections (if $arrSections is not empty)
  // Role is used to hide the protected sections.
  // If an argument is empty, uses the current role/list of sections.
  // When a domain don't have sections, it is not returned ($arr[domainid] is not set).
  $arr = array();
  if ( empty($arrSections) ) $arrSections = sMem::Get('sys_sections');
  if ( empty($role) ) $role = sUser::Role();
  foreach($arrSections as $id=>$arrSection)
  {
    if ( ($role==='V' || $role==='U') && isset($arrSection['type']) && $arrSection['type']==='1' ) continue;
    $arr[(int)$arrSection['domainid']][$id] = $arrSection;
  }
  return $arr;
}

// --------

function GetStats($bClosed=false)
{
  // returns topics and replies per section id
  $arr = array('all'=>array('topics'=>0,'replies'=>0,'topicsZ'=>0,'repliesZ'=>0));
  global $oDB;
  $oDB->Query('SELECT s.id,count(t.id) as topics,sum(t.replies) as replies FROM '.TABSECTION.' s LEFT JOIN '.TABTOPIC.' t ON s.id=t.forum GROUP BY s.id' );
  while($row=$oDB->Getrow())
  {
    $i = (isset($row['topics']) ? intval($row['topics']) : 0);
    $arr[intval($row['id'])]['topics']=$i; $arr['all']['topics'] += $i;
    $i = (isset($row['replies']) ? intval($row['replies']) : 0);
    $arr[intval($row['id'])]['replies']=$i; $arr['all']['replies'] += $i;
  }
  if ( $bClosed )
  {
    $oDB->Query('SELECT s.id,count(t.id) as topics,sum(t.replies) as replies FROM '.TABSECTION.' s LEFT JOIN '.TABTOPIC.' t ON s.id=t.forum WHERE t.status="1" GROUP BY s.id' );
    while($row=$oDB->Getrow())
    {
    $i = (isset($row['topicsZ']) ? intval($row['topicsZ']) : 0);
    $arr[intval($row['id'])]['topicsZ']=$i; $arr['all']['topicsZ'] += $i;
    $i = (isset($row['repliesZ']) ? intval($row['repliesZ']) : 0);
    $arr[intval($row['id'])]['repliesZ']=$i; $arr['all']['repliesZ'] += $i;
    }
  }
  return $arr;
}

// --------

function GetURI($reject=array(),$uri='')
{
  if ( is_string($reject) ) $reject=explode(',',$reject);
  if ( !is_array($reject) ) die('GetURI: invalid argument #1');
  $arr = QTexplodeUri($uri);
  foreach($reject as $key) unset($arr[trim($key)]);
  return QTimplodeUri($arr);
}

/**
 * GetUserInfo (and GetUsersInfo)
 *
 * $id can be [int, array of int, string of 1 int, csv-string], or "A" (all admin) or "S" (all staff)
 * $field is ONE field. When Passing to GetUsersInfo, the id is added as the array-key in the result 
 */
function GetUserInfo($id,$field='name')
{
  if ( !is_string($field) ) die ('GetUserInfo: Missing field');
  if ( is_string($id) && is_numeric($id) && $id==(int)$id ) $id=(int)$id;
  if ( is_int($id) )
  {
    if ( $id<0 ) die ('GetUserInfo: Missing user id');
    global $oDB;
    $oDB->Query('SELECT '.$field.' FROM '.TABUSER.' WHERE id='.$id);
    $row = $oDB->Getrow();
    return $row[$field];
  }
  return GetUsersInfo($id,$field); // handle array,"A","S" and csv
}
function GetUsersInfo($ids,$field='name')
{
  if ( !is_string($field) ) die ('GetUsersInfo: Missing field');
  $sql='';
  if ( $ids==='A' || $ids==='S' ) $sql = 'SELECT id,'.$field.' FROM '.TABUSER.' WHERE role="'.$ids.'"';
  if ( is_string($ids) && strstr($ids,',') ) $ids = explode(',',$ids);
  if ( is_array($ids) ) $sql = 'SELECT id,'.$field.' FROM '.TABUSER.' WHERE id IN ('.implode(',',$ids).')';
  if ( empty($sql) ) die ('GetUsersInfo: Missing ids');

  $lst = array();
  global $oDB;
  $oDB->Query($sql);
  while( $row=$oDB->Getrow() ) $lst[(int)$row['id']] = $row[$field];
  return $lst;
}
/**
 * GetUserInfo (and GetUsersInfo)
 *
 * $id can be [int, array of int, string of 1 int, csv-string], or "A" (all admin) or "S" (all staff)
 * $field is an array of fields. When Passing to GetUsersInfos, the id is added as the array-key in the result 
 */
function GetUserInfos($id,$fields=array('id','name'))
{
  if ( !is_array($fields) ) die ('GetUserInfos: Missing fields');
  if ( !in_array('id',$fields) ) $fields[]='id';
  
  if ( is_string($id) && is_numeric($id) && $id==(int)$id ) $id=(int)$id;
  if ( is_int($id) )
  {
    if ( $id<0 ) die ('GetUserInfos: Missing user id');
    global $oDB;
    $oDB->Query('SELECT '.implode(',',$fields).' FROM '.TABUSER.' WHERE id='.$id);
    $row = $oDB->Getrow();
    return $row;
  }
  return GetUsersInfos($id,$field); // handle array,"A","S" and csv
}
function GetUsersInfos($ids,$fields=array('id','name'))
{
  if ( !is_array($fields) ) die ('GetUsersInfos: Missing fields');
  if ( !in_array('id',$fields) ) $fields[]='id';  
  $sql='';
  if ( $ids==='A' || $ids==='S' ) $sql = 'SELECT '.implode(',',$fields).' FROM '.TABUSER.' WHERE role="'.$ids.'"';
  if ( is_string($ids) && strstr($ids,',') ) $ids = explode(',',$ids);
  if ( is_array($ids) ) $sql = 'SELECT '.implode(',',$fields).' FROM '.TABUSER.' WHERE id IN ('.implode(',',$ids).')';
  if ( empty($sql) ) die ('GetUsersInfos: Missing fields');
  
  $lst = array();
  global $oDB;
  $oDB->Query($sql);
  while( $row=$oDB->Getrow() ) $lst[(int)$row['id']] = $row;
  return $lst;  
}

// --------

function GetUsers($strRole='A',$strValue='',$strOrder='name',$iMax=200)
{
  // Return an array of maximum iMax=200 users id/name
  // $strRole: Search 'A' admins, 'M' staff(+admin), 'M-' staff(-admin), 'NAME' a name, 'A*' a name beginning by A, 'ID' the user having id=$strValue
  // Attention: names are htmlquoted in the db, no need to stripslashes

  global $oDB;
  if ( substr($strRole,-1,1)==='*' )
  {
    $strQ = 'name '.($oDB->type==='pg' ? 'ILIKE' : 'LIKE' ).' "'.substr($strRole,0,-1).'%" ORDER BY '.$strOrder;
  }
  else
  {
    switch(strtoupper($strRole))
    {
    case 'A':   $strQ = 'role="A" ORDER BY '.$strOrder; break;
    case 'M':   $strQ = 'role="A" OR role="M" ORDER BY '.$strOrder; break;
    case 'M-':  $strQ = 'role="M" ORDER BY '.$strOrder; break;
    case 'NAME':$strQ = 'name="'.$strValue.'" ORDER BY '.$strOrder; break;
    case 'ID':  $strQ = 'id='.$strValue; break;
    default: die('GetUsers: Unkown search rule ['.$strRole.']');
    }
  }
  $oDB->Query('SELECT id,name FROM '.TABUSER.' WHERE '.$strQ );
  $arrUsers = array();
  $i=1;
  while ($row=$oDB->Getrow())
  {
    $arrUsers[$row['id']]=$row['name'];
    ++$i; if ( $i>$iMax ) break;
  }
  return $arrUsers;
}

// --------

function InvalidUpload($arrFile=array(),$strExtensions='',$strMimes='',$intSize=0,$intWidth=0,$intHeight=0)
{
  // For the uploaded document ($arrFile), this function returns (as string):
  // '' (empty string) if it matches with all conditions (see parameters)
  // An error message if not, and unlink the uploaded document.
  //
  // @$arrFile: The uploaded document ($_FILES['fieldname']).
  // @$strExtensions: List of valid extensions (as string, without point). Empty to skip.
  // @$strMimes: List of valid mimetypes as csv (can be an array). Empty to skip
  // @$intSize: Maximum file size (kb). 0 to skip.
  // @$intWidth: Maximum image width (pixels). 0 to skip.
  // @$intHeight: Maximum image width (pixels). 0 to skip.

  if ( is_array($strExtensions) ) $strExtensions=implode(', ',$strExtensions);
  if ( is_array($strMimes) ) $strMimes=implode(', ',$strMimes);

  if ( !is_array($arrFile) ) die('CheckUpload: argument #1 must be an array');
  if ( !is_string($strExtensions) ) die('CheckUpload: argument #2 must be a string');
  if ( !is_string($strMimes) ) die('CheckUpload: argument #3 must be a string');
  if ( !is_int($intSize) ) die('CheckUpload: argument #4 must be an integer');
  if ( !is_int($intWidth) ) die('CheckUpload: argument #5 must be an integer');
  if ( !is_int($intHeight) ) die('CheckUpload: argument #6 must be an integer');

  global $L;

  // check load

  if ( !is_uploaded_file($arrFile['tmp_name']) )
  {
    if ( isset($arrFile['error']) )
    {
      switch($arrFile['error'])
      {
      case 1: return 'Upload error #1. File size exceeds the server limit.'; break;
      case 2: return 'Upload error #2. File size exceeds the form limit (&lt;'.$intSize.' Kb)'; break;
      case 3: return 'Upload error #3. File not fully transmitted.'; break;
      default: return 'Upload error #'.$arrFile['error'].'. File not uploaded'; break;
      }
    }
    return 'You id not upload a file!';
  }

  // check size (kb)

  if ( $intSize>0 ) {
  if ( $arrFile['size'] > ($intSize*1024+16) ) {
    unlink($arrFile['tmp_name']);
    return $L['E_file_size'].' (&lt;'.$intSize.' Kb)';
  }}

  // check extension

  if ( !empty($strExtensions) )
  {
    $strExt = strrchr($arrFile['name'],'.');
    if ( $strExt===FALSE )
    {
    unlink($arrFile['tmp_name']);
    return 'File without extension not supported... Use '.$strExtensions;
    }
    $strExt = substr($strExt,1); //remove the point
    if ( strpos(strtolower($strExtensions),strtolower($strExt))===FALSE )
    {
    unlink($arrFile['tmp_name']);
    return 'File extension ['.$strExt.'] not supported... Use '.$strExtensions;
    }
  }

  // check mimetype

  if ( !empty($strMimes) ) {
  if ( strpos(strtolower($strMimes),strtolower($arrFile['type']))===FALSE ) {
    unlink($arrFile['tmp_name']);
    return 'Format ['.$arrFile['type'].'] not supported... Use '.$strExtensions;
  }}

  // check size (pixels)

  if ( $intWidth>0 || $intHeight>0 )
  {
    $size = getimagesize($arrFile['tmp_name']);
    if ( $intWidth>0 ) {
    if ( $size[0] > $intWidth ) {
      unlink($arrFile['tmp_name']);
      return $intWidth.'x'.$intHeight.' '.$L['E_pixels_max'];
    }}
    if ( $intHeight>0 ) {
    if ( $size[1] > $intHeight ) {
      unlink($arrFile['tmp_name']);
      return $intWidth.'x'.$intHeight.' '.$L['E_pixels_max'];
    }}
  }

  return '';
}

// --------

function MakePager($uri,$count,$intPagesize=50,$currentpage=1)
{
  if ( !is_int($intPagesize) || $intPagesize<5 ) $intPagesize=50;
  if ( !is_int($currentpage) || $currentpage<1 ) $currentpage=1;
  $arrUri = parse_url($uri); if ( !isset($arrUri['query']) ) $arrUri['query']='';
  $uri = Href($arrUri['path']);
  $arg = $arrUri['query'];
  $arg = str_replace('&amp;','&',$arg);
  $arrArg = explode('&',$arg);
  $arrNew = array();
  foreach($arrArg as $strValue)
  {
    if ( substr($strValue,0,4)=='page' ) continue;
    $arrNew[]=$strValue;
  }
  $arg = implode('&amp;',$arrNew);

  $strPages='';
  $firstpage='';
  $lastpage='';
  $top = ceil($count/$intPagesize);
  if ( $currentpage<5 )
  {
    $arrPages=array(1,2,3,4,5);
  }
  elseif ( $currentpage==$top )
  {
    $arrPages=array($currentpage-4,$currentpage-3,$currentpage-2,$currentpage-1,$currentpage);
  }
  else
  {
    $arrPages=array($currentpage-2,$currentpage-1,$currentpage,$currentpage+1,$currentpage+2);
  }

  // pages
  foreach($arrPages as $page)
  {
    if ( $count>$intPagesize && $page>=1 && $page<=$top )
    {
    $strPages .= ' '.($currentpage==$page ? '<span class="current">'.$page.'</span>' : '<a href="'.$uri.'?'.$arg.'&amp;page='.$page.'">'.$page.'</a>');
    }
  }
  // extreme
  if ( $count>($intPagesize*5) )
  {
    if ( $arrPages[0]>1 ) $firstpage = ' <a href="'.$uri.'?'.$arg.'&amp;page=1" title="'.L('First').'">&laquo;</a>';
    if ( $arrPages[4]<$top ) $lastpage = ' <a href="'.$uri.'?'.$arg.'&amp;page='.$top.'" title="'.L('Last').': '.$top.'">&raquo;</a>';
  }
  return $firstpage.$strPages.$lastpage;
}

// --------

function TargetDir($strRoot='',$intId=0)
{
  // This check if directory/subdirectory is available for an Id

  $strDir = '';
  $intDir = ($intId>0 ? floor($intId/1000) : 0);
  if ( is_dir($strRoot.strval($intDir).'000') )
  {
    $strDir = strval($intDir).'000/';
    $intSDir = $intId-($intDir*1000);
    $intSDir = ($intSDir>0 ? floor($intSDir/100) : 0);
    if ( is_dir($strRoot.$strDir.strval($intDir).strval($intSDir).'00') ) $strDir .= strval($intDir).strval($intSDir).'00/';
  }
  return $strDir;
}

// --------

function ToCsv($str,$strSep=';',$strEnc='"',$strSepAlt=',',$strEncAlt="'")
{
  // Converts a value ($str) to a csv text with final separator [;]. A string is enclosed by ["].
  // When $str contains the separator or the encloser character, they are replaced by the alternates ($strSepAlt,$strEncAlt)
  // TIP: $strSep empty (or "\r\n") to generate a end-line value
  if ( is_int($str) || is_float($str) ) return $str.$strSep;
  if ( $str==='' || is_null($str) ) return $strEnc.$strEnc.$strSep;
  $str = str_replace('&nbsp;',' ',$str);
  $str = str_replace("\r\n",' ',$str);
  $str = QTconv($str,'-4');
  $str = str_replace($strSep,$strSepAlt,$str);
  $str = str_replace($strEnc,$strEncAlt,$str);
  return $strEnc.$str.$strEnc.$strSep;
}