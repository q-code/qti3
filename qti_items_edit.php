<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2015 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
if ( !sUser::IsStaff() ) die(Error(12));

// INITIALISE

$a = ''; // mandatory action
$s = -1; // section id (or user id)
$t = -1; // topic
$p = -1; // post
$v = ''; // value
$v1 = ''; // value
$v2 = ''; // value
$src = ''; // value
$ids = ''; // list of comma separated id
$ok = ''; // submitted
QThttpvar('a s t p v v1 v2 src ids ok','str int int int str str str str str str');

$oVIP->selfurl = 'qti_items_edit.php';
$oVIP->selfname = 'QuickTicket command';
$oVIP->exitname = L('Exit');
$oVIP->exiturl = empty($_POST['uri']) ? 'qti_index.php' : 'qti_items.php?'.GetURI('cb',$_POST['uri']);

// --------
// EXECUTE COMMAND
// --------

switch($a)
{

// --------------
case 'items_sta':
// --------------

$oVIP->selfname = L('Change').' '.L('status');

// Check mandatory status (if confirmation done)

if ( !empty($ok) )
{
  $b=false; // ok when at least one status is not '-1'
  foreach( cTopic::Types() as $type=>$typename)
  {
  if ( isset($_POST['status-'.$type]) && $_POST['status-'.$type]!=='-1' ) { $b=true; break; }
  }
  if ( !$b ) { $error = '<p class="error">'.L('Status').' '.L('unknown').'...</p>'; $_POST['t1-cb']=$ids; } // restore checkboxes to be able to re-ask confirmation
}

// Ask confirmation (or if previous post contains an $error)

if ( empty($ok) || !empty($error) )
{
  $arrId = GetCheckedIds();
  if ( $oVIP->exiturl!=='qti_index.php' ) $oVIP->exiturl .= '&amp;cb='.implode(',',$arrId);

  // By type
  $oDBSearch = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);
  $arrStr = array();
  foreach( cTopic::Types() as $type=>$typename)
  {
    $arrNames = array();
    $oDBSearch->Query( 'SELECT t.*, p.title FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid=p.id WHERE t.type="'.$type.'" AND t.id IN ('.implode(',',$arrId).')' );
    $i=0;
    while ( $row=$oDBSearch->GetRow() )
    {
      ++$i;
      if ( $i<5 )
      {
      $oTopic = new cTopic($row);
      $arrNames[] = $oTopic->GetIcon($_SESSION[QT]['skin_dir']).' '.cTopic::GetRef($oTopic->numid,$oTopic->parentid).' &middot; '.QTtrunc($oTopic->title,30);
      }
    }
    if ( $i>4 ) $arrNames[]='...';
    if ( count($arrNames)>0 )
    {
    $arrStr[$type] = '<table class="t-data horiz">'.PHP_EOL;
    $arrStr[$type] .= '<tr><th>'.$typename.' ('.$i.')</th><td>'.implode('<br/>',$arrNames).'</td></tr>'.PHP_EOL;
    $arrStr[$type] .= '<tr><th>'.L('Status').'</th><td><select name="status-'.$type.'" size="1"><option value="-1">&nbsp;</option>'.QTasTag(cTopic::Statuses($type)).'</select></td></tr>'.PHP_EOL;
    $arrStr[$type] .= '</table>'.PHP_EOL;
    }
  }

  $oHtml->PageMsg
  (
  NULL,
  '<form method="post" action="'.Href().'">'.$error.'
  '.implode(PHP_EOL,$arrStr).'
  <table class="t-data horiz">
  <tr>
  <th>'.L('Notification').'</th>
  <td><input type="checkbox" id="notify" name="notify" checked/><label for="notify"> '.L('Allow_emails').'</label></td>
  </tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="uri" value="'.(empty($_POST['uri']) ? '' : $_POST['uri']).'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input type="submit" name="ok" value="'.L('Update').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;
  <input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '600px'
  );
  exit;
}

// Process

if ( !isset($ids) || $ids==='' ) die('Nothing selected');
$arrId = explode(',',$ids);
$arrId = array_map('intval', $arrId);
$bNotify = isset($_POST['notify']) ? true : false;

