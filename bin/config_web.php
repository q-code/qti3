<?php
// Here are the configrations that can be changed by the webmaster
// They are constants about layout, format, repository location, or web-technologie than can be enabled or disabled
// Application uses PHP5 (5.4 or next)

// -----------------
// Interface constants
// -----------------

define('QTI_BACKBUTTON', '<i class="fa fa-chevron-left"></i>');  // use FALSE to hide backbutton. ex: &#9665; or <i class="fa fa-chevron-left"></i>
define('QTI_GOTOBUTTON', '<i class="fa fa-chevron-circle-right fa-lg"></i>'); // use to go to the last message. ex: &rsaquo; or <i class="fa fa-chevron-circle-right fa-lg"></i>
define('QTI_CRUMBTRAIL', ' &middot; '); // crumbtrail separator (dont forget spaces)
define('QTI_MENUSEPARATOR', ' &middot; '); // menu separator in the FOOTER of the pages (dont forget spaces)
define('QTI_NEWS_STAMP', false);     // Shows 'News:' before ticket of type news. 

define('QTI_DFLT_VIEWMODE', 'n');    // default view mode: n=normal view, c=compact view
define('QTI_SHOW_VIEWMODE', true);   // allow user changing view mode
define('QTI_SHOW_TIME', true);       // show time in the bottom bar
define('QTI_SHOW_MEMBERLIST', true); // show memberlist in the menu
define('QTI_SHOW_MODERATOR', true);  // show moderator in the bottom bar
define('QTI_SHOW_GOTOLIST', true);   // show selection-list in the bottom bar
define('QTI_SHOW_DOMAIN', false);    // show domain + section name in the crumb trail bar
define('QTI_NOTIFY_NEWACTOR', true); // notify new actor when topic actor changes (this option is applicable only in sections having notification activated!)
define('QTI_NOTIFY_OLDACTOR', true); // notify old actor when topic actor changes (this option is applicable only in sections having notification activated!)
define('QTI_CONVERT_AMP', false);    // save &amp; instead of &. Use TRUE will make html &#0000; symbols NOT working in the messages.
define('QTI_SIMPLESEARCH', true);    // Simple search by default. Use false to directly open advanced search.
define('QTI_BBC', true);             // Allow using bbc codes in text messages.
define('QTI_DIR_PIC', 'avatar/');    // Storage location for uploaded userphoto, if allowed (with final '/')
define('QTI_DIR_DOC', 'upload/');    // Storage location for uploaded files attachement, if allowed (with final '/')
define('QTI_MY_REPLY', '<i class="fa fa-registered"></i>'); // In the ticket list, symbol indicating: i replied to the ticket. Using False will DISABLE the search and the symbol.
define('QTI_LIST_TAG', true);        // display a quick search link for the tags in section list.
define('QTI_JAVA_MAIL', false);      // Protect e-mail by a javascript
define('QTI_WEEKSTART', 1);          // Start of the week (use code 1=monday,...,7=sunday)
define('QTI_STAFFEDITSTAFF',true);   // Staff member can edit posts issued by an other staff member
define('QTI_STAFFEDITADMIN',true);   // Staff member can edit posts issued by an administrator
define('QTI_STAFFEDITPROFILES',true);// Staff members can edit user/staff profiles (not admin profile)
define('QTI_CHANGE_USERNAME',true);  // User can change username (if false, only admin can change usernames)

// -----------------
// MEMCACHE
// -----------------
// Memcache allow storing frequently used values in server-cache (instead of runnning database sql requests)
// If memcache library is not available on your server use FALSE as host.
define('MEMCACHE_HOST', 'localhost'); // Memcache server hostname (ex: 'localhost' or 'tcp://10.10.0.5'). Use FALSE to disable memcache.
define('MEMCACHE_PORT', 11211);       // Memcache server port (integer). Default port is 11211.
define('MEMCACHE_FAILOVER', false);   // Use session as failover for memcache values. (use this only if session and memcache are on separate servers)
define('MEMCACHE_LIVETIME', 600);     // Livetime (in seconds) of a memcache. Recommanded: 600

// -----------------
// JQUERY CDN
// -----------------
// Content Delivery Network for jQuery and jQuery-UI.
// Using a CDN will increase performances.
// Possible CDN are: Google, Microsoft, jQuery-Media-Temple.
// You can also decide to use your local copy (in the bin/js/ directory) to avoid using a CDN.
define('JQUERY_CDN', 'http://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js');
  // define('JQUERY_CDN', 'http://ajax.aspnetcdn.com/ajax/jQuery/jquery-2.1.4.min.js');
  // define('JQUERY_CDN', 'http://code.jquery.com/jquery-2.1.4.min.js');
  // define('JQUERY_CDN', 'bin/js/jquery.min.js');
define('JQUERYUI_CDN', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js');
  // define('JQUERYUI_CDN', 'http://ajax.aspnetcdn.com/ajax/jquery.ui/1.11.4/jquery-ui.min.js');
  // define('JQUERYUI_CDN', 'http://code.jquery.com/ui/1.11.4/jquery-ui.js');
  // define('JQUERYUI_CDN', 'bin/js/jquery-ui.min.js');
define('JQUERYUI_CSS_CDN', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css');
  // define('JQUERYUI_CSS_CDN', 'http://ajax.aspnetcdn.com/ajax/jquery.ui/1.11.4/themes/smoothness/jquery-ui.min.css');
  // define('JQUERYUI_CSS_CDN', 'http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
  // define('JQUERYUI_CSS_CDN', 'bin/css/jquery-ui-min.css');

// -----------------
// ICONS CDN
// -----------------
// Content Delivery Network for vetor icons css
// Possible CDN are: Maxcdn.
// You can also decide to use your local copy (in the bin/css/ directory) to avoid using a CDN.
define('WEBICONS_CDN', 'bin/css/font-awesome-4.4.0/css/font-awesome.min.css');
  //define('WEBICONS_CDN', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css');

// -----------------
// URL REWRITE
// -----------------
define('QTI_URLREWRITE',false);
// URL rewriting (for expert only):
// Rewriting url requires that your server is configured with following rule for the application folder: RewriteRule ^(.+)\.html(.*) qti_$1.php$2 [L]
// This can NOT be activated if your application folder contains .html pages (they will not be accessible anymore when urlrewriting is acticated)