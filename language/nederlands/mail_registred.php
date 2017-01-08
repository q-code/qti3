<?php

$strSubject = $_SESSION[QT]['site_name'].' - Welkom';

$strMessage = "
Gelieve te vinden hier na uw login en wachtwoord om tot de forum {$_SESSION[QT]['site_name']} toegang te hebben.
U kunt dit wachtwoord in de sectie Profiel veranderen.

Gebruikersnaam: %s
Wachtwoord: %s

Vriendelijke groeten,
Webmaster van {$_SESSION[QT][site_name']}
{$_SESSION[QT]['site_url']}/index.php
";