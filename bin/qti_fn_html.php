<?php // QuickTicket 3.0 build:20160703

function Sectionlist($selected=-1,$arrReject=array(),$arrDisabled=array(),$strAll='',$textsize=40,$bAsAdmin=false)
{
  // Attention $selected is type-sensitive. To pre-select an option provide an [int] (because section-ids are [int]). '*' is also possible when $strAll is used.
  // If $strAll is not empty, the list includes in first position an 'all' option having the value '*' and the label $strAll.
  // To remove some section(s) from this list, use $arrReject and provide an array of id's [int]. Providing one id [int] is also possible.
  // List excludes HIDDEN sections (for user/visitor) to force having all sections use $bAsAdmin=true
  if ( is_int($arrReject) || is_string($arrReject) ) $arrReject = array((int)$arrReject);
  if ( is_int($arrDisabled) || is_string($arrDisabled) ) $arrDisabled = array((int)$arrDisabled);
  QTargs('Sectionlist',array($arrReject,$arrDisabled,$strAll),array('arr','arr','str'));
  $str = '';
  $arr = sMem::Get('sys_sections');
  $role = $bAsAdmin ? 'A' : sUser::Role();
  if ( !empty($strAll) ) $str ='<option value="*"'.($selected==='*' ? QSEL : '').(in_array('*',$arrDisabled,true) ? QDIS : '').'>'.QTtrunc($strAll,$textsize).'</option>';
  if ( is_array($arr) )
  {
    // reject
    if ( count($arrReject)>0 ) { foreach($arrReject as $id) if ( isset($arr[$id]) ) unset($arr[$id]); }
    // format
    $arrDomains = sMem::Get('sys_domains');
    if ( count($arr)>3 && count($arrDomains)>1 )
    {
      $arr = SectionsByDomain($role,$arr); // Uses sections groupped by domain. Empty domain are skipped
      foreach ($arrDomains as $intDom=>$strDom)
      {
        if ( isset($arr[$intDom]) )
        {
        $str .= '<optgroup label="'.QTtrunc($strDom,$textsize).'">';
        foreach($arr[$intDom] as $id=>$row) $str .= '<option value="'.$id.'"'.($id===$selected ? QSEL : '').(in_array($id,$arrDisabled,true) ? QDIS : '').'>'.QTtrunc($row['title'],$textsize).'</option>';
        $str .= '</optgroup>';
        }
      }
    }
    else
    {
      foreach($arr as $id=>$row)
      {
      if ( ($role==='V' || $role==='U') && isset($row['type']) && $row['type']==='1' ) continue; // reject hidden section
      $str .= '<option value="'.$id.'"'.($id===$selected ? QSEL : '').(in_array($id,$arrDisabled,true) ? QDIS : '').'>'.QTtrunc($row['title'],$textsize).'</option>';
      }
    }
  }
  return $str;
}
  
// --------

function EchoPage($content='Page not defined')
{
if ( !is_string($content) && !is_int($content) ) die('EchoPage: invalid argument');

global $oVIP,$oHtml,$L;
$oVIP->selfurl='qti_index.php';
$oVIP->exiturl='qti_index.php';
include 'qti_inc_hd.php';

if ( is_int($content) )
{
  $oHtml->Msgbox('!');
  if ( $content===99 )
  {
    $content = Translate('sys_offline.txt',false);
    if ( file_exists($content) ) { include $content; } else { echo Error(99); }
  }
  else
  {
    echo Error($content);
  }
  $oHtml->Msgbox(END);
}
else
{
  echo $content;
}

include 'qti_inc_ft.php';
}

// --------

function EchoBanner($id='banner',$logo='',$langMenu='',$mainMenu='',$class='')
{
echo '<div id="'.$id.'"'.(empty($class) ? '' : ' class="'.$class.'"').'>',PHP_EOL;
if ( !empty($logo) ) echo '<img id="logo" src="',$logo,'" alt="',QTstrh($_SESSION[QT]['site_name'],24),'" title="',QTstrh($_SESSION[QT]['site_name']),'"/>',PHP_EOL;
if ( !empty($langMenu) ) echo $langMenu,PHP_EOL;
if ( !empty($mainMenu) ) echo $mainMenu,PHP_EOL;
echo '</div>',PHP_EOL;
}

// --------

function GetCheckedIds($checkbox='t1-cb',$altCsv=false)
{
  if ( isset($_POST[$checkbox]) ) 
  {
  $arrId = is_array($_POST[$checkbox]) ? $_POST[$checkbox] : explode(',',$_POST[$checkbox]);
  }
  elseif ( is_string($altCsv) )
  {
  $arrId = explode(',',$altCsv);
  }
  else
  {
  die('Nothing selected');
  }
  return array_map('intval', $arrId);
}

// --------

