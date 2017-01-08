<?php

// If your board accept several languages (latin origin), the charset 'windows-1252' is recommended in order to render all accents correctly.
// If your board accept english only, you can use the charset 'utf-8'.
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'es');
if ( !defined('QT_HTML_SEPARATOR') ) define ('QT_HTML_SEPARATOR', ';');

// It is recommended to always use capital on first letter in the translation, script changes to lower case if necessary.
// The character doublequote ["] is FORBIDDEN (reserved for html tags)
// To make a single quote use slashe [\']

$L['Y']='S&iacute;';
$L['N']='No';
$L['Ok']='Ok';
$L['Cancel']='Cancelar';

// -------------
// TOP LEVEL VOCABULARY
// -------------
// Use the top level vocabulary to give the most appropriate name
// for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request, Faq,...

$L['Item']='Ticket'; $L['Items']='Tickets'; $L['item']='ticket'; $L['items']='tickets';
$L['Domain']='Dominio'; $L['Domains']='Dominios';
$L['Section']='Secci&oacute;n'; $L['Sections']='Secciones';
$L['User']='Usuario'; $L['Users']='Usuarios';
$L['Status']='Estado'; $L['Statuses']='Estados';
$L['Message']='Mensaje'; $L['Messages']='Mensajes';
$L['Reply']='Respuesta'; $L['Replys']='Respuestas';
$L['News']='Noticias'; $L['Newss']='M&aacute;s noticias'; // In other languages: News=One news, Newss=Several news
$L['Inspection']='Inspecci&oacute;n'; $L['Inspections']='Inspecciones';
$L['Forward']='Enviar'; $L['Forwards']='Envios';

// User and role

$L['Actor']='Actor';
$L['Author']='Autor';
$L['Coordinator']='Coordinador';
$L['Deleted_by']='Borrado por';
$L['Handled_by']='Manejado por';
$L['Modified_by']='Modificado por';
$L['Notified_user']='Notificaci&oacute;n usuario';
$L['Notify_also']='Notificar tambi&eacute;n';
$L['Role']='Role';
$L['Role_A']='Administrador'; $L['Role_As']='Administradores';
$L['Role_M']='Funcionario';   $L['Role_Ms']='Funcionarios';
$L['Role_U']='Usuario';       $L['Role_Us']='Usuarios';
$L['Role_V']='Visitante';     $L['Role_Vs']='Visitantes';
$L['Top_participants']='Top participantes';
$L['Username']='Nombre&nbsp;de&nbsp;usuario';

// Common

