<?php // version 6.0 build:20160703

/*
PUBLIC METHODS:
__construct()  Construstor (uses the Connect() private method)
Disconnect()   Disconnect
Query()        Perform an sql query (select)
Exec()         Perform an sql query (insert,update,delete,create). For non-PDO frameworks Exec() uses Query()
BeginTransac() CommitTransac() RolbackTransac() Manage transaction (only with pdo framework)
Nextid()       Returns the next id [int] from a field
Getrows(999)   Returns the rows (after a select query). By default the limit is 1000 rows.
Getrow()       Returns the next row (after a select query)
StartStats()   Starts the statistical info (timer and queries counter)

PRIVATE METHODS:
IsDebug()      Return true when in debug mode
Connect()      Connect database host, login and select database
Halt()         Build error messages, show errors, and stop (level of error message is defined by $showerror)
AddErrorInfo() Build error messages for non-PDO connection frameworks.

DEVELOPPER TIPS: following properties can be overrided with session variables.
$debug can be set to TRUE through the session variable $_SESSION['QTdebugsql'] (not empty)
StartStats() can be triggered through the session variable $_SESSION['QTstatsql'] (not empty)
$showerror can be set to 2 through the session variable $_SESSION['QTshowerror'] (not empty)
$stoponerror is TRUE (recommended). You can turn FALSE with a session variable $_SESSION['QTstoponerror']='false' (the string 'false' exactly!)
*/

