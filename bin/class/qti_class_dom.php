<?php // v3.0 build:20160703

class cDomain extends aQTcontainer
{

function __construct($aDom=null)
{
  if ( isset($aDom) )
  {
    if ( is_int($aDom) )
    {
      if ( $aDom<0 ) die('No domain '.$aDom);
      global $oDB;
      $oDB->Query('SELECT * FROM '.TABDOMAIN.' WHERE id='.$aDom);
      $row = $oDB->Getrow();
      if ( $row===False ) die('No domain '.$aDom);
      $this->MakeFromArray($row);
    }
    elseif ( is_array($aDom) )
    {
      $this->MakeFromArray($aDom);
    }
    else
    {
      die('Invalid constructor parameter #1 for the class cDomain');
    }
  }
}

// --------

private function MakeFromArray($arr)
{
  foreach ($arr as $strKey=>$oValue)
  {
    switch($strKey)
    {
      case 'id': $this->id = (int)$oValue; break;
      case 'title': $this->title = $oValue; break;
    }
  }
}

// --------

public function Rename($str='')
{
  if ( !is_string($str) || empty($str) ) die('cDomain->Rename: Argument #1 must be a string');
  $this->title = substr($str,0,64);

  global $oDB;
  $r = $oDB->Exec( 'UPDATE '.TABDOMAIN.' SET title=:title WHERE id=:id', array(':title'=>$this->title,':id'=>$this->id) );
  if ( !$r ) return false;

  sAppMem::Control( get_class().':'.__FUNCTION__, array('id'=>$this->id) ); //System update
  return true;
}

// --------
// aQTcontainer implementations
// --------

public static function Create($title,$parentid)
{
  // parentid is no used here
  global $oDB, $error;
  
  $oDB->BeginTransac();
    $id = $oDB->Nextid(TABDOMAIN);
    $oDB->Exec( 'INSERT INTO '.TABDOMAIN.' (id,title,titleorder) VALUES (:id,:title,0)', array(':id'=>$id,':title'=>$title) );    
  $oDB->CommitTransac();
  
  sAppMem::Control( get_class().':'.__FUNCTION__, compact('id') ); //System update
  return $id;
}

public static function Drop($id)
{
  if ( !is_int($id) ) die('cDomain->Drop: argument must be integer');
  if ( $id<1 ) die('cDomain->Drop: Cannot delete domain 0');
  global $oDB, $error;
  $oDB->BeginTransac();
    $oDB->Exec( 'UPDATE '.TABSECTION.' SET domainid=0 WHERE domainid='.$id ); // sections return to domain 0
    $oDB->Exec( 'DELETE FROM '.TABDOMAIN.' WHERE id='.$id );
    cLang::Delete('domain','d'.$id);
  $oDB->CommitTransac();
  
  sAppMem::Control( get_class().':'.__FUNCTION__, compact('id') ); //System update
}

public static function MoveItems($id,$destination)
{
  if ( !is_int($id) || !is_int($destination) ) die('cDomain->MoveItems: arguments must be integer');
  if ( $id<0 || $destination<0 ) die('cDomain->MoveItems: source and destination cannot be <0');
  global $oDB;
  $oDB->Exec( 'UPDATE '.TABSECTION.' SET domainid='.$destination.' WHERE domainid='.$id );

  //sAppMem::Control(get_class().':'.__FUNCTION__, compact('id','destination')); // System does not control this method as it is not used in qti3
}

public static function CountItems($id,$status='')
{
  if ( !is_int($id) ) die('cDomain->MoveItems: argument must be integer');
  if ( !is_string($status) ) $status='';

  // Count Sections in domain $id
  if ( $id<0 ) die('cDomain->CountItems: id cannot be <0');
  global $oDB;
  $oDB->Query( 'SELECT count(*) as countid FROM '.TABSECTION.' WHERE domainid='.$id.($status==='' ? '' : ' AND status="'.$status.'"') );
  $row = $oDB->Getrow();
  return (int)$row['countid'];
}

// --------

}