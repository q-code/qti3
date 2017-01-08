/*
Summary
-------
To work with this function, the table <table> and each row <tr> must have unique id.
If your page contains several tables, be sure that the <tr> id are unique through all tables.
*/

function qtCheckboxAll(tableid,boxid,name,useHighlight)
{
  var checkbox = doc.getElementById(tableid+'-'+boxid); if ( !checkbox ) return;
	var checkboxes = doc.getElementsByName(name);
	var i = checkboxes.length-1; if ( i<0 ) return;
	do
	{
	checkboxes[i].checked=checkbox.checked;
  if (useHighlight) qtHighlight(tableid+'-tr-'+checkboxes[i].value, checkbox.checked);
	}
	while(i--);
}
function qtCheckboxOne(name,id)
{
	// Check/uncheck header checkbox when all/none boxes are checked
	// This function is not mandatory
	if ( !doc.getElementById(id) ) return;
	var checkboxes = doc.getElementsByName(name); if ( checkboxes.length<1 ) return;
	var n = 0, i = checkboxes.length-1; if ( i<0 ) return;
	do { if ( checkboxes[i].checked ) n++; } while(i--);
	doc.getElementById(id).checked = ( n==checkboxes.length );
}
function qtCheckboxToggle(id)
{
	var doc = document.getElementById(id); if ( !doc ) return;
	doc.click();
}
function qtHighlight(id,bHighlighted)
{
	var doc = document.getElementById(id); if ( !doc ) return;
	doc.className = doc.className.replace(' checked','');
	if ( bHighlighted ) doc.className += ' checked';
}
function qtCheckboxIds(ids,cbPrefix='t1-cb-',highlight=true,trPrefix='t1-tr-')
{
  var i = ids.length;
  while(i)
  {
    --i;
    if ( doc.getElementById(cbPrefix+ids[i]) )
    {
    doc.getElementById(cbPrefix+ids[i]).checked=true;
    if ( highlight ) qtHighlight(trPrefix+ids[i],true);
    }
  }
}