<?php

// If your board accept several languages (latin origin), the charset 'windows-1252' is recommended in order to render all accents correctly.
// If your board accept english only, you can use the charset 'utf-8'.
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'nl');
if ( !defined('QT_HTML_SEPARATOR') ) define ('QT_HTML_SEPARATOR', ';');

// Is is recommended to always use capital on first letter in the translation, script changes to lower case if necessary.
// The character doublequote ["] is FORBIDDEN (reserved for html tags)
// To make a single quote use slash [\']

$L['Y']='Ja';
$L['N']='Nee';
$L['Ok']='Ok';
$L['Cancel']='Annuleren';

// -------------
// TOP LEVEL VOCABULARY
// -------------
// Use the top level vocabulary to give the most appropriate name
// for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request, Faq,...

$L['Item']='Ticket'; $L['Items']='Tickets'; $L['item']='ticket'; $L['items']='tickets';
$L['Domain']='Domein'; $L['Domains']='Domeinen';
$L['Section']='Sectie'; $L['Sections']='Secties';
$L['User']='Gebruiker'; $L['Users']='Gebruikers';
$L['Status']='Statuut'; $L['Statuses']='Statuten';
$L['Message']='Bericht'; $L['Messages']='Berichten';
$L['Reply']='Antwoord'; $L['Replys']='Antwoorden';
$L['News']='Info'; $L['Newss']='Infos'; // In other languages: News=One news, Newss=Several news
$L['Inspection']='Inspection'; $L['Inspections']='Inspections';
$L['Forward']='Verstuurd bericht'; $L['Forwards']='verstuurd berichten';

// User and role

$L['Actor']='Acteur';
$L['Author']='Auteur';
$L['Coordinator']='Moderator';
$L['Deleted_by']='Geschrapt door';
$L['Handled_by']='Behandeld door';
$L['Modified_by']='Bewerkt door';
$L['Notified_user']='Gebruiker op de hoogte';
$L['Notify_also']='Op de hoogte brengen';
$L['Role']='Rang';
$L['Role_A']='Administrateur'; $L['Role_As']='Administrateurs';
$L['Role_M']='Staff';          $L['Role_Ms']='Staffs';
$L['Role_U']='Lid';            $L['Role_Us']='Leden';
$L['Role_V']='Bezoeker';       $L['Role_Vs']='Bezoekers';
$L['Top_participants']='Top deelnemers';
$L['Username']='Gebruikersnaam';

// Common