// Change status
$arrS = array(); // affected sections (for updatestat)
foreach($arrId as $t)
{
  $oTopic = new cTopic($t); if ( !isset($_POST['status-'.$oTopic->type]) ) continue;
  $oTopic->SetStatus($_POST['status-'.$oTopic->type],$bNotify,$oTopic->firstpostid,false); // no update sectionstats
  if ( !in_array($oTopic->parentid,$arrS) && ($oTopic->status==='Z' || $_POST['status-'.$oTopic->type]==='Z') ) $arrS[]=$oTopic->parentid; // update section stats only for Z or changing to Z
}

// Update affected sectionstats
foreach($arrS as $s)
{
  $oSEC = new cSection($s);
  $topicsZ = cSection::CountItems($s,'topicsZ');
  $oSEC->ChangeStat('topicsZ',(string)$topicsZ);
}

// End message
$_SESSION['pagedialog'] = 'O|'.L('S_update').'|'.count($arrId);
$oHtml->Redirect($oVIP->exiturl);

break;

// --------------
case 'items_act':
// --------------

$oVIP->selfname = L('Change').' '.L('Actor');

// Check mandatory username (if confirmation already done).
if ( !empty($ok) )
{
$actorid = sUser::GetUserId($_POST['actor']); // return false if wrong name or empty post)
if ( $_POST['actor']==='0' || strtolower(trim($_POST['actor']))==='null' ) $actorid=-1;
if ( $actorid===false ) { $error = '<p class="error">'.L('Actor').' '.L('unknown').'...</p>'; $_POST['t1-cb']=$ids; }  // restore checkboxes to be able to re-ask confirmation
}

// Ask confirmation (or if previous post contains an $error)
if ( empty($ok) || !empty($error) )
{
  $arrId = GetCheckedIds();
  if ( $oVIP->exiturl!=='qti_index.php' ) $oVIP->exiturl .= '&amp;cb='.implode(',',$arrId);

  $oDBSearch = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);
  $oDBSearch->Query( 'SELECT t.*, p.title FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid=p.id WHERE t.id IN ('.implode(',',array_slice($arrId,0,4)).')' );
  while ( $row=$oDBSearch->GetRow() )
  {
    $oTopic = new cTopic($row);
    $arrNames[] = $oTopic->GetIcon($_SESSION[QT]['skin_dir']).' '.cTopic::GetRef($oTopic->numid,$oTopic->parentid).' &middot; '.QTtrunc($oTopic->title,30).' '.(empty($oTopic->actorname) ? '('.L('none').')' : '('.$oTopic->actorname.')');
  }
  if ( count($arrId)>4 ) $arrNames[]='...';

  $oHtml->scripts[] = '<script type="text/javascript">
  function Rolename(key)
  {
    switch(key)
    {
    case "U": return "'.L('Role_U').'";
    case "M": return "'.L('Role_M').'";
    case "A": return "'.L('Role_A').'";
    default: return key;
    }
  }
  var e0 = "'.L('No_result').'";
  </script>';

  $oHtml->scripts_jq[] = '
  $(function() {
    $( "#actor" ).autocomplete({
      minLength: 1,
      source: function(request, response) {
        $.ajax({
          url: "bin/qti_j_name.php",
          dataType: "json",
          data: { term: request.term, r:"M", e0: e0 },
          success: function(data) { response(data); }
        });
      },
      select: function( event, ui ) {
        $( "#actor" ).val( ui.item.rItem );
        return false;
      }
    })
    .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
      return $( "<li></li>" )
        .data( "item.autocomplete", item )
        .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + Rolename(item.rInfo) + ")</span>") + "</a>" )
        .appendTo( ul );
    };
  });
  ';

  $oHtml->PageMsg
  (
  NULL,
  '<form method="post" action="'.Href().'">'.$error.'
  <table class="t-data horiz">
  <tr>
  <th>'.L('Items').'<br/>('.L('actor').')</th>
  <td>'.implode('<br/>',$arrNames).'</td>
  </tr>
  <tr><th>'.L('Actor').'</th><td><input id="actor" type="text" name="actor" value"" size="32" maxlenght="64"/></td></tr>
  <tr><th>'.L('Reply').'</th><td><input type="checkbox" id="reply" name="reply" checked/><label for="reply"> '.L('Insert_forward_reply').'</label></td></tr>
  <tr><th>'.L('Notification').'</th><td><input type="checkbox" id="notify" name="notify" checked/><label for="notify"> '.L('Allow_emails').'</label></td></tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="uri" value="'.(empty($_POST['uri']) ? '' : $_POST['uri']).'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input id="submit" type="submit" name="ok" value="'.L('Update').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '600px'
  );
  exit;
}

