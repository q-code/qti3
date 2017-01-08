<?php

// QuickTicket 2 build:20160703

switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.$qti_prefix.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.$qti_prefix.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  CONSTRAINT pk_'.$qti_prefix.'qtilang PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.$qti_prefix.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.$qti_prefix.'qtilang (
  objtype text,
  objlang text,
  objid text,
  objname text,
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.firebird':
case 'ibase':
  $strQ='CREATE TABLE '.$qti_prefix.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qti_prefix.'qtilang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.$qti_prefix.'qtilang (
  objtype varchar2(10),
  objlang varchar2(2),
  objid varchar2(24),
  objname varchar2(4000),
  CONSTRAINT pk_'.$qti_prefix.'qtilang PRIMARY KEY (objtype,objlang,objid))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, firebird, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Exec($strQ);
echo '</span>';

if ( !empty($oDB->error) || $b===false )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qti_prefix.'qtilang',$qti_database,$qti_user),'</div>';
  echo '<br /><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qti_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}