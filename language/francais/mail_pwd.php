<?php

$strSubject = $_SESSION[QT]['site_name'].' - Nouveau mot de passe';

$strMessage = "
Veuillez trouver ci-après votre login et mot de passe pour le site {$_SESSION[QT]['site_name']}.
Vous pouvez changer ce mot de passe dans votre page Profil.

Utilisateur: %s
Mot de passe: %s

Salutations,
Le webmaster de {$_SESSION[QT]['site_name']}
{$_SESSION[QT]['site_url']}/index.php
";