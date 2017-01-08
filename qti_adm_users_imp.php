<?php

/**
* PHP version 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTicket
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2015 The PHP Group
* @version    3.0 build:20160703
*/

session_start();
require 'bin/init.php';
include Translate(APP.'_adm.php');

if ( sUser::Role()!='A' ) die($L['E_admin']);

// INITIALISE

$strTitle   = '';
$strDelimit = ';';
$strEnclose = '"';
$strSkip    = 'N';

$oVIP->selfurl = 'qti_adm_users_imp.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Users_import_csv'];
$oVIP->exiturl = 'qti_adm_users.php';
$oVIP->exitname = '&laquo; '.$L['Users'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // Check uploaded document

  $error = InvalidUpload($_FILES['title'],'csv,txt,text','',500);

  // Check form value

  if ( empty($error) )
  {
    $strDelimit = trim($_POST['delimit']);
    if ( isset($_POST['skip']) ) $strSkip='Y';
    if ( empty($strDelimit) ) $error="{$L['Separator']} {L('invalid')}";
    if ( strlen($strDelimit)!=1 ) $error="{$L['Separator']} {L('invalid')}";
    if ( preg_match('/[0-9A-Za-z]/',$strDelimit) ) $error=$L['Separator'].' '.Error(1);
  }

  // Read file

  if ( empty($error) )
  {
    if ( $handle = fopen($_FILES['title']['tmp_name'],'r') )
    {
      $i = 0;
      $intCountUser = 0;

      $oDB->BeginTransac();
      $intNextUser = $oDB->Nextid(TABUSER);
      while( ($row=fgetcsv($handle,500,$strDelimit))!==FALSE )
      {
        ++$i;
        if ( $strSkip=='Y' && $i==1 ) continue;
        if ( count($row)==1 ) continue;
        if ( count($row)==4 )
        {
          $strRole = 'U'; if ( $row[0]=='A' || $row[0]=='M' || $row[0]=='a' || $row[0]=='m') $strRole=strtoupper($row[0]);
          $strLog = trim($row[1]); if ( !empty($strLog) ) $strLog=utf8_decode($strLog);
          $strPwd = trim($row[2]);
          if ( substr($strPwd,0,3)=='SHA' || substr($strPwd,0,3)=='sha' ) $strPwd = sha1($strPwd);
          if ( empty($strPwd) ) $strPwd=sha1($strLog);
          $strMail = $row[3];
          // insert
          if ( !empty($strLog) )
          {
            if ( $oDB->Exec('INSERT INTO '.TABUSER.' (id,name,pwd,closed,role,mail,privacy,firstdate,lastdate,numpost,children,parentmail,photo) VALUES ('.$intNextUser.',"'.$strLog.'","'.$strPwd.'","0","'.$strRole.'","'.$strMail.'","1","'.Date('Ymd His').'","'.Date('Ymd His').'",0,"0","","0")' ) )
            {
              ++$intNextUser;
              ++$intCountUser;
            }
            else
            {
              echo ' - Cannot insert a new user with username ',$strLog,'<br/>';
            }
          }
        }
        else
        {
          $error='Number of parameters ('.count($row).') not matching in line '.$i;
        }
      }
      $oDB->CommitTransac();
    }
    fclose($handle);
    // Unregister global sys (will be recomputed on next page)
    sMem::Clear('sys_lastmember');
  }

  // End message

  if ( empty($error) )
  {
    unlink($_FILES['title']['tmp_name']);
   $oVIP->selfname = $L['Users_import_csv'];
    if ( $intCountUser==0 )
    {
    $oHtml->PageMsgAdm(NULL, '<p>No user inserted... Check the file and check that you don\'t have duplicate usernames.</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '500px');
    }
    else
    {
    $oHtml->PageMsgAdm(NULL, '<p>'.$intCountUser.' '.$L['Users'].'<br/>'.$L['S_update'].'</p><p><a href="'.$oVIP->exiturl.'">'.$oVIP->exitname.'</a></p>', 0, '500px');
    }
  }
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.L('Missing').' File")); return false; }
  if (theForm.delimit.value.length==0) { alert(qtHtmldecode("'.L('Missing').': '.$L['Separator'].'")); return false; }
  return null;
}
</script>
';

include APP.'_adm_inc_hd.php';

echo '<form method="post" action="',$oVIP->selfurl,'" enctype="multipart/form-data" onsubmit="return ValidateForm(this);">
<h2 class="subtitle">',$L['File'],'</h2>
<input type="hidden" name="maxsize" value="5242880"/>
<table class="t-data horiz">
<tr>
<th style="width:200px"><label for="title">CSV file</label></th>
<td><input type="file" id="title" name="title" size="32" value="',$strTitle,'"/></td>
</tr>
<tr>
<th><label for="delimit">',$L['Separator'],'</label></th>
<td><input type="text" id="delimit" name="delimit" size="1" maxlength="5" value="',$strDelimit,'"/></td>
</tr>
<tr>
<th>',$L['First_line'],'</th>
<td><input type="checkbox" id="skip" name="skip"',($strSkip=='Y' ? QCHE : ''),'/><label for="skip">',$L['Skip_first_line'],'</label></td>
</tr>
</table>
';
echo '
<p style="margin:0 0 5px 0;text-align:center"><input type="submit" name="ok" value="',$L['Ok'],'"/></p>
</form>
<p><a href="',Href($oVIP->exiturl),'">',$oVIP->exitname,'</a></p>
';

// HTML END

include APP.'_adm_inc_ft.php';