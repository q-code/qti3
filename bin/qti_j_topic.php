<?php

// QuickTicket 3.0 build:20160703

if ( empty($_GET['term']) || substr($_GET['term'],0,1)!=='t' ) { echo 'configuration error'; return; }

include 'config.php';
include 'qt_lib_sys.php';
include 'qt_lib_txt.php';
include 'class/qt_class_db.php';
include 'qti_fn_base.php';

// Protection against injection (accept only 3 'lang')
$id = (int)substr($_GET['term'],1);
$iso = strip_tags($_GET['iso']);
$lang = strip_tags($_GET['lang']);
include '../'.$lang.'qti_main.php';

$oDBAJAX = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDBAJAX->error) ) exit;

// Query

$oDBAJAX->Query('SELECT t.*,p.icon,p.title,p.icon as smile,p.textmsg FROM ('.$qti_prefix.'qtitopic t INNER JOIN '.$qti_prefix.'qtipost p ON t.firstpostid = p.id) WHERE t.id='.$id);
if ( $row=$oDBAJAX->GetRow() )
{
  $row['title'] = QTconv(stripslashes($row['title']),'-4');
  $row['textmsg'] = QTconv(stripslashes($row['textmsg']),'-4');

  $oDBAJAX->Query('SELECT s.numfield,s.title,l.objname FROM '.$qti_prefix.'qtiforum s LEFT JOIN '.$qti_prefix.'qtilang l ON (s.id=l.objid AND l.objtype="sec" AND l.objlang="'.$iso.'") WHERE s.id='.$row['forum']);
  $row2 = $oDBAJAX->GetRow();

  // Output the response
  echo '<p class="preview_section">',$L['Section'],': ',(empty($row2['objname']) ? $row2['title'] : $row2['objname']),'</p>';
  echo '<div class="preview"><p class="preview_title"><span id="preview-itemicon" style="margin:0 5px 0 0"></span>';
  if ( $row2['numfield']!='N' )
  {
    printf($row2['numfield'],$row['numid']);
    echo '<br />';
  }
  echo htmlentities($row['title'],ENT_NOQUOTES),'</p>';

  echo '<p class="preview_message">',htmlentities(QTcompact(QTunbbc($row['textmsg']),250,' '),ENT_NOQUOTES),'</p>';
  echo '<p class="preview_user">',$row['firstpostname'],'</p></div>';
  echo '<p class="preview_date">',L('Created'),': ',QTdatestr($row['firstpostdate'],'M d','',true),'</p>';
  if ( !empty($row['wisheddate']) ) echo '<p class="preview_date">',L('Wisheddate').': '.QTdatestr($row['wisheddate'],'M d','',true),'</p>';
  if ( $row['actorid']>=0 ) echo '<p class="preview_date">',$L['Actor'],': ',$row['actorname'],'</p>';
}
else
{
  echo 'Missing post. Unable to show topic details.';
}