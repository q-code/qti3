<?php

// QuickTicket 3.0 build:20160703

echo '
</div>
';

if ( isset($strPrevUrl) && isset($strNextUrl) )
{
echo '
<div id="banner">
<table>
<tr>
<td style="color:white;font-size:8pt">powered by <a style="color:white" href="http://www.qt-cute.org">QT-cute</a></td>
<td style="text-align:right"><a class="button" href="',$strPrevUrl,'">',$strPrevLabel,'</a><a class="button" href="',$strNextUrl,'">',$strNextLabel,'</a></td>
</tr>
</table>
</div>
';
}

echo '
</div>
</body>
</html>';