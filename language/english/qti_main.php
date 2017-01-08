<?php

// If your board accept several languages (latin origin), the charset 'windows-1252' is recommended in order to render all accents correctly.
// If your board accept english only, you can use the charset 'utf-8'.
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'en');
if ( !defined('QT_HTML_SEPARATOR') ) define ('QT_HTML_SEPARATOR', ';');

// It is recommended to always use capital on first letter in the translation, script changes to lower case if necessary.
// The character doublequote ["] is FORBIDDEN (reserved for html tags)
// To make a single quote use slashe [\']

$L['Y']='Yes';
$L['N']='No';
$L['Ok']='Ok';
$L['Cancel']='Cancel';

// -------------
// TOP LEVEL VOCABULARY
// -------------
// Use the top level vocabulary to give the most appropriate name
// for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request, Faq,...

$L['Item']='Ticket'; $L['Items']='Tickets'; $L['item']='ticket'; $L['items']='tickets';
$L['Domain']='Domain'; $L['Domains']='Domains';
$L['Section']='Section'; $L['Sections']='Sections';
$L['User']='User'; $L['Users']='Users';
$L['Status']='Status'; $L['Statuses']='Statuses';
$L['Message']='Message'; $L['Messages']='Messages';
$L['Reply']='Reply'; $L['Replys']='Replies';
$L['News']='News'; $L['Newss']='News'; // In other languages: News=One news, Newss=Several news
$L['Inspection']='Inspection'; $L['Inspections']='Inspections';
$L['Forward']='Forward'; $L['Forwards']='Forwards';

// User and role

$L['Actor']='Actor';
$L['Author']='Author';
$L['Coordinator']='Coordinator';
$L['Deleted_by']='Deleted by';
$L['Handled_by']='Handled by';
$L['Modified_by']='Modified by';
$L['Notified_user']='Notified user';
$L['Notify_also']='Notify also';
$L['Role']='Role';
$L['Role_A']='Administrator'; $L['Role_As']='Administrators';
$L['Role_M']='Staff member';  $L['Role_Ms']='Staff members';
$L['Role_U']='Member';        $L['Role_Us']='Members';
$L['Role_V']='Visitor';       $L['Role_Vs']='Visitors';
$L['Top_participants']='Top participants';
$L['Username']='Username';

// Common

$L['Action']='Action';
$L['Add']='Add';
$L['Add_user']='New user';
$L['Advanced_reply']='Preview...';
$L['All']='All';
$L['and']='and'; // lowercase
$L['Attachment']='Attachment';
$L['Avatar']='Photo';
$L['By']='By';
$L['Change']='Change';
$L['Change_name']='Change username';
$L['Change_status']='Change status...';
$L['Change_type']='Change type...';
$L['Changed']='Changed';
$L['Charts']='Charts';
$L['Close']='Close';
$L['Closed']='Closed';
$L['Column']='Column';
$L['Contact']='Contact';
$L['Containing']='Containing';
$L['Continue']='Continue';
$L['Coord']='Coordinates';
$L['Created']='Created';
$L['Csv']='Export'; $L['H_Csv']='Download to spreadsheet';
$L['Date']='Date';
$L['Dates']='Dates';
$L['Day']='Day';
$L['Days']='Days';
$L['Delete']='Delete';
$L['Delete_tags']='Delete (click a word or type * to delete all)';
$L['Destination']='Destination';
$L['Details']='Details';
$L['Disable']='Disable';
$L['Display_at']='Display at date';
$L['Drop_attachment']='Drop&nbsp;attachment';
$L['Edit']='Edit';
$L['Email']='E-mail'; $L['No_Email']='No e-mail';
$L['Exit']='Exit';
$L['First']='First';
$L['Found_from']='Found from';
$L['Goodbye']='You are disconnected... Goodbye';
$L['Goto']='Jump to...';
$L['H_Website']='Url of your website (with http://)';
$L['H_Wisheddate']='desired delivery date';
$L['Help']='Help';
$L['Hidden']='Hidden';
$L['I_wrote']='I wrote';
$L['Information']='Information';
$L['Items_per_month']='Tickets per month';
$L['Items_per_month_cumul']='Cumulative tickets per month';
$L['Joined']='Joined';
$L['Last']='Last';
$L['latlon']='(lat,lon)';
$L['Legend']='Legend';
$L['Location']='Location';
$L['Maximum']='Maximum';
$L['Me']='Me';
$L['Message_deleted']='Message deleted...';
$L['Minimum']='Minimum';
$L['Missing']='Missing information';
$L['Modified']='Modified';
$L['Month']='Month';
$L['More']='More';
$L['Move']='Move';
$L['Name']='Name';
$L['News_stamp']='News: '; //put a space after the stamp
$L['Next']='Next';
$L['None']='None';
$L['Notification']='Notification';
$L['Open']='Open';
$L['Options']='Options';
$L['or']='or'; // lowercase
$L['Other']='Other'; $L['Others']='Others';
$L['Page']='Page';   $L['Pages']='Pages';
$L['Parameters']='Parameters';
$L['Password']='Password';
$L['Phone']='Phone';
$L['Picture']='Picture';
$L['Preview']='Preview';
$L['Previous']='Previous';
$L['Privacy']='Privacy';
$L['Reason']='Reason';
$L['Ref']='Ref.';
$L['Remove']='Remove';
$L['Result']='Result';
$L['Results']='Results';
$L['Save']='Save';
$L['Score']='Score';
$L['Seconds']='Seconds';
$L['Security']='Security';
$L['Selected_from']='selected from';
$L['Send']='Send';
$L['Send_on_behalf']='Send on behalf of';
$L['Settings']='Settings';
$L['Show']='Show';
$L['Signature']='Signature';
$L['Smiley']='Prefix icon';
$L['Statistics']='Statistics';
$L['Tag']='Category';
$L['Tags']='Categories';
$L['Time']='Time';
$L['Title']='Title';
$L['Total']='Total';
$L['Type']='Type';
$L['Update']='Update';
$L['Views']='Views';
$L['Website']='Website'; $L['No_Website']='No website';
$L['Welcome']='Welcome';
$L['Welcome_not']='I\'m not %s !';
$L['Welcome_to']='We welcome a new member, ';
$L['Wisheddate']='Wished&nbsp;date';
$L['Year']='Year';
$L['yyyy-mm-dd']='yyyy-mm-dd';

