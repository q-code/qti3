<?php

// If your board accept several languages (latin origin), the charset 'windows-1252' is recommended in order to render all accents correctly.
// If your board accept english only, you can use the charset 'utf-8'.
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'fr');
if ( !defined('QT_HTML_SEPARATOR') ) define ('QT_HTML_SEPARATOR', ';');

// It is recommended to always use capital on first letter in the translation, script changes to lower case if necessary.
// The character doublequote ["] is FORBIDDEN (reserved for html tags)
// To make a single quote use slashe [\']

$L['Y']='Oui';
$L['N']='Non';
$L['Ok']='Ok';
$L['Cancel']='Annuler';

// -------------
// TOP LEVEL VOCABULARY
// -------------
// Use the top level vocabulary to give the most appropriate name
// for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request, Faq,...

$L['Item']='Ticket'; $L['Items']='Tickets'; $L['item']='ticket'; $L['items']='tickets';
$L['Domain']='Domaine'; $L['Domains']='Domaines';
$L['Section']='Section'; $L['Sections']='Sections';
$L['User']='Utilisateur'; $L['Users']='Utilisateurs';
$L['Status']='Statut'; $L['Statuses']='Statuts';
$L['Message']='Message'; $L['Messages']='Messages';
$L['Reply']='R&eacute;ponse'; $L['Replys']='R&eacute;ponses';
$L['News']='News'; $L['Newss']='News'; // In other languages: News=One news, Newss=Several news
$L['Inspection']='Inspection'; $L['Inspections']='Inspections';
$L['Forward']='Transfers'; $L['Forwards']='Transfers';

// User and role

$L['Actor']='Acteur';
$L['Author']='Auteur';
$L['Coordinator']='Coordinateur';
$L['Deleted_by']='Effac&eacute; par';
$L['Handled_by']='G&eacute;r&eacute; par';
$L['Modified_by']='Modifi&eacute; par';
$L['Notified_user']='Utilisateur notifi&eacute;';
$L['Notify_also']='Notifier aussi';
$L['Role']='R&ocirc;le';
$L['Role_A']='Administrateur'; $L['Role_As']='Administrateurs';
$L['Role_M']='Staff';          $L['Role_Ms']='Staffs';
$L['Role_U']='Membre';         $L['Role_Us']='Membres';
$L['Role_V']='Visiteur';       $L['Role_Vs']='Visiteurs';
$L['Top_participants']='Top participants';
$L['Username']='Nom d\'utilisateur';

// Common

