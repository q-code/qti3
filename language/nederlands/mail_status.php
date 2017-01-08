<?php

$strSubject=$_SESSION[QT]['site_name'].' - '.$L['Notification'];

$strMessage="
Ticket is nu : %s
-------------------------------
%s
-------------------------------

Vriendelijke groeten,
Webmaster van {$_SESSION[QT]['site_name']}
Voor meer info zie {$_SESSION[QT]['site_url']}/index.php
";