$L['Action']='Acci&oacute;n';
$L['Add']='A&ntilde;adir';
$L['Add_user']='Nuevo usuario';
$L['Advanced_reply']='Vista previa...';
$L['All']='Todo';
$L['and']='y'; // lowercase
$L['Attachment']='Adjunto';
$L['Avatar']='Fotograf&iacute;a';
$L['By']='Por';
$L['Change']='Cambio';
$L['Change_name']='Cambie el nombre de usuario';
$L['Change_status']='Estado del cambio...';
$L['Change_type']='Tipo de cambio...';
$L['Changed']='Cambiado';
$L['Charts']='Gr&aacute;ficos';
$L['Close']='Cerrar';
$L['Closed']='Cerrado';
$L['Column']='Columna';
$L['Contact']='Contacta';
$L['Containing']='Contenido';
$L['Continue']='Seguir';
$L['Coord']='Datos';
$L['Created']='Creado';
$L['Csv']='Export'; $L['H_Csv']='Abrase en hoja de balance';
$L['Date']='Fecha';
$L['Dates']='Fechas';
$L['Day']='D&iacute;a';
$L['Days']='D&iacute;as';
$L['Delete']='Borrar';
$L['Delete_tags']='Borrar (haga clic en una palabra o tipo * para eliminar todos)';
$L['Destination']='Destino';
$L['Details']='Detalles';
$L['Disable']='Desactivar';
$L['Display_at']='Mostrar la fecha';
$L['Drop_attachment']='Adjuntar&nbsp;archivo';
$L['Edit']='Editar';
$L['Email']='E-mail'; $L['No_Email']='No e-mail';
$L['Exit']='Salida';
$L['First']='Primero';
$L['Found_from']='Filtrado de';
$L['Goodbye']='Est&aacute;s desconectado... Adios';
$L['Goto']='Saltar a...';
$L['H_Website']='Url de tu sitio web (con http://)';
$L['H_Wisheddate']='fecha de entrega deseada';
$L['Help']='Ayuda';
$L['Hidden']='Ocultar';
$L['I_wrote']='Escrib&iacute;';
$L['Information']='Informaci&oacute;n';
$L['Items_per_month']='Tickets por mes';
$L['Items_per_month_cumul']='Tickets acumulados por mes';
$L['Joined']='Entrar';
$L['Last']='&Uacute;ltimo';
$L['latlon']='(lat,lon)';
$L['Legend']='Leyenda';
$L['Location']='Situaci&oacute;n';
$L['Maximum']='M&aacute;ximo';
$L['Me']='M&iacute;';
$L['Message_deleted']='Mensaje borrado...';
$L['Minimum']='M&iacute;nimo';
$L['Missing']='El campo de destino est&aacute; vac&iacute;o';
$L['Modified']='Modificado';
$L['Month']='Mes';
$L['More']='M&aacute;s';
$L['Move']='Mover';
$L['Name']='Nombre';
$L['News_stamp']='Noticias: '; //put a space after the stamp
$L['Next']='Siguiente';
$L['None']='Nada';
$L['Notification']='Notificaci&oacute;n';
$L['Open']='Abrir';
$L['Options']='Opciones';
$L['or']='o'; // lowercase
$L['Other']='Otro'; $L['Others']='Otros';
$L['Page']='p&aacute;gina';
$L['Pages']='p&aacute;ginas';
$L['Parameters']='Par&aacute;metro';
$L['Password']='Contrase&ntilde;a';
$L['Phone']='Llamar';
$L['Picture']='Imagen';
$L['Preview']='Vista previa';
$L['Previous']='Previo';
$L['Reason']='Motivo';
$L['Ref']='Ref.';
$L['Remove']='Quitar';
$L['Result']='Resultado';
$L['Results']='Resultados';
$L['Save']='Guardar';
$L['Score']='Cuenta';
$L['Seconds']='Segundos';
$L['Security']='Seguridad';
$L['Selected_from']='seleccionar de';
$L['Send']='Enviar';
$L['Send_on_behalf']='Enviar a nombre de';
$L['Settings']='Ajustes';
$L['Show']='Mostrar';
$L['Signature']='Firma';
$L['Smiley']='Emotic&oacute;n';
$L['Statistics']='Estad&iacute;sticas';
$L['Tag']='';
$L['Tags']='Categor&iacute;as';
$L['Time']='Hora';
$L['Title']='T&iacute;tulo';
$L['Total']='Total';
$L['Type']='Tipo';
$L['Update']='Actualizar';
$L['Views']='Views';
$L['Website']='Website'; $L['No_Website']='No website';
$L['Welcome']='Bienvenido';
$L['Welcome_not']='No soy %s !';
$L['Welcome_to']='Damos la bienvenida a un nuevo miembro, ';
$L['Wisheddate']='Fecha&nbsp;deseada';
$L['Year']='A&ntilde;o';
$L['yyyy-mm-dd']='aaaa-mm-dd';

// Menu

$L['Administration']='Adminstraci&oacute;n';
$L['FAQ']='FAQ';
$L['Login']='Acceder';
$L['Logout']='Desconectar';
$L['Memberlist']='Lista de miembros';
$L['Privacy']='Privado';
$L['Profile']='Perfil';
$L['Register']='Registrar';
$L['Search']='Buscar';

// Section // use &nbsp; to avoid double ligne buttons

$L['Allow_emails']='Permitir el env&iacute;o de e-mail';
$L['Change_actor']='Cambiar actor';
$L['Close_item']='Cerrar el ticket';
$L['Close_my_item']='Cerrar my ticket';
$L['Edit_start']='Comience a corregir';
$L['Edit_stop']='Pare el corregir';
$L['First_message']='Primer&nbsp;mensaje';
$L['Goto_message']='Ver el &Uacute;ltimo mensaje';
$L['Insert_forward_reply']='A&ntilde;adir informaci&oacute;n hacia adelante en las respuestas';
$L['Item_closed']='Cerrado&nbsp;ticket';
$L['Item_closed_hide']='Ocultar tickets cerrados';
$L['Item_closed_show']='Mostrar tickets cerrados';
$L['Item_forwarded']='Ticket que han sido enviados a %s.';
$L['Item_handled']='Ticket manejado';
$L['Item_insp_hide']='Ocultar inspecciones';
$L['Item_insp_show']='Mostar inspecciones';
$L['Item_news_hide']='Ocultar noticias';
$L['Item_news_show']='Mostar noticias';
$L['Item_show_all']='Mostrar todas las secciones en una';
$L['Item_show_this']='Mostrar s&oacute;lo esta secci&oacute;n';
$L['Items_deleted']='Borrar tickets';
$L['Items_handled']='Tickets manejados';
$L['Last_message']='&Uacute;ltimo&nbsp;mensaje';
$L['Move_follow']='Renumerar el destino de la siguiente direcci&oacute;n';
$L['Move_keep']='Mantener la fuente de la referencia';
$L['Move_reset']='Resetear la referencia a cero';
$L['Move_to']='Mover hacia';
$L['My_last_item']='Mi&nbsp;&uacute;ltimo&nbsp;ticket';
$L['My_preferences']='Mis preferencias';
$L['News_on_top']='noticias activas en la parte superior';
$L['New_item']='Nuevo ticket';
$L['Post_reply']='Responder';
$L['Previous_replies']='Contestaciones anteriores';
$L['Quick_reply']='Contestaci&oacute;n r&aacute;pida';
$L['Quote']='Cuota';
$L['Show_news_on_top']='Noticias en la parte superior';
$L['You_reply']='Yo contest&eacute;';

