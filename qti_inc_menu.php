<?php
$strPageMenu='';
$strDataTitles='';
$strDataCommand = false;

switch($oVIP->selfurl)
{

// ----------
case 'qti_items.php':
// ----------

  if ( !isset($_SESSION[QT]['Items_Edit']) ) $_SESSION[QT]['Items_Edit']='0';
  if ( !isset($_SESSION[QT]['show_closed']) ) $_SESSION[QT]['show_closed']='1';
  if ( empty($_SESSION[QT]['lastcolumn']) || $_SESSION[QT]['lastcolumn']==='-' || $_SESSION[QT]['lastcolumn']==='none' ) $_SESSION[QT]['lastcolumn']='0';
  if ( !isset($intCount) ) $intCount=0;

  // USER PREFERENCE Menu

  $strPageMenu .= PHP_EOL.'<div id="optionblock">'.PHP_EOL;
  $strPageMenu .= '<div id="showoptions" onclick="showoptions();" title="'.L('My_preferences').'"><i class="fa fa-cog fa-3x"></i></div>'.PHP_EOL;
  $strPageMenu .= '<div id="optionsbar">'.PHP_EOL;
  $strPageMenu .= '<form method="post" action="'.$oVIP->selfurl.'?'.GetURI('page').'" id="modaction">'.L('Show').' <select name="Maction" onchange="document.getElementById(\'option-ok\').click();">'.PHP_EOL;
  $strPageMenu .= '<option value="-">&nbsp;</option>';
  $strPageMenu .= '<optgroup label="'.L('Items').'">';
  foreach(array(10,25,50,100) as $i) $strPageMenu .= '<option value="n'.$i.'"'.($_SESSION[QT]['items_per_page']==$i ? QDIS : '').'>'.$i.' / '.L('page').'</option>';
  $strPageMenu .= '</optgroup><optgroup label="'.L('Options').'">';
  if ( $_SESSION[QT]['show_closed']=='0' ) $strPageMenu .= '<option value="show_Z">'.L('Item_closed_show').'</option>';
  if ( $_SESSION[QT]['show_closed']=='1' ) $strPageMenu .= '<option value="hide_Z">'.L('Item_closed_hide').'</option>';
  $strPageMenu .= '<option value="newsontop"'.(empty($strOnTop) ? '' : QDIS).'>'.L('Show_news_on_top').'</option>';
  $strPageMenu .= '</optgroup></select>'.PHP_EOL;
  $strPageMenu .= '<input type="submit" name="Mok" value="'.$L['Ok'].'" id="option-ok"/></form>'.PHP_EOL;

  if ( sUser::IsStaff() )
  {
    if ( !empty($intCount) )
    {
    $arr = array('actor'=>L('Actor'),'views'=>L('Views'),'status'=>L('Status'));
    if ( !empty($_SESSION[QT]['tags']) ) $arr['tags']=L('Tags'); // list of last columns
    $arr['id']='Id';
    $current = isset($_SESSION[QT]['lastcolumn']) ? $_SESSION[QT]['lastcolumn'] : '0';
    $strPageMenu .= '<form method="post" action="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'" id="modaction2">'.PHP_EOL;
    $strPageMenu .= ' '.$L['Add'].' <select id="Maction2" name="Maction2" onchange="document.getElementById(\'option2-ok\').click();">';
    $strPageMenu .= '<option value="" disabled selected>&nbsp;</option>';
    $strPageMenu .= '<optgroup label="'.L('Staff').' '.L('action').'">';
    if ( isset($oSEC) && is_a($oSEC,'cSection') && $oSEC->uid>=0 ) $strPageMenu .= '<option value="nt">'.L('New_item').'...</option>';
    if ( $intCount>0 && $_SESSION[QT]['Items_Edit']=='0' ) $strPageMenu .= '<option value="Edit_start">'.L('Edit_start').'</option>';
    if ( $intCount>0 && $_SESSION[QT]['Items_Edit']=='1' ) $strPageMenu .= '<option value="Edit_stop">'.L('Edit_stop').'</option>';
    $strPageMenu .= '</optgroup>';
    $strPageMenu .= '<optgroup label="'.L('Column').'">';
    foreach($arr as $key=>$val) $strPageMenu .= '<option value="'.$key.'"'.($current===$key ? QDIS : '').'>'.$val.'</option>';
    $strPageMenu .= '<option value="0"'.($current==='0' ? QDIS : '').'>('.L('none').')</option>';
    $strPageMenu .= '</optgroup>';
    $strPageMenu .= '</select>'.PHP_EOL;
    $strPageMenu .= '<input type="submit" name="Mok2" value="'.$L['Ok'].'" id="option2-ok"/></form>'.PHP_EOL;
    }
  }
  $strPageMenu .= '</div>'.PHP_EOL;
  if ( $intCount>0 && sUser::IsStaff() ) 
  {
    if ( $_SESSION[QT]['Items_Edit']=='0' ) $strPageMenu .= '<div id="showeditor" onclick="showedit(true);" title="'.L('Edit_start').'"><i class="fa fa-pencil-square fa-3x"></i></div>'.PHP_EOL;
    if ( $_SESSION[QT]['Items_Edit']=='1' ) $strPageMenu .= '<div id="showeditor" onclick="showedit(false);" title="'.L('Edit_stop').'"><i class="fa fa-pencil-square fa-rotate-90 fa-3x"></i></div>'.PHP_EOL;
  }
  
  $strPageMenu .= '</div>
  <script type="text/javascript">
  var doc = document;
  doc.getElementById("optionsbar").style.display="none";
  doc.getElementById("option-ok").style.display="none";
  doc.getElementById("option2-ok").style.display="none";
  function showoptions()
  {
  var doc = document.getElementById("optionsbar");
  if ( doc ) doc.style.display=(doc.style.display!="inline-block" ? "inline-block" : "none");
  }
  function showedit(i)
  {
    var d = document.getElementById("Maction2");
    if ( i ) { d.value="Edit_start";  } else { d.value="Edit_stop";}
    document.getElementById("option2-ok").click();
  }
  
  </script>
  ';

  if ( sUser::IsStaff() && !empty($_SESSION[QT]['Items_Edit']) )
  {
  $strDataCommand = L('selection').': <a onclick="datasetcontrol_click(\'t1-cb[]\',\'items_sta\'); return false;" href="#">'.L('Status').'</a>';
  $strDataCommand .= ' &middot; <a onclick="datasetcontrol_click(\'t1-cb[]\',\'items_act\'); return false;" href="#">'.L('Actor').'</a>';
  $strDataCommand .= ' &middot; <a onclick="datasetcontrol_click(\'t1-cb[]\',\'items_tag\'); return false;" href="#">'.L('Tags').'</a>';
  $strDataCommand .= ' &middot; <a onclick="datasetcontrol_click(\'t1-cb[]\',\'items_mov\'); return false;" href="#">'.L('Move').'</a>';
  $strDataCommand .= ' &middot; <a onclick="datasetcontrol_click(\'t1-cb[]\',\'items_more\'); return false;" href="#">'.L('Delete').' '.L('and').' '.L('more').'</a>'.PHP_EOL;

  $oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qt_table_cb.js"></script>
  <script type="text/javascript">
  function datasetcontrol_click(checkboxname,action)
  {
    var checkboxes = doc.getElementsByName(checkboxname);
    var n = 0;
    for (var i=0; i<checkboxes.length; ++i) if ( checkboxes[i].checked ) ++n;
    if ( n>0 )
    {
    doc.getElementById("form_items_action").value=action;
    doc.getElementById("form_items").submit();
    return;
    }
    else
    {
    alert(qtHtmldecode("'.L('No_selected_row').'"));
    return false;
    }
  }
  </script>';
  $oHtml->scripts_jq[] = '
  $(function() {

    // CHECKBOX ALL ROWS
    $("input[id=\'t1-cb-all\']").click(function() { qtCheckboxAll("t1","cb-all","t1-cb[]",true); });

    // SHIFT-CLICK CHECKBOX
    var lastChecked1 = null;
    var lastChecked2 = null;
    $("input[name=\'t1-cb[]\']").click(function(event) {

      if(!lastChecked1) lastChecked1 = this;
      if(event.shiftKey)
      {
        var start = $("input[name=\'t1-cb[]\']").index(this);
        var end = $("input[name=\'t1-cb[]\']").index(lastChecked1);
        for(var i=Math.min(start,end);i<=Math.max(start,end);++i)
        {
        $("input[name=\'t1-cb[]\']")[i].checked = lastChecked1.checked;
        qtHighlight("t1-tr-"+$("input[name=\'t1-cb[]\']")[i].value,lastChecked1.checked);
        }
      }
      lastChecked1 = this;
      qtHighlight("t1-tr-"+this.value,this.checked);
    });

  });
  ';
  }

$strDataTitles .= '<p id="data-title">';
switch($q)
{
  case 's': if ( $_SESSION[QT]['section_desc']==='1' || ($_SESSION[QT]['section_desc']==='2' && $oSEC->ReadOption('sd')!=='0') ) if (!empty($oSEC->descr)) $strDataTitles .= $oSEC->descr; break;
  case 'ref': $strDataTitles .= sprintf( L('Search_results_ref'), $intCount, $v ); break;
  case 'kw':
    $strDataTitles .= sprintf( L('Search_results_keyword'), $intCount, strtolower(implode(' '.L('or').' ',$arrVlbl)) );
    if ( count($arrV)==1 )
    {
      if ( strpos($v,' ')!==false ) $strRefineSearch = '<p><a href="'.$oVIP->selfurl.'?q=kw2&amp;v2='.$v2.'&amp;v='.urlencode($v).'">'.$L['Search_by_words'].'</a></p>';
    }
    break;
  case 'kw2':
    $strDataTitles .= sprintf( L('Search_results_keyword'), $intCount, strtolower(implode(' '.L('and').' ',$arrVlbl)) );
    $strRefineSearch = '<p><a href="'.$oVIP->selfurl.'?q=kw&amp;v2='.$v2.'&amp;v='.urlencode(str_replace(',',' ',$v)).'">'.$L['Search_exact_words'].' "'.str_replace(',',' ',$v).'"</a></p>';
    break;
  case 'user': $strDataTitles .= sprintf( L('Search_results_user'), $intCount, implode(' '.L('or').' ',$arrVlbl) ); break;
  case 'actor': $strDataTitles .= sprintf( L('Search_results_actor'), $intCount, implode(' '.L('or').' ',$arrVlbl) ); break;
  case 'last': $strDataTitles .= sprintf( L('Search_results_last'), $intCount ); break;
  case 'news': $strDataTitles .= sprintf( L('Search_results_news'), $intCount ); break;
  case 'adv':
    $strDataTitles .= sprintf( L(empty($arrVlbl) ? 'Search_results' : 'Search_results_tags'), $intCount, strtolower(implode(' '.L('or').' ',$arrVlbl)) ); break;
  default:
    if ( empty($arrVlbl) )
      $strDataTitles .= L('Items',$oSEC->items);
    else
      $strDataTitles .= sprintf( L('Search_results'), $oSEC->items, implode(' '.L('or').' ',$arrVlbl) );
}
$strDataTitles .= '</p>';
if ( $q!=='s' )
{
  $str = '';
  $arrS = sMem::Get('sys_statuses');
  if ( $s>=0 ) $str .= L('Only_in_section').' "'.(isset($arrSEC[$s]) ? $arrSEC[$s]['title'] : 'section '.$s).'".<br/>';
  if ( isset($st) && $st!=='*' ) $str .= L('Status').' "'.(isset($arrS[$st]['statusname']) ? $arrS[$st]['statusname'] : 'unknown').'". ';
  if ( $q=='adv' && $v2!=='*' )
  {
    switch($v2)
    {
    case 'w': $str .= $L['This_week'].'. '; break;
    case 'm': $str .= $L['This_month'].'. '; break;
    case 'y': $str .= $L['This_year'].'. '; break;
    default: $str .= (isset($L['dateMMM'][(int)$v2]) ? $L['dateMMM'][(int)$v2].'. ' : '');
    }
  }
  if ( $q=='kw' && $v2=='1' ) $str .= L('In_title_only');
  if ( $q=='kw2' && $v2=='1' ) $str .= L('In_title_only');
  if ( !empty($str) ) $strDataTitles .= '<p id="data-subtitle">'.$str.'</p>'.PHP_EOL;
}

$strDataTitles .= $strRefineSearch;

break;

// ----------
case 'qti_item.php':
  if ( sUser::IsStaff() ) {
// ----------

// When Moderator change actor+status, actor must be changed first to notify him on the status change...

if ( isset($_POST['Maction']) ) {
if ( !empty($_POST['Mactor']) || !empty($_POST['Maction']) ) {

  $oVIP->exiturl = 'qti_items.php?s='.$s;
  $oVIP->exitname = $L['Section'];

  if ( !empty($_POST['Mactor']) )
  {
    if ( $_POST['Mactor']=='actor*' ) $oHtml->Redirect('qti_change.php?a=topicactor&amp;s='.$s.'&amp;t='.$t.'&amp;v=*&amp;old='.$_POST['Moldactor'],$L['Change'].' '.$L['Status'] );
    $oTopic = new cTopic($t);
    $oTopic->SetActor(intval($_POST['Mactor']));
    // exit (if no action, if action, continue with action
    if ( empty($_POST['Maction']) )
    {
      $oVIP->selfname = L('Change').' '.L('Actor');
      $oHtml->PageMsg( NULL, '<p>'.$L['S_update'].'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a>', 2 );
    }
  }

  if ( substr($_POST['Maction'],0,7)=='status_' ) $oHtml->Redirect('qti_change.php?a=topicstatus&amp;s='.$s.'&amp;t='.$t.'&amp;v='.substr($_POST['Maction'],-1,1),$L['Change'].' '.$L['Status'] );
  if ( substr($_POST['Maction'],0,5)=='type_' ) $oHtml->Redirect('qti_change.php?a=topictype&amp;s='.$s.'&amp;t='.$t.'&amp;v='.substr($_POST['Maction'],-1,1),$L['Change'].' '.$L['Status'] );
  if ($_POST['Maction']=='reply') $oHtml->Redirect('qti_form_edit.php?s='.$s.'&amp;t='.$t.'&amp;a=re',$L['Post_reply'] );
  if ($_POST['Maction']=='move') $oHtml->Redirect('qti_change.php?a=topicmove&amp;s='.$s.'&amp;t='.$t,$L['Move'] );
  if ($_POST['Maction']=='delete') $oHtml->Redirect('qti_change.php?a=topicdelete&amp;s='.$s.'&amp;t='.$t,$L['Delete'] );

}}

$strPageMenu .= '
<!-- Moderator actions -->
<div id="optionblock">
<div id="optionsbar">
<form method="post" action="'.Href().'" id="modaction">
'.L('Item').' <input type="hidden" name="s" value="'.$oTopic->parentid.'" />
<input type="hidden" name="t" value="'.$oTopic->id.'" />
<input type="hidden" name="Mref" value="'.$oTopic->numid.'" />
<select name="Maction" onchange="document.getElementById(\'modaction\').submit();">
<option value="">&nbsp;</option>
<optgroup label="'.L('Action').'">
<option value="reply">'.$L['Post_reply'].'...</option>
<option value="move">'.$L['Move'].'...</option>
<option value="delete">'.$L['Delete'].'...</option>
</optgroup>
<optgroup label="'.L('Change').' '.L('status').'">
'.QTasOption(array_prefix_keys('status_',cTopic::Statuses($oTopic->type)),'',array(),array('status_'.$oTopic->status)).'
</optgroup>
<optgroup label="'.L('Change').' '.L('Type').'">
'.QTasOption(array_prefix_keys('type_',cTopic::Types()),'',array(),array('type_'.$oTopic->type)).'
</optgroup>
</select>&nbsp;'.$L['Change_actor'].'&nbsp;
<select name="Mactor" onchange="document.getElementById(\'modaction\').submit();">
<option value="">&nbsp;</option>
';
$arr = GetUsers('M','','numpost DESC,name ASC',13);
if ( count($arr)>12 ) { $b=array_pop($arr); $b=true; } else { $b=false; }
asort($arr);
foreach($arr as $intId=>$strValue) $strPageMenu .= '<option value="'.$intId.'"'.($oTopic->actorid==$intId ? ' class="bold"' : '').'>'.$strValue.'</option>
';
if ( $b ) $strPageMenu .= '<option value="actor*">'.L('More').'...</option>';
$strPageMenu .= '</select> <input type="submit" name="Msubmit" value="'.$L['Ok'].'" class="small" id="option-ok" />
<script type="text/javascript">document.getElementById("option-ok").style.display="none";</script>
</form>
</div>
</div>
';

}
break;

// ----------
case 'qti_s_search.php':
case 'qti_search.php':
// ----------

echo '<div id="searchcmd">',PHP_EOL;
echo '<a class="button" href="',Href('qti_items.php'),'?q=last">',AsImg($_SESSION[QT]['skin_dir'].'/ico_topic_t_0.gif','T'),$L['Recent_messages'],'</a>',PHP_EOL;
echo '<a class="button" href="',Href('qti_items.php'),'?q=news">',AsImg($_SESSION[QT]['skin_dir'].'/ico_topic_a_0.gif','T'),$L['All_news'],'</a>',PHP_EOL;
if ( sUser::Role()!=='V' ) echo '<a class="button" href="',Href('qti_items.php'),'?q=user&amp;v=',sUser::Id(),'&amp;v2='.urlencode(sUser::Name()),'"><i class="fa fa-user fa-lg"></i>',L('All_my_items'),'</a>',PHP_EOL;
echo '</div>',PHP_EOL;

break;

// ----------
case 'qti_users.php':
case 'qti_adm_users.php':
if ( sUser::IsStaff() ) {
// ----------

// SUBMITTED for add

if ( isset($_POST['add']) )
{
  // check
  if ( empty($error) )
  {
    if ( !QTislogin($_POST['title']) ) $error = L('Username').' '.L('invalid');
  }
  if ( empty($error) )
  {
    $oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE name="'.QTstrd($_POST['title'],64).'"');
    $row = $oDB->Getrow();
    if ($row['countid']!=0) $error = $L['Username'].' '.$L['Already_used'];
  }
  if ( empty($error) )
  {
    if ( !QTispassword($_POST['pass']) ) $error = $L['Password'].' '.L('invalid');
    $strNewpwd = $_POST['pass'];
  }
  if ( empty($error) )
  {
    if ( isset($_POST['role']) ) { $_POST['role']=substr(strtoupper($_POST['role']),0,1); } else { $_POST['role']='U'; }
    if ( !in_array($_POST['role'],array('A','M','U')) ) $_POST['role']='U';
  }
  if ( empty($error) )
  {
    if ( !QTismail($_POST['mail']) ) $error = $L['Email'].' '.L('invalid');
  }
  // save
  if ( empty($error) )
  {
    $newid = sUser::AddUser($_POST['title'],$strNewpwd,$_POST['mail'],$_POST['role']); // return false in case of error
    if ( $newid )
    {
      // Unregister global sys (will be recomputed on next page)
      sMem::Clear('sys_members');
      sMem::Clear('sys_lastmember');

      // send email
      if ( isset($_POST['notify']) )
      {
        include 'bin/class/qt_class_smtp.php';
        $strSubject='Welcome';
        $strMessage='Please find here after your login and password to access the board '.$_SESSION[QT]['site_name'].PHP_EOL.'Login: %s\nPassword: %s';
        $strFile = GetLang().'mail_registred.php';
        if ( file_exists($strFile) ) include $strFile;
        $strMessage = sprintf($strMessage,$_POST['title'],$strNewpwd);
        QTmail($_POST['mail'],QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QT_HTML_CHAR);
      }

      $_SESSION['pagedialog'] = 'O|'.L('Register_completed');
    }
    else
    {
      $error=true;
      $_SESSION['pagedialog'] = 'E|Unable to create the user';
    }

    // exit
    unset($_POST['pass']);
  }
}

$strPageMenu .= '<a onclick="ToggleForms(); return false;" href="#">'.L('Add_user').'...</a>';
$oHtml->scripts[] = '<script type="text/javascript">
function ToggleForms()
{
  div = doc.getElementById("adduser");
  if ( div )
  {
    div.style.display = (div.style.display=="none" ? "block" : "none");
    if ( doc.getElementById("topparticipants") ) doc.getElementById("topparticipants").style.display = (div.style.display=="none" ? "block" : "none");
  }
}
</script>';
$oHtml->scripts_jq[] = '
$(function() {
  $("#title").blur(function() {
    $.post("bin/qti_j_exists.php",
           {f:"name",v:$("#title").val(),e1:"'.sprintf(L('E_char_min'),4).'",e2:"'.L('Already_used').'"},
           function(data) { if ( data.length>0 ) document.getElementById("formerror").innerHTML=data; }
           );
    });
});
';
$strUserform  = '<div id="adduser" style="display:none">
<form method="post" action="'.$oVIP->selfurl.'">
<table class="t-data horiz">
<tr><th>'.$L['Role'].'</th><td><select name="role" size="1">'.(sUser::Role()==='A' ? '<option value="A">'.$L['Role_A'].'</option>' : '').'<option value="M">'.$L['Role_M'].'</option><option value="U"'.QSEL.'>'.$L['Role_U'].'</option></select></td></tr>
<tr><th>'.$L['Username'].'</th><td><input required id="title" name="title" type="text" size="32" maxlength="64" value="'.(isset($_POST['title']) ? $_POST['title'] : '').'" onfocus="document.getElementById(\'formerror\').innerHTML=\'\';" /></td></tr>
<tr><th>'.$L['Password'].'</th><td><input required id="pass" name="pass" type="text" size="32" maxlength="64"  value="'.(isset($_POST['pass']) ? $_POST['pass'] : '').'" /></td></tr>
<tr><th>'.$L['Email'].'</th><td><input requried id="mail" name="mail" type="email" size="32" maxlength="255"  value="'.(isset($_POST['mail']) ? $_POST['mail'] : '').'" /></td></tr>
<tr><th colspan="2"><span id="formerror" class="error">'.(empty($error) ? '' : $error).'</span> <input id="notify" name="notify" type="checkbox" /> <label for="notify">'.$L['Send'].' '.L('email').'</label>&nbsp; <input type="submit" id="add" name="add" value="'.$L['Add'].'" /></th></tr>
</table>
</form>
</div>
';
if ( isset($_POST['title']) ) $oHtml->scripts_end[] = '<script type="text/javascript">ToggleForms();</script>';

$strDataCommand = L('selection').': <a class="datasetcontrol" onclick="datasetcontrol_click(\'t1-cb[]\',\'usersrole\'); return false;" href="#">'.L('role').'</a> &middot; <a class="datasetcontrol" onclick="datasetcontrol_click(\'t1-cb[]\',\'usersdel\'); return false;" href="#">'.L('delete').'</a> &middot; <a class="datasetcontrol" onclick="datasetcontrol_click(\'t1-cb[]\',\'usersban\'); return false;" href="#">'.strtolower(L('Ban')).'</a>';

}
break;

}