$L['Action']='Action';
$L['Add']='Ajouter';
$L['Add_user']='Nouvel utilisateur';
$L['Advanced_reply']='Pr&eacute;visualisation...';
$L['All']='Tous';
$L['and']='et'; // lowercase
$L['Attachment']='Document';
$L['Avatar']='Photo';
$L['By']='Par';
$L['Change']='Changer';
$L['Change_name']='Changer l\'identifiant';
$L['Change_status']='Changer le statut...';
$L['Change_type']='Changer le type...';
$L['Changed']='Chang&eacute;';
$L['Charts']='Graphiques';
$L['Close']='Fermer';
$L['Closed']='Ferm&eacute;';
$L['Column']='Colonne';
$L['Contact']='Contacte';
$L['Containing']='Contenant';
$L['Continue']='Continuer';
$L['Coord']='Coordonn&eacute;es';
$L['Created']='Cr&eacute;&eacute;';
$L['Csv']='Export'; $L['H_Csv']='Ouvrir dans un tableur';
$L['Date']='Date';
$L['Dates']='Dates';
$L['Day']='Jour';
$L['Days']='Jours';
$L['Delete']='Effacer';
$L['Delete_tags']='Effacer (clickez un mot ou tappez * pour tout effacer)';
$L['Destination']='Destination';
$L['Details']='D&eacute;tails';
$L['Disable']='D&eacute;sactiver';
$L['Display_at']='Afficher &agrave; la date';
$L['Drop_attachment']='Effacer&nbsp;le&nbsp;document';
$L['Edit']='Editer';
$L['Email']='E-mail'; $L['No_Email']='Pad d\'e-mail';
$L['Exit']='Exit';
$L['First']='Premi&egrave;re';
$L['Found_from']='Parmi';
$L['Goodbye']='Vous &ecirc;tes d&eacute;connect&eacute;... Au revoir';
$L['Goto']='Atteindre...';
$L['H_Website']='Url avec http://';
$L['H_Wisheddate']='date de livraison souhait&eacute;e';
$L['Help']='Aide';
$L['Hidden']='Cach&eacute;';
$L['I_wrote']='J\'ai &eacute;crit';
$L['Information']='Information';
$L['Items_per_month']='Tickets par mois';
$L['Items_per_month_cumul']='Cumul des tickets par mois';
$L['Joined']='Depuis';
$L['Last']='Derni&egrave;re';
$L['latlon']='(lat,lon)';
$L['Legend']='L&eacute;gende';
$L['Location']='Localisation';
$L['Maximum']='Maximum';
$L['Me']='Moi';
$L['Message_deleted']='Message effac&eacute;...';
$L['Minimum']='Minimum';
$L['Missing']='Un champ obligatoire est vide';
$L['Modified']='Modifi&eacute;';
$L['Month']='Mois';
$L['More']='Plus';
$L['Move']='D&eacute;placer';
$L['Name']='Nom';
$L['News_stamp']='News: ';
$L['Next']='Suivante';
$L['None']='Aucun';
$L['Notification']='Notification';
$L['Open']='Ouvrir';
$L['Options']='Options';
$L['or']='ou'; // lowercase
$L['Other']='Autre'; $L['Others']='Autres';
$L['Page']='Page';   $L['Pages']='Pages';
$L['Parameters']='Parametres';
$L['Password']='Mot de passe';
$L['Phone']='T&eacute;l&eacute;phone';
$L['Picture']='Image';
$L['Preview']='Pr&eacute;visualisation';
$L['Previous']='Pr&eacute;c&eacute;dente';
$L['Privacy']='Vie priv&eacute;e';
$L['Reason']='Raison';
$L['Ref']='Ref.';
$L['Remove']='Enlever';
$L['Result']='R&eacute;sultat';
$L['Results']='R&eacute;sultats';
$L['Save']='Sauver';
$L['Score']='Score';
$L['Seconds']='Secondes';
$L['Security']='S&eacute;curit&eacute;';
$L['Selected_from']='s&eacute;lectionn&eacute;s sur';
$L['Send']='Envoyer';
$L['Send_on_behalf']='Au nom de';
$L['Settings']='Param&egrave;tres';
$L['Show']='Afficher';
$L['Signature']='Signature';
$L['Smiley']='Pr&eacute;fixe icone';
$L['Statistics']='Statistiques';
$L['Tag']='Cat&eacute;gorie';
$L['Tags']='Cat&eacute;gories';
$L['Time']='Heure';
$L['Title']='Titre';
$L['Total']='Total';
$L['Type']='Type';
$L['Update']='Mettre &agrave; jour';
$L['Views']='Vues';
$L['Website']='Site web'; $L['No_Website']='Aucun site web';
$L['Welcome']='Bienvenue';
$L['Welcome_not']='Je ne suis pas %s !';
$L['Welcome_to']='Bienvenue &agrave; un nouveau membre, ';
$L['Wisheddate']='Date demand&eacute;e';
$L['Year']='Ann&eacute;e';
$L['yyyy-mm-dd']='aaaa-mm-jj';

// Menu

$L['Administration']='Administration';
$L['FAQ']='FAQ';
$L['Legal']='Notices&nbsp;l&eacute;gales';
$L['Login']='Connexion';
$L['Logout']='D&eacute;connexion';
$L['Memberlist']='Membres';
$L['Profile']='Profil';
$L['Register']='S\'enregistrer';
$L['Search']='Chercher';

// Section // use &nbsp; to avoid double ligne buttons

$L['Allow_emails']='Permettre l\'envoi des emails';
$L['Change_actor']='Changer d\'acteur';
$L['Close_item']='Fermer le ticket';
$L['Close_my_item']='Je ferme mon ticket';
$L['Edit_start']='Mode &eacute;dition';
$L['Edit_stop']='Arr&ecirc;ter l\'&eacute;dition';
$L['First_message']='Premier&nbsp;message';
$L['Goto_message']='Voir le dernier message';
$L['Insert_forward_reply']='Ajouter l\'info de transfert dans les r&eacute;ponses';
$L['Item_closed']='Ticket&nbsp;ferm&eacute;';
$L['Item_closed_hide']='Masquer les tickets ferm&eacute;s';
$L['Item_closed_show']='Montrer les tickets ferm&eacute;s';
$L['Item_forwarded']='Ticket a &eacute;t&eacute; pris en charge par %s.';
$L['Item_handled']='Ticket g&eacute;r&eacute;';
$L['Item_insp_hide']='Masquer les inspections';
$L['Item_insp_show']='Montrer les inspections';
$L['Item_news_hide']='Masquer les news';
$L['Item_news_show']='Montrer les news';
$L['Item_show_all']='Montrer toutes les sections';
$L['Item_show_this']='Montrer cette section uniquement';
$L['Items_deleted']='Tickets effac&eacute;s';
$L['Items_handled']='Tickets g&eacute;r&eacute;s';
$L['Last_message']='Dernier message';
$L['Move_follow']='Renum&eacute;rot&eacute; (suivant la section de destination)';
$L['Move_keep']='Conserver la r&eacute;f&eacute;rence originale';
$L['Move_reset']='Remettre la r&eacute;f&eacute;rence &agrave; z&eacute;ro';
$L['Move_to']='D&eacute;placer vers';
$L['My_last_item']='Mon&nbsp;dernier&nbsp;ticket';
$L['My_preferences']='Mes pr&eacute;f&eacute;rences';
$L['News_on_top']='news actives en d&eacute;but de liste';
$L['New_item']='Nouveau ticket';
$L['Post_reply']='R&eacute;pondre';
$L['Previous_replies']='Pr&eacute;c&eacute;dentes r&eacute;ponses';
$L['Quick_reply']='Reponse rapide';
$L['Quote']='Citer';
$L['Show_news_on_top']='News en d&eacute;but de liste';
$L['You_reply']='J\'ai r&eacute;pondu';

