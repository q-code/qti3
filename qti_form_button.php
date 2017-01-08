<?php

// QuickTicket 3.0 build:20160703

echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'b\')" title="'.$L['Ico_bold'].'"><i class="fa fa-bold fa-fw"></i></a>';
echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'i\')" title="'.$L['Ico_italic'].'"><i class="fa fa-italic fa-fw"></i></a>';
echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'u\')" title="'.$L['Ico_under'].'"><i class="fa fa-underline fa-fw"></i></a>';
echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'quote\')" title="'.$L['Ico_quote'].'"><i class="fa fa-quote-right fa-fw"></i></a>';
if ( isset($intBbc) )
{
  if ( $intBbc>1 )
  {
  echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'code\')" title="',$L['Ico_code'],'"><i class="fa fa-code fa-fw"></i></a>';
  echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'url\')" title="',$L['Ico_url'],'"><i class="fa fa-link fa-fw"></i></a>';
  echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'mail\')" title="',$L['Ico_mail'],'"><i class="fa fa-at fa-fw"></i></a>';
  }
  if ( $intBbc>2 ) echo '<a class="bbc" href="#textarea" onclick="qtCaret(\'img\')" title="',$L['Ico_image'],'"><i class="fa fa-image fa-fw"></i></a>';
}