function HtmlLettres($baseUrl='',$strGroup='all',$strAll='All',$strClass='lettres',$strTitle='',$intSize=1,$bFilterForm=true)
{
  // $strGroup is the current group, $strAll is the label of the 'all' group
  if ( empty($baseUrl) ) $baseUrl=Href();
  if ( !strpos($baseUrl,'?') ) $baseUrl .='?';
  $or = ' '.L('or').' ';
  switch($intSize)
  {
  case 1: $arr = explode('.','A.B.C.D.E.F.G.H.I.J.K.L.M.N.O.P.Q.R.S.T.U.V.W.X.Y.Z'); break;
  case 2: $arr = explode('.','A|B.C|D.E|F.G|H.I|J.K|L.M|N.O|P.Q|R.S|T.U|V.W|X.Y|Z'); break;
  case 3: $arr = explode('.','A|B|C.D|E|F.G|H|I.J|K|L.M|N|O.P|Q|R.S|T|U.V|W.X|Y|Z'); break;
  case 4: $arr = explode('.','A|B|C|D.E|F|G|H.I|J|K|L.M|N|O|P.Q|R|S|T.U|V|W.X|Y|Z'); break;
  }
  $str = '<a class="primary"'.($strGroup==='all' ? ' id="active"' : '').' href="'.($strGroup==='all' ? 'javascript:void(0)' : $baseUrl.'&amp;group=all').'">'.$strAll.'</a>';
  foreach($arr as $g)
  {
  $str .= '<a'.($strGroup===$g ? ' id="active"' : '').' href="'.($strGroup===$g ? 'javascript:void(0)' : $baseUrl.'&amp;group='.$g).'"'.( empty($strTitle) ? '' : ' title="'.$strTitle.str_replace('|',$or,$g).'"' ).'>'.str_replace('|','',$g).'</a>';
  }
  $str .= '<a class="primary"'.($strGroup==='0' ? ' id="active"' : '').' href="'.($strGroup==='0' ? 'javascript:void(0)' : $baseUrl.'&amp;group=0').'"'.( empty($strTitle) ? '' : ' title="'.$strTitle.L('Other_char').'"' ).'>#</a>';

  $strGroups  = '<div class="'.$strClass.'">';
  $strGroups .= '<span class="label">'.L('Show').'</span>'.$str;
  if ( $bFilterForm )
  {
  $strGroups .= '<form method="get" action="'.$baseUrl.'"><input required type="text" class="'.$strClass.'" value="'.($strGroup=='all' || in_array($strGroup,$arr) ? '' : $strGroup).'" name="group" maxlength="7" title="'.$strTitle.'"/><input type="image" src="bin/css/find.png" id="searchbutton"/>'.QTuritoform($baseUrl,true,'page,group').'</form>';
  }
  $strGroups .= '</div>';

  return $strGroups;
}

// --------

function HtmlCsvLink($strUrl,$intCount=20,$intPage=1)
{
  if ( empty($strUrl) ) return '';
  if ( $intCount<=$_SESSION[QT]['items_per_page'] )
  {
  return '<a class="csv" href="'.$strUrl.'&amp;size=all&amp;n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').'</a>';
  }
  else
  {
  $strCsv = '<a class="csv" href="'.$strUrl.'&amp;size=p'.$intPage.'&amp;n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">('.strtolower(L('Page')).')</span></a>';
  if ( $intCount<=1000 )                   $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&amp;size=all&amp;n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">('.strtolower(L('All')).')</span></a>';
  if ( $intCount>1000 && $intCount<=2000 ) $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&amp;size=m1&amp;n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1-1000)</span></a> &middot; <a href="'.$strUrl.'&amp;size=m2&amp;n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1000-'.$intCount.')</span></a>';
  if ( $intCount>2000 && $intCount<=5000 ) $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&amp;size=m5&amp;n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1-5000)</span></a>';
  if ( $intCount>5000 )                    $strCsv .= ' &middot; <a class="csv" href="'.$strUrl.'&amp;size=m5&amp;n='.$intCount.'" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(1-5000)</span></a> &middot; <a href="'.$strUrl.'&amp;size=m10&amp;n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' <span class="csvpages">(5000-10000)</span></a>';
  }
  return $strCsv;
}

// --------

