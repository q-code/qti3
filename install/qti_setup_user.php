<?php

// QuickTicket 3.0 build:20160703

switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiuser (
  id int,
  name varchar(64) NOT NULL UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) NOT NULL default "1",
  location varchar(24),
  firstdate varchar(20) NOT NULL default "0",
  lastdate varchar(20) NOT NULL default "0",
  birthday varchar(20),
  numpost int,
  signature varchar(255),
  photo varchar(24),
  children char(1) NOT NULL default "0",
  parentmail varchar(64),
  parentagree char(1),
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiuser (
  id int NOT NULL CONSTRAINT pk_'.$qti_prefix.'qtiuser PRIMARY KEY,
  name varchar(64) NOT NULL CONSTRAINT uk_'.$qti_prefix.'qtiuser UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40) NULL,
  www varchar(64) NULL,
  mail varchar(64) NULL,
  phone varchar(64) NULL,
  privacy char(1) NOT NULL default "1",
  location varchar(24) NULL,
  firstdate varchar(20) NOT NULL default "0",
  lastdate varchar(20) NOT NULL default "0",
  birthday varchar(20) NULL,
  numpost int NULL,
  signature varchar(255) NULL,
  photo varchar(24) NULL,
  children char(1) NOT NULL default "0",
  parentmail varchar(64) NULL,
  parentagree char(1) NULL,
  secret_q varchar(255) NULL,
  secret_a varchar(255) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  ip varchar(24) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiuser (
  id integer,
  name varchar(64) UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) NOT NULL default "1",
  location varchar(24),
  firstdate varchar(20) NOT NULL default "0",
  lastdate varchar(20) NOT NULL default "0",
  birthday varchar(20),
  numpost integer,
  signature varchar(255),
  photo varchar(24),
  children char(1) NOT NULL default "0",
  parentmail varchar(64),
  parentagree char(1),
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.firebird':
case 'ibase':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiuser (
  id integer,
  name varchar(64) UNIQUE,
  closed char(1) default "0",
  role char(1) default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) default "1",
  location varchar(24),
  firstdate varchar(20) default "0",
  lastdate varchar(20) default "0",
  birthday varchar(20),
  numpost integer,
  signature varchar(255),
  photo varchar(24),
  children char(1) default "0",
  parentmail varchar(64),
  parentagree char(1),
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiuser (
  id integer,
  name text UNIQUE,
  closed text NOT NULL default "0",
  role text NOT NULL default "V",
  pwd text,
  www text,
  mail text,
  phone text,
  privacy text NOT NULL default "1",
  location text,
  firstdate text,
  lastdate text,
  birthday text,
  numpost integer,
  signature text,
  photo text,
  children text NOT NULL default "0",
  parentmail text,
  parentagree text,
  secret_q text,
  secret_a text,
  x real,
  y real,
  z real,
  ip text,
  PRIMARY KEY (id)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiuser (
  id integer NOT NULL,
  name varchar(64) NOT NULL UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) NOT NULL default "1",
  location varchar(24),
  firstdate varchar(20) NOT NULL default "0",
  lastdate varchar(20) NOT NULL default "0",
  birthday varchar(20),
  numpost integer,
  signature varchar(255),
  photo varchar(24),
  children char(1) NOT NULL default "0",
  parentmail varchar(64),
  parentagree char(1),
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.$qti_prefix.'qtiuser (
  id number(32),
  name varchar2(64),
  closed char(1) default "0" NOT NULL,
  role char(1) default "V" NOT NULL,
  pwd varchar2(40),
  www varchar2(64),
  mail varchar2(64),
  phone varchar2(64),
  privacy char(1) default "1" NOT NULL,
  location varchar2(24),
  firstdate varchar2(20) default "0" NOT NULL,
  lastdate varchar2(20) default "0" NOT NULL,
  birthday varchar2(20) default "0" NOT NULL,
  numpost number(32),
  signature varchar2(255),
  photo varchar2(24),
  children char(1) default "0" NOT NULL,
  parentmail varchar2(64),
  parentagree char(1),
  secret_q varchar2(255),
  secret_a varchar2(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar2(24),
  CONSTRAINT pk_'.$qti_prefix.'qtiuser PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oracle, sqlite, firebird, db2");

}

echo '<span style="color:blue;">';
$b=$oDB->Exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qti_prefix.'qtiuser',$qti_database,$qti_user),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$oDB->Exec('INSERT INTO '.$qti_prefix.'qtiuser (id,name,photo,closed,role,firstdate,lastdate,numpost,privacy,children,parentagree) VALUES (0,"Visitor","0","0","V","'.Date('Ymd His').'","'.Date('Ymd His').'",0,"0","0","0")' );
$oDB->Exec('INSERT INTO '.$qti_prefix.'qtiuser (id,name,photo,closed,role,pwd,firstdate,lastdate,numpost,privacy,signature,children,parentagree) VALUES (1,"Admin","0","0","A","'.sha1('Admin').'","'.Date('Ymd His').'","'.Date('Ymd His').'",0,"0","[i][b]The board Administrator[/b][/i]","0","0")' );