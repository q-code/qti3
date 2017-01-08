<?php

// QTI 3 build:20160703 used in qti_topic

if ( $oSEC->status=='0' && $oTopic->status!='Z' && $oTopic->status!='0' ) {
if ( sUser::Role()==='V' && $_SESSION[QT]['visitor_right']<7 ) {} else {

echo '
<!-- Quick reply -->
<div class="quickreply">
';

// Quick Reply form, according to the topic type (I, A or T)

switch(strtoupper($oTopic->type))
{

//-------
case 'I':
//-------

echo '<form method="post" action="',Href('qti_form_edit.php'),'">
<table>
<tr>
<td class="title" style="width:175px"><span class="title">',$L['Quick_reply'],'</span></td>
<td>',L('Message'),' ';
if ( QTI_BBC ) include 'qti_form_button.php';
echo '</td>
<td class="commands"></td>
</tr>
<tr>
<td class="value">',HtmlScore($oTopic->ReadOptions('Ilevel')),'</td>
<td class="message"><a href="textarea"></a><textarea id="text" name="text" rows="4" cols="64" maxlength="'.(empty($_SESSION[QT]['chars_per_post']) ? '4000' : $_SESSION[QT]['chars_per_post']).'" accesskey="q"></textarea></td>
<td class="commands">
<input type="submit" id="dosend" name="dosend" value="',$L['Send'],'" />
<input type="hidden" name="s" value="',$oSEC->uid,'" />
<input type="hidden" name="t" value="',$oTopic->id,'" />
<input type="hidden" name="a" value="re" />
<input type="hidden" name="ref" value="',$oTopic->numid,'" />
<input type="hidden" name="icon" value="00" />
</td>
</tr>
</table>
</form>
';
break;

//-------
default:
//-------

echo '<form method="post" action="',Href('qti_form_edit.php'),'">
<div class="quickreplyheader">
<span class="title">',L('Quick_reply'),'</span> &nbsp; ';
if ( QTI_BBC ) include 'qti_form_button.php';
if ( $oTopic->type!='I' && $oTopic->status!='Z' && $oTopic->firstpostuser==sUser::Id() )
{
echo ' &nbsp; <input type="checkbox" style="vertical-align:middle" id="topicstatususer" name="topicstatususer[]" value="Z" tabindex="96" />&nbsp;<label for="topicstatususer">',L('Close_my_item'),'</label>';
}
echo '</div>
<div class="quickreplybody">
<table>
<tr>
<td class="textarea"><textarea required id="text" name="text" rows="5" maxlength="'.(empty($_SESSION[QT]['chars_per_post']) ? '4000' : $_SESSION[QT]['chars_per_post']).'" accesskey="q"></textarea><a href="textarea"></a></td>
<td class="commands">
<input type="hidden" name="s" value="',$oSEC->uid,'" />
<input type="hidden" name="t" value="',$oTopic->id,'" />
<input type="hidden" name="a" value="re"/>
<input type="hidden" name="ref" value="',$oTopic->numid,'" />
<input type="hidden" name="icon" value="00" />
<input type="hidden" name="title" value="" />
<input type="submit" id="dosend" name="dosend" value="',$L['Send'],'" /><br />
<input type="submit" id="dopreview" name="dopreview" value="',$L['Advanced_reply'],'" />
</td>
</tr>
</table>
</div>
</form>
';

break;

}

echo '</div>
';

}}