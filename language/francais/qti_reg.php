<?php

// Is is recommended to always use capital on first letter in the translation
// software change to lower case if necessary.

$L['Agree']='J\'ai lu ce r&egrave;glement et j\'accepte de le suivre.';
$L['Proceed']='S\'enregister';

// registration

$L['Choose_name']='Choisissez un nom';
$L['Choose_password']='Choisissez un mot de passe';
$L['Old_password']='Ancien';
$L['New_password']='Nouveau';
$L['Confirm_password']='Confirmez';
$L['Password_updated']='Mot de passe modifi&eacute;';
$L['Password_by_mail']='Un mot de passe temporaire sera envoy&eacute; &agrave; votre adresse e-mail.';
$L['Your_mail']='Votre e-mail';
$L['Parent_mail']='Parent/tuteur e-mail';
$L['Reset_pwd']='R&eacute;initialiser mot de passe';
$L['Reset_pwd_help']='L\'application va envoyer par e-mail un nouveau mot de passe &agrave; l\'utilisateur';
$L['Type_code']='Copiez le code que vous voyez.';
$L['Register_completed']='Inscription termin&eacute;e...';
$L['Unregister']='D&eacute;sinscription';
$L['H_Unregister']='<p>En vous d&eacute;sinscrivant, vous n\'aurez plus acc&egrave;s &agrave; cette application en tant que membre.<br />Votre profil sera effac&eacute; et ne sera plus visible dans la liste des membres. Vos messages resterons visible.<br />Si des utilisateurs tente d\'acc&eacute;der &agrave; votre profil, ils verront un profil anonyme "Visiteur".</p><p>Entrez votre mot de passe pour confirmer votre d&eacute;sinscription...</p>';

// login and profile

$L['Remember']='Se souvenir de moi';
$L['Forgotten_pwd']='Mot de passe perdu';

$L['Change_password']='Mot de passe';
$L['Change_picture']='Changer de photo';
$L['Picture_thumbnail'] = 'L\'image est trop grande.<br />Pour d&eacute;finir votre photo, tracez un carr&eacute; dans la grande image.';
$L['H_Change_picture']='(maximum '.$_SESSION[QT]['avatar_width'].'x'.$_SESSION[QT]['avatar_height'].' pixels, '.$_SESSION[QT]['avatar_size'].' Kb)';
$L['Delete_picture']='Effacer la photo';
$L['Change_signature']='Changer de signature';
$L['Change_role']='Changer de r&ocirc;le';
$L['W_Somebody_else']='Attention... Vous &eacute;ditez le profil de quelqu\'un d\'autre';

$L['H_no_signature']='Votre signauture s\'affiche en bas de vos messages. Pour effacer votre signature, sauvez un texte vide ci-apr&egrave;s.';
$L['Is_banned']='Est bloqu&eacute;';
$L['Is_banned_nomore']='<h2>Bienvenue &agrave; nouveau...</h2><p>Votre compte est &agrave; pr&eacute;sent r&eacute;-ouvert.<br />Vous pouvez maintenant vous re-connecter...</p>';
$L['Since']='depuis';
$L['Retry_tomorrow']='R&eacute;-essayez demain ou contactez l\'Administrateur du site.';

// Secret question

$L['Secret_question']='Question secr&egrave;te';
$L['H_Secret_question']='Cette question vous sera pos&eacute;e si vous avez oubli&eacute; votre mot de passe.';
$L['Update_secret_question']='Votre profil doit &ecirc;tre mis &agrave; jour...<br /><br />Afin d\'am&eacute;liorer la s&eacute;curit&eacute;, nous vous demandons de d&eacute;finir, votre "Question secr&egrave;te". Cette question vous sera pos&eacute;e si vous avez oubli&eacute; votre mot de passe.';
$L['Secret_q']['What is the name of your first pet?']='Quel est le nom de votre premier chien/chat ?';
$L['Secret_q']['What is your favorite character?']='Quel est votre personnage pr&eacute;f&eacute;r&eacute; ?';
$L['Secret_q']['What is your favorite book?']='Quel est votre livre pr&eacute;f&eacute;r&eacute; ?';
$L['Secret_q']['What is your favorite color?']='Quelle est votre couleur pr&eacute;f&eacute;r&eacute;e ?';
$L['Secret_q']['What street did you grow up on?']='Dans quelle rue avez-vous grandi ?';

// Error

$L['E_pixels_max']='Pixels maximum';
$L['E_min_4_char']='Minimum 4 caract&egrave;res';
$L['E_pwd_char']='Le mot de passe contient des carac&egrave;tres non-valides.';

// Help

$L['Reg_help']='Veuillez remplir ce formulaire afin de compl&eacute;ter votre inscription.<br /><br />Le nom d\'utilisateur et le mot de passe doivent avoir au moins 4 caract&egrave;res et &ecirc;tre sans balise ni espace au d&eacute;but et &agrave; la fin.<br /><br />L\'adresses e-mail sert &agrave; vous renvoyer un nouveau mot de passe en cas d\'oubli. Elle n\'est visible  que pour les membres enregistr&eacute;s. Vous pouvez la rendre invisible dans votre profil.<br /><br />Si vous &ecirc;tes malvoyant ou que vous ne voyez pas le code de s&eacute;curit&eacute;, contactez l\'<a href="mailto:'.$_SESSION[QT]['admin_email'].'">Administrateur</a>.<br /><br />';
$L['Reg_mail']='Vous allez recevoir par e-mail un mot de passe temporaire.<br /><br />Vous &ecirc;tes invit&eacute; &agrave; vous connecter et &agrave; changer votre mot de passe dans la page Profil.';
$L['Reg_pass']='R&eacute;initialisation du mot de passe.<br /><br />Si vous avez oubli&eacute; votre mot de passe, veuillez entrer votre nom d\utilisateur. Nous vous enverrons un mot de passe temporaire qui vous permettra de vous reconnecter et de d&eacute;finir un nouveau mot de passe.';
$L['Reg_pass_reset']='Nous pouvons vous envoyer un nouveau mot de passe si vous savez r&eacute;pondre &agrave; votre question secr&egrave;te.';