<?php

// QuickTicket 3.0 build:20160703

session_start();

if ( isset($_GET['language']) ) $_SESSION['qti_setup_lang']=$_GET['language'];
if ( isset($_GET['error']) ) $_SESSION['showerror']=(int)$_GET['error'];
//$showerror = isset($_SESSION['showerror']) ? (int)$_SESSION['showerror'] : 1;

if ( !isset($_SESSION['qti_setup_lang']) ) $_SESSION['qti_setup_lang']='en';
if ( !file_exists('qti_lang_'.$_SESSION['qti_setup_lang'].'.php') ) $_SESSION['qti_setup_lang']='en';
$debug = isset($_SESSION['debugsql']) ? 2 : 1;

include 'qti_lang_'.$_SESSION['qti_setup_lang'].'.php';
include '../bin/config.php';

$strAppl = 'QuickTicket';
$strPrevUrl = 'qti_setup.php';
$strNextUrl = 'qti_setup_2.php';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Next'];
$strError = '';

// --------
// HTML START
// --------

include 'qti_setup_hd.php';

echo '
<table>
<tr>
<td width="475" style="padding:0px;vertical-align:top">';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  include '../bin/class/qt_class_db.php';

  $qti_dbsystem = strip_tags(trim($_POST['qti_dbsystem']));
  $qti_host     = strip_tags(trim($_POST['qti_host']));
  $qti_database = strip_tags(trim($_POST['qti_database']));
  $qti_prefix   = strip_tags(trim($_POST['qti_prefix']));
  $qti_user     = strip_tags(trim($_POST['qti_user']));
  $qti_pwd      = strip_tags(trim($_POST['qti_pwd']));
  $str = strip_tags(trim($_POST['qti_dbo_login']));
  if ( $str!='') $_SESSION['qti_dbologin'] = $str;
  $str = strip_tags(trim($_POST['qti_dbo_pswrd']));
  if ( $str!='') $_SESSION['qti_dbopwd'] = $str;

  // Test Connection

  if ( isset($_SESSION['qti_dbologin']) )
  {
    $oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$_SESSION['qti_dbologin'],$_SESSION['qti_dbopwd'],2);
  }
  else
  {
    $oDB = new cDB($qti_dbsystem,$qti_host,$qti_database,$qti_user,$qti_pwd,2);
  }

  if ( empty($oDB->error) )
  {
    echo '<div class="setup_ok">',$L['S_connect'],'</div>';
  }
  else
  {
    echo '<div class="setup_err">',sprintf ($L['E_connect'],$qti_database,$qti_host),'</div>';
  }

  // Save Connection

  $strFilename = '../bin/config.php';
  $content = '<?php
  $qti_dbsystem = "'.$qti_dbsystem.'";
  $qti_host = "'.addslashes($qti_host).'";
  $qti_database = "'.$qti_database.'";
  $qti_prefix = "'.$qti_prefix.'";
  $qti_user = "'.$qti_user.'";
  $qti_pwd = "'.$qti_pwd.'";
  $qti_install = "'.date('Y-m-d').'";';

  if (!is_writable($strFilename)) $strError="Impossible to write into the file [$strFilename].";
  if ( empty($strError) )
  {
  if (!$handle = fopen($strFilename, 'w')) $strError="Impossible to open the file [$strFilename].";
  }
  if ( empty($strError) )
  {
  if ( fwrite($handle, $content)===FALSE ) $strError="Impossible to write into the file [$strFilename].";
  fclose($handle);
  }

  // End message
  if ( empty($strError) )
  {
    echo '<div class="setup_ok">',$L['S_save'],'</div>';
  }
  else
  {
    echo '<div class="setup_err">',$strError,$L['E_save'],'</div>';
  }
}

// --------
// FORM
// --------