$L['Action']='Actie';
$L['Add']='Toevoegen';
$L['Add_user']='Nieuw gebruiker';
$L['Advanced_reply']='Voorproef...';
$L['All']='Alles';
$L['and']='en'; // lowercase
$L['Attachment']='Attachment';
$L['Avatar']='Foto';
$L['By']='Per';
$L['Change']='Bewerk';
$L['Change_name']='Gebruikersnaam veranderen...';
$L['Change_status']='Statuut veranderen...';
$L['Change_type']='Type veranderen...';
$L['Changed']='Bewerkt';
$L['Charts']='Grafieken';
$L['Close']='Afsluiten';
$L['Closed']='Gesloten';
$L['Column']='Kolom';
$L['Contact']='Contact';
$L['Containing']='Bevat';
$L['Continue']='Voortduren';
$L['Coord']='Co&ouml;rdinaten';
$L['Created']='Gemaakt';
$L['Csv']='Export'; $L['H_Csv']='Tonen in spreadsheet';
$L['Date']='Datum';
$L['Dates']='Datum';
$L['Day']='Dag';
$L['Days']='Dagen';
$L['Delete']='Uitwissen';
$L['Delete_tags']='Verwijderen (click een woord of type * om alles te verwijderen)';
$L['Destination']='Bestemming';
$L['Details']='Details';
$L['Disable']='Uit te schakelen';
$L['Display_at']='Tonen op datum van';
$L['Drop_attachment']='Attachment wegnemen';
$L['Edit']='Bewerken';
$L['Email']='E-mail'; $L['No_Email']='Geen e-mail';
$L['Exit']='Uitrit';
$L['First']='Eerste';
$L['Found_from']='Gevonden van';
$L['Goodbye']='U bent uitgelogd... Tot ziens';
$L['Goto']='Ga naar...';
$L['H_Website']='Uw website url (met http://)';
$L['H_Wisheddate']='Gewenste leveringsdatum';
$L['Help']='Hulp';
$L['Hidden']='Verborgen';
$L['I_wrote']='Ik schreef';
$L['Information']='Informatie';
$L['Items_per_month']='Tickets per maand';
$L['Items_per_month_cumul']='Cumul tickets per maand';
$L['Joined']='Geregistreerd op';
$L['Last']='Laatste';
$L['latlon']='(lat,lon)';
$L['Legend']='Legend';
$L['Location']='Woonplaats';
$L['Maximum']='Maximum';
$L['Me']='Mij';
$L['Message_deleted']='Bericht verwijderd...';
$L['Minimum']='Minimum';
$L['Missing']='Verplicht data niet gevonden';
$L['Modified']='Verandered';
$L['Month']='Maand';
$L['More']='Meer';
$L['Move']='Verplaatsen';
$L['Name']='Naam';
$L['News_stamp']='Nieuws: ';
$L['Next']='Volgende';
$L['None']='Geen';
$L['Notification']='Nota';
$L['Open']='Openen';
$L['Options']='Opties';
$L['or']='of'; // lowercase
$L['Other']='Ander'; $L['Others']='Anderen';
$L['Page']='Pagina';  $L['Pages']='Pagina\'s';
$L['Parameters']='Parameters';
$L['Password']='Wachtwoord';
$L['Phone']='Telefoon';
$L['Picture']='Beeld';
$L['Preview']='Voorproef';
$L['Previous']='Vorige';
$L['Privacy']='Priv&eacute;-leven';
$L['Reason']='Reden';
$L['Ref']='Ref.';
$L['Remove']='Uitwissen';
$L['Result']='Resultaat';
$L['Results']='Resultaten';
$L['Save']='Saven';
$L['Score']='Score';
$L['Seconds']='Seconden';
$L['Security']='Veiligheid';
$L['Selected_from']='geselecteerd uit';
$L['Send']='Zenden';
$L['Send_on_behalf']='Zenden namens';
$L['Settings']='Instellingen';
$L['Show']='Tonen';
$L['Signature']='Onderschrift';
$L['Smiley']='Voorvoegsel';
$L['Statistics']='Statistieken';
$L['Tag']='Categorie';
$L['Tags']='Categorie&euml;n';
$L['Time']='Uren';
$L['Title']='Titel';
$L['Total']='Totaal';
$L['Type']='Type';
$L['Update']='Bijwerken';
$L['Views']='Bekeken';
$L['Website']='Website'; $L['No_Website']='Geen website';
$L['Welcome']='Welkom';
$L['Welcome_not']='Ik ben %s niet !';
$L['Welcome_to']='Welkom voor een nieuwe gebruiker, ';
$L['Wisheddate']='Oplevering';
$L['Year']='Jaar';
$L['yyyy-mm-dd']='jjjj-mm-dd';

// Menu

$L['Administration']='Administratie';
$L['FAQ']='FAQ';
$L['Legal']='Privacybeleid';
$L['Login']='Inloggen';
$L['Logout']='Uitloggen';
$L['Memberlist']='Gebruikerslijst';
$L['Profile']='Profiel';
$L['Register']='Registreer';
$L['Search']='Zoeken';

// Section // use &nbsp; to avoid double ligne buttons

$L['Allow_emails']='Laat het verzenden van e-mails';
$L['Change_actor']='Werknemer';
$L['Close_item']='Sluit dit ticket';
$L['Close_my_item']='Ik sluit mijn ticket';
$L['Edit_start']='Wijziging beginnen';
$L['Edit_stop']='Wijziging stoppen';
$L['First_message']='Eerste&nbsp;bericht';
$L['Goto_message']='Laatste&nbsp;bericht';
$L['Insert_forward_reply']='Voeg vooruit info in antwoorden';
$L['Item_closed']='Gesloten&nbsp;ticket';
$L['Item_closed_hide']='Gesloten aanvragen: verborgen';
$L['Item_closed_show']='Gesloten aanvragen: tonen';
$L['Item_forwarded']='Ticket is naar %s gestruurd.';
$L['Item_handled']='Ticket behandeld';
$L['Item_insp_hide']=$L['Inspections'].': verborgen';
$L['Item_insp_show']=$L['Inspections'].': tonen';
$L['Item_news_hide']=$L['News'].': verborgen';
$L['Item_news_show']=$L['News'].': tonen';
$L['Item_show_all']='Alle secties verzamelen';
$L['Item_show_this']='Alleen dit sectie tonen';
$L['Items_deleted']='Uitgewist tickets';
$L['Items_handled']='Tickets behandeld';
$L['Last_message']='Laatste&nbsp;bericht';
$L['Move_follow']='Volgt sectienummer';
$L['Move_keep']='Houd origineel nummer';
$L['Move_reset']='Verwijzingen naar 0';
$L['Move_to']='Verplatsen';
$L['My_last_item']='Mijn&nbsp;laatste&nbsp;ticket';
$L['My_preferences']='Mijn voorkeuren';
$L['News_on_top']='actieve nieuws op de top';
$L['New_item']='Nieuwe ticket';
$L['Post_reply']='Antwoord';
$L['Previous_replies']='Vorige berichten';
$L['Quick_reply']='Snel antwoord';
$L['Quote']='Quote';
$L['Show_news_on_top']='Nieuws op de top tonen';
$L['You_reply']='Ik antwoord';

