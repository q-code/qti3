<?php

/*****
 * Name    : cTable
 * Content : cTable is a package of 5 classes (cTableEntity, cTableRow, cTableHead, cTableData and cTable)
 * version : 1.6
 * Date    : 24/05/2013  
 * Author  : q-code.org
 * Abstract: These classes allow creating table and populating cells and cells headers in an easy way.
 *           It also supports header hyperlinks allowing to build a change order mechanism. 
 *           It makes possible to have html attributes "dynamic" (i.e. depending on dataset values)
 ******/

/******
 * cTableEntity is a base class included in all other classes. 
 * It provides basic methods to design the html entities (only table, tr, th and td) and their attributes
 * 
 * $entity property stores the entity name: it is 'table', 'tr', 'th' or 'td'.
 * $attr property stores the html attributes in a stack: the array $attr where the array-key is the attribute name and the array-value is the attribute value.
 * Add() method allows adding (or chaging) one attribute and his value.
 * Append() method appends a value to one attribute already in the stack. When using Append() be sure that you include the appropriate separator: [space] before a classname, [;] after a style statement
 * GetAttr() method returns all the attributes (and their values) as one formatted string. The attribute name in lowercase, and the attribute value quoted. Note that empty value (like id="") are skipped.
 * Start() method returns the entity name and the attributes.
 * End() method just returns the end tag entity
 ******/
 
class cTableEntity
{
  public $entity;   // [string] Html entity
  public $attr;     // [array of string] List of html attributes in the entity. Array-key is the attribute name, array-value is the attribute value (will be quoted by the GetAttr method)
  public $attrtemp; // [array of string] List of html attributes that must be added dynamically (is added for one specific row) instead of being added in for each row

  public function __construct($entity='',$id='',$class='')
  {
    $this->entity = strtolower($entity); if ( !in_array($this->entity,array('table','tr','td','th')) ) die('cTableEntity: unsupported entity '.$this->entity);
    $this->attr = array();
    $this->attrtemp = array();
    if ( !empty($id) )    $this->Add('id',$id);
    if ( !empty($class) ) $this->Add('class',$class);
  }
  
  public function Add($key='',$value='')
  {
    if ( !empty($key) ) $this->attr[$key] = $value;
  }
  
  public function Append($key='',$value='')
  {
    if ( !isset($this->attr[$key]) ) return $this->Add($key,$value); // create attribute if not yet existing
    $this->attr[$key] .= $value;
  }
  
  private function GetAttr()
  {
    // Return the attributes (quoted)
    $str = '';
    // process attributes
    foreach($this->attr as $key=>$value)
    {
      if ( isset($this->attrtemp[$key]) ) { $value .= ($value==='' ? '' : ' ').$this->attrtemp[$key]; unset($this->attrtemp[$key]); } // if attribute already exists, the attribute value is merged.
      if ( $key!=='' && $value!=='' ) $str .= ' '.strtolower($key).'="'.$value.'"';
    }
    // if exist, add also dynmamic attribute
    foreach($this->attrtemp as $key=>$value)
    {
      if ( $key!=='' && $value!=='' ) $str .= ' '.strtolower($key).'="'.$value.'"';
    }
    // Bugfix 20130524: remove attrtemp after usage (must be cleared to not interfere with next row)
    $this->attrtemp = array();
    
    return $str;
  }

  public function Start() { return '<'.$this->entity.$this->GetAttr().'>'; }
  public function End() { return '</'.$this->entity.'>'; }
}

/******
 * An instance of cTableRow is row object (i.e. a cTableEntity representing a <tr> entity).
 * See cTableEntity for the description of the properties and methods.
 ******/

class cTableRow extends cTableEntity
{
  public function __construct($id='',$class='') { parent::__construct('tr',$id,$class); }
}

/******
 * An instance of cTableHead is a column header object (i.e. a cTableEntity representing a <th> entity).
 ******/

class cTableHead extends cTableEntity
{
  public $content = ''; // [string]  Content of the <th></th> entity
  public $link = '';    // [string]  Pattern to apply to $content (e.g. '<a href="">%s</a>'). If $link=='', the initial $content will be used. 
  public function __construct($content='',$id='',$class='',$link='')
  {
    parent::__construct('th',$id,$class);
    $this->content = $content;
    $this->link = $link;
  }
}

