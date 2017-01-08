<?php

/* QT v3.0 (php5) */

interface IDatabase
{
public static function GetTable();                // Returns the table name (in case of NE, it depends on the class)
public static function GetFields();               // Returns the table name (in case of NE, it depends on the class)
public static function GetSqlValue($strField,$strValue); // Returns quoted (or not quoted, or nulled) value depending on the field
public function UpdateField($strField,$strValue); // Updates one field value (using GetSqlValue)
public function Insert();                         // Insert object value (using GetSqlValue) into the database
}

/**
* IMultifield and allows managing a group of named values (array key=>value) stored as one string (semi-column separated)
* i.e. "last_visit=12/12/2010;last_ip=127.0.0.1;last_message=155"
*/
interface IMultifield
{
public function MRead($prop,$bAssign=true,$prefix=''); // Returns a array of keys=>values (from one string property)
public function MChange($prop,$key,$value=''); // Change or add one key=>value (in one string)
public function MGet($prop,$key,$na=''); // Get a key value (or $na if not existing)
}

abstract class aQTcontainer
{
public $uid = -1;   // [int] unique id
public $pid = -1;   // [int] parent unique id (-1=none)
public $class = ''; // i.e. section: 0=visible, 1=hidden, 2=hidden by user
public $type = '';
public $status =''; // 1=closed
public $items = 0;  // number of sub items
public $stats = ''; // list of other stats, can be accessed with IMulitfield interface
public $error = ''; // can be use to report errors/warnings
}

interface IClassStatus
{
  public static function Classname($key);  // Returns class name (from  a [object] or [key])
  public static function Classnames(); // Returns an array of classes, key=>classname
  public static function IsClass($key);
  public static function Statusname($key);  // Returns status name (from  a [object] or [key])
  public static function Statusnames(); // Returns an array of status, key=>classname
  public static function IsStatus($key);
}
