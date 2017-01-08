<?php

/* ============
 * qt_lib_pdf.php
 * ------------
 * version: 4.0 build:20160703
 * This is a library of public functions
 * ============ */

require 'bin/fpdf.php';

// ------

function PdfClean($str)
{
  $str = strip_tags($str);
  if ( strstr($str,'&') ) $str = str_replace('&nbsp;',' ',$str);
  if ( strstr($str,'&') ) $str = QTconv($str,'-4');
  return $str;
}

// ------

class cPdfCell
{
  public $s = ' ';
  public $w = 0;
  public $h = 5;
  public $align = 'C';
  public $fill = false;
  public $fillcolor = array(240,240,240);
  public $font = array('Arial','',8);
  public $color = array(0,0,0);
  public $border = 0;
  function cPdfCell($str=' ',$w=0,$h=5,$a='C',$fill=false,$fillcolor=array(240,240,240)) { $this->s=$str; $this->w=$w; $this->h=$h; $this->align=$a; $this->fill=$fill; $this->fillcolor=$fillcolor; }
}

// ------

class PDF extends FPDF
{

function Header()
{
  $this->Image('admin/qti_logo_prt.jpg',9,8,0,0,'JPG');
  $this->Ln(20);
}

function Footer()
{
  $this->SetY(-15);
  $this->SetFont('Arial','I',8);
  $this->Cell(0,10,PdfClean($_SESSION[QT]['site_name']).', '.date('Y-m-d G:i'),0,0);
  $this->Cell(0,10,$this->PageNo().'/{nb}',0,0,'R');
}

function BasicTable($arrRows,$arrFont=array('Arial','',8),$iSpacing=1)
{
  //Data
  $iXrow = $this->GetX(); 
  $iYrow = $this->GetY();
  foreach($arrRows as $arrRow)
  {
    $iXcel = $iXrow;
    $iBott = $iYrow;
    foreach($arrRow as $oCell)
    {
      if ( $oCell->s!=' ' ) $oCell->s=PdfClean($oCell->s);
      $this->SetFont($oCell->font[0],$oCell->font[1],$oCell->font[2]);
      $this->SetTextColor($oCell->color[0],$oCell->color[1],$oCell->color[2]);
      if ( $oCell->fill ) { $this->SetFillColor($oCell->fillcolor[0],$oCell->fillcolor[1],$oCell->fillcolor[2]); } else { $this->SetFillColor(0); }
      $this->SetXY($iXcel,$iYrow);
      $this->MultiCell($oCell->w,$oCell->h,$oCell->s,$oCell->border,$oCell->align,$oCell->fill);
      $iXcel = $iXcel + $oCell->w;
      if ( $this->GetY()<$iYrow ) $iBott=0; // special in case of pagebreak
      $iBott = max($iBott,$this->GetY());
    }
    $iYrow = $iBott+$iSpacing;   
  }
}

}