/******
 * An instance of cTableData is a column cell object (i.e. a cTableEntity representing a <td> entity).
******/

class cTableData extends cTableEntity
{
  public $content;       // [string]  Content of the <td></td> entity
  public $dynamicValues; // [array of values]  List of refence values (key) and their corresponding attribute values.

  public function __construct($content='',$id='',$class='')
  {
    parent::__construct('td',$id,$class);
    $this->content = $content;
    $this->dynamicValues = array();
  }

  public function AddDynamicAttr($attr='',$value='')
  {
    if ( $attr!=='' && $value!=='' && isset($this->dynamicValues[$value]) ) $this->attrtemp[$attr]=$this->dynamicValues[$value];
  }
}

/******
 * An instance of cTable is table object (i.e. a cTableEntity representing a <table> entity).
 * It can contains a list of column header objects, a list of column data objects and a default row object
 * It also contains properties to handled active column header 
 * The advanced methods allow creating/changing properties of the columns headers or columns data objects.
 ******/

class cTable extends cTableEntity
{
  public $row;         // [cTableRow]           Store a default <tr> entity. If not defined, the methods GetRow create this cTableRow 
  public $th;          // [array of cTableHead] List of <th> entities. Note, array-key is used to identify the column in the advanced methods. The key can be a name or a column number 
  public $td;          // [array of cTableData] List of <td> entities. Note, array-key is used to identify the column in the advanced methods. The key can be a name or a column number
  public $rowcount;    // [integer]             Number of rows.
  public $rowcountmin; // [integer]             Minimum number of rows to apply the $actvielink content pattern to the active column header
  public $activecol;   // [string|integer]      Current active column (i.e. array key of $th). This column header will use the content pattern $activelink
  public $activelink;  // [string]              Content pattern to apply when to the active column header (if $rowcount>$rowcountmin)

  public function __construct($id='',$class='',$rowcount=0,$rowcountmin=2)
  {
    parent::__construct('table',$id,$class);
    $this->th = array();
    $this->td = array();
    $this->rowcount=$rowcount;
    $this->rowcountmin=$rowcountmin;
    $this->activecol = '';
    $this->activelink = '';
  }

  public function End($bUnsetData=false,$bUnsetHead=false,$bUnsetRow=false)
  {
    // overrides basic method cTableEntity::End (allow reset properties)
    if ( $bUnsetData ) $this->td = array(); // removes the <td> cells
    if ( $bUnsetHead ) $this->th = array(); // removes the <th> cells
    if ( $bUnsetRow ) $this->row = null;    // removes the default <tr> row
    return parent::End();
  }
  
  private function GetRow($entity='td',$id='',$class='')
  {
    if ( !isset($this->$entity) ) die('cTable::GetRow() unknown properties '.$entity);
    // if not yet defined, the method create a new cTableRow object. Attention, if $row is alreay set, $id and $class are not used!
    if ( !isset($this->row) ) $this->row = new cTableRow($id,$class);
    // process
    $str='';
    foreach($this->$entity as $key=>$col)
    {   
      if ( is_a($col,'cTableHead') )
      {
        // replace link for the active column
        if ( $this->activecol===$key ) {
        if ( $this->rowcount>$this->rowcountmin ) {
        if ( $col->link!=='' ) {
          $col->link=$this->activelink;
        }}}
        // build column
        $str .= $col->Start();
        $str .= ($this->rowcount>$this->rowcountmin && $col->link!=='' ? str_replace('%s',$col->content,$col->link) : $col->content);
        $str .= $col->End();
      }
      if ( is_a($col,'cTableData') )
      {
         $str .= $col->Start().$col->content.$col->End();
      }
    }
    // return the row
    return $this->row->Start().(empty($str) ? '' : $str).$this->row->End();    
  }
  
  public function GetTHrow($id='',$class='') { return $this->GetRow('th',$id,$class);  }
  public function GetTDrow($id='',$class='') { return $this->GetRow('td',$id,$class);  }
  
  public function GetTHnames()
  {
    $arr = array();
    foreach($this->th as $key=>$col) $arr[$key]=$col->content;
    return $arr;
  }

