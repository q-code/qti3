<?php

// This is included in $oVIP->login function when LDAP module is installed and configured
// This part will validate $username/$password (using a ldap bind)
// and add a new profile (when profile not yet existing)

include 'qtim_ldap_lib.php';
$bLdapLogin = qtim_ldap_bind($username,$password);
if ( $bLdapLogin )
{  
  // add profile if new user
  if ( $iProfile==0 )
  {
  qtim_ldap_profile($username,$password); // create new profile (will search email from ldap) 
  $iProfile = cVIP::SysCount('members',' AND name="'.$username.'" AND pwd="'.sha1($password).'"'); // check profile is created
  }
}
else
{
  // abord login if authority is configured with ldap ONLY, otherwhise continues.
  if ( isset($_SESSION[QT]['m_ldap_users']) && $_SESSION[QT]['m_ldap_users']=='ldap' ) return false;
}