// Menu

$L['Administration']='Administration';
$L['FAQ']='FAQ';
$L['Legal']='Legal notices';
$L['Login']='Sign in';
$L['Logout']='Sign out';
$L['Memberlist']='Memberlist';
$L['Profile']='Profile';
$L['Register']='Register';
$L['Search']='Search';

// Section // use &nbsp; to avoid double ligne buttons

$L['Allow_emails']='Allow sending notification emails';
$L['Change_actor']='Change actor';
$L['Close_item']='Close the ticket';
$L['Close_my_item']='I close my ticket';
$L['Edit_start']='Start editing';
$L['Edit_stop']='Stop editing';
$L['First_message']='First&nbsp;message';
$L['Goto_message']='View last message';
$L['Insert_forward_reply']='Add forward info in replies';
$L['Item_closed']='Ticket&nbsp;closed';
$L['Item_closed_hide']='Hide closed tickets';
$L['Item_closed_show']='Show closed tickets';
$L['Item_forwarded']='Ticket has been forwarded to %s.';
$L['Item_handled']='Ticket handled';
$L['Item_insp_hide']='Hide inspections';
$L['Item_insp_show']='Show inspections';
$L['Item_news_hide']='Hide news';
$L['Item_news_show']='Show news';
$L['Item_show_all']='Show all sections at once';
$L['Item_show_this']='Show this section only';
$L['Items_deleted']='Deleted tickets';
$L['Items_handled']='Tickets handled';
$L['Last_message']='Last&nbsp;message';
$L['Move_follow']='Renumber following the destination section';
$L['Move_keep']='Keep source reference';
$L['Move_reset']='Reset reference to zero';
$L['Move_to']='Move to';
$L['My_last_item']='My last ticket';
$L['My_preferences']='My preferences';
$L['News_on_top']='active news on top';
$L['New_item']='New ticket';
$L['Post_reply']='Reply';
$L['Previous_replies']='Previous replies';
$L['Quick_reply']='Quick reply';
$L['Quote']='Quote';
$L['Show_news_on_top']='Show news on top';
$L['You_reply']='I replied';

// Stats

$L['General_site']='General site';
$L['Board_start_date']='Board start date';

// Search

$L['Advanced_search']='Advanced search';
$L['All_my_items']='All&nbsp;my&nbsp;tickets';
$L['All_news']='All&nbsp;news';
$L['Any_status']='Any status';
$L['Any_time']='Any time';
$L['At_least_0']='With or without reply';
$L['At_least_1']='At least 1 reply';
$L['At_least_2']='At least 2 replies';
$L['At_least_3']='At least 3 replies';
$L['H_Advanced']='(type a '.$L['item'].' number or a keyword)';
$L['H_Reference']='(type the numeric part only)';
$L['H_Tag_input']='You can enter several words separated by '.QT_HTML_SEPARATOR.' (ex.: t1'.QT_HTML_SEPARATOR.'t2 means tickets containing "t1" or "t2").';
$L['In_all_sections']='In all sections';
$L['In_title_only']='In title only';
$L['Keywords']='Keyword(s)';
$L['Only_in_section']='Only in section';
$L['Recent_messages']='Recent&nbsp;'.$L['items'];
$L['Search_by_date']='Search by date';
$L['Search_by_key']='Search by keyword(s)';
$L['Search_by_ref']='Search reference number';
$L['Search_by_status']='Search by status';
$L['Search_by_tag']='Search by category';
$L['Search_by_words']='Search each word separately';
$L['Search_criteria']='Search criteria';
$L['Search_exact_words']='Search exact words';
$L['Search_option']='Search option';
$L['Search_result']='Search result';
$L['Search_results']=$L['Items'].' (%s)';
$L['Search_results_actor']='%1$s '.$L['items'].' handled by %2$s';
$L['Search_results_keyword']='%1$s '.$L['items'].' containing %2$s';
$L['Search_results_last']='%s '.$L['items'].' last week';
$L['Search_results_news']=$L['News'].' (%s)';
$L['Search_results_ref']='%1$s '.$L['items'].' with ref. %2$s';
$L['Search_results_tags']='%1$s '.$L['items'].' in category %2$s';
$L['Search_results_user']='%1$s '.$L['items'].' issued by %2$s';
$L['Show_only_tag']='Show only tickets of category';
$L['Tag_only']='only category'; // must be in lowercase
$L['This_month']='This month';
$L['This_week']='This week';
$L['This_year']='This year';
$L['Too_many_keys']='Too many keys';
$L['With_tag']= 'Category';

