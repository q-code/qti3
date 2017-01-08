<?php // v3.0 build:20160703

// BODY END

echo '
</div>
';

// LINE END

echo '
<!-- bottom bar -->
<div id="body-ft">
<div id="body-ft-l">';
if ( defined('QTI_SHOW_TIME') && QTI_SHOW_TIME )
{
  echo gmdate($_SESSION[QT]['formattime'], time() + 3600*($_SESSION[QT]['time_zone']));
  if ( $_SESSION[QT]['show_time_zone']==='1' )
  {
    echo ' (gmt';
    if ( $_SESSION[QT]['time_zone']>0 ) echo '+',$_SESSION[QT]['time_zone'];
    if ( $_SESSION[QT]['time_zone']<0 ) echo $_SESSION[QT]['time_zone'];
    echo ')';
  }
}
// no moderator in case of index page and search results page (where $s=-1)
if ( defined('QTI_SHOW_MODERATOR') && QTI_SHOW_MODERATOR && isset($oSEC) && $s>=0 )
{
  if ( !empty($oSEC->modid) && !empty($oSEC->modname) ) echo ' &middot; ',L('Coordinator'),': <a href="',Href('qti_user.php?id='.$oSEC->modid),'">',$oSEC->modname,'</a>';
}

echo '</div>
<div id="body-ft-r">&nbsp;'; // &nbsp; is required as empty rigth part makes left part floating outside the div
if ( defined('QTI_SHOW_GOTOLIST') && QTI_SHOW_GOTOLIST )
{
	echo '<select id="jumpto" name="s" size="1" onchange="if (this.value==-1) return false; if (this.value==-2) {window.location=\''.Href('qti_search.php').'\';} else { window.location=\''.Href('qti_items.php').'?s=\'+this.value;}" accesskey="j">';
  echo '<option value="-1" disabled selected>',L('Goto'),'</option>';
	if ( $oVIP->selfurl!='qti_search.php' && sUser::CanView('V5') ) echo '<option value="-2">'.L('Advanced_search').'</option>';
	echo Sectionlist(-1,array(),array(),'',25);
	echo '</select>';
}
echo '</div>
</div>
<!-- END BODY -->
</div>
';

// --------
// INFO & LEGEND
// --------

if ( $_SESSION[QT]['board_offline']!='1' ) {
if ( $_SESSION[QT]['show_legend']=='1' ) {
if ( in_array($oVIP->selfurl,array('index.php','qti_index.php','qti_items.php','qti_calendar.php','qti_item.php')) ) {

echo '
<!-- legend -->
<table class="info">
<tr>
<td>
<div class="infobox stat">
<h1>',L('Information'),'</h1>
<p>';

// section info

if ( !isset($arrStats) ) $arrStats = GetStats();

if ( isset($oSEC) && is_a($oSEC,'cSection') && $oSEC->uid>=0 )
{
  echo cLang::ObjectName('sec','s'.$s,$oSEC->name),':<br/>';
  if ( $_SESSION[QT]['show_closed'] )
  {
    echo '&bull; ',L('Item',$arrStats[$s]['topics']),', ',L('Reply',$arrStats[$s]['replies']);
  }
  else
  {
    $intTopicsZ = (isset($arrStats[$s]['topicsZ']) ? $arrStats[$s]['topicsZ'] : cSection::CountItems($s,'itemsZ') );
    if ( $intTopicsZ==0 )
    {
    echo '&bull;  ',L('Item',$arrStats[$s]['topics']),', ',L('Reply',$arrStats[$s]['replies']);
    echo '<br/>&bull;  ',$L['Closed'],': ',strtolower($L['None']);
    }
    else
    {
    $intRepliesZ = (isset($arrStats[$s]['repliesZ']) ? $arrStats[$s]['repliesZ'] : cSection::CountItems($s,'repliesZ') );
    echo '&bull;  ',L('Item',$arrStats[$s]['topics']),', ',L('Reply',$arrStats[$s]['replies']-$intRepliesZ);
    echo '<br/>&bull;  ',$L['Closed'],': ',L('Item',$intTopicsZ),', ',L('Reply',$intRepliesZ);
    }
  }
}
echo '</p>
<p>';

// application info

echo cLang::ObjectName(),':<br/>'; // index name
if ( isset($arrStats['all']) )
{
echo '&bull; ',L('Item', $arrStats['all']['topics']),', ',L('Reply',$arrStats['all']['replies']);
}

echo '</p>
<p>';

// new user info
$arr = sMem::Get('sys_lastmember');
if ( isset($arr['newuserid']) && !empty($arr['newuserdate']) )
{
  if ( DateAdd($arr['newuserdate'],30,'day')>Date('Ymd') ) 
  {
  $str = isset($arr['newusername'][15]) ? QTstrh($arr['newusername']) : '';
  echo L('Welcome_to'),'<a href="',Href('qti_user.php?id='.$arr['newuserid']),'" title="'.$str.'">',QTtrunc($arr['newusername'],15),'</a>';
  }
}
echo '</p>
</div>
</td>
<td>
';
if ( isset($strDetailLegend) )
{
echo '<div class="infobox details"><h1>',$L['Details'],'</h1>',PHP_EOL;
echo $strDetailLegend;
echo '</div>',PHP_EOL;
}
echo '</td>',PHP_EOL;
echo '<td>',PHP_EOL;
echo '<div class="infobox legend"><h1>',$L['Legend'],'</h1>',PHP_EOL;
if ( $oVIP->selfurl=='qti_index.php' )
{
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_section_0_0.gif','F',$L['Ico_section_0_0'],'ico i-sec'),' ',$L['Ico_section_0_0'],'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_section_2_0.gif','F',$L['Ico_section_2_0'],'ico i-sec'),' ',$L['Ico_section_2_0'],'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_section_0_1.gif','F',$L['Ico_section_0_1'],'ico i-sec'),' ',$L['Ico_section_0_1'],'<br/>',PHP_EOL;
}
else
{
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_topic_a_0.gif','T','','ico i-item'),' ',$L['Ico_item_a'],PHP_EOL;
  if ( QTI_MY_REPLY && $oVIP->selfurl!='qti_item.php' )
  {
  $str = '&reg;'; if ( is_string(QTI_MY_REPLY) ) $str = QTI_MY_REPLY;
  echo ' &nbsp;',$str,'&nbsp;',$L['You_reply'];
  }
  echo '<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_topic_i_0.gif','I','','ico i-item'),' ',$L['Ico_item_i'],'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_topic_t_0.gif','T','','ico i-status'),' ',$L['Ico_item_t'],'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_topic_t_1.gif','T','','ico i-status'),' ',$L['Ico_item_tZ'],'<br/>',PHP_EOL;
  if ( $oVIP->selfurl==='qti_item.php' )
  {
  echo '<i class="fa fa-reply"></i> ',L('Ico_post_r'),'<br/>',PHP_EOL;
  echo '<i class="fa fa-forward"></i> ',L('Ico_post_f'),'<br/>',PHP_EOL;
  }
}
echo '</div>',PHP_EOL;
echo '</td>',PHP_EOL;
echo '</tr>',PHP_EOL;
echo '</table>',PHP_EOL;

}}}