// Process

if ( !isset($ids) || $ids==='' || empty($actorid) ) die('Nothing selected'); // actorid can be -1 to unset actor
$bCanNotify = isset($_POST['notify']) ? true : false;
$bInsertForwardMessage = isset($_POST['reply']) ? true : false;
$arrId = explode(',',$ids);
$arrId = array_map('intval', $arrId);
// Update topics actors
foreach ($arrId as $id)
{
  if ( $actorid===-1 )
  {
  $oDB->Exec('UPDATE '.TABTOPIC.' SET actorid=-1, actorname="",modifdate="'.date('Ymd His').'" WHERE id='.$id);
  }
  else
  {
  $oTopic = new cTopic((int)$id);
  $oTopic->SetActor($actorid,$bCanNotify,$bInsertForwardMessage);
  }
}
// End message
$_SESSION['pagedialog'] = 'O|'.$L['S_update'].'|'.count($arrId);
$oHtml->Redirect($oVIP->exiturl);

break;

// --------------
case 'items_tag':
// --------------

$oVIP->selfname = L('Change').' '.L('tags');

// Check mandatory username (if confirmation already done).
if ( !empty($ok) )
{
if ( empty($_POST['addtags']) && empty($_POST['remtags']) ) { $error = '<p class="error">'.L('Missing').': '.L('tags').'</p>'; $_POST['t1-cb']=$ids; }  // restore checkboxes to be able to re-ask confirmation
}