function HtmlScore($strLevel='3',$strSep='<br/>',$i=-1)
{

if ( is_string($i) ) $i=trim(substr($i,0,4));
if ( !is_string($strSep) ) $strSep='<br/>';
if ( !is_numeric($i) ) $i=-1;
$i = floatval($i);

switch($strLevel)
{
case '3':
return '
<input type="radio" name="title" id="i00" value="0" /><label for="i00">'.ValueScalebar(1,3).' '.ValueName(1,3).'</label>'.$strSep.'
<input type="radio" name="title" id="i50" value="50"'.(QTisbetween($i,33.33,66.66) ? QCHE : '').'/><label for="i50">'.ValueScalebar(50,3).' '.ValueName(50,3).'</label>'.$strSep.'
<input type="radio" name="title" id="i99" value="99"'.(QTisbetween($i,66.66) ? QCHE : '').'/><label for="i99">'.ValueScalebar(99,3).' '.ValueName(99,3).'</label>'.$strSep.'
<input type="radio" name="title" id="inull" value="null"'.($i<0 ? QCHE : '').'/><label for="inull">'.L('Unknown').'</label>'.$strSep.'
';
break;
case '5':
return '
<input type="radio" name="title" id="i00" value="0" /><label for="i00">'.ValueScalebar(1,5).' '.ValueName(1,5).'</label><br />
<input type="radio" name="title" id="i20" value="20"'.(QTisbetween($i,20,39.99) ? QCHE : '').'/><label for="i20">'.ValueScalebar(25,5).' '.ValueName(25,5).'</label>'.$strSep.'
<input type="radio" name="title" id="i40" value="40"'.(QTisbetween($i,40,59.99) ? QCHE : '').'/><label for="i40">'.ValueScalebar(50,5).' '.ValueName(50,5).'</label>'.$strSep.'
<input type="radio" name="title" id="i60" value="60"'.(QTisbetween($i,60,79.99) ? QCHE : '').'/><label for="i60">'.ValueScalebar(75,5).' '.ValueName(75,5).'</label>'.$strSep.'
<input type="radio" name="title" id="i99" value="99"'.(QTisbetween($i,80) ? QCHE : '').'/><label for="i99">'.ValueScalebar(99,5).' '.ValueName(99,5).'</label>'.$strSep.'
<input type="radio" name="title" id="inull" value="null"'.($i<0 ? QCHE : '').'/><label for="inull">'.L('Unknown').'</label><br />
';
break;
case '100':
return '
<input type="radio" name="title" id="i00" value="pc"'.($i>=0 ? QCHE : '').'/><input type="number" name="titlevalue" id="titlevalue" value="'.($i<0 ? '50' : $i).'" size="3" min="0" max="100" onfocus="document.getElementById(\'i00\').checked=true;" style="max-width:50px"/><label for="i00">%</label>'.$strSep.'
<input type="radio" name="title" id="inull" value="null"'.($i<0 ? QCHE : '').'/><label for="inull">'.L('Unknown').'</label>'.$strSep.'
';
break;
case '2':
return '
<input type="radio" name="title" id="i00" value="0"'.(QTisbetween($i,0,49.99) ? QCHE : '').'/><label for="i00">'.ValueScalebar(1,2).' '.ValueName(1,2).'</label>'.$strSep.'
<input type="radio" name="title" id="i99" value="99"'.(QTisbetween($i,50) ? QCHE : '').'/><label for="i99">'.ValueScalebar(99,2).' '.ValueName(99,2).'</label>'.$strSep.'
<input type="radio" name="title" id="inull" value="null"'.($i<0 ? QCHE : '').'/><label for="inull">'.L('Unknown').'</label>'.$strSep.'
';
break;
}

}

// --------

function HtmlTabs($arrTabs=array(0=>'Empty'),$strUrl='',$keyCurrent=0,$intMax=6,$strWarning='Data not yet saved. Quit without saving?')
{

// tabx means the last tab (can be special due to popup)
// if defined, the class/style tab_on replaces the class/style tab (but you can cumulate the classes in the definition)
// if defined, the class/style tabx_on replaces the class/style tabx (but you can cumulate the styles in the definition)
// When strCurrent is defined, this tab will not be clickable
// $arrTabs can be an array of: strings, arrays, cTab

// check

if ( !is_array($arrTabs) ) die('HtmlTabs: Argument #1 must be an array');
if ( !empty($strUrl) ) { if ( !strstr($strUrl,'?') ) $strUrl .= '?'; }

// check current (if not found or not set, uses the first as current)

if ( !isset($arrTabs[$keyCurrent]) ) { $arr=array_keys($arrTabs); $keyCurrent=$arr[0]; }

// display

$strOuts='';
$strOut='';
$intCol=0;

foreach($arrTabs as $key=>$oTab)
{
  ++$intCol;
  $strTab = '';
  $strTabDesc = '';

    if ( is_string($oTab) )
    {
      $strTab = $oTab;
    }
    elseif ( is_array($oTab) )
    {
      if ( isset($oTab['tabdesc']) )
      {
        if ( !empty($oTab['tabdesc']) ) $strTabDesc = $oTab['tabdesc'];
      }
      if ( isset($oTab['tabname']) )
      {
        if ( !empty($oTab['tabname']) ) { $strTab=$oTab['tabname']; } else { $strTab=cLang::ObjectName('tab',$key); }
      }
      else
      {
        $strTab=cLang::ObjectName('tab',$key);
      }
    }
    elseif ( is_a($oTab,'ctab') )
    {
      $strTabDesc = $oTab->tabdesc;
      $strTab = $oTab->tabname; if ( empty($strTab) ) $strTab = $oTab->tabid;
    }
    else
    {
      die('HtmlTabs: Arg #1 must be an array of strings, arrays or cTab');
    }

    $strOut .= '<li'.( $keyCurrent===$key ? ' class="active"' : '').'>';
    if ( empty($strUrl) || $keyCurrent===$key )
    {
      $strOut .= $strTab;
    }
    else
    {
      $strOut .= '<a href="'.$strUrl.'&amp;tt='.$key.'"'.(empty($strTabDesc) ? '' : ' title="'.$strTabDesc.'"').' onclick="return qtEdited(bEdited,\''.$strWarning.'\');">'.$strTab.'</a>';
    }
    $strOut .= '</li>'.PHP_EOL;

  if ( $intCol>=count($arrTabs) )
  {
    $strOuts = '<ul>'.$strOut.'</ul>'.PHP_EOL.$strOuts;
    break;
  }
  if ( $intCol>=$intMax )
  {
    $strOuts = '<ul>'.$strOut.'</ul>'.PHP_EOL.$strOuts;
    $intCol=0;
  }
}

return '
<!-- tab header begin -->
<div class="tab">
'.$strOuts.'
</div>
<!-- tab header end -->
';

}