// --------
// COPYRIGHT
// --------

// MODULE RSS
if ( $_SESSION[QT]['board_offline']!='1' ) {
if ( UseModule('rss') ) {
if ( $_SESSION[QT]['m_rss']=='1' ) {
if ( sUser::Role()!='V' || sUser::Role().substr($_SESSION[QT]['m_rss_conf'],0,1)=='VV' ) {
if ( $oVIP->selfurl!='qtim_rss.php' ) {
  $arrMenus[]=array('h'=>false,'f'=>true, 'n'=>'<i class="fa fa-rss-square fa-lg"></i> RSS', 'u'=>'qtim_rss.php');
}}}}}
echo '
<!-- footer -->
<div id="footer">
<div id="footer-l">';
$i=0;
foreach($arrMenus as $arrMenu) {
if ( $arrMenu['f'] ) {
  if ( !isset($arrMenu['s']) ) $arrMenu['s']=$arrMenu['u'];
  if ( $i!=0 ) echo QTI_MENUSEPARATOR;
  ++$i;
  if ( empty($arrMenu['u']) )
  {
  echo $arrMenu['n'];
  }
  else
  {
  echo '<a href="',Href($arrMenu['u']),'"',(strstr($arrMenu['s'],$oVIP->selfurl) ? ' onclick="return false;"' : ''),'>',$arrMenu['n'],'</a>';
  }
}}
if ( sUser::Role()==='A' ) echo QTI_MENUSEPARATOR,'<a class="footer_menu" href="qti_adm_index.php">['.L('Administration').']</a>';

echo '</div>
<div id="footer-r">powered by <a href="http://www.qt-cute.org">QT-cute</a> <span title="',QTIVERSION,'">v',substr(QTIVERSION,0,3),'</span></div>
</div>
';

// END PAGE CONTROL

echo cHtml::Page(END);

// HTML END

if ( isset($oDB->stats) )
{
  $end = (float)vsprintf('%d.%06d', gettimeofday());
  if ( isset($oDB->stats['num']) ) echo $oDB->stats['num'],' queries. ';
  if ( isset($oDB->stats['start']) ) echo 'End queries in ',round($end-$oDB->stats['start'],4),' sec. ';
  if ( isset($oDB->stats['pagestart']) ) echo 'End page in ',round($end-$oDB->stats['pagestart'],4),' sec. ';
}

echo $oHtml->End();

ob_end_flush();