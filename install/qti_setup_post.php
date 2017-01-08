<?php

switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.$qti_prefix.'qtipost (
  id int,
  forum int NOT NULL default 0,
  topic int NOT NULL default 0,
  icon char(2) NOT NULL default "00",
  title varchar(64),
  type char(1) NOT NULL default "R",
  userid int NOT NULL default 0,
  username varchar(64),
  issuedate varchar(20) NOT NULL default "0",
  modifdate varchar(20) NOT NULL default "0",
  modifuser int,
  modifname varchar(64),
  attach varchar(255),
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.$qti_prefix.'qtipost (
  id int NOT NULL CONSTRAINT pk_'.$qti_prefix.'qtipost PRIMARY KEY,
  forum int NOT NULL default 0,
  topic int NOT NULL default 0,
  icon char(2) NOT NULL default "00",
  title varchar(64) NULL,
  type char(1) NOT NULL default "R",
  userid int NOT NULL default 0,
  username varchar(64) NULL,
  issuedate varchar(20) NOT NULL default "0",
  modifdate varchar(20) NOT NULL default "0",
  modifuser int NULL,
  modifname varchar(64) NULL,
  attach varchar(255) NULL,
  textmsg text
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.$qti_prefix.'qtipost (
  id integer,
  forum integer NOT NULL default 0,
  topic integer NOT NULL default 0,
  icon char(2) NOT NULL default "00",
  title varchar(64) NULL,
  type char(1) NOT NULL default "R",
  userid integer NOT NULL default 0,
  username varchar(64) NULL,
  issuedate varchar(20) NOT NULL default "0",
  modifdate varchar(20) NOT NULL default "0",
  modifuser integer NULL,
  modifname varchar(64) NULL,
  attach varchar(255) NULL,
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.$qti_prefix.'qtipost (
  id integer,
  forum integer NOT NULL default 0,
  topic integer NOT NULL default 0,
  icon text NOT NULL default "00",
  title text,
  type text NOT NULL default "R",
  userid integer NOT NULL default 0,
  username text,
  issuedate text NOT NULL default "0",
  modifdate text NOT NULL default "0",
  modifuser integer,
  modifname text,
  attach text,
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.firebird':
case 'ibase':
  $strQ='CREATE TABLE '.$qti_prefix.'qtipost (
  id integer,
  forum integer default 0,
  topic integer default 0,
  icon char(2) default "00",
  title varchar(64),
  type char(1) default "R",
  userid integer default 0,
  username varchar(64),
  issuedate varchar(20) default "0",
  modifdate varchar(20) default "0",
  modifuser integer,
  modifname varchar(64),
  attach varchar(255),
  textmsg varchar(32700),
  PRIMARY KEY (id)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qti_prefix.'qtipost (
  id integer NOT NULL,
  forum integer NOT NULL default 0,
  topic integer NOT NULL default 0,
  icon char(2) NOT NULL default "00",
  title varchar(64),
  type char(1) NOT NULL default "R",
  userid integer NOT NULL default 0,
  username varchar(64),
  issuedate varchar(20) NOT NULL default "0",
  modifdate varchar(20) NOT NULL default "0",
  modifuser integer,
  modifname varchar(64),
  attach varchar(255),
  textmsg long varchar,
  textmsg2 varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.$qti_prefix.'qtipost (
  id number(32),
  forum number(32) default 0 NOT NULL,
  topic number(32) default 0 NOT NULL,
  icon char(2) default "00" NOT NULL,
  title varchar2(64),
  type char(1) default "R" NOT NULL,
  userid number(32) default 0 NOT NULL,
  username varchar2(64),
  issuedate varchar2(20) default "0" NOT NULL,
  modifdate varchar2(20) default "0" NOT NULL,
  modifuser number(32),
  modifname varchar2(64),
  attach varchar2(255),
  textmsg varchar2(4000),
  CONSTRAINT pk_'.$qti_prefix.'qtipost PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oci, sqlite, firebird, db2");

}

echo '<span style="color:blue;">';
$b=$oDB->Exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qti_prefix.'qtipost',$qti_database,$qti_user),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}