// Stats

$L['General_site']='Sitio general';
$L['Board_start_date']='Tab&oacute;n de la fecha de inicio';

// Search

$L['Advanced_search']='B&uacute;squeda avanzada';
$L['All_my_items']='Todos&nbsp;mis&nbsp;tickets';
$L['All_news']='Todas&nbsp;noticias';
$L['Any_status']='Cualquier';
$L['Any_time']='Cualquier';
$L['At_least_0']='Con o sin contestaci&oacute;n';
$L['At_least_1']='Al menos una contestaci&oacute;n';
$L['At_least_2']='Al menos 2 contestaciones';
$L['At_least_3']='Al menos 3 contestaciones';
$L['H_Advanced']='(Tipo de ticket de n&Uacute;mero referencia o de clave)';
$L['H_Reference']='(Tipo s&oacute;lo en la parte num&eacute;rica)';
$L['H_Tag_input']='Puede indicar varias palabras separadas por un '.QT_HTML_SEPARATOR.' (ej.: c1'.QT_HTML_SEPARATOR.'c2 busca los ticket " c1" o " c2").';
$L['In_all_sections']='En todas las secciones';
$L['In_title_only']='En el t&iacute;tulo s&oacute;lo';
$L['Keywords']='Clave(s)';
$L['Only_in_section']='S&oacute;lo en la secci&oacute;n';
$L['Recent_messages']='Tickets&nbsp;recientes';
$L['Search_by_date']='Buscar por fecha';
$L['Search_by_key']='Buscar por clave(s)';
$L['Search_by_ref']='Buscar por n&Uacute;mero de referencia';
$L['Search_by_status']='Buscar por estatus';
$L['Search_by_tag']='Buscar por categor&iacute;a';
$L['Search_by_words']='Buscar cada palabra separadamente';
$L['Search_criteria']='Criterios de b&uacute;squeda';
$L['Search_exact_words']='Buscar exactamente las palabras';
$L['Search_option']='Opciones de la b&uacute;squeda';
$L['Search_result']='B&uacute;squeda';
$L['Search_results']=$L['Items'].' (%s)';
$L['Search_results_actor']='%1$s '.$L['items'].' manejado por %2$s';
$L['Search_results_keyword']='%1$s '.$L['items'].' que contengan %2$s';
$L['Search_results_last']='%s '.$L['items'].' el &uacute;ltimo semana';
$L['Search_results_news']=$L['News'].' (%s)';
$L['Search_results_ref']='%1$s '.$L['items'].' con claves %2$s';
$L['Search_results_tags']='%1$s '.$L['items'].' con categor&iacute;a %2$s';
$L['Search_results_user']='%1$s '.$L['items'].' emitidos por los %2$s';
$L['Show_only_tag']='Indicar solamente los de la categor&iacute;a';
$L['Tag_only']='solamente los categor&iacute;a'; // must be in lowercase
$L['This_month']='Este mes';
$L['This_week']='Esta semana';
$L['This_year']='Este a&ntilde;o';
$L['Too_many_keys']='Demasiadas claves';
$L['With_tag']= 'Categor&iacute;a';

// Inspection

$L['I_aggregation']='M&eacute;todo de la agregaci&oacute;n';
$L['I_closed']='Inspecci&oacute;n cerrada';
$L['I_level']='Nivel de respuesta';
$L['I_r_bad']='Malo';
$L['I_r_good']='Bueno';
$L['I_r_high']='Arriba';
$L['I_r_low']='Bajo';
$L['I_r_medium']='Medio';
$L['I_r_no']='No';
$L['I_r_veryhigh']='Muy arriba';
$L['I_r_verylow']='Muy bajo';
$L['I_r_yes']='S&iacute;';
$L['I_running']='Inspecci&oacute;n funcionando';
$L['I_v_first']='Primer valor';
$L['I_v_last']='Valor pasado';
$L['I_v_max']='M&aacute;ximo';
$L['I_v_mean']='Valor medio';
$L['I_v_min']='M&iacute;nimo';
$L['Use_star_to_delete_all']='Tipo * para eliminar todos';

