<?php

// QuickTicket 2 build:20160703

switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiforum (
  id int,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid int NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 255,
  moderator int NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),  
  options varchar(255),  
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiforum (
  id int NOT NULL CONSTRAINT pk_'.$qti_prefix.'qtiforum PRIMARY KEY,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid int NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  moderator int NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),  
  options varchar(255),  
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1) NULL,
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiforum (
  id integer,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid integer NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),  
  options varchar(255),  
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1) NULL,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiforum (
  id integer,
  type text NOT NULL default "0",
  status text NOT NULL default "0",
  notify text NOT NULL default "1",
  domainid integer NOT NULL default 0,
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname text NOT NULL default "Administrator",
  stats text,  
  options text,  
  numfield text NOT NULL default " ",
  titlefield text NOT NULL default "0",
  wisheddate text NOT NULL default "0",
  alternate text NOT NULL default "0",
  prefix text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.firebird':
case 'ibase':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiforum (
  id integer,
  type char(1) default "0",
  status char(1) default "0",
  notify char(1) default "1",
  domainid integer default 0,
  title varchar(64) default "untitled",
  titleorder integer default 255,
  moderator integer default 0,
  moderatorname varchar(24) default "Administrator",
  stats varchar(255),  
  options varchar(255),  
  numfield varchar(24) default " ",
  titlefield char(1) default "0",
  wisheddate char(1) default "0",
  alternate char(1) default "0",
  prefix char(1),
  PRIMARY KEY (id)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiforum (
  id integer NOT NULL,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid integer NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),  
  options varchar(255),  
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiforum (
  id number(32),
  type char(1) default "0" NOT NULL,
  status char(1) default "0" NOT NULL,
  notify char(1) default "1" NOT NULL,
  domainid number(32) default 0 NOT NULL,
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 255 NOT NULL,
  moderator number(32) default 0 NOT NULL,
  moderatorname varchar2(24) default "Administrator" NOT NULL,
  stats varchar2(255),  
  options varchar(255),  
  numfield varchar2(24) default " " NOT NULL,
  titlefield char(1) default "0" NOT NULL,
  wisheddate char(1) default "0" NOT NULL,
  alternate char(1) default "0" NOT NULL,
  prefix char(1),
  CONSTRAINT pk_'.$qti_prefix.'qtiforum PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, firebird, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qti_prefix.'qtiforum',$qti_database,$qti_user),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$strQ='INSERT INTO '.$qti_prefix.'qtiforum (
id,type,status,notify,domainid,title,titleorder,moderator,moderatorname,stats,options,numfield,titlefield,wisheddate,alternate,prefix)
VALUES (0,"1","0","0",0,"Admin section",0,0,"Admin","","logo=0","T-%03s","0","0","0","a")';

$oDB->Exec($strQ);