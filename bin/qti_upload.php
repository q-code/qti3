<?php

// The initial setting allows checking the filename extension, (not the mimetype).
// As the mimetype may not be provided by the html client, or may not be standard,
// the initial setting of the application doesn't use the mimetypes.

// Note #1:
// It's nevertheless possible to use mimetype to check uploaded file types.
// To do so, empty the $arrFileextension array and fill in the mimetypes array here after.
// Example : $arrMimetypes = array("text/plain","image/gif");

// Note #2:
// If you insert data in both $arrFileextensions and $arrMimetypes,
// the system will check FIRST the file extensions (and stop uploading if file extension doesn't match).
// Thus, if you want to use mimetypes only, it's recommended to empty the $arrFileextensions array.

// -----------------
// uploaded files: allowed extensions. Must be noted in lowercase!
// -----------------

// Note here the extensions allowed. Use empty array() to allow all extensions

$arrFileextensions = array(
'asp','aspx',
'csv',
'doc','docx',
'gif',
'htm','html',
'inc',
'jpg','jpeg',
'js',
'log',
'pdf',
'php',
'png',
'pps','ppt','pptx',
'rar','tar',
'rtf','txt','text',
'xls','xlsx',
'zip'
);

// -----------------
// uploaded files: allowed mimetypes. Must be noted in lowercase!
// -----------------

// Note here the mimetypes allowed. Use empty array() to allow all mimetypes

$arrMimetypes = array();