// ----------------

function TableRowShow($arrFLD,$arr,$strRowClass='table_o r1',$arrSrc=array())
{
  global $oVIP;
  echo '<tr class="',$strRowClass,'">',PHP_EOL;
  foreach($arrFLD as $strKey=>$oFLD)
  {
    $strFullClass = $oFLD->class_td.($oFLD->class_dynamic ? $oFLD->AddClassDynamic($arrSrc) : ''); // in case of dynamic class
    $strFullStyle = $oFLD->style_td.($oFLD->style_dynamic ? $oFLD->AddStyleDynamic($arrSrc) : ''); // in case of dynamic style
    $strClass = ''; if ( !empty($strFullClass) ) $strClass = ' class="'.$strFullClass.'"';
    $strStyle = ''; if ( !empty($strFullStyle) ) $strStyle = ' style="'.$strFullStyle.'"';
    // show column (empty value is replaced by &nbsp;)
    if ( !isset($arr[$strKey]) ) $arr[$strKey]='&nbsp;';
    if ( $arr[$strKey]==='' ) $arr[$strKey]='&nbsp;';
    echo '<td',$strClass,$strStyle,'>',$arr[$strKey],'</td>'.PHP_EOL;
  }
  echo '</tr>',PHP_EOL;
}

// --------

function FormatCsvRow($arrFLD,$row,$arrSEC=array())
{
  if ( is_a($row,'cTopic') ) $row = get_object_vars($row);
  if ( !is_array($row) ) die('FormatCsvRow: Wrong argument #3');
  if ( !is_array($arrSEC) ) die('FormatItemRow: Wrong argument $arrSEC'); // In case of item data row $arrSEC is required. In case of user data rows $arrSEC can by empty.
  $arrRows = array();
  $strMail='';
  $strWww='';
  $s = isset($row['forum']) ? (int)$row['forum'] : -1; // section index
  if ( !isset($row['type']) ) $row['type']='T';
  if ( !isset($row['status']) ) $row['status']='A';
  if ( isset($arrFLD['numid']) && isset($arrSEC[$s]) ) $row['s.numfield'] = $arrSEC[$s]['numfield'];


  foreach(array_keys($arrFLD) as $strKey)
  {
    $str='';
    switch($strKey)
    {
      case 'id': $str = (int)$row['id']; break;
      case 'numid': $str = cTopic::GetRef( $row['numid'], (empty($row['s.numfield']) ? '' : $row['s.numfield']) ); break;
      case 'status':  $str = cTopic::Statusname($row['type'],$row['status']); break;
      case 'text':    $str = $row['preview']; break;
      case 'section': $str = cLang::ObjectName('sec','s'.$row['section']); break;
      case 'insertdate': $str = QTdatestr($row['insertdate'],'$',''); break;
      case 'posts': $str = (int)$row['posts']; break;
      case 'coord':
        if ( $bMap && isset($row['y']) && isset($row['x']) )
        {
          $y = floatval($row['y']);
          $x = floatval($row['x']);
          if ( !empty($y) && !empty($x) )  $str = str_replace('&#176;','°',QTdd2dms($y).','.QTdd2dms($x));
        }
        break;
      case 'tags':
        $arrTags = ( empty($row['tags']) ? array() : explode(';',$row['tags']) );
        foreach (array_keys($arrTags) as $i) if ( empty($arrTags[$i]) ) unset($arrTags[$i]);
        if ( count($arrTags)>5 )
        {
          $arrTags = array_slice($arrTags,0,5);
          $arrTags[]='...';
        }
        $str = implode(' ',$arrTags);
        break;
      case 'user.id': $str = (int)$row['id']; break;
      case 'user.name': $str = $row['name']; break;
      case 'user.role': $str = $row['role']; break;
      case 'user.contact': $str = (isset($row['mail']) ? $row['mail'].' ' : '').(isset($row['www']) ? $row['www'] : ''); break;
      case 'user.location': $str = $row['location']; break;
      case 'user.name': $str = $row['name']; break;
      case 'user.notes': $str = (int)$row['notes']; break;
      case 'user.firstdate': $str = QTdatestr($row['firstdate'],'Y-m-d',''); break;
      case 'user.lastdate': $str = QTdatestr($row['lastdate'],'Y-m-d','').(empty($row['ip']) ?  '&nbsp;' : ' ('.$row['ip'].')'); break;
      default: if ( isset($row[$strKey]) ) $str = $row[$strKey]; break;
    }
    $arrRows[$strKey] = ToCsv($str);
  }
  return $arrRows;
}

