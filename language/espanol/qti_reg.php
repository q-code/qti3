<?php

// Is is recommended to always use capital on first letter in the translation
// software change to lower case if necessary.

$L['Agree']='He le&iacute;do, y acepto respetar estas reglas.';
$L['Proceed']='Puede registrarse';

// registration

$L['Choose_name']='Elige un nombre de usuario';
$L['Choose_password']='Elige una contrase&ntilde;a';
$L['Old_password']='Antigua';
$L['New_password']='Nueva';
$L['Confirm_password']='Confirma';
$L['Password_updated']='Contrase&ntilde;a cambiada';
$L['Password_by_mail']='La contrase&ntilde;a le ser&aacute; enviada a su direcci&oacute;n de e-mail.';
$L['Your_mail']='Tu e-mail';
$L['Parent_mail']='Padre/tutor e-mail';
$L['Reset_pwd']='Reset contrase&ntilde;a';
$L['Reset_pwd_help']='La aplicaci&oacute;n enviar&aacute; por e-mail una nueva contrase&ntilde;a de usuario.';
$L['Type_code']='Escriba el c&oacute;digo que ve con.';
$L['Register_completed']='Registro completado...';
$L['Unregister']='Darse de baja';
$L['H_Unregister']='<p>Usted parar&aacute; el tener de acceso a este uso como miembro. Su perfil ser&aacute; suprimido y su cuenta ser&aacute; no m&aacute;s visible en el memberlist. Sus mensajes seguir&aacute;n siendo visibles. Si otros usuarios intentan tener acceso a su perfil, consiguieron el perfil del "Visitor".</p><p>Incorpore su contrase&aacute;a para confirmar...</p>';

// login and profile

$L['Remember']='Recordarme';
$L['Forgotten_pwd']='Contrase&ntilde;a olvidada';
$L['Change_password']='Cambiar contrase&ntilde;a';
$L['Change_picture']='Cambiar fotograf&iacute;a';
$L['Picture_thumbnail'] = 'La imagen cargada es demasiado grande.<br />Para definir su imagen, dibujar un cuadrado en la imagen grande.';
$L['H_Change_picture']='(maximum '.$_SESSION[QT]['avatar_width'].'x'.$_SESSION[QT]['avatar_height'].' pixels, '.$_SESSION[QT]['avatar_size'].' Kb)';
$L['Delete_picture']='Borrar fotograf&iacute;a';
$L['Change_signature']='Cambiar firma';
$L['Change_role']='Cambiar rol';
$L['W_Somebody_else']='Precauci&oacute;n ... Usted est&aacute; corrigiendo el perfil del alguien diferente';

$L['H_no_signature']='Su firma se exhibe en la parte inferior de sus mensajes. Si usted no quiere la firma, excepto un texto vac&iacute;o aqu&iacute;.';
$L['Is_banned']='Est&aacute; prohibida';
$L['Is_banned_nomore']='<h2Bienvenido de nuevo...</h2><p>Su cuenta ha sido reabierto.<br />Vuelva a intentar iniciar sesi&oacute;n ahora...</p>';
$L['Since']='desde';
$L['Retry_tomorrow']='Intente otra vez ma&ntilde;ana o entre en contacto con al Administrador.';

// Secret question

$L['Secret_question']='Pregunta secreta';
$L['H_Secret_question']='Esta pregunta ser&aacute; hecha si usted olvida su contrase&ntilde;a.';
$L['Update_secret_question']='Su perfil debe ser actualizado... Para mejorar seguridad, le solicitamos definir su propio "Pregunta secreta". Esta pregunta ser&aacute; hecha si usted olvida su contrase&ntilde;a.';
$L['Secret_q']['What is the name of your first pet?']='&iquest;Cu&aacute;l es el nombre de su primer animal dom&eacute;stico?';
$L['Secret_q']['What is your favorite character?']='&iquest;Cu&aacute;l es su car&aacute;cter preferido?';
$L['Secret_q']['What is your favorite book?']='&iquest;Cu&aacute;l es su libro preferido?';
$L['Secret_q']['What is your favorite color?']='&iquest;Cu&aacute;l es su color preferido?';
$L['Secret_q']['What street did you grow up on?']='&iquest;Qu&eacute; calle usted creci&oacute; encendido?';

// Error

$L['E_pixels_max']='Pixels maximum';
$L['E_min_4_char']='Caracteres del m&iacute;nimo 4';
$L['E_pwd_char']='La contrase&ntilde;a contiene el car&aacute;cter inv&aacute;lido.';

// Help

$L['Reg_help']='Complete por favor esta p&aacute;gina para terminar su registro.<br /><br />Username and password must be at least 4 characters without tags or trailing spaces.<br /><br />E-mail address will be used to send you a new password if you forgot it. It is visible for registrered members only. To make it invisible, change your privacy settings in your profile.<br /><br />If you are visually impaired or cannot otherwise read the security code please contact the <a href="mailto:'.$_SESSION[QT]['admin_email'].'">Administrator</a> for help.<br /><br />';
$L['Reg_mail']='Usted recibir&aacute; un email pronto incluyendo una contrase&ntilde;a temporal.<br /><br />A le invitan que abra una sesi&oacute;n y corrija su perfil para definir su propia contrase&ntilde;a.';
$L['Reg_pass']='Password reset.<br /><br />If you have forgotten your password, please enter your username. We will send you a single-use access password key that will allow you to select a new password.';
$L['Reg_pass_reset']='Podemos enviarle una nueva contrase&ntilde;a si usted puede contestar a su pregunta secreta.';