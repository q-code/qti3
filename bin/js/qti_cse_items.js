var sseSource = new EventSource('bin/qti_j_sse.php');
var cseGarbageSection = new Array();
var cseGarbageTopic = new Array();
var cseGarbageReply = new Array();
var cseNewRow = 0;

// Event handlers

sseSource.addEventListener('topic', function(e) {

  var jd = cseReadSse(e,cseGarbageTopic,['s','t']); if ( !jd ) return;
  var b =  doc.getElementById('pg-s'+jd.s); // can insert
  if ( !b ) b = doc.getElementById('pg-q-last');
  if ( !b ) b = doc.getElementById('pg-q-news') && ('type' in jd) && jd.type=='A';

  console.log('SSE type topic: '+e.data);
  if ( doc.getElementById('t1-tr-'+jd.t) ) { cseUpdate(jd,true); } else { if ( b ) cseInsert('t1',jd); }

}, false);

sseSource.addEventListener('reply', function(e) {

  var jd = cseReadSse(e,cseGarbageReply,['s','t']); if ( !jd ) return;
  var b =  doc.getElementById('pg-s'+jd.s);
  if ( !b ) b = doc.getElementById('pg-q-last');
  if ( !b ) b = doc.getElementById('pg-q-news') && ('type' in jd) && jd.type=='A';
  if ( !b ) return;
  if ( !doc.getElementById('t1-tr-'+jd.t) ) return;

  console.log('SSE type reply: '+e.data);
  cseUpdate(jd,true);

}, false);

// Error
// We use a named-event 'error' because the method .onerror is triggered when server script ends
// When server script ends sseSource must stay opened as the client retry automatically.

sseSource.addEventListener('error', function(e) {
  if ( !('data' in e) ) return;
  var hm = new Date();
  console.log('SSE('+hm.getHours()+':'+hm.getMinutes()+') Server send error event with data: '+e.data);
  sseSource.close();
  console.log('SSE('+hm.getHours()+':'+hm.getMinutes()+') Client stops sse communication');
}, false);

// Default message

sseSource.onmessage = function(e) {
  if ( !('origin' in e) || sseOrigin.indexOf(e.origin)<0 ) { console.log('Unknown SSE origin send message... Origin was '+e.origin); return; }
  if ( !('data' in e) ) return;
  console.log('Message '+JSON.stringify(e.data));
  if ( doc.getElementById('serverData') )
  {
    if ( doc.getElementById('serverData').innerHTML.length>255 ) doc.getElementById('serverData').innerHTML='';
    doc.getElementById('serverData').innerHTML += JSON.stringify(e.data)+'<br/>';
  }
};

function cseReadSse(e,garbage,minimumData=[]) {
  // This checks the event origin, format, and manage a garbage of already processed events (byref).
  // Returns an object (json data parsed) or FALSE when the data is in the garbage (or when format/minimumdata is wrong)
  if ( !('origin' in e) || sseOrigin.indexOf(e.origin)<0 ) { console.log('Unknown SSE origin send message... Origin was '+e.origin); return false; }
  if ( !('data' in e) ) return false;
  var jd = JSON.parse(e.data);
  for (i = minimumData.length - 1; i >= 0; --i) if ( !(minimumData[i] in jd) ) return false;
  if ( garbage.indexOf(e.data) > -1 ) return false;
  if ( garbage.length > 5 ) garbage.shift();
  garbage.push(e.data);
  return jd;
}
function cseGetStatusname(id) {
  return (id in cseStatusnames) ? cseStatusnames[id] : 'status '+id;
}
function cseGetTypename(id) {
  return (id in cseTypenames) ? cseTypenames[id] : 'type '+id;
}
function cseGetIconname(type,status) {
  return type=='T' ? cseGetStatusname(status) : cseGetTypename(type);
}
function cseIsset(jd,prop,d,id,diff=false) {
  if ( !(prop in jd) ) return false;
  if ( !d.getElementById(id) ) return false;
  if ( diff && jd[prop].toString()==d.getElementById(id).innerHTML ) return false;
  return true;
}

// Update page content

