<?php

function TagsRead($strLang='en',$strSection='*',$bLower=false,$dir='../upload/')
{
  $arrTags = array();
  $strFile = $dir.'tags_'.$strLang.($strSection==='*' ? '' : '_'.$strSection).'.csv';

  // 'en' language is file missing

  if ( $strLang!='en' ) { if ( !file_exists($strFile) ) $strFile = $dir.'tags_en'.($strSection==='*' ? '' : '_'.$strSection).'.csv'; }
 
  // read the file

  if ( file_exists($strFile) ) {
  if ( $handle = fopen($strFile,'r') ) {

    while (($row = fgetcsv($handle,500,';')) !== FALSE)
    {
      if ( isset($row[0]) ) { $strKey = utf8_decode(trim($row[0])); } else { $strKey=''; }
      if ( isset($row[1]) ) { $strVal = utf8_decode(trim($row[1])); } else { $strVal=''; }
      if ( !empty($strKey) ) $arrTags[$strKey] = ($strVal==='' ? $strKey : trim($strVal) );
    }
    fclose($handle);

  }}
  
  if ( count($arrTags)>0 && $bLower ) $arrTags = array_change_key_case($arrTags,CASE_LOWER);
  
  return $arrTags;
}

// --------

function TagsWrite($strLang='en',$strSection='*',$arrTags=array(),$dir='../upload/')
{
  $strFile = $dir.'tags_'.$strLang.($strSection==='*' ? '' : '_'.$strSection).'.csv';
 
  // write to file

  if ( $handle = fopen($strFile,'w') )
  {
    foreach($arrTags as $strKey=>$strValue)
    {
      fwrite($handle, $strKey.';'.$strValue."\r\n");
    }
    fclose($handle);
    return true;
  }
  else
  {
    return false;
  }
}

// --------

function TagsDesc($arrMyTags,$dir='upload/')
{
  // Find corresponding tag description (for each tag in arrMyTags)

  if ( !is_array($arrMyTags) ) die('TagsDesc: Argument #1 must be an array.');
  
  // read existing tags (in lowercase)
  
  $arrTags = TagsRead(QTiso(),'*',true,$dir);
  for ($i=0;$i<20;++$i)
  {
    $arr = TagsRead(QTiso(),$i,true,$dir);
    if ( count($arr)>0 ) $arrTags = array_merge($arrTags,$arr);
    if ( count($arrTags)>100) break;
  }

  // returns matching descriptions
  
  foreach($arrMyTags as $strKey=>$strDesc)
  {
    if ( isset($arrTags[strtolower($strKey)]) ) {
    if ( !empty($arrTags[strtolower($strKey)]) ) {
      $arrMyTags[$strKey] = $arrTags[strtolower($strKey)];
    }}
  }
  return $arrMyTags;
}