// Stats

$L['General_site']='Site en g&eacute;neral';
$L['Board_start_date']='Mise en service';

// Search

$L['Advanced_search']='Recherche avanc&eacute;e';
$L['All_my_items']='Mes&nbsp;tickets';
$L['All_news']='Toutes&nbsp;les&nbsp;news';
$L['Any_status']='Tout statut';
$L['Any_time']='Toute date';
$L['At_least_0']='Avec ou sans r&eacute;ponse';
$L['At_least_1']='Au moins 1 r&eacute;ponse';
$L['At_least_2']='Au moins 2 r&eacute;ponses';
$L['At_least_3']='Au moins 3 r&eacute;ponses';
$L['H_Advanced']='(entrez un num&eacute;ro de '.$L['item'].' ou un mot cl&eacute;)';
$L['H_Reference']='(entrez seulement la partie num&eacute;rique)';
$L['H_Tag_input']='Vous pouvez indiquer plusieurs mots s&eacute;par&eacute;s par '.QT_HTML_SEPARATOR.' (ex.: t1'.QT_HTML_SEPARATOR.'t2 recherche les tickets contenant "t1" ou "t2").';
$L['In_all_sections']='Dans toutes sections';
$L['In_title_only']='Dans le titre uniquement';
$L['Keywords']='Mot(s) cl&eacute;';
$L['Only_in_section']='Dans la section';
$L['Recent_messages']=$L['Items'].'&nbsp;r&eacute;cents';
$L['Search_by_date']='Chercher par date';
$L['Search_by_key']='Chercher par mot(s) cl&eacute;';
$L['Search_by_ref']='Chercher par num&eacute;ro';
$L['Search_by_status']='Chercher par statut';
$L['Search_by_tag']='Chercher par cat&eacute;gorie';
$L['Search_by_words']='Chercher chaque mot s&eacute;par&eacute;ment';
$L['Search_criteria']='Crit&egrave;re de recherche';
$L['Search_exact_words']='Chercher les mots ensemble';
$L['Search_option']='Option de recherche';
$L['Search_result']='R&eacute;sultat de recherche';
$L['Search_results']=$L['Items'].' (%s)';
$L['Search_results_actor']='%1$s '.$L['items'].' g&eacute;r&eacute;s par %2$s';
$L['Search_results_keyword']='%1$s '.$L['items'].' contenant %2$s (%1$s)';
$L['Search_results_last']='%s '.$L['items'].' de cette semaine';
$L['Search_results_news']=$L['News'].' (%s)';
$L['Search_results_ref']='%1$s '.$L['items'].' ayant la r&eacute;f. %2$s';
$L['Search_results_tags']='%1$s '.$L['items'].' de cat&eacute;gorie %2$s';
$L['Search_results_user']='%1$s '.$L['items'].' cr&eacute;&eacute;s par %2$s';
$L['Show_only_tag']='Afficher seulement ceux de la cat&eacute;gorie';
$L['Tag_only']='uniquement de cat&eacute;gorie'; // must be in lowercase
$L['This_month']='Ce mois';
$L['This_week']='Cette semaine';
$L['This_year']='Cette ann&eacute;e';
$L['Too_many_keys']='Trop de mots cl&eacute;s';
$L['With_tag']= 'Cat&eacute;gorie';

// Inspection - Category

$L['I_aggregation']='M&eacute;thode d\'agr&eacute;gation';
$L['I_closed']='Inspection ferm&eacute;e';
$L['I_level']='Niveau de r&eacute;ponse';
$L['I_r_bad']='Mauvais';
$L['I_r_good']='Bon';
$L['I_r_high']='Haut';
$L['I_r_low']='Faible';
$L['I_r_medium']='Moyen';
$L['I_r_no']='Non';
$L['I_r_veryhigh']='Tr&egrave;s haut';
$L['I_r_verylow']='Tr&egrave;s faible';
$L['I_r_yes']='Oui';
$L['I_running']='Inspection en cours';
$L['I_v_first']='Premi&egrave;re valeur';
$L['I_v_last']='Derni&egrave;re valeur';
$L['I_v_max']='Maximum';
$L['I_v_mean']='Valeur moyenne';
$L['I_v_min']='Minimum';
$L['Use_star_to_delete_all']='Utilisez * pour effacer tout';