// Privacy

$L['Privacy_0']='No visible';
$L['Privacy_1']='Visible para los miembros solamente';
$L['Privacy_2']='Visible para todos';

// Restrictions

$L['Ban']='Prohibicion';
$L['Ban_user']='Prohibir usuario';

// Errors

$L['Already_used']='Se est&aacute; usando actualmente';
$L['E_char_min']='M&iacute;nimos %d characteres';
$L['E_char_max']='M&aacute;ximos %d caracteres';
$L['E_editing']='Se han cambiado los datos. Parado sin el ahorro?';
$L['E_file_size']='El archivo es demasiado largo';
$L['Invalid']='Inv&aacute;lido';
$L['E_javamail']='Protecci&oacute;n: se necesita java para ver la direcci&oacute;n de este e-mail';
$L['E_line_max']='(m&aacute;ximas %d lineas)';
$L['E_missing_http']='URL debe comenzar con http:// de https://';
$L['E_no_public_section']='El tabl&oacute;n no contiene ninguna secci&oacute;n p&Uacute;blica. Para el acceso a la secci&oacute;n privada necesita conectarse.';
$L['E_no_title']='Por favor, dele un t&iacute;tulo a este nuevo ticket';
$L['E_no_item']='Ning&uacute;n '.$L['item'].' encontrado';
$L['E_no_visible_section']='El tabl&oacute;n no contiene ninguna secci&oacute;n visible para usted.';
$L['E_ref_search']='N&Uacute;mero decimal (o coma) no v&aacute;lido. Use comillas si quiere buscar un n&Uacute;mero como clave.';
$L['E_section_closed']='La secci&oacute;n est&aacute; cerrada';
$L['E_text']='Problema con tu texto.';
$L['E_too_long']='Mensaje demasiado largo';
$L['E_too_much']='Demasiados post por hoy...<br /><br />Por razones de seguridad, el n&Uacute;mero de post permitidos est&aacute; limitado. Int&eacute;ntelo ma&ntilde;ana. Gracias.';
$L['E_item_private']='(Los tickets son privados)';
$L['E_wait']='Por favor, espera un momento';

$L['No_description']='Ninguna descripci&oacute;n';
$L['No_result']='Ninguna resultado';
$L['Already_in_section']='Ya en la secci&oacute;n de';
$L['Try_without_options']='Pruebe sin opciones';
$L['Tag_not_used']='Categor&iacute;a nunca us&oacute;';

// Success

$L['S_update']='Modificaci&oacute;n correcta...';
$L['S_delete']='Borrado correcto...';
$L['S_insert']='Creaci&oacute;n correcta...';
$L['S_save']='Guardado correctamente...';
$L['S_message_saved']='Mensaje guardado...<br />Muchas gracias';

// Dates

$L['dateMMM']=array(1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
$L['dateMM'] =array(1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
$L['dateM']  =array(1=>'E','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado','Domingo');
$L['dateDD'] =array(1=>'Lun','Mar','Mie','Jue','Vie','Sab','Dom');
$L['dateD']  =array(1=>'L','M','X','J','V','S','D');
$L['dateSQL']=array(
  'January'  => 'Enero',
  'February' => 'Febrero',
  'March'    => 'Marzo',
  'April'    => 'Abril',
  'May'      => 'Mayo',
  'June'     => 'Junio',
  'July'     => 'Julio',
  'August'   => 'Agosto',
  'September'=> 'Septiembre',
  'October'  => 'Octubre',
  'November' => 'Noviembre',
  'December' => 'Diciembre',
  'Monday'   => 'Lunes',
  'Tuesday'  => 'Martes',
  'Wednesday'=> 'Mi&eacute;rcoles',
  'Thursday' => 'Jueves',
  'Friday'   => 'Viernes',
  'Saturday' => 'S&aacute;bado',
  'Sunday'   => 'Domingo',
  'Today'=>'Hoy',
  'Yesterday'=> 'Ayer',
  'Jan'=>'Ene',
  'Feb'=>'Feb',
  'Mar'=>'Mar',
  'Apr'=>'Abr',
  'May'=>'May',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Ago',
  'Sep'=>'Sep',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Dic',
  'Mon'=>'Lun',
  'Tue'=>'Mar',
  'Wed'=>'Mie',
  'Thu'=>'Jue',
  'Fri'=>'Vie',
  'Sat'=>'Sab',
  'Sun'=>'Dom');