String.prototype.replaceAll = function(f,r){return this.split(f).join(r);}

var bEdited=false;
var doc = document;

function qtHtmldecode(str)
{
  var ta=document.createElement("textarea");
  ta.innerHTML=str.replace("<","&lt;").replace(">","&gt;");
  return ta.value;
}
function qtFocusEnd(id)
{
  if ( doc.getElementById(id) )
  {
  var str = doc.getElementById(id).value;
  doc.getElementById(id).value="";
  doc.getElementById(id).focus();
  doc.getElementById(id).value=str;
  }
}
function qtKeypress(e,s)
{
  if (window.event)
  {
  if (e.keyCode==13) document.getElementById(s).click();
  }
  else if(e.which)
  {
  if (e.which==13) document.getElementById(s).click();
  }
  return null;
}
function qtEdited(bEdited,e)
{
  if (typeof(bEdited)==='undefined' || !bEdited || e==="") return true;
  if (typeof(e)==='undefined' || e==0) e="Data not yet saved. Quit without saving?";
  if (!confirm(qtHtmldecode(e))) return false;
  return true;
}
function qtVmail(id)
{
  var str = doc.getElementById('href'+id).href;
  str = str.replace(/-at-/g,'@');
  str = str.replace(/-dot-/g,'.');
  str = str.replace('java:','mailto:');
  doc.getElementById('href'+id).href = str;
  if ( doc.getElementById('img'+id) )
  {
  str = doc.getElementById('img'+id).title;
  str = str.replace(/-at-/g,'@');
  str = str.replace(/-dot-/g,'.');
  doc.getElementById('img'+id).title = str;
  }
  return null;
}
function qtHmail(id)
{
  var str = doc.getElementById('href'+id).href;
  str = str.replace(/\@/g,'-at-');
  str = str.replace(/\./g,'-dot-');
  str = str.replace('javamail:','mailto:');
  doc.getElementById('href'+id).href = str;
  if ( doc.getElementById('img'+id) )
  {
  str = doc.getElementById('img'+id).title;
  str = str.replace(/\@/g,'-at-');
  str = str.replace(/\./g,'-dot-');
  doc.getElementById('img'+id).title = str;
  }
  return null;
}
function qtWritemailto(str1,str2,separator)
{
  doc.write('<a class="small" href="mailto:' + str1 + '@' + str2 + '">');
  doc.write(str1 + '@' + str2);
  doc.write('<\/a>');
  if ( separator ) doc.write(separator);
  return null;
}
function qtUpdateItemIcon(jd,suffix='-itemicon')
{
  if ( !('id' in jd) && !('t' in jd) ) return false;
  var id = 't' + (('t' in jd) ? jd.t : jd.id) + suffix;
  if ( !doc.getElementById(id) ) return false;
  if ( !('imgsrc' in jd) ) jd.imgsrc = 'bin/js/qti_cse_items.gif';
  jd.imgsrc = jd.imgsrc.replace(/\\/g, '');
  if ( doc.getElementById(id).src.indexOf(jd.imgsrc)>0 ) return false;
  if ( !('imgtitle' in jd) ) jd.imgtitle = '';
  if ( !('imgalt' in jd) ) jd.imgalt = '';
  doc.getElementById(id).src = jd.imgsrc;
  doc.getElementById(id).title = jd.imgtitle;
  doc.getElementById(id).alt = jd.imgalt;
  return true;
}
function qtHide(id)
{
  if ( doc.getElementById(id) ) doc.getElementById(id).style.display="none";
}

function qtAbsolutePosition2(obj,iShiftTop,iShiftLeft)
{
  if ( iShiftTop===undefined) iShiftTop=0;
  if ( iShiftLeft===undefined) iShiftLeft=0;
  var top = left = 0;
  var rect = obj.getBoundingClientRect();

  return {top: rect.top, left: rect.left};
}

function qtAbsolutePosition(obj,iShiftTop,iShiftLeft)
{
  if ( iShiftTop===undefined) iShiftTop=0;
  if ( iShiftLeft===undefined) iShiftLeft=0;
  var top = left = 0;
  do {
    top += obj.offsetTop  || 0;
    left += obj.offsetLeft || 0;
    obj = obj.offsetParent;
  } while(obj);
  return {top: top+iShiftTop, left: left+iShiftLeft};
}

function qtPopupImage(img,target,iShiftTop,iShiftTop)
{
  if (iShiftTop===undefined) iShiftTop=-12;
  if (iShiftTop===undefined) iShiftTop=-12;
  var imgpop = doc.getElementById("popup_"+img.id);
  if ( imgpop )
  {
    var absImg = qtAbsolutePosition(img,iShiftTop,iShiftTop);
    imgpop.style.maxHeight = "none";
    imgpop.style.height = "auto";
    imgpop.style.display = "block";
    imgpop.style.left = absImg.left + "px";
    imgpop.style.top = absImg.top + "px";
  }
}