// Ask confirmation (or if previous post contains an $error)
if ( empty($ok) || !empty($error) )
{
  $arrId = GetCheckedIds();
  if ( $oVIP->exiturl!=='qti_index.php' ) $oVIP->exiturl .= '&amp;cb='.implode(',',$arrId);

  $arrTags = array();

  $oDBSearch = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);

  //Get all tags
  $oDBSearch->Query( 'SELECT tags FROM '.TABTOPIC.' WHERE tags<>"" AND id IN ('.implode(',',$arrId).')' );
  $strTags = '';
  while ( $row=$oDBSearch->GetRow() ) { $strTags .= ';'.$row['tags']; }
  $strTags = cTopic::TagsClear($strTags);
  if ( !empty($strTags) ) $arrTags = explode(';',$strTags);

  // Get top 4 topics info
  $oDBSearch->Query( 'SELECT t.*, p.title FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid=p.id WHERE t.id IN ('.implode(',',array_slice($arrId,0,4)).') ORDER BY t.tags DESC' );
  $arrNames = array();
  while ( $row=$oDBSearch->GetRow() )
  {
    $oTopic = new cTopic($row);
    $i = empty($oTopic->tags) ? 0 : count(explode(';',$oTopic->tags));
    $arrNames[] = $oTopic->GetIcon($_SESSION[QT]['skin_dir']).' '.cTopic::GetRef($oTopic->numid,$oTopic->parentid).' &middot; '.QTtrunc($oTopic->title,30).($i===0 ? '' : '&nbsp;<i class="fa fa-tag'.($i>1 ? 's' : '').'" title="'.L('Tag',$i).'"></i>');
    if ( count($arrNames)>=4 ) break;
  }
  if ( count($arrId)>4 ) $arrNames[]='...';

  $oHtml->scripts[] = '<script type="text/javascript">
  function split( val ) { return val.split( "'.QT_HTML_SEPARATOR.'" ); }
  function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }
  var e0 = "'.L('No_result').'";
  var e5 = "'.L('Tag_not_used').'";
  var usedtags = ["'.implode('","',$arrTags).'"];
  </script>';
  $oHtml->scripts_jq[] = '
  $(function() {

    $("#addtags").autocomplete({
     source: function(request, response) {
       $.ajax({
         url: "bin/qti_j_tag.php",
         dataType: "json",
         data: { term: extractLast( request.term ), s:"'.$s.'", lang:"'.QTiso().'", e5:e5 },
         success: function(data) { response(data); }
       });
     },
     search: function() {
       // custom minLength
       var term = extractLast( this.value );
       if ( term.length < 1 ) { return false; }
     },
     focus: function( event, ui ) { return false; },
     select: function( event, ui ) {
       var terms = split( this.value );
       terms.pop(); // remove current input
       if ( ui.item.rItem.length==0 ) return false;
       terms.push( ui.item.rItem ); // add the selected item
       terms.push( "" ); // add placeholder to get the comma-and-space at the end
       this.value = terms.join( "'.QT_HTML_SEPARATOR.'" );
       return false;
     }
    })
    .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
     return $( "<li></li>" )
       .data( "item.autocomplete", item )
       .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
       .appendTo( ul );
    };

    $("#remtags").autocomplete({
      source: usedtags
    });

  });
  ';

  if ( count($arrTags)>0 )
  {
  $str = implode(', ',array_slice($arrTags,0,4));
  if ( count($arrTags)>4 ) $str .=', ...';
  $str .= ' ('.count($arrTags).')';
  }
  else
  {
  $str = L('none');
  }

  $oHtml->PageMsg
  (
  NULL,
  '<form method="post" action="'.Href().'">'.$error.'
  <table class="t-data horiz">
  <tr>
  <th>'.L('Items').'</th>
  <td>'.implode('<br/>',$arrNames).'</td>
  </tr>
  <tr>
  <th>'.L('Tags').'</th>
  <td>'.$str.'</td>
  </tr>
  <tr>
  <th>'.L('Add').'</th>
  <td><input id="addtags" type="text" name="addtags" value"" size="32" maxlenght="255"/></td>
  </tr>
  <tr>
  <th>'.L('Remove').'</th>
  <td><input id="remtags" type="text" name="remtags" value"" size="32" maxlenght="255" title="'.L('Use_star_to_delete_all').'"/></td>
  </tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="uri" value="'.(empty($_POST['uri']) ? '' : $_POST['uri']).'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input type="submit" name="ok" value="'.L('Update').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '600px'
  );
  exit;
}

// Process

if ( !isset($ids) || $ids==='' ) die('Nothing selected');
$arrId = explode(',',$ids);
$arrId = array_map('intval', $arrId);
// Update topics tags
$arrS = array(); // affected sections (to update stats after)
foreach ($arrId as $id)
{
  if ( !empty($_POST['addtags']) )
  {
  $oTopic = new cTopic((int)$id);
  $oTopic->TagsAdd($_POST['addtags']);
  if ( !in_array($oTopic->parentid,$arrS) ) $arrS[]=$oTopic->parentid;
  }
  if ( !empty($_POST['remtags']) )
  {
  $oTopic = new cTopic((int)$id);
  $oTopic->TagsDel($_POST['remtags']);
  if ( !in_array($oTopic->parentid,$arrS) ) $arrS[]=$oTopic->parentid;
  }
}

// update sections stats (if required)
foreach($arrS as $id)
{
  $oSEC = new cSection($id);
  $oSEC->stats = QTimplodeIni(QTarradd(QTexplodeIni($oSEC->stats),'tags',cSection::CountItems($oSEC->uid,'tags')));
  $oSEC->WriteStats();
}

// End message
$_SESSION['pagedialog'] = 'O|'.L('S_update').'|'.count($arrId);
$oHtml->Redirect($oVIP->exiturl);

break;

// --------------
case 'items_mov':
// --------------

$oVIP->selfname = L('Move').' '.L('items');

