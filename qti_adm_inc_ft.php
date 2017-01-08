<?php // v3.0 build:20160703

echo cHtml::Page(END);

echo '<!-- MENU/PAGE -->
</td>
</tr>
</table>
<!-- END MENU/PAGE -->
';

if ( isset($oDB->stats) )
{
  $end = (float)vsprintf('%d.%06d', gettimeofday());
  if ( isset($oDB->stats['num']) ) echo $oDB->stats['num'],' queries. ';
  if ( isset($oDB->stats['start']) ) echo 'End queries in ',round($end-$oDB->stats['start'],4),' sec. ';
  if ( isset($oDB->stats['pagestart']) ) echo 'End page in ',round($end-$oDB->stats['pagestart'],4),' sec. ';
}

if ( isset($strFooterScript) ) echo $strFooterScript; // remain for backward compatibility of the modules

echo '</div>'.PHP_EOL; // end class="pg-admin"

echo $oHtml->End();

ob_end_flush();