// Inspection

$L['I_aggregation']='Aggregation method';
$L['I_closed']='Inspection closed';
$L['I_level']='Response level';
$L['I_r_bad']='Bad';
$L['I_r_good']='Good';
$L['I_r_high']='High';
$L['I_r_low']='Low';
$L['I_r_medium']='Medium';
$L['I_r_no']='No';
$L['I_r_veryhigh']='Very high';
$L['I_r_verylow']='Very low';
$L['I_r_yes']='Yes';
$L['I_running']='Inspection running';
$L['I_v_first']='First value';
$L['I_v_last']='Last value';
$L['I_v_max']='Maximum';
$L['I_v_mean']='Mean value';
$L['I_v_min']='Minimum';
$L['Use_star_to_delete_all']='Use * to remove all';

// Privacy

$L['Privacy_0']='Not visible';
$L['Privacy_1']='Visible for members only';
$L['Privacy_2']='Visible for all';

// Restrictions

$L['Ban']='Locked';
$L['Ban_user']='Lock user';

// Errors

$L['Already_used']='Already used';
$L['E_char_min']='Minimum %d characters';
$L['E_char_max']='Maximum %d characters';
$L['E_editing']='Data not yet saved. Quit without saving?';
$L['E_file_size']='File is too large';
$L['Invalid']='invalid';
$L['E_javamail']='Protection: java required to see e-mail addresses';
$L['E_line_max']='(maximum %d lines)';
$L['E_missing_http']='The url must start with http:// or https://';
$L['E_no_public_section']='The board does not contain any public section.<br /><br />To access private sections, please log-in.';
$L['E_no_title']='Please give a title to this new ticket';
$L['E_no_item']='No '.$L['item'].' found';
$L['E_no_visible_section']='The board does not contain section visible for you.';
$L['E_ref_search']='Decimal number (or comma) not valid. Use quotes if you want to search a number as keyword.';
$L['E_section_closed']='Section is closed';
$L['E_text']='Problem with your text.';
$L['E_too_long']='Message too long';
$L['E_too_much']='Too much posts today...<br /><br />For security reasons, the number of posts allowed is limited. Try again tomorrow. Thanks.';
$L['E_item_private']='(or tickets are private)';
$L['E_wait']='Please wait a few seconds';

$L['No_description']='No description';
$L['No_result']='No result';
$L['Already_in_section']='Already in the section';
$L['Try_without_options']='Try without options';
$L['Tag_not_used']='Category not yet used';

// Success

$L['S_update']='Update successful...';
$L['S_delete']='Delete completed...';
$L['S_insert']='Creation successful...';
$L['S_save']='Successfully saved...';
$L['S_message_saved']='Message saved...<br />Thank you';

// Dates

$L['dateMMM']=array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$L['dateMM'] =array(1=>'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
$L['dateDD'] =array(1=>'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
$L['dateD']  =array(1=>'M','T','W','T','F','S','S');
$L['dateSQL']=array(
  'January'  => 'January',
  'February' => 'February',
  'March'    => 'March',
  'April'    => 'April',
  'May'      => 'May',
  'June'     => 'June',
  'July'     => 'July',
  'August'   => 'August',
  'September'=> 'September',
  'October'  => 'October',
  'November' => 'November',
  'December' => 'December',
  'Monday'   => 'Monday',
  'Tuesday'  => 'Tuesday',
  'Wednesday'=> 'Wednesday',
  'Thursday' => 'Thursday',
  'Friday'   => 'Friday',
  'Saturday' => 'Saturday',
  'Sunday'   => 'Sunday',
  'Today'=>'Today',
  'Yesterday'=> 'Yesterday',
  'Jan'=>'Jan',
  'Feb'=>'Feb',
  'Mar'=>'Mar',
  'Apr'=>'Apr',
  'May'=>'May',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Aug',
  'Sep'=>'Sep',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Dec',
  'Mon'=>'Mon',
  'Tue'=>'Tue',
  'Wed'=>'Wed',
  'Thu'=>'Thu',
  'Fri'=>'Fri',
  'Sat'=>'Sat',
  'Sun'=>'Sun');