// ask confirmation
if ( empty($ok) )
{
  $arrId = GetCheckedIds();
  if ( $oVIP->exiturl!=='qti_index.php' ) $oVIP->exiturl .= '&amp;cb='.implode(',',$arrId);

  $oDBSearch = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);
  $oDBSearch->Query( 'SELECT t.*, p.title FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid=p.id WHERE t.id IN ('.implode(',',array_slice($arrId,0,4)).')' );
  while ( $row=$oDBSearch->GetRow() )
  {
    $oTopic = new cTopic($row);
    $arrNames[] = $oTopic->GetIcon($_SESSION[QT]['skin_dir']).' '.cTopic::GetRef($oTopic->numid,$oTopic->parentid).' &middot; '.QTtrunc($oTopic->title,30);
  }
  if ( count($arrId)>4 ) $arrNames[]='...';

  $oHtml->PageMsg
  (
  NULL,
  '<form method="post" action="'.Href().'">
  <table class="t-data horiz">
  <tr>
  <th>'.L('Items').'</th>
  <td>'.implode('<br/>',$arrNames).'</td>
  </tr>
  <tr>
  <th>'.L('Destination').'</th>
  <td><select name="p" size="1">'.Sectionlist().'</select></td>
  </tr>
  <tr class="tr">
  <th>'.L('Ref').'</th>
  <td><select name="v" size="1">
  <option value="1">'.L('Move_keep').'</option>
  <option value="0">'.L('Move_reset').'</option>
  <option value="2">'.L('Move_follow').'</option>
  </select></td>
  </tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="uri" value="'.(empty($_POST['uri']) ? '' : $_POST['uri']).'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input type="submit" name="ok" value="'.L('Move').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '600px'
  );
  exit;
}

// Process

if ( !isset($ids) || $ids==='' ) die('Nothing selected');
if ( $p<0 ) die('Wrong parameters dest');
if ( $v<0 ) die('Wrong parameters ref');
$arrId = explode(',',$ids);
$arrId = array_map('intval', $arrId);

// Move topics (if not yet in this section)
$iMoved = 0;
foreach ($arrId as $id)
{
  $oTopic = new cTopic($id);
  if ( $oTopic->parentid===$p ) continue; // already in the section
  $oSEC = new cSection($oTopic->parentid);
  $oSEC->MoveTopics($p,$v,$id); // move only one ticket
  ++$iMoved;
}

// End message
$_SESSION['pagedialog'] = $iMoved>0 ? 'O|'.L('S_update').'|'.count($arrId) : 'W|'.L('No_result').'. '.L('Items').' '.L('already_in_section');
$oHtml->Redirect($oVIP->exiturl);

break;

// --------------
case 'items_more':
// --------------

$oVIP->selfname = L('Items');

// ask confirmation
if ( empty($ok) )
{
  $arrId = GetCheckedIds();
  if ( $oVIP->exiturl!=='qti_index.php' ) $oVIP->exiturl .= '&amp;cb='.implode(',',$arrId);

  $oDBSearch = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);
  $oDBSearch->Query( 'SELECT t.*, p.title FROM '.TABTOPIC.' t INNER JOIN '.TABPOST.' p ON t.firstpostid=p.id WHERE t.id IN ('.implode(',',array_slice($arrId,0,4)).')' );
  while ( $row=$oDBSearch->GetRow() )
  {
    $oTopic = new cTopic($row);
    $arrNames[] = $oTopic->GetIcon($_SESSION[QT]['skin_dir']).' '.cTopic::GetRef($oTopic->numid,$oTopic->parentid).' &middot; '.QTtrunc($oTopic->title,30);
  }
  if ( count($arrId)>4 ) $arrNames[]='...';

  $oHtml->PageMsg
  (
  NULL,
  '<form method="post" action="'.Href().'">
  <table class="t-data horiz">
  <tr>
  <th>'.L('Items').'</th>
  <td>'.implode('<br/>',$arrNames).'</td>
  </tr>
  <tr>
  <th>'.L('Action').'</th>
  <td><select name="v" size="1">
  <option value="deletetopics">'.L('Delete').' '.L('items').' '.L('and').' '.L('replys').'</option>
  <option value="deletereplies">'.L('Delete').' '.L('replys').'</option>
  <option value="nullxy">'.L('Delete').' '.L('coord').' '.L('latlon').'</option>
  <option value="dropattach">'.L('Delete').' '.L('attachment').'</option>
  <option value="countreplies">'.L('Update').' '.L('replys').' (count)</option>
  <option value="lastmessage">'.L('Update').' '.L('last_message').'</option>
  </select></td>
  </tr>
  </table>
  <p class="submit">
  <input type="hidden" name="a" value="'.$a.'"/>
  <input type="hidden" name="uri" value="'.(empty($_POST['uri']) ? '' : $_POST['uri']).'"/>
  <input type="hidden" name="ids" value="'.implode(',',$arrId).'"/>
  <span id="process" style="display:none">Processing... </span>
  <input type="submit" name="ok" value="'.L('Ok').' ('.count($arrId).')" onclick="this.style.display=\'none\'; document.getElementById(\'process\').style.display=\'inline\'"/>&nbsp;<input type="button" id="cancel" name="cancel" value="'.L('Cancel').'" onclick="window.location=\''.$oVIP->exiturl.'\';"/>
  </p>
  </form>
  <p class="exit"><a id="exiturl" href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a></p>',
  0,
  '600px'
  );
  exit;
}