function cseUpdate(jd,light=false) {

  if ( !('t' in jd) ) return;
  if ( ('a' in jd) && jd.a==1) return;
  var idrow = 't1-tr-'+jd.t;

  if ( cseIsset(jd,'numid',doc,'t'+jd.t+'-c-numid',true) ) { $('#t'+jd.t+'-c-numid').html(jd.numid); if (light) $('#t'+jd.t+'-c-numid').effect('highlight', {}, 3000); }
  if ( cseIsset(jd,'title',doc,idrow) ) { $('#'+idrow+' > .c-title > a').html(jd.title); $('#'+idrow+' > .c-title > a').attr('href','qti_item.php?t='+jd.t); if (light) $('#'+idrow+' > .c-title').effect('highlight', {}, 3000); }
  if ( cseIsset(jd,'replies',doc,'t'+jd.t+'-replies',true) )
  {
    var b = true;
    if ( jd.replies=='+1' || jd.replies=='-1' )
    {
      var r = $('#t'+jd.t+'-replies') ? parseInt($('#t'+jd.t+'-replies').html()) : 0;
      if ( cseIsset(jd,'lastpostid',doc,'t'+jd.t+'-lastpostid',true) ) { if ( jd.replies=='+1' ) { ++r; } else { --r; if (r<0) r=0; } } else { b=false; }
      jd.replies=r;
    }
    $('#t'+jd.t+'-replies').html(jd.replies); if (light && b) $('#'+idrow+' > .c-replies').effect('highlight', {}, 3000);
  }
  if ( cseIsset(jd,'firstpostid',doc,'t'+jd.t+'-firstpostid',true) )
  {
    if ( ('firstpostdate' in jd) && ('firstpostuser' in jd) && ('firstpostname' in jd) )
    {
      if (light) $('#'+idrow+' > .c-firstpostname').effect('highlight', {}, 3000);
      $('#t'+jd.t+'-firstpostdate').html(jd.firstpostdate);
      $('#t'+jd.t+'-firstpostname').html(jd.firstpostname);
      $('#t'+jd.t+'-firstpostname').attr('href','qti_user.php?id='+jd.firstpostuser);
    }
  }
  if ( cseIsset(jd,'lastpostid',doc,'t'+jd.t+'-lastpostid',true) )
  {
    if ( ('lastpostdate' in jd) && ('lastpostuser' in jd) && ('lastpostname' in jd) )
    {
      if (light) $('#'+idrow+' > .c-lastpostdate').effect('highlight', {}, 3000);
      $('#t'+jd.t+'-lastpostid').html(jd.lastpostid);
      $('#t'+jd.t+'-lastpostico').attr('href','qti_item.php?t='+jd.t+"#p"+jd.lastpostid);
      $('#t'+jd.t+'-lastpostdate').html(jd.lastpostdate);
      $('#t'+jd.t+'-lastpostname').html(jd.lastpostname);
      $('#t'+jd.t+'-lastpostname').attr('href','qti_user.php?id='+jd.lastpostuser);
    }
  }
  if ( cseIsset(jd,'status',doc,idrow) )
  {
    if ( $('#'+idrow+' > .c-status') )
    {
      var nameN = cseGetStatusname(jd.status);
      var nameO = $('#'+idrow+' > .c-status > span').html();
      if ( nameN!=nameO )
      {
        if (light) $('#'+idrow+' > .c-status').effect('highlight', {}, 3000);
        $('#'+idrow+' > .c-status > span').html(nameN);
        if ( ('statusdate' in jd) ) $('#'+idrow+' > .c-status > span').attr('title',jd.statusdate);
      }
    }
  }

  if ( cseIsset(jd,'type',doc,'t'+jd.t+'-itemicon') )
  {
    if ( !('status' in jd) ) jd.status='A';
    jd.imgtitle = cseGetIconname(jd.type,jd.status);
    var b = qtUpdateItemIcon(jd); if (light && b) $('#'+idrow+' > .c-icon').effect('highlight', {}, 3000);
  }

  if ( cseIsset(jd,'actorname',doc,'t'+jd.t+'-actor'),true )
  {
    if ( !('actorid' in jd) ) jd.actorid = 1;
    if (light) $('#'+idrow+' > .c-actor').effect('highlight', {}, 3000);
    $('#t'+jd.t+'-actor').attr('href','qti_user.php?id='+jd.actorid);
    $('#t'+jd.t+'-actor').html(jd.actorname);
  }

  if ( cseIsset(jd,'imgsrc',doc,'t'+jd.t+'-itemicon') )
  {
    if ( !('type' in jd) ) jd.type='T';
    if ( !('status' in jd) ) jd.status='A';
    jd.imgtitle = cseGetIconname(jd.type,jd.status);
    var b = qtUpdateItemIcon(jd); if (light && b) $('#'+idrow+' > .c-icon').effect('highlight', {}, 3000);
  }

  if ( cseIsset(jd,'prefixsrc',doc,'t'+jd.t+'-c-prefix') )
  {
    doc.getElementById('t'+jd.t+'-c-prefix').innerHTML = (jd.prefixsrc=='' ? '&nbsp;' : '<img src="'+jd.prefixsrc+'"/>');
    if (light) $('#t'+jd.t+'-c-prefix').effect('highlight', {}, 3000);
  }

  if ( ('stamp' in jd) )
  {
    if (jd.stamp=='' ) { $('#'+idrow+' > .c-title > span.news').remove(); } else { $('#'+idrow+' > .c-title').prepend('<span class="news">'+jd.stamp+'</span>'); }
  }
}

