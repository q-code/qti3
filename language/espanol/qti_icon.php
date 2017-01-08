<?php

// Prefix icons can be use to label the tickets.
// Each section can have a distinct set of icons.
// To create a set of icons, create a series here after and upload the corresponding GIF images in the /skin/ directory
// A serie begin with a letter (a,b,...) and contains members noted '_01' to '_09'.
// The member 00 is used to define none, meaning no icon displayed (and has no corresponding icon file).

// SERIE A: EMOTICONS
$L['Prefix_serie']['a']='a) Emoticon'; // name of the serie
$L['Ico_prefix']['a_01']='Happy';
$L['Ico_prefix']['a_02']='Sad';
$L['Ico_prefix']['a_03']='Hoops';
$L['Ico_prefix']['a_04']='Wink';
$L['Ico_prefix']['a_05']='Angry';
$L['Ico_prefix']['a_06']='Surprise';
$L['Ico_prefix']['a_07']='Question';
$L['Ico_prefix']['a_08']='Important';
$L['Ico_prefix']['a_09']='Idea';

// SERIE B: TECHNICAL PHASES
$L['Prefix_serie']['b']='b) Technical phase'; // name of the serie
$L['Ico_prefix']['b_01']='A';
$L['Ico_prefix']['b_02']='AA';
$L['Ico_prefix']['b_03']='AAA';
$L['Ico_prefix']['b_04']='X';
$L['Ico_prefix']['b_05']='XX';
$L['Ico_prefix']['b_06']='XXX';

// SERIE C: USUAL
$L['Prefix_serie']['c']='c) Usual'; // name of the serie
$L['Ico_prefix']['c_01']='Minor';
$L['Ico_prefix']['c_02']='Major';
$L['Ico_prefix']['c_03']='Critical';

// SERIE D: Stars
$L['Prefix_serie']['d']='d) Stars'; // name of the serie
$L['Ico_prefix']['d_01']='No star';
$L['Ico_prefix']['d_02']='1 star';
$L['Ico_prefix']['d_03']='2 stars';
$L['Ico_prefix']['d_04']='3 stars';

// If you add a new serie here:
// don't forget to update this file in all languages
// don't forget to upload the corresponding .gif images in all skins

// SYSTEM ICONS (do not change)
$L['Ico_section_0_0']='Public section (actif)';
$L['Ico_section_0_1']='Public section (frosen)';
$L['Ico_section_1_0']='Hidden section (actif)';
$L['Ico_section_1_1']='Hidden section (frosen)';
$L['Ico_section_2_0']='Private section (actif)';
$L['Ico_section_2_1']='Private section (frosen)';

$L['Ico_view_n']='Normal view';
$L['Ico_view_c']='Compact view';
$L['Ico_view_p']='Print view';
$L['Ico_view_f_c']='Calendar view';
$L['Ico_view_f_n']='Table view';

$L['Ico_user_p']='User';
$L['Ico_user_pZ']='User (no profile)';
$L['Ico_user_w']='Open website';
$L['Ico_user_wZ']='no website';
$L['Ico_user_e']='Send e-mail';
$L['Ico_user_eZ']='no e-mail';

$L['Ico_item_t']='Post';
$L['Ico_item_tZ']='Post closed';
$L['Ico_item_a']='News';
$L['Ico_item_aZ']='News closed';
$L['Ico_item_i']='Inspection';
$L['Ico_item_iZ']='Inspection closed';

$L['Ico_post_p']='Message';
$L['Ico_post_r']='Reply message';
$L['Ico_post_f']='Forwarded message';
$L['Ico_post_d']='Deleted message';

$L['Ico_bold']='Bold';
$L['Ico_italic']='Italic';
$L['Ico_under']='Underline';
$L['Ico_bullet']='Bullet';
$L['Ico_quote']='Quote';
$L['Ico_code']='Code';
$L['Ico_url']='Url';
$L['Ico_mail']='E-mail';
$L['Ico_image']='Image (use @ to view attached image)';