// Stats

$L['General_site']='Algemene site';
$L['Board_start_date']='Applicatie begin datum';

// Search

$L['Advanced_search']='Geavanceerd onderzoek';
$L['All_my_items']='Alle&nbsp;mijn&nbsp;'.$L['items'];
$L['All_news']='Alle&nbsp;mededelingen';
$L['Any_status']='Alle statuut';
$L['Any_time']='Alle tijd';
$L['At_least_0']='Met of zonder antwoord';
$L['At_least_1']='Minstens een antwoord';
$L['At_least_2']='Minstens 2 antwoorden';
$L['At_least_3']='Minstens 2 antwoorden';
$L['H_Advanced']='(een tiketnummer of een woord)';
$L['H_Reference']='(typ het numerieke deel)';
$L['H_Tag_input']='Met '.QT_HTML_SEPARATOR.' kunt u verschillende worden invoeren (b.v.: t1'.QT_HTML_SEPARATOR.'t2 betekend tickets met "t1" of "t2").';
$L['In_all_sections']='In alle secties';
$L['In_title_only']='In titel alleen';
$L['Keywords']='Sleutelwoord(en)';
$L['Only_in_section']='Alleen in sectie';
$L['Recent_messages']='Recente&nbsp;'.$L['items'];
$L['Search_by_date']='Zoeken met datum';
$L['Search_by_key']='Zoeken met sleutelwoord(en)';
$L['Search_by_ref']='Zoeken met nummer';
$L['Search_by_status']='Zoeken met statuut';
$L['Search_by_tag']='Zoeken per categorie';
$L['Search_by_words']='Elk woord afzonderlijk zoeken';
$L['Search_criteria']='Onderzoekscriterium';
$L['Search_exact_words']='Worden samen zoeken';
$L['Search_option']='Onderzoeksoptie';
$L['Search_result']='Zoekresultaten';
$L['Search_results']=$L['Items'].' (%s)';
$L['Search_results_actor']='%1$s '.$L['items'].' behandeld door %2$s';
$L['Search_results_keyword']='%1$s '.$L['items'].' met woord %2$s';
$L['Search_results_last']='%1s '.$L['items'].' laaste week';
$L['Search_results_news']=$L['News'].' (%s)';
$L['Search_results_ref']='%1$s '.$L['items'].' met ref. %2$s';
$L['Search_results_tags']='%1$s '.$L['items'].' met categorie %2$s';
$L['Search_results_user']='%1$s '.$L['items'].' door %2$s';
$L['Show_only_tag']='Tonen alleen deze met categorie';
$L['Tag_only']='alleen met categorie'; // must be in lowercase
$L['This_month']='Deze maand';
$L['This_week']='Deze week';
$L['This_year']='Dit jaar';
$L['Too_many_keys']='Te veel sleutelwoorden';
$L['With_tag']= 'Met categorie';

// Inspection

$L['I_aggregation']='Samenvoeging methode';
$L['I_closed']='Gesloten inspectie';
$L['I_level']='Antworden waarden';
$L['I_r_bad']='Slecht';
$L['I_r_good']='Goed';
$L['I_r_high']='Hoog';
$L['I_r_low']='Laag';
$L['I_r_medium']='Middel';
$L['I_r_no']='Nee';
$L['I_r_veryhigh']='Zeer hoog';
$L['I_r_verylow']='Zeer laag';
$L['I_r_yes']='Ja';
$L['I_running']='Lopende inspectie';
$L['I_v_first']='Eerste waarde';
$L['I_v_last']='Laatste waarde';
$L['I_v_max']='Maximum';
$L['I_v_mean']='Gemiddelde waarde';
$L['I_v_min']='Minimum';
$L['Use_star_to_delete_all']='Typt * om alles te verwijderen';

