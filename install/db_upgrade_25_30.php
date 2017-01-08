<?php

// QuickTicket 3.0 build:20160703

session_start();

include '../bin/config.php';
include '../bin/class/qt_class_db.php';

$oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd);
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br />Check that server is up and running.<br />Check that the settings in the file <b>bin/config.php</b> are correct for your database.</p>');

$oDB->debug=true;

$oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET setting="3.0" WHERE param="version"');
$oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET param="show_welcome" WHERE param="sys_welcome"');
$oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET param="posts_per_item" WHERE param="posts_per_topic"');
$oDB->Exec('UPDATE '.$qti_prefix.'qtisetting SET param="items_per_page" WHERE param="topics_per_page"');
echo '<p>Database upgraded to 3.0</p>';