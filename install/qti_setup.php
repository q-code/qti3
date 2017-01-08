<?php

// QuickTicket 3.0 build:20160703

session_start();
$strAppl = 'QuickTicket';
if ( !isset($_SESSION['boardmail']) ) $_SESSION['boardmail']='';
if ( !isset($_SESSION['qti_setup_lang']) ) $_SESSION['qti_setup_lang']='en';
if ( isset($_GET['error']) ) $_SESSION['showerror']=(int)$_GET['error'];

$arrLangs = array();
$arrLangs['en'] = 'English';
if ( file_exists('qti_lang_fr.php') ) $arrLangs['fr'] = 'Fran&ccedil;ais';
if ( file_exists('qti_lang_nl.php') ) $arrLangs['nl'] = 'Nederlands';
if ( file_exists('qti_lang_it.php') ) $arrLangs['it'] = 'Italiano';
if ( file_exists('qti_lang_es.php') ) $arrLangs['es'] = 'Espa&ntilde;ol';
if ( file_exists('qti_lang_de.php') ) $arrLangs['de'] = 'Deutsche';
if ( file_exists('qti_lang_pt.php') ) $arrLangs['pt'] = 'Portuguese';

include 'qti_lang_'.$_SESSION['qti_setup_lang'].'.php';

// --------
// HTML START
// --------

include 'qti_setup_hd.php';

echo '<h1>',$strAppl,'</h1>';
echo '<h2>Language ?</h2>';
echo '
<form method="get" action="qti_setup_1.php">
<select name="language" size="1">';
foreach($arrLangs as $strKey=>$strLang)
{
echo '<option value="',$strKey,'"',($_SESSION['qti_setup_lang']==$strKey ? ' selected="selected"' : ''),'>',$strLang,'</option>';
}
echo '</select>
<input type="submit" name="ok" value="Ok" />
</form>
';

include 'qti_setup_ft.php';