var isMozilla = (navigator.userAgent.toLowerCase().indexOf('gecko')!=-1) ? true : false;

function qtCaret(selec) {

var oField = document.getElementById('text');
if (isMozilla)
{
var objectValue = oField.value;
var deb = oField.selectionStart;
var fin = oField.selectionEnd;
var objectValueDeb = objectValue.substring( 0 , oField.selectionStart );
var objectValueFin = objectValue.substring( oField.selectionEnd , oField.textLength );
var objectSelected = objectValue.substring( oField.selectionStart ,oField.selectionEnd );
oField.value = objectValueDeb + "[" + selec + "]" + objectSelected + "[/" + selec + "]" + objectValueFin;
oField.selectionStart = objectValueDeb.length;
oField.selectionEnd = (objectValueDeb + "[" + selec + "]" + objectSelected + "[/" + selec + "]").length;
oField.focus();
oField.setSelectionRange(objectValueDeb.length + selec.length + 2, objectValueDeb.length + selec.length + 2);
}
else
{
var str = document.selection.createRange().text;
if (str.length>0)
{
// If selected text
var sel = document.selection.createRange();
sel.text = "[" + selec + "]" + str + "[/" + selec + "]";
sel.collapse();
sel.select();
}
else
{
oField.focus(oField.caretPos);
oField.focus(oField.value.length);
oField.caretPos = document.selection.createRange().duplicate();
var bidon = "%~%";
var orig = oField.value;
oField.caretPos.text = bidon;
var i = oField.value.search(bidon);
oField.value = orig.substr(0,i) + "[" + selec + "][/" + selec + "]" + orig.substr(i, oField.value.length);
var pos = i + 2 + selec.length;
var r = oField.createTextRange();
r.moveStart('character', pos);
r.collapse();
r.select();
}
}

}
