<?php

// QuickTicket 2.0 build:20160703

// is uploaded ok
if ( empty($qti_error) )
{
  if ( !is_uploaded_file($_FILES['attach']['tmp_name']) ) $qti_error = $L['E_file_size']." (&lt;{$_SESSION[QT]['upload_size']} Kb)";
}

// is size ok
if ( empty($qti_error) )
{
  if ( $_FILES['attach']['size'] > (intval($_SESSION[QT]['upload_size'])*1024+16) )
  {
  $qti_error = $L['E_file_size']." (&lt;{$_SESSION[QT]['upload_size']} Kb)";
  unlink($_FILES['attach']['tmp_name']);
  }
}

// check format
if ( empty($qti_error) )
{
  include 'bin/qti_upload.php';
  // check extension
  if ( isset($arrFileextensions) )
  {
    if ( empty($arrFileextensions) )
    {
      $bUpload=true;
    }
    else
    {
      $bUpload = false;
      $str = strtolower(substr(strrchr($_FILES['attach']['name'],'.'),1));
      if ( in_array($str,$arrFileextensions) )
      {
        $bUpload=true;
      }
      else
      {
        $qti_error = "Format not supported... [.$str]";
        unlink($_FILES['attach']['tmp_name']);
      }
    }
  }
  // check mimetype
  if ( empty($qti_error) )
  {
    if ( isset($arrMimetypes) )
    {
      if ( empty($arrMimetypes) )
      {
        $bUpload=true;
      }
      else
      {
        $bUpload = false;
        $str = strtolower($_FILES['attach']['type']);
        if ( in_array($str,$arrMimetypes) )
        {
          $bUpload=true;
        }
        else
        {
          $qti_error = "Format not supported... {$_FILES['attach']['type']}";
          unlink($_FILES['attach']['tmp_name']);
        }
      }
    }
  }
  // define target name
  if ( empty($qti_error) && $bUpload )
  {
    $strUpload=strtr($_FILES['attach']['name'],'éèêëÉÈÊËáàâäÁÀÂÄÅåíìîïÍÌÎÏóòôöÓÒÔÖõÕúùûüÚÙÛÜ','eeeeeeeeaaaaaaaaaaiiiiiiiioooooooooouuuuuuuu');
    $strUpload=strtolower($strUpload);
    $strUpload=preg_replace('/[^a-z0-9_\-\.]/i', '_', $strUpload);
  }
}
