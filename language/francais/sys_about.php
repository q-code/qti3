<?php

echo '<p><b>Application g&eacute;r&eacute;e par</b></p>
<p>',$_SESSION[QT]['site_name'],'</p>
<p>Webmaster: <a href="mailto:',$_SESSION[QT]['admin_email'],'">',$_SESSION[QT]['admin_email'],'</a></p>
<p>Contact: ',$_SESSION[QT]['admin_name'],' ',$_SESSION[QT]['admin_addr'],'</p>
<br />
<p><b>Application cr&eacute;&eacute;e par</b></p>
<p>QT-cute (www.qt-cute.org) version ',QTIVERSION,'</p>
<br />
<p><b>Licences (anglais)</b></p>
<p><img src="admin/vgplv3.png" width="88" height="31" alt="GPL" title="GNU General Public License" /></p>
<p>Voyez les documents <a href="admin/license.txt">Application License</a> et <a href="admin/license_gpl.txt">GNU General Public License</a> pour de plus amples informations.</p>
<br />
<p><b>Conformit&eacute;s</b></p>
';