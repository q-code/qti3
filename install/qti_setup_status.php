<?php

// QuickTicket 2 build:20160703

switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.$qti_prefix.'qtistatus (
  id char(1),
  name varchar(24),
  icon varchar(24),
  mailto varchar(255),
  color varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.$qti_prefix.'qtistatus (
  id char(1) NOT NULL CONSTRAINT pk_'.$qti_prefix.'qtistatus PRIMARY KEY,
  name varchar(24) NULL,
  icon varchar(24) NULL,
  mailto varchar(255) NULL,
  color varchar(24) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.$qti_prefix.'qtistatus (
  id char(1),
  name varchar(24),
  icon varchar(24),
  mailto varchar(255),
  color varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.firebird':
case 'ibase':
  $strQ='CREATE TABLE '.$qti_prefix.'qtistatus (
  id char(1),
  name varchar(24),
  icon varchar(24),
  mailto varchar(255),
  color varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.$qti_prefix.'qtistatus (
  id text,
  name text,
  icon text,
  mailto text,
  color text,
  PRIMARY KEY (id)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qti_prefix.'qtistatus (
  id char(1) NOT NULL,
  name varchar(24),
  icon varchar(24),
  mailto varchar(255),
  color varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.$qti_prefix.'qtistatus (
  id char(1),
  name varchar2(24),
  icon varchar2(24),
  mailto varchar2(255),
  color varchar2(24),
  CONSTRAINT pk_'.$qti_prefix.'qtistatus PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oci, sqlite, firebird, db2");

}

echo '<span style="color:blue;">';
$b=$oDB->Exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qti_prefix.'qtistatus',$qti_database,$qti_user),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

// add default values

$oDB->Exec('INSERT INTO '.$qti_prefix.'qtistatus (id,name,icon) VALUES ("A","Submitted","ico_status0.gif")');
$oDB->Exec('INSERT INTO '.$qti_prefix.'qtistatus (id,name,icon) VALUES ("B","In process","ico_status2.gif")');
$oDB->Exec('INSERT INTO '.$qti_prefix.'qtistatus (id,name,icon,color) VALUES ("C","Completed","ico_status4.gif","#AFED9A")');
$oDB->Exec('INSERT INTO '.$qti_prefix.'qtistatus (id,name,icon,color) VALUES ("X","Cancelled","ico_status8.gif","#FF8181")');
$oDB->Exec('INSERT INTO '.$qti_prefix.'qtistatus (id,name,icon,color) VALUES ("Z","Closed","ico_topic_t_1.gif","#EEEEEE")');