function cseInsert(tableid,jd) {

  if ( cseMaxRows<1 || cseMaxRows>5 ) cseMaxRows=2;
  var t1 = doc.getElementById(tableid);
  if ( t1==null ) return;
  if ( cseShowZ==0 && ('status' in jd) && jd.status=='Z' ) return; // Skip z-status message when client hides Z (closed)
  if ( cseNewRow==cseMaxRows ) { t1.deleteRow(cseMaxRows-1); --cseNewRow; }

  var row1 = t1.rows[t1.rows.length-1];
  var row1topicid = row1.id.replace(tableid+'-tr-','');
  var row0 = t1.insertRow(0); row0.id = tableid+'-tr-'+jd.t;
  ++cseNewRow;
  for(i=0;i<row1.cells.length;i++)
  {
    row0.insertCell(i);
    row0.cells[i].className = row1.cells[i].className;
    row0.cells[i].innerHTML = row1.cells[i].innerHTML.replaceAll('id="t'+row1topicid+'-','id="t'+jd.t+'-');
    var sep = row1.cells[i].id.indexOf('-');
    row0.cells[i].id = 't'+jd.t + row1.cells[i].id.substr(sep);
  }
  if ( ('a' in jd) ) jd.a=0;
  cseClearRow(tableid,jd.t);
  cseUpdate(jd);
  $('#'+tableid+'-tr-'+jd.t).css('background-color','#FFFFAA');
}

function cseClearRow(tableid,id) {
  $('#t'+id+'-itemicon').attr('src','bin/js/qti_cse_items.gif');
  $('#t'+id+'-itemicon').attr('title','');
  $('#'+tableid+'-tr-'+id+' > .c-title').html('<a class="topic" href="javascript:void(0);">unknown title</a>');
  $('#'+tableid+'-tr-'+id+' > .c-numid').html('000');
  $('#'+tableid+'-tr-'+id+' > .c-replies').html('0');
  $('#'+tableid+'-tr-'+id+' > .c-sectiontitle').html('&nbsp;');
  $('#'+tableid+'-tr-'+id+' > .c-prefix').html('&nbsp;');
  $('#t'+id+'-firstpostid').html('-1');
  $('#t'+id+'-lastpostid').html('-1');
  $('#t'+id+'-firstpostdate').html('now');
  $('#t'+id+'-firstpostname').html('visitor'); $('#t'+id+'-firstpostname').attr('href','javascript:void(0);');
  $('#t'+id+'-lastpostico').attr('href','javascript:void(0);');
  $('#t'+id+'-lastpostdate').html('now');
  $('#t'+id+'-lastpostname').html('visitor'); $('#t'+id+'-lastpostname').attr('href','javascript:void(0);');
}
