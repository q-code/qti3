var sseSource = new EventSource('bin/qti_j_sse.php');
var cseGarbageTopic = new Array();
var cseNewRow = 0;

// Event handlers

sseSource.addEventListener('topic', function(e) {

  var jd = cseReadSse(e,cseGarbageTopic,['s','t']); if ( !jd ) return;
  if ( !doc.getElementById('t'+jd.t) )  return;

  console.log('SSE send topic event with data: '+e.data);
  qtUpdateItemIcon(jd);
  qtUpdateItemIcon(jd,'-itemicon-preview');
  $('td.date:has(#t'+jd.t+')').effect('highlight', {}, 3000);

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

// Read and control sse event

function cseReadSse(e,garbage,minimumData=[])
{
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