echo '<form method="post" name="install" action="qti_setup_1.php">
<h2>',$L['Connection_db'],'</h2>
<table class="t-conn">
<tr>
<td>',$L['Database_type'],'</td>
<td><select name="qti_dbsystem">
<optgroup label="PDO connectors">
<option value="pdo.mysql"',($qti_dbsystem=='pdo.mysql' ? ' selected="selected"' : ''),'>MySQL 5 or next</option>
<option value="pdo.sqlsrv"',($qti_dbsystem=='pdo.sqlsrv' ? ' selected="selected"' : ''),'>SQL sever (or Express)</option>
<option value="pdo.pg"',($qti_dbsystem=='pdo.pg' ? ' selected="selected"' : ''),'>PostgreSQL</option>
<option value="pdo.oci"',($qti_dbsystem=='pdo.oci' ? ' selected="selected"' : ''),'>Oracle</option>
<option value="pdo.sqlite"',($qti_dbsystem=='pdo.sqlite' ? ' selected="selected"' : ''),'>SQLite</option>
<option value="pdo.firebird"',($qti_dbsystem=='pdo.firebird' ? ' selected="selected"' : ''),'>Firebird</option>
</optgroup>
<optgroup label="Legacy connectors">
<option value="mysql"',($qti_dbsystem=='mysql' ? ' selected="selected"' : ''),'>MySQL</option>
<option value="sqlsrv"',($qti_dbsystem=='sqlsrv' ? ' selected="selected"' : ''),'>SQL server (or Express)</option>
<option value="pg"'.($qti_dbsystem=='pg' ? 'selected="selected"' : ''),'>PostgreSQL</option>
<option value="sqlite"'.($qti_dbsystem=='sqlite' ? 'selected="selected"' : ''),'>SQLite</option>
<option value="ibase"'.($qti_dbsystem=='ibase' ? 'selected="selected"' : ''),'>Interbase/FireBird</option>
<option value="db2"',($qti_dbsystem=='db2' ? ' selected="selected"' : ''),'>IBM DB2</option>
<option value="oci"',($qti_dbsystem=='oci' ? ' selected="selected"' : ''),'>Oracle</option>
</optgroup>
</select></td>
</tr>
';
echo '<tr>
<td>',$L['Database_host'],'</td>
<td>
<input type="text" name="qti_host" value="',$qti_host,'" size="30" maxlength="255"/>
</td>
</tr>
<tr>
<td>',$L['Database_name'],'</td>
<td><input type="text" name="qti_database" value="',$qti_database,'" size="15" maxlength="100" /></td>
</tr>
<tr>
<td>',$L['Table_prefix'],'</td>
<td><input type="text" name="qti_prefix" value="',$qti_prefix,'" size="15" maxlength="100" /></td>
</tr>
<tr>
<td>',$L['Database_user'],'</td>
<td>
<input name="qti_user" value="',$qti_user,'" size="15" maxlength="100" />
<input type="password" name="qti_pwd" value="',$qti_pwd,'" size="15" maxlength="100" />
</td>
</tr>
<tr>
<td colspan="2" style="background-color:#CCCCCC">',$L['Htablecreator'],'</td>
</tr>
<tr>
<td style="background-color:#CCCCCC"><label for="qti_dbo_login">Table creator (login/password)</label></td>
<td style="background-color:#CCCCCC">
<input type="text" id="qti_dbo_login" name="qti_dbo_login" value="',(isset($_SESSION['qti_dbologin']) ? $_SESSION['qti_dbologin'] : ''),'" size="15" maxlength="100" />
<input type="password" name="qti_dbo_pswrd" value="',(isset($_SESSION['qti_dbopwd']) ? $_SESSION['qti_dbopwd'] : ''),'" size="15" maxlength="100" />
</td>
</tr>
<tr>
<td colspan="2" style="padding:10px;text-align:center"><input class="submit" type="submit" name="ok" value="',$L['Save'],'" onclick="this.style.visibility=\'hidden\';"/></td>
</tr>
</table>
</form>
<p>',$L['Upgrade'],'</p>';

echo '
</td>
<td class="hidden" style="vertical-align:top"><div class="setup_help">',$L['Help_1'],'</div></td>
</tr>
</table>
';

// --------
// HTML END
// --------

include 'qti_setup_ft.php';