// ------------

function FormatItemRow($arrFLD,$row,$bMap=false,$arrSEC=array())
{
  if ( is_a($row,'cTopic') ) {$row=get_object_vars($row); $row['forum']=$row['parentid'];}
  if ( !is_array($row) ) die('FormatItemRow: Wrong argument $row');
  if ( !is_array($arrSEC) ) die('FormatItemRow: Wrong argument $arrSEC'); // In case of item data row $arrSEC is required. In case of user data rows $arrSEC can by empty.
  if ( !isset($row['id']) ) die('FormatItemRow: Missing id in $row');
  if ( !isset($row['replies']) ) $row['replies']=0;

  // INIT

  $arr = array(); // returned cells
  $s = isset($row['forum']) ? (int)$row['forum'] : -1; // section index
  //!!if ( isset($row['firstpostname'][15]) ) $row['firstpostname'] = QTtrunc($row['firstpostname'],15);
  //!!if ( isset($row['lastpostname'][15]) ) $row['lastpostname'] = QTtrunc($row['lastpostname'],15);

  // PRE-PROCESS if required, this adds section-data or user-data into $row[]

  if ( isset($arrFLD['numid']) && isset($arrSEC[$s]) )
  {
    $row['s.numfield'] = $arrSEC[$s]['numfield'];
  }

  if ( isset($arrFLD['prefix'])  && isset($arrSEC[$s]) )
  {
    $row['s.prefix'] = $arrSEC[$s]['prefix'];
    if ( !isset($row['icon']) ) $row['icon'] = '00';
  }

  if ( isset($arrFLD['replies']) && QTI_MY_REPLY && $row['replies']>0 )
  {
    $str = '&reg;';
    if ( is_string(QTI_MY_REPLY) ) $str = QTI_MY_REPLY;
    global $arrMe;
    if ( !empty($arrMe) && array_key_exists($row['id'],$arrMe) )
    {
    $row['youreply'] = '<span title="'.QTstrh( L('You_reply').(empty($arrMe[$row['id']]) ? '' : ', '.QTdatestr($arrMe[$row['id']],'d M','H:i',true,true)) ).'">'.$str.'</span>';
    }
  }

  if ( isset($arrFLD['sectiontitle']) && isset($arrSEC[$s]['title']) )
  {
    $row['s.title'] = $arrSEC[$s]['title'];
  }

  if ( isset($arrFLD['tags']) || isset($arrFLD['title']) )
  {
    $arrTags=array();
    $arrMoreTags=array();
    if ( !empty($row['tags']) ) $arrTags=explode(';',$row['tags']);
    if ( count($arrTags)>3 ) $arrMoreTags = array_slice($arrTags,3,10);
    $arrTags = array_slice($arrTags,0,3);
  }

  if ( isset($arrFLD['title']) )
  {
    // when searching in posts without title, use this to report empty title
    if ( trim($row['title'])=='' ) $row['title']='('.strtolower(L('Reply')).')';
    if ( empty($row['title']) && $row['title']!='0' ) $row['title']='('.strtolower(L('Reply')).')';
  }

  if ( isset($arrFLD['icon']) )
  {
    // icon (Note: Hottopic is not used in qti)
    switch(strtoupper($row['type']))
    {
    case 'A':
      $strTicon = $_SESSION[QT]['skin_dir'].'/ico_topic_a_'.($row['status']=='Z' ? '1' : '0').'.gif';
      $strTname = L('Ico_item_a');
      $strTalt = 'A';
      break;
    case 'T':
      $arr=sMem::Get('sys_statuses');
      $strTicon = (isset($arr[$row['status']]['icon']) ? $_SESSION[QT]['skin_dir'].'/'.$arr[$row['status']]['icon'] : 'unknown status');
      $strTname = (isset($arr[$row['status']]['statusname']) ? $arr[$row['status']]['statusname'] : 'unknown status');
      $strTalt = 'T';
      break;
    case 'I':
      $strTicon = $_SESSION[QT]['skin_dir'].'/ico_topic_i_'.($row['status']=='Z' ? '1' : '0').'.gif';
      $strTname = L('Ico_item_i'.($row['status']=='Z' ? 'Z' : ''));
      $strTalt = 'I';
      break;
    default:
      $strTicon = $_SESSION[QT]['skin_dir'].'/ico_topic_t_1.gif';
      $strTname = 'Unknwon type';
      $strTalt = 'T';
    }
  }

  if ( isset($arrFLD['mail']) || isset($arrFLD['usercontact']) )
  {
    $str = '';
    if ( !empty($row['mail']) )
    {
    if ( $row['privacy']==2 ) $str = AsEmailImg($row['mail'],'','mail-'.$row['id'],true,QTI_JAVA_MAIL);
    if ( $row['privacy']==1 && sUser::Role()!='V' ) $str = AsEmailImg($row['mail'],'','mail-'.$row['id'],true,QTI_JAVA_MAIL);
    if ( sUser::Id()==$row['id'] || sUser::IsStaff() ) $str = AsEmailImg($row['mail'],'','mail-'.$row['id'],true,QTI_JAVA_MAIL);
    }
    $row['u.mail'] = $str;
  }
  if ( isset($arrFLD['www']) || isset($arrFLD['usercontact']) )
  {
    $row['www'] = empty($row['www']) ? '' : '<a href="'.$row['www'].'" target="_blank"><i class="fa fa-globe fa-lg" title="'.QTstrh($row['www']).'"/></i></a>';
  }
  if ( isset($row['privacy']) )
  {
    $row['u.privacy'] = '';
    if ( sUser::Id()>0 )
    {
    if ( $row['privacy']==0 ) $row['u.privacy'] = '<i class="fa fa-lock'.(sUser::IsStaff() || sUser::Id()==$row['id'] ? ' private' : '').'" title="'.L('Privacy_0').'"></i>';
    if ( $row['privacy']==1 ) $row['u.privacy'] = '<i class="fa fa-lock" title="'.L('Privacy_1').'"></i>';
    if ( $row['privacy']==2 ) $row['u.privacy'] = '<i class="fa fa-unlock" title="'.L('Privacy_2').'"></i>';
    }
  }
  if ( $bMap && isset($row['y']) && isset($row['x']) )
  {
    $row['coord']='';
    $row['latlon']='';
    $y = floatval($row['y']);
    $x = floatval($row['x']);
    if ( !empty($y) && !empty($x) )
    {
      $row['coord'] = '<a class="gmappoint" href="javascript:void(0)"'.($_SESSION[QT]['m_map_hidelist'] ? '' : ' onclick="gmapPan(\''.$y.','.$x.'\');"').' title="'.L('Coord').': '.round($y,8).','.round($x,8).'"><i class="fa fa-map-marker" title="'.L('latlon').' '.QTdd2dms($y).','.QTdd2dms($x).'"></i></a>';
      $row['latlon'] = QTdd2dms($y).'<br/>'.QTdd2dms($x);
    }
  }
  if ( $bMap && empty($row['coord']) ) $row['coord'] = '<i class="fa fa-map-marker disabled" title="No coordinates"></i>';

  // FORMAT

  // ::::::::::
  foreach(array_keys($arrFLD) as $key) {
  // ::::::::::

    switch($key)
    {
    case 'checkbox':
      $arr[$key] = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'"/>';
      break;
    case 'icon':
      $arr[$key] = AsImg($strTicon,$strTalt,$strTname,'ico','',Href('qti_item.php').'?t='.$row['id'],'t'.$row['id'].'-itemicon');
      break;
    case 'numid':
      $arr[$key] = cTopic::GetRef( $row['numid'], (empty($row['s.numfield']) ? '' : $row['s.numfield']) );
      break;
    case 'prefix':
      $arr[$key] = ( $row['icon']=='00' || empty($row['icon']) || empty($row['s.prefix']) ? '&nbsp;' : AsImg($_SESSION[QT]['skin_dir'].'/ico_prefix_'.$row['s.prefix'].'_'.$row['icon'].'.gif','[o]',L('Ico_prefix_'.$row['s.prefix'].'_'.$row['icon']),'prefix') );
      break;
    case 'title':
      if ( empty($row['preview']) && !empty($row['textmsg']) ) $row['preview'] = QTcompact(QTunbbc(substr($row['textmsg'],0,210))); // QTcompact uses QTtrunc(200)
			$arr[$key] = ( $row['type']==='A' && QTI_NEWS_STAMP ? '<span class="news">'.L('News_stamp').'</span>' : '' ).'<a class="topic" href="'.Href('qti_item.php').'?t='.$row['id'].'"'.(!empty($row['preview']) ? ' title="'.QTstrh($row['preview']).'"' : '').'>'.$row['title'].'</a>';
			if ( $row['type']==='I' && $row['replies']>0 )
			{
			  $arrOptions = empty($row['options']) ? array() : QTexplodeIni($row['options']);
			  $optionlevel = isset($arrOptions['Ilevel']) ? $arrOptions['Ilevel'] : '3';
        $arr[$key] .= (isset($row['title'][64]) ? '<br/>': ' &nbsp;').ValueScalebar($row['z'],$optionlevel);
			}
			if ( !empty($arrTags) && !isset($arrFLD['tags']) ) $arr[$key] .= ' <i class="tags fa fa-tag'.(count($arrTags)>1 ? 's' : '').'" title="'.implode(', ',$arrTags).(empty($arrMoreTags) ? '' : '...').'"></i>';
      if ( !empty($row['coord']) ) $arr[$key] .= ' '.$row['coord'];

			break;
    case 'replies':
      $arr[$key] = (empty($row['youreply']) ? '' : $row['youreply'].' ').'<span id="t'.$row['id'].'-replies">'.$row['replies'].'</span>';
      break;
    case 'sectiontitle':
			$arr[$key] = empty($row['s.title']) ? '[section '.$s.']' : $row['s.title'];
			break;
    case 'firstpostname':
      $str = isset($row['firstpostname'][15]) ? QTstrh($row['firstpostname']) : '';
      $arr[$key] = '<a id="t'.$row['id'].'-firstpostname" href="qti_user.php?id='.$row['firstpostuser'].'" title="'.$str.'">'.QTtrunc($row['firstpostname'],15).'</a><br/>'.QTdatestr($row['firstpostdate'],'$','$',true,true,true,'t'.$row['id'].'-firstpostdate').'';
      if ( !empty($row['firstpostid']) ) $arr[$key] .= '<span style="display:none" id="t'.$row['id'].'-firstpostid">'.$row['firstpostid'].'</span>';
      break;
    case 'lastpostdate':
      $str = isset($row['lastpostname'][15]) ? QTstrh($row['lastpostname']) : '';
      $arr[$key] = ( empty($row['lastpostdate']) ? '&nbsp;' : QTdatestr($row['lastpostdate'],'$','$',true,true,true,'t'.$row['id'].'-lastpostdate').' <a id="t'.$row['id'].'-lastpostico" class="lastitem" href="'.Href('qti_item.php').'?t='.$row['id'].'#p'.$row['lastpostid'].'" title="'.L('Goto_message').'">'.QTI_GOTOBUTTON.'</a><br/>'.L('by').' <a id="t'.$row['id'].'-lastpostname" href="qti_user.php?id='.$row['lastpostuser'].'" title="'.$str.'">'.QTtrunc($row['lastpostname'],15).'</a>' );
      if ( !empty($row['lastpostid']) ) $arr[$key] .= '<span style="display:none" id="t'.$row['id'].'-lastpostid">'.$row['lastpostid'].'</span>';
      break;
    case 'status':
      $arrS = sMem::Get('sys_statuses');
      $arr[$key] = '<span title="'.(empty($row['statusdate']) ? '' : QTdatestr($row['statusdate'],'d M','H:i',true,true)).'">'.(isset($arrS[$row['status']]['statusname']) ? $arrS[$row['status']]['statusname'] : $row['status']).'</span>';
      break;
    case 'actor':
      $arr[$key] = '<a id="t'.$row['id'].'-actor" href="qti_user.php?id='.$row['actorid'].'" title="'.L('Ico_user_p').'">'.QTtrunc($row['actorname'],15).'</a>';
      break;
    case 'tags':
    	$strTags = '';
    	foreach($arrTags as $str) if ( !empty($str) ) $strTags .= '<span class="tag" title="">'.$str.'</span>';
    	if ( !empty($arrMoreTags) ) $strTags .= '<abbr title="'.implode(', ',$arrMoreTags).'">...</abbr>';
    	$arr[$key] = (empty($strTags) ? '&nbsp;' : $strTags);
    	break;
    case 'usercontact':
/*
      $arr[$key] = empty($row['u.mail']) ? '<i class="fa fa-envelope fa-lg disabled" title="('.L('unknown').')"></i> ' : $row['u.mail'].' ';
      $arr[$key] .= empty($row['www']) ? '<i class=" fa fa-globe fa-lg disabled" title="(no web site)"/></i> ' : $row['www'].' ';
      $arr[$key] .= empty($row['u.privacy']) ? '' : $row['u.privacy'];
*/
      $arr[$key] = empty($row['u.mail']) ? '<i class="fa fa-envelope fa-lg disabled" title="('.L('unknown').')"></i> ' : $row['u.mail'].' ';
      $arr[$key] .= empty($row['www']) ? '<i class=" fa fa-globe fa-lg disabled" title="(no web site)"/></i> ' : $row['www'].' ';
      $arr[$key] .= empty($row['u.privacy']) ? '' : $row['u.privacy'];
      break;
    case 'userphoto': $arr[$key] = empty($row['photo']) ? '' : AsImgPopup('usr_'.$row['id'],QTI_DIR_PIC.$row['photo'],'&rect;'); break;
    case 'username':
      $str = isset($row['name'][24]) ? QTstrh($row['name']) : '';
      $arr[$key] = '<a href="qti_user.php?id='.$row['id'].'" title="'.$str.'">'.QTtrunc($row['name'],24).'</a>';
      break;
    case 'usermarker': $arr[$key] = empty($row['coord']) ? '&nbsp;' : $row['coord']; break;
    case 'userrole': $arr[$key] = L('Role_'.$row['role']); break;
    case 'userlocation': $arr[$key] = empty($row['location']) ? '&nbsp;' : QTtrunc($row['location'],24); break;
    case 'usernumpost': $arr[$key] = $row['numpost']; break;
    case 'firstdate': $arr[$key] = empty($row['firstdate']) ? '&nbsp;' : QTdatestr($row['firstdate'],'$','',true,false,true); break;
    case 'modifdate': $arr[$key] = empty($row['modifdate']) ? '&nbsp;' : QTdatestr($row['modifdate'],'$','',true,false,true); break;
    case 'coord': $arr[$key] = empty($row['u.latlon']) ? '' : $row['u.latlon']; break;
    default:
      if ( isset($row[$key]) )
      {
        $arr[$key] = $row[$key];
      }
      else
      {
        $arr[$key] = '';
      }
      break;
    }

  // ::::::::::
  }
  // ::::::::::

  return $arr;
}

