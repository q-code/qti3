<?php

// QTI build:20160703

if ( empty($_POST['term']) ) exit;
if ( empty($_POST['lang']) ) exit;

$strKey = strtoupper($_POST['term']);
$s = '*';
if ( isset($_POST['s']) ) $s = strip_tags($_POST['s']);
if ( $s==='' || $s==='-1' ) $s='*';
$lang  = $_POST['lang'];

include 'qti_fn_tags.php';

// search in specific (if value provided)

if ( $s!=='*' )
{
  $arrTags = TagsRead($lang,$s);
  if ( count($arrTags)>0 )
  {
    $arrTags = array_change_key_case($arrTags, CASE_UPPER);
    if ( isset($arrTags[$strKey]) )
    {
      echo utf8_encode($arrTags[$strKey]);
      exit;
    }
  }
}

// search in common

  $arrTags = TagsRead($lang,'*');
  if ( count($arrTags)>0 )
  {
    $arrTags = array_change_key_case($arrTags, CASE_UPPER);
    if ( isset($arrTags[$strKey]) )
    {
      echo utf8_encode($arrTags[$strKey]);
      exit;
    }
  }

// search others

for ($i=0;$i<20;$i++)
{
  $arrTags = TagsRead($lang,$i);
  if ( count($arrTags)>0 )
  {
    $arrTags = array_change_key_case($arrTags, CASE_UPPER);
    if ( isset($arrTags[$strKey]) )
    {
      echo utf8_encode($arrTags[$strKey]);
      exit;
    }
  }
}

// No result

if ( isset($_POST['e0']) ) echo utf8_encode($_POST['e0']);