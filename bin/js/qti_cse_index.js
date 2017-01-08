var sseSource = new EventSource('bin/qti_j_sse.php');
var cseGarbageSection = new Array();
var cseGarbageTopic = new Array();
var cseGarbageReply = new Array();

sseSource.addEventListener('topic', function(e) {
  var jd = cseReadSse(e,cseGarbageTopic,['t']); if ( !jd ) return;
  console.log('SSE type topic: '+e.data);
  cseUpdate(jd,true);
}, false);

sseSource.addEventListener('section', function(e) {
  var jd = cseReadSse(e,cseGarbageTopic,['s']); if ( !jd ) return;
  console.log('SSE type section: '+e.data);
  if ( !('origin' in e) || sseOrigin.indexOf(e.origin)<0 ) { console.log('Unknown SSE origin send message... Origin was '+e.origin); return; }
  if ( !('data' in e) || cseGarbageSection.indexOf(e.data) > -1 ) return;
  if ( jd.s=='reset' ) { window.setTimeout(function(){ location.reload(true); },10000); return; }
  if ( !doc.getElementById('s'+jd.s+'-row') ) return;
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
  if ( !('origin' in e) || e.origin!=sseOrigin ) { console.log('Unknown SSE origin send message... Origin was '+e.origin); return false; }
  if ( !('data' in e) ) return false;
  var jd = JSON.parse(e.data);
  for (i = minimumData.length - 1; i >= 0; --i) { if ( !(minimumData[i] in jd) ) return false; }
  if ( garbage.indexOf(e.data) > -1 ) return false;
  if ( garbage.length > 5 ) garbage.shift();
  garbage.push(e.data);
  return jd;
}

function cseIsset(jd,prop,d,id,diff=false) {
  if ( !(prop in jd) ) return false;
  if ( !d.getElementById(id) ) return false;
  if ( diff && jd[prop].toString()==d.getElementById(id).innerHTML ) return false;
  return true;
}

// Update page content

function cseUpdate(jd,light=false) {
  if ( !('s' in jd) || jd.s<0 ) jd.s='null';
  var id = 's'+jd.s;
  if ( cseIsset(jd,'sumitems',doc,id+'-items',true) ) { doc.getElementById(id+'-items').innerHTML = jd.sumitems; if (light) $('#'+id+'-items').effect('highlight', {}, 3000); }
  if ( cseIsset(jd,'sumreplies',doc,id+'-replies',true) ) { doc.getElementById(id+'-replies').innerHTML = jd.sumreplies; if (light) $('#'+id+'-replies').effect('highlight', {}, 3000); }
  if ( cseIsset(jd,'lastpostdate',doc,id+'-issue') )
  {
    if (light) $('#'+id+'-issue').effect('highlight', {}, 3000);
    if ( cseIsset(jd,'lastpostdate',doc,id+'-lastpostdate') ) doc.getElementById(id+'-lastpostdate').innerHTML = jd.lastpostdate;
    if ( cseIsset(jd,'lastposttopic',doc,id+'-lastposttopic') ) doc.getElementById(id+'-lastposttopic').href = 'qti_item.php?t='+jd.lastposttopic+'#p'+jd.lastpostid;
    if ( cseIsset(jd,'lastpostuser',doc,id+'-lastpostuser') ) doc.getElementById(id+'-lastpostuser').href = 'qti_user.php?id'+jd.lastpostuser;
    if ( cseIsset(jd,'lastpostname',doc,id+'-lastpostname') ) doc.getElementById(id+'-lastpostname').innerHTML = jd.lastpostname;
  }
  // MyLastTicket
  if ( cseIsset(jd,'t',doc,'t'+jd.t+'-itemicon') ) { b = qtUpdateItemIcon(jd); if (light && b) $('#mylastitem > p.title').effect('highlight', {}, 3000);  }
  return;
}