// --------

function ValueName($i=0,$strLevel='3')
{
  if ( $i<0 ) return '';
  // strLevel can be empty when ticket param is empty (and '' is returned)
  switch($strLevel)
  {
  case '2':
    if ( QTisbetween($i,50) ) return L('I_r_yes');
    return L('I_r_no');
    break;
  case '3':
    if ( QTisbetween($i,66.67) ) return L('I_r_good');
    if ( QTisbetween($i,33.33,66.66) ) return L('I_r_medium');
    return L('I_r_bad');
    break;
  case '5':
    if ( QTisbetween($i,80) ) return L('I_r_veryhigh');
    if ( QTisbetween($i,60,79.99) ) return L('I_r_high');
    if ( QTisbetween($i,40,59.99) ) return L('I_r_medium');
    if ( QTisbetween($i,20,39.99) ) return L('I_r_low');
    return L('I_r_verylow');
    break;
  case '100':
    return strval(round($i)).'%';
    break;
  }
  return '';
}

// --------

function ValueScalebar($i=0,$strLevel='3',$intWidth=50,$bTitle=true)
{
  if ( $i<0 ) return '';
  // strLevel can be empty when ticket param is empty (and null image is returned)
  $str = '00';
  switch($strLevel)
  {
  case '2':
    if ( QTisbetween($i,50) ) $str='99';
    break;
  case '3':
    if ( QTisbetween($i,66.67) ) $str='99';
    elseif ( QTisbetween($i,33.33,66.66) ) $str='50';
    break;
  case '5':
    if ( QTisbetween($i,80) ) $str='99';
    elseif ( QTisbetween($i,60,79.99) ) $str='70';
    elseif ( QTisbetween($i,40,59.99) ) $str='50';
    elseif ( QTisbetween($i,20,39.99) ) $str='30';
    break;
  case '100':
    if ( QTisbetween($i,96) ) $str='99';
    elseif ( QTisbetween($i,85,96) ) $str='90';
    elseif ( QTisbetween($i,75,85) ) $str='80';
    elseif ( QTisbetween($i,65,75) ) $str='70';
    elseif ( QTisbetween($i,55,65) ) $str='60';
    elseif ( QTisbetween($i,45,55) ) $str='50';
    elseif ( QTisbetween($i,35,45) ) $str='40';
    elseif ( QTisbetween($i,25,35) ) $str='30';
    elseif ( QTisbetween($i,15,25) ) $str='20';
    elseif ( QTisbetween($i,4,15) ) $str='10';
    break;
  }
  return '<img src="admin/scalebar'.$str.'.gif" style="height:15px; width:'.$intWidth.'px; vertical-align:middle"'.($bTitle ? ' title="'.ValueName($i,$strLevel).'"': '').' alt="--" />';
}