class cDB
{

public $type; // framework used to connect database engine (must be in lowercase). Ex: pdo.mysql
public $error = ''; // Error message (must be set as string)
public $showerror = 1; // Message shown on failure: 0=Message not displayed, 1=Minimum message (recommended for production environment), 2=Debug message includes db error message (recommended for development environment)
public $stoponerror = true; // On error, stop executing page (after displaying error message level 1 or 2)
public $debug = false; // With debug true, queries are shown (and full error message)
public $stats; // timer and query counter, initialized by using StartStats() method

private $host; // server host name
private $db;   // database name
private $user;
private $pwd;
private $con;  // Connection as PDO object (or connection id for legacy driver)
private $qry;  // PDOstatement object (or query id for legacy driver)
private $transac = false; // is transaction started
private $singlequote = true; // use singlequote 
private function IsDebug() { return ($this->debug || isset($_SESSION['QTdebugsql'])); }

public function __construct($type,$host,$db,$user,$pwd,$showerror=1,$stoponerror=true)
{
  $this->type = $type;
  if ( in_array($this->type,array('mysql','pdo.mysql','db2','ibase','pdo.firebird')) ) $this->singlequote=false;
  $this->host = $host;
  $this->db = $db;
  $this->user = $user;
  $this->pwd = $pwd;
  if ( $showerror===0 || $showerror===2 ) $this->showerror=$showerror; // default is 1
  if ( $stoponerror===false ) $this->stoponerror=false; // default is true
  if ( !empty($_SESSION['QTstatsql']) ) $this->StartStats();
  if ( !empty($_SESSION['QTshowerror']) ) $this->showerror=2;
  if ( !empty($_SESSION['QTstoponerror']) && $_SESSION['QTstoponerror']==='false') $this->stoponerror=false;
  return $this->Connect();
}

// This function connects the database (and select database if required)
// Returns true if connection was successful otherwise false

private function Connect()
{
  try
  {
    switch($this->type)
    {
    case 'pdo.mysql': $this->con = new PDO('mysql:host='.$this->host.';dbname='.$this->db, $this->user, $this->pwd); break;
    case 'pdo.sqlsrv': $this->con = new PDO('sqlsrv:Server='.$this->host.';Database='.$this->db, $this->user, $this->pwd); break;
    case 'pdo.pg': $this->con = new PDO('pgsql:host='.$this->host.';dnname='.$this->db, $this->user, $this->pwd); break;
    case 'pdo.sqlite': $this->con = new PDO('sqlite:'.$this->host.$this->db, $this->user, $this->pwd); break; // $this->db can be '' or contains the sqlite file
    case 'pdo.firebird': $this->con = new PDO('firebird:dbname='.$this->host.$this->db, $this->user, $this->pwd); break;
    case 'pdo.oci': $this->con = new PDO('oci:dbname='.$this->host, $this->user, $this->pwd); break;
    case 'mysql4':
    case 'mysql':
      $this->con = mysql_connect($this->host,$this->user,$this->pwd); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      if ( !mysql_select_db($this->db,$this->con) ) throw new Exception('Cannot select database.');
      return true;
      break;
    case 'pg':
      $this->con = pg_connect('host='.$this->host.' dbname='.$this->db.' user='.$this->user.' password='.$this->pwd); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    case 'ibase':
      $this->con = ibase_connect($this->host.$this->db,$this->user,$this->pwd); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    case 'sqlite':
      $this->con = sqlite_open($this->host.$this->db,0666,$e); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    case 'sqlsrv':
      $arr=array('Database'=>$this->db,'UID'=>$this->user,'PWD'=>$this->pwd);
      // use windows authentication if no UID and no PWD
      if ( empty($this->user) && empty($this->pwd) ) $arr=array('Database'=>$this->db);
      $this->con = sqlsrv_connect($this->host,$arr); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    case 'db2':
      $this->con = db2_connect($this->host,$this->user,$this->pwd); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      if ( !db2_select_db($this->db,$this->con) ) throw new Exception('Cannot select database.');
      return true;
      break;
    case 'oci':
      $this->con = oci_connect($this->user,$this->pwd,$this->db); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    default: die('Database object interface ['.$this->type.'] is not supported.');
    }
    $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return true;
  }
  catch(PDOException $e)
  {
    $this->Halt($e,'Unable to connect the database.');
    return false;
  }
  catch(Exception $e)
  {
    $this->Halt($e,'Unable to connect the database.');
    return false;
  }
  return true;
}

// ---------

public function Disconnect()
{
  $this->con = null;
  switch($this->type)
  {
  case 'mysql': mysql_close($this->con); break;
  case 'sqlsrv': sqlsrv_close($this->con); break;
  case 'pg': pg_close($this->con); break;
  case 'ibase': ibase_close($this->con); break;
  case 'sqlite': sqlite_close($this->con); break;
  case 'db2': db2_close($this->con); break;
  case 'oci': oci_close($this->con); break;
  }
  return true;
}

// ---------

public function BeginTransac()
{
  if ( $this->transac ) return; // already in transaction

  switch($this->type)
  {
  case 'pdo.mysql':
  case 'pdo.sqlsrv':
  case 'pdo.pg':
  case 'pdo.oci':
  case 'pdo.sqlite':
    $this->transac = $this->con->beginTransaction(); // Can be false (in case of transaction not supported by server)
    break; 
  }
  if ( $this->IsDebug() ) echo '<p class="debug">SQL: begin transaction ('.($this->transac ? 'success' : 'failed').')</p>';
}

public function CommitTransac()
{
  if ( !$this->transac ) return; // already committed

  $b = false; // commit successful
  switch($this->type)
  {
  case 'pdo.mysql':
  case 'pdo.sqlsrv':
  case 'pdo.pg':
  case 'pdo.sqlite':
  case 'pdo.oci': $b = $this->con->commit(); break;
  }
  $this->transac=false;
  if ( $this->IsDebug() ) echo '<p class="debug">SQL: transaction committed ('.($b ? 'success' : 'failed').')</p>';
}

public function RollbackTransac()
{
  if ( !$this->transac ) return; // already committed

  $b = false; // rollback successful
  switch($this->type)
  {
  case 'pdo.mysql':
  case 'pdo.sqlsrv':
  case 'pdo.pg':
  case 'pdo.sqlite':
  case 'pdo.oci': $b = $this->con->rollBack(); break;
  }
  $this->transac=false;
  if ( $this->IsDebug() ) echo '<p class="debug">SQL: transaction rollbacked ('.($b ? 'success' : 'failed').')</p>';
}

// ---------

private function SqlPrepare($sql,$args=false)
{
  // Emulate prepare function for non pdo driver (support only :named placeholder !)
  // Values must be string or numeric in an array with :named key (it is not possible to include function with value nor concatenating several values with comma)
  // String values are quoted and slashed in SqlQuote function.
  // Function can be skipped by using $args=false (source $sql is returned)
  if ( $args===false || substr($this->type,0,4)==='pdo.' ) return $sql;
  if ( !is_array($args) || empty($args) ) { $this->error='SqlPrepare: type mismatch in arguments (queried :named key array)'; return false; }
  foreach($args as $key=>$value)
  {
    if ( !is_string($key) || substr($key,0,1)!==':' ) { $this->error='SqlPrepare: type mismatch in arguments (queried :named key array)'; return false; }
    $sql = str_replace($key,$this->SqlQuote($value),$sql);
  }
  if ( $this->IsDebug() ) printf('<p class="debug">SqlPrepare: %s</p>',$sql);
  return $sql;
}
private function SqlQuote($value)
{
  if ( !is_string($value) ) return $value;
  // string values are quoted and slashed
  $q = $this->singlequote ? "'" : '"';
  return $q.addslashes($value).$q;
}

// ---------

public function Query($sql,$args=false)
{  
  if ( $this->singlequote ) $sql = str_replace('"',"'",$sql);
  
  // ::::::::::::
  // Direct query (no preparation args)  
  // ::::::::::::
  if ( empty($args) || !is_array($args) )
  {
    if ( $this->IsDebug() ) printf('<p class="debug">Query direct: %s</p>',$sql);
    try
    {
      switch($this->type)
      {
      case 'pdo.mysql':
      case 'pdo.sqlsrv':
      case 'pdo.pg':
      case 'pdo.firebird':
      case 'pdo.sqlite':
      case 'pdo.oci':    $this->qry = $this->con->query($sql); break; // warning this->qry is now a PDOstatement object
      case 'mysql':      $this->qry = mysql_query($sql,$this->con); break;
      case 'sqlsrv':     $this->qry = sqlsrv_query($this->con,$sql); break;
      case 'pg':         $this->qry = pg_query($this->con,$sql); break;
      case 'ibase':      $this->qry = ibase_query($this->con,$sql); break;
      case 'sqlite':     $this->qry = sqlite_query($this->con,$sql); break;
      case 'db2':        $this->qry = db2_query($this->con,$sql); break;
      case 'oci':        $this->qry = oci_parse($this->con,$sql); oci_execute($this->qry); break;
      default:           die('db_type ['.$this->type.'] not supported.');
      }
    }
    catch(PDOException $e)
    {
      $this->Halt($e,'Unable to perform query.');
      return false;
    }
    catch(Exception $e)
    {
      $this->Halt($e,'Unable to perform query.');
      return false;
    }
    if ( !$this->qry )
    {
      $this->AddErrorInfo();
      $this->Halt(null,'Unable to perform query.');
      return false;
    }
    if ( isset($this->stats) ) { ++$this->stats['num']; $this->stats['end']=(float)vsprintf('%d.%06d', gettimeofday()); }
    return true; // success
  }

  // ::::::::::::
  // Using preparation. PDO uses prepare() and execute(), non-pdo emulate a preparation with SqlPrepare
  // ::::::::::::
  if ( $this->IsDebug() ) echo '<p class="debug">Query prepared: ',$sql,' [',implode('|',array_keys($args)),'] [',implode('|',$args),']</p>';
  try
  {
    switch($this->type)
    {
    case 'pdo.mysql':
    case 'pdo.sqlsrv':
    case 'pdo.pg':
    case 'pdo.firebird':
    case 'pdo.sqlite':
    case 'pdo.oci':
      $this->qry = $this->con->prepare($sql); // if ($this->qry ===false) throw new Exception('Preparation failed');
      $b = $this->qry->execute($args); //returns false if failed
      break; // warning this->qry is now a PDOstatement object
    case 'mysql':  $sql = $this->SqlPrepare($sql,$args); if ($sql===false) throw new Exception($this->error); $this->qry = mysql_query($sql,$this->con); break;
    case 'sqlsrv': $sql = $this->SqlPrepare($sql,$args); if ($sql===false) throw new Exception($this->error); $this->qry = sqlsrv_query($this->con,$sql); break;
    case 'pg':     $sql = $this->SqlPrepare($sql,$args); if ($sql===false) throw new Exception($this->error); $this->qry = pg_query($this->con,$sql); break;
    case 'ibase':  $sql = $this->SqlPrepare($sql,$args); if ($sql===false) throw new Exception($this->error); $this->qry = ibase_query($this->con,$sql); break;
    case 'sqlite': $sql = $this->SqlPrepare($sql,$args); if ($sql===false) throw new Exception($this->error); $this->qry = sqlite_query($this->con,$sql); break;
    case 'db2':    $sql = $this->SqlPrepare($sql,$args); if ($sql===false) throw new Exception($this->error); $this->qry = db2_query($this->con,$sql); break;
    case 'oci':    $sql = $this->SqlPrepare($sql,$args); if ($sql===false) throw new Exception($this->error); $this->qry = oci_parse($this->con,$sql); oci_execute($this->qry); break;
    default:       die('db_type ['.$this->type.'] not supported.');
    }
  }
  catch(PDOException $e)
  {
    $this->Halt($e,'Unable to perform query.');
    return false;
  }
  catch(Exception $e)
  {
    $this->Halt($e,'Unable to perform query.');
    return false;
  }
  if ( !$this->qry )
  {
    $this->AddErrorInfo();
    $this->Halt(null,'Unable to perform query.');
    return false;
  }
  if ( isset($this->stats) ) { ++$this->stats['num']; $this->stats['end']=(float)vsprintf('%d.%06d', gettimeofday()); }
  return true; // success
}

// ----------------

public function Exec($sql,$args=false)
{  
  if ( isset($this->stats) ) ++$this->stats['num'];
  if ( $this->singlequote ) $sql = str_replace('"',"'",$sql);
  // ::::::::::::
  // Direct query (no preparation args)  
  // ::::::::::::

  if ( empty($args) || !is_array($args) )
  {
    // PDO exec
    if ( substr($this->type,0,4)==='pdo.' )
    {
      try
      {
        if ( $this->IsDebug() ) printf('<p class="debug">Exec direct: %s</p>',$sql);
        return $this->con->exec($sql); // Returns the number of affected rows. With CREATE TABLE, returns false if table exists
      }
      catch(PDOException $e)
      {
        $this->Halt($e,'Unable to perform query.');
        return false;
      }
      catch (Exception $e)
      {
        $this->Halt($e,'Unable to perform query.');
        return false;
      }
    }
    // Non PDO uses defaut query method
    return $this->Query($sql);
  }
  
  // ::::::::::::
  // Using preparation
  // ::::::::::::
  
  // PDO execute
  if ( substr($this->type,0,4)==='pdo.' )
  {
    try
    {
      if ( $this->IsDebug() ) echo '<p class="debug">Exec prepared: ',$sql,' [',implode('|',array_keys($args)),'] [',implode('|',$args),']</p>';
      $this->qry = $this->con->prepare($sql); // if ($this->qry ===false) throw new Exception('Preparation failed');
      return $this->qry->execute($args); // CAUTION: returns true or false (no number of affect rows!)
    }
    catch(PDOException $e)
    {
      $this->Halt($e,'Unable to perform query.');
      return false;
    }
    catch (Exception $e)
    {
      $this->Halt($e,'Unable to perform query.');
      return false;
    }
  }
  // Non PDO uses defaut query method. CAUTION: returns the number of affected rows. With CREATE TABLE, returns false if table exists
  return $this->Query($sql,$args);
}

// --------

public function Nextid($table='',$field='id',$where='')
{
  if ( !is_string($table) || empty($table) ) die('cDB->Nextid: argument #1 must be a string');
  if ( !is_string($field) || empty($field) ) die('cDB->Nextid: argument #2 must be a string');
  if ( !is_string($where) ) die('cDB->Nextid: argument #3 must be a string');
  $this->Query('SELECT max('.$field.')+1 as newnum FROM '.$table.' '.$where);
  $row = $this->Getrow();
  $i = $row['newnum'];
  if ( empty($i) ) $i=1;
  return (int)$i;
}

// --------

public function Getrows($max=999)
{
  $rows=array();
  $i=0;
  while($row=$this->Getrow()) { $rows[]=$row; ++$i; if ( $i===$max ) break; }
  return $rows;
}

public function Getrow()
{
  $row = false;
  switch($this->type)
  {
	case 'pdo.mysql':
	case 'pdo.sqlsrv':
	case 'pdo.pg':
	case 'pdo.firebird':
	case 'pdo.sqlite':
	case 'pdo.oci': $row = $this->qry->fetch(PDO::FETCH_ASSOC); break;
  case 'mysql': $row = mysql_fetch_assoc($this->qry); break; // php 5.0.3
  case 'sqlsrv':$row = sqlsrv_fetch_array($this->qry,SQLSRV_FETCH_ASSOC); break;
  case 'pg': $row = pg_fetch_assoc($this->qry); break;// php 4.3.0
  case 'ibase': $row = ibase_fetch_assoc($this->qry); break;// php 4.3.0
  case 'sqlite':
    $row = sqlite_fetch_array($this->qry,SQLITE_ASSOC);// php 5.0
    if ( $row===false ) return false;
    $arr = array();
    foreach($row as $key=>$val)
    {
      if ( substr($key,1,1)==='.') $key = strtolower(substr($key,2));
      $arr[$key]=$val;
    }
    $row = $arr;
    break;
  case 'db2': $row = db2_fetch_assoc($this->qry); break; // php unknown version
  case 'oci':
    $row = oci_fetch_assoc($this->qry); if ( $row===false ) return false;
    $arr = array();
    foreach($row as $key=>$val) $arr[strtolower($key)]=$val;
    $row = $arr;
    break;
  case 'mysql4': $row = mysql_fetch_assoc($this->qry); break; // php 4.0.3
  default: die('db_type ['.$this->type.'] not supported.');
  }
  return $row;
}

// ---------
// Puts error message in $this->error, shows error and stop (following settings)

private function Halt($e,$msg='Undefined error using the database.')
{
  // Prepare $this->error
  if ( is_a($e,'PDOException') )
  {
  $this->error .= 'Error: '.$e->getCode().' '.$e->getMessage();
  }
  elseif ( is_a($e,'Exception') )
  {
  $this->AddErrorInfo();
  }
  // Show error (short or full)
  if ( $this->showerror===1 ) echo '<p>',$msg,'</p>';
  if ( $this->showerror===2 || $this->debug ) echo '<p>',$msg,'</p><p>',$this->error,'</p>';
  // Disconnect and stop
  $this->con = null;
  if ( $this->stoponerror ) exit;
}

// --------
// Puts error message in $this->error (only used with non PDO)

private function AddErrorInfo()
{
  $this->error .= 'Error: ';
  switch($this->type)
  {
  case 'mysql4':
  case 'mysql':  $this->error .= '['.mysql_errno().'] '.mysql_error(); break;
  case 'sqlsrv': $err=end(sqlsrv_errors());  $this->error .= $err['message']; break;
  case 'pg':     $this->error .= pg_last_error(); break;
  case 'ibase':  $this->error .= ibase_errmsg(); break;
  case 'sqlite': $this->error .= '['.sqlite_last_error($this->con).'] '.sqlite_error_string(sqlite_last_error($this->con)); break;
  case 'db2':    $this->error .= db2_conn_errormsg(); break;
  case 'oci':    $e=oci_error(); $this->error .= $e['message']; break;
  }
}

// --------

public function StartStats()
{
  $t = (float)vsprintf('%d.%06d', gettimeofday());
  $this->stats=array( 'num'=>0, 'start'=>$t, 'pagestart'=>$t );
}

}