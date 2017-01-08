<?php
define("SSE_CONNECT",10000);
define("SSE_ORIGIN","http://localhost");
define("SSE_MAX_ROWS",2);
define("SSE_TIEMOUT",30);
define("SSE_LATENCY",10000);
// -----------------
// SSE (server-sent events) allows automatic update of the page content. To disable this set SSE_CONNECT to 0
// -----------------

// SSE_CONNECT: To enable SSE set a value in milliseconds (recommended 10000). This is the delay before the client page re-connect server
// SSE_ORIGIN: Domain of the script sending the SSE events.
// SSE_MAX_ROWS: Number of recent topics that can be added in the list of topics. When more topics arrive, the oldest is replaced. Recommended 2, maximum 5.
// SSE_TIMEOUT: Server message duration in seconds (recommended 30).
// SSE_LATENCY: This is the delay in miliseconds (recommended 10000) before starting the sse process and updating the page content.

// About SSE polyfill: For legacy browser that does not support SSE, an auto-refresh is used (120 seconds). Setting SSE_CONNECT to 0 will also disable this auto-resh.
// When SSE is enabled following settings can be defined.

// Note on SSE_ORIGIN
// SSE_ORIGIN is used as security control to reject messages coming from other servers. It is possible to enter here several origins (with semicolumn)
// If the qti_sse.php script (i.e. the server script) is on the same server as the pages, it must be http://localhost.
// To identify the correct origin, put temporary http://x here, then check the javascript consol log on the index page. The origin will be reported after 10 secondes.