<?php

// QuickTicket 2 build:20160703

switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.$qti_prefix.'qtidomain (
  id int,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.$qti_prefix.'qtidomain (
  id int NOT NULL CONSTRAINT pk_'.$qti_prefix.'qtidomain PRIMARY KEY,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.$qti_prefix.'qtidomain (
  id integer,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.$qti_prefix.'qtidomain (
  id integer,
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.firebird':
case 'ibase':
  $strQ='CREATE TABLE '.$qti_prefix.'qtidomain (
  id integer,
  title varchar(64) default "untitled",
  titleorder integer default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qti_prefix.'qtidomain (
  id integer NOT NULL,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.$qti_prefix.'qtidomain (
  id number(32),
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 0 NOT NULL,
  CONSTRAINT pk_'.$qti_prefix.'qtidomain PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, firebird, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qti_prefix.'qtidomain',$qti_database,$qti_user),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$strQ='INSERT INTO '.$qti_prefix.'qtidomain (id,title,titleorder) VALUES (0,"Admin hidden domain",0)';
$result=$oDB->Exec($strQ);