// Process

if ( !isset($ids) || $ids==='' ) die('Nothing selected');
if ( empty($v) ) die('Wrong parameters v');
$arrId = explode(',',$ids);
$arrId = array_map('intval', $arrId);
$bUpdateSection = false; // update section stats

foreach ($arrId as $id)
{
  switch($v)
  {
  case 'countreplies':
    $oDB->Exec( 'UPDATE '.TABTOPIC.' SET replies=(SELECT COUNT(*) FROM '.TABPOST.' WHERE topic='.$id.' AND type<>"P") WHERE id='.$id );
    break;
  case 'lastmessage':
    $oDB->BeginTransac();
    $oDB->Query( 'SELECT id,issuedate,userid,username FROM '.TABPOST.' WHERE id=(SELECT max(id) FROM '.TABPOST.' WHERE topic='.$id.')' );
    if ( $row = $oDB->Getrow() )
    {
    $oDB->Exec('UPDATE '.TABTOPIC.' SET lastpostid='.$row['id'].',lastpostdate="'.$row['issuedate'].'",lastpostuser='.$row['userid'].',lastpostname="'.QTstrd($row['username'],64).'" WHERE id='.$id);
    }
    $oDB->CommitTransac();
    break;
  case 'deletereplies':
    $oDB->BeginTransac();
    $oDB->Exec('DELETE FROM '.TABPOST.' WHERE topic='.$id.' AND type<>"P"' );
    $oDB->Exec('UPDATE '.TABTOPIC.' SET replies=0,lastpostid=firstpostid,lastpostuser=firstpostuser,lastpostdate=firstpostdate WHERE id='.$id); // update topic stats
    $oDB->CommitTransac();
    $bUpdateSection = true; // update section stats
    break;
  case 'deletetopics':
    $oDB->BeginTransac();
      foreach ($arrId as $id) { cTopic::Drop($id); }
    $oDB->CommitTransac();
    $bUpdateSection = true; // update section stats
    break;
  case 'nullxy':
    foreach ($arrId as $id) { cTopic::SetCoord($id); } // no coord = null,null
    break;
  case 'dropattach': break; // is performed all at once outside the ids-loop
  default: die('Wrong parameters v');
  }
}

if ($v==='dropattach' )
{
  $arr = array(); // register $oDB results to avoid $oDB in-loop re-query
  $oDB->Query( 'SELECT id,attach FROM '.TABPOST.' WHERE attach<>"" AND topic IN ('.$ids.')' );
  while( $row=$oDB->Getrow() ) $arr[(int)$row['id']]=$row['attach'];
  foreach($arr as $id=>$attach) cPost::baseDropattach($id,$attach);
}

if ( $bUpdateSection )
{
  // Update stats of affected section
  foreach (cTopic::GetSections($arrId) as $s)
  {
    switch($v)
    {
    case 'deletereplies':
    case 'deletetopics':
      $str = cSection::baseUpdateStats($s);
      sAppMem::Control('cSection:WriteStats', array('section'=>$s,'stats'=>$str));
      break;
    }
  }
}

// End message
$_SESSION['pagedialog'] = 'O|'.L('S_update').'|'.count($arrId);
$oHtml->Redirect($oVIP->exiturl);

break;

// --------------
default:
// --------------

echo 'Unknown action';
break;

// --------------
}