// Privacy

$L['Privacy_0']='Verborgen';
$L['Privacy_1']='Zichtbaar voor leden alleen';
$L['Privacy_2']='Zichtbaar voor iedereen';

// Restrictions

$L['Ban']='Lock';
$L['Ban_user']='Gebruiker sluiten';

// Errors

$L['Already_used']='Reeds gebruikt';
$L['E_char_min']='Minimum %d tekens';
$L['E_char_max']='Maximum %d tekens';
$L['E_editing']='Data zijn verandered. Verlaten zonder saven?';
$L['E_file_size']='Het bestand is te groot';
$L['Invalid']='ongeldig';
$L['E_javamail']='Veiligheid: java is nodig om e-mail te zien';
$L['E_line_max']='(maximum %d lijnen)';
$L['E_missing_http']='url moet met http:// of https:// beginnen';
$L['E_no_public_section']='Dit systeem bevat geen openbaar sectie. De priv&eacute;sectie toegang vereist login.';
$L['E_no_title']='Een titel is verplicht';
$L['E_no_item']='Geen '.$L['item'].' gevond';
$L['E_no_visible_section']='Dit systeem bevat geen sectie zichtbaar voor u.';
$L['E_ref_search']='Decimaal (of komma) niet geldig. Het gebruik aanhalingsteken als u een aantal als sleutelwoord wilt zoeken.';
$L['E_section_closed']='Sectie&nbsp;is&nbsp;gesloten'; // use &nbsp; as space to avoid double ligne buttons
$L['E_text']='Probleem met uw bericht.';
$L['E_too_long']='Te lang bericht';
$L['E_too_much']='Te veel berichten vandaag...<br /><br />Om veiligheidsredenen, is de aantallen toegestane posten beperkt. Probeer opnieuw morgen. Bedankt...';
$L['E_item_private']='(of priv&eacute; aanvragen)';
$L['E_wait']='Gelieve te wachten een paar seconden';

$L['No_description']='Geen beschrijving';
$L['No_result']='Geen r&eacute;sultaat';
$L['Already_in_section']='Al in sectie';
$L['Try_without_options']='Probeer zonder opties';
$L['Tag_not_used']='Categorie nooit gebruikt';

// Success

$L['S_update']='Voltooide update...';
$L['S_delete']='Schrap voltooid...';
$L['S_insert']='Succesvolle verwezenlijking...';
$L['S_save']='Sparen voltooid...';
$L['S_message_saved']='Het bericht wordt bewaard...<br />Dank u';

// Dates

$L['dateMMM']=array(1=>'Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','Septembre','Oktober','November','December');
$L['dateMM'] =array(1=>'Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zondag');
$L['dateDD'] =array(1=>'Ma','Di','Wo','Do','Vr','Za','Zo');
$L['dateD']  =array(1=>'M','D','W','D','V','Z','Z');
$L['dateSQL']=array(
  'January'  => 'januari',
  'February' => 'februari',
  'March'    => 'maart',
  'April'    => 'april',
  'May'      => 'mei',
  'June'     => 'juni',
  'July'     => 'juli',
  'August'   => 'augustus',
  'September'=> 'september',
  'October'  => 'oktober',
  'November' => 'november',
  'December' => 'december',
  'Monday'   => 'maandag',
  'Tuesday'  => 'dinsdag',
  'Wednesday'=> 'woensdag',
  'Thursday' => 'donderdag',
  'Friday'   => 'vrijdag',
  'Saturday' => 'zaterdag',
  'Sunday'   => 'zondag',
  'Today'=>'Vandaag',
  'Yesterday'=>'Gisteren',
  'Jan'=>'jan',
  'Feb'=>'feb',
  'Mar'=>'mrt',
  'Apr'=>'apr',
  'May'=>'mei',
  'Jun'=>'jun',
  'Jul'=>'jul',
  'Aug'=>'aug',
  'Sep'=>'sep',
  'Oct'=>'okt',
  'Nov'=>'nov',
  'Dec'=>'dec',
  'Mon'=>'ma',
  'Tue'=>'di',
  'Wed'=>'wo',
  'Thu'=>'do',
  'Fri'=>'vr',
  'Sat'=>'za',
  'Sun'=>'zo');