// Pricacy

$L['Privacy_0']='Non visible';
$L['Privacy_1']='Visible par les membres uniquement';
$L['Privacy_2']='Visible par tous';

// Restrictions

$L['Ban']='Bloqu&eacute;';
$L['Ban_user']='Bloquer l\'utilisateur';

// Errors

$L['Already_used']='D&eacute;j&agrave; utilis&eacute;';
$L['E_char_min']='Minimum %d caract&egrave;res';
$L['E_char_max']='Maximum %d caract&egrave;res)';
$L['E_editing']='Des donn&eacute;es sont modifi&eacute;es. Quitter sans sauver?';
$L['E_file_size']='Fichier trop gros';
$L['Invalid']='erron&eacute;';
$L['E_javamail']='Protection: activez java pour voir les adresses e-mail';
$L['E_line_max']='(maximum %d lignes)';
$L['E_missing_http']='l\'url doit commencer par \'http://\' ou \'https://\'';
$L['E_no_public_section']='Le site ne contient pas de section publique. Pour acc&eacute;der aux sections priv&eacute;s, vous devez vous identifier.';
$L['E_no_title']='Veuillez donner un titre &agrave; ce nouveau sujet';
$L['E_no_item']='Aucun '.$L['item'];
$L['E_no_visible_section']='Le site ne contient pas de section visible pour vous.';
$L['E_ref_search']='D&eacute;cimale (ou virgule) non accept&eacute;e comme num&eacute;ro de r&eacute;f&eacute;rence. Utilisez des guillemets pour chercher un nombre en tant que text.';
$L['E_section_closed']='Section&nbsp;ferm&eacute;e'; // use &nbsp; as space to avoid double ligne buttons
$L['E_text']='Probl&egrave;me avec votre texte.';
$L['E_too_long']='Message trop long';
$L['E_too_much']='Trop de messages aujourd\'hui...<br /><br />Pour des raisons de s&eacute;curit&eacute;, le nombre de messages est limit&eacute;. Essayez &agrave; nouveau demain. Merci de votre compr&eacute;hension.';
$L['E_item_private']='(ou les tickets sont priv&eacute;s)';
$L['E_wait']='Veuillez patienter quelques secondes...';

$L['No_description']='Aucune description';
$L['No_result']='Aucun r&eacute;sultat';
$L['Already_in_section']='D&eacute;j&agrave; dans la section';
$L['Try_without_options']='Essayez sans options';
$L['Tag_not_used']='Nouvelle categorie';

// Success

$L['S_update']='Changement effectu&eacute;...';
$L['S_delete']='Effacement effectu&eacute;...';
$L['S_insert']='Cr&eacute;ation termin&eacute;e...';
$L['S_save']='Sauvegarde r&eacute;ussie...';
$L['S_message_saved']='Message sauv&eacute;...<br />Merci';

// Timezones

$L['dateMMM']=array(1=>'Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin','Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','D&eacute;cembre');
$L['dateMM'] =array(1=>'Jan','Fev','Mar','Avr','Mai','Juin','Juil','Aout','Sept','Oct','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche');
$L['dateDD'] =array(1=>'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
$L['dateD']  =array(1=>'L','M','M','J','V','S','D');
$L['dateSQL']=array(
  'January'  => 'Janvier',
  'February' => 'F&eacute;vrier',
  'March'    => 'Mars',
  'April'    => 'Avril',
  'May'      => 'Mai',
  'June'     => 'Juin',
  'July'     => 'Juillet',
  'August'   => 'Aout',
  'September'=> 'Septembre',
  'October'  => 'Octobre',
  'November' => 'Novembre',
  'December' => 'D&eacute;cembre',
  'Monday'   => 'Lundi',
  'Tuesday'  => 'Mardi',
  'Wednesday'=> 'Mercredi',
  'Thursday' => 'Jeudi',
  'Friday'   => 'Vendredi',
  'Saturday' => 'Samedi',
  'Sunday'   => 'Dimanche',
  'Today'=>'Aujourd\'hui',
  'Yesterday'=>'Hier',
  'Jan'=>'Jan',
  'Feb'=>'F&eacute;v',
  'Mar'=>'Mar',
  'Apr'=>'Avr',
  'May'=>'Mai',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Aout',
  'Sep'=>'Sept',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'D&eacute;c',
  'Mon'=>'Lu',
  'Tue'=>'Ma',
  'Wed'=>'Me',
  'Thu'=>'Je',
  'Fri'=>'Ve',
  'Sat'=>'Sa',
  'Sun'=>'Di');