  // public method allowing to create an empty table (with message 'No data...')
  public function GetEmptyTable($content='No data...',$bShowHeaders=false,$id='',$class='')
  {
    // $id and $class are the attributes of the <td> entity. The <tr> entity uses the current default row attributes (or create a <tr> entity without attributes)
    if ( !is_string($content) ) die('cTable::GetEmptyTable() invalid argument $content');
    $str = $this->Start().PHP_EOL;
    if ( $bShowHeaders )
    {
      $i = $this->rowcount; // ensure that rowcount is null to disable the header links (if any)
      $this->rowcount = 0;
      $str .= $this->GetTHrow().PHP_EOL;
      $this->rowcount = $i;
    }
    if ( isset($this->row) ) {
      $str .= $this->row->Start();
    } else { $str .= '<tr>';
    }
    if ( $id!=='' ) $id = ' id="'.$id.'"';
    if ( $class!=='' ) $class = ' class="'.$class.'"';
    return $str.'<td'.$id.$class.' colspan="'.count($this->th).'">'.$content.'</td></tr>'.PHP_EOL.$this->End().PHP_EOL;
  }
  
  // Advanced methods: allows changing all columns at once (entity, content or an attribute)
  
  public function SetTHentity($arr=array(),$bCreateColumn=true,$bNamedColumn=true) { $this->SetProperty('th','[entity]',$arr,$bCreateColumn,$bNamedColumn); }
  public function SetTDentity($arr=array(),$bCreateColumn=true,$bNamedColumn=true) { $this->SetProperty('td','[entity]',$arr,$bCreateColumn,$bNamedColumn); }
  public function SetTHcontent($arr=array(),$bCreateColumn=true,$bNamedColumn=true) { $this->SetProperty('th','[content]',$arr,$bCreateColumn,$bNamedColumn); }
  public function SetTDcontent($arr=array(),$bCreateColumn=true,$bNamedColumn=true) { $this->SetProperty('td','[content]',$arr,$bCreateColumn,$bNamedColumn); }
  public function SetTHattr($attr,$arr=array(),$bCreateColumn=true,$bNamedColumn=true) { $this->SetProperty('th',$attr,$arr,$bCreateColumn,$bNamedColumn); }
  public function SetTDattr($attr,$arr=array(),$bCreateColumn=true,$bNamedColumn=true) { $this->SetProperty('td',$attr,$arr,$bCreateColumn,$bNamedColumn); }
    
  private function SetProperty($property,$attr,$arr,$bCreateColumn=true,$bNamedColumn=true)
  {
    // When $arr is 1 value, it will be inserted in each column
    if ( !is_array($arr) )
    {
      $value=$arr;
      $arr = array();
      switch($property)
      {
        case 'th': foreach(array_keys($this->th) as $key) $arr[$key] = $value; break;
        case 'td': foreach(array_keys($this->td) as $key) $arr[$key] = $value; break;
        default: die('SetProperty: invalid argument #1, must be th or td.');
      }
    }
    // Process
    $i=0;
    foreach($arr as $key=>$value)
    {
      if ( !$bNamedColumn ) $key=$i;
      switch($property)
      {
        case 'th': 
          if ( !isset($this->th[$key]) ) if ( $bCreateColumn ) $this->th[$key] = new cTableHead($value);
          if ( isset($this->th[$key]) )
          {
            switch($attr)
            {
              case '[content]':$this->th[$key]->content=$value; break;
              case '[entity]': $this->th[$key]->entity=$value; break;
              default:         $this->th[$key]->Add($attr,$value); break;
            }
          }
          break;
        case 'td':
          if ( !isset($this->td[$key]) ) if ( $bCreateColumn ) $this->td[$key] = new cTableData($value);
          if ( isset($this->td[$key]) )
          {
            switch($attr)
            {
              case '[content]':$this->td[$key]->content=$value; break;
              case '[entity]': $this->td[$key]->entity=$value; break;
              default:         $this->td[$key]->Add($attr,$value); break;
            }
          }
          break;
        default: die('SetProperty: invalid argument #1, must be th or td.');   
      }
      ++$i;
    }
  }
  
}