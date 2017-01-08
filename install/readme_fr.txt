==============================
UPGRADE vers QuickTicket v2.5
==============================

Pour passer de la version 2.x � 2.5, vous pouvez proc�der � une installation standard (voir ci-apr�s).

  Remarque #1
  Il est recommand� de faire une sauvegarde du fichier /bin/config.php
  (au cas o� vous ne vous souvenez plus des param�tres de connexion vers votre base de donn�e).
  
  Remarque #2
  Si votre application autorisait les photos (avatars) et les documents (upload),
  il est recommand� de pr�server les r�pertoires /avatar/ et /upload/
  Les autres fichiers et r�pertoires peuvent �tre effac�s.

==============================
INSTALLATION de QuickTicket v2.5
==============================

AVANT de commencer l'installation, assurez-vous que vous connaissez :
- Le type de base de donn�e que vous utilisez (MySQL, SQLserver, PostgreSQL, SQLite, Firebird, Oracle ou DB2).
- Le nom de l'hote de votre base de donn�e (le nom du serveur de base de donn�e, souvent "localhost").
- Le nom de votre base de donn�e (o� QuickTicket peut installer ses tables).
- Le nom d'utilisateur pour cette base de donn�e (ayant le droit de cr�er des tables).
- Le mot de passe de celui-ci.


1. Envoyez l'application sur votre espace web
---------------------------------------------
Vous devez simplement envoyer (ftp) tous les fichiers et repertoires sur votre espace web (par exemple dans un r�pertoire /quickticket/).
Si vous aviez une version pr�c�dente, veillez � ne PAS effacer les r�pertoires /avatar/ et /upload/.


2. D�finir les permissions
--------------------------
Cette �tape est tr�s importante ! Sans elle, le programme d'installation ne pourra pas s'ex�cuter et votre base de donn�e ne pourra �tre configur�e.

Changer les permissions sur le fichier /bin/config.php afin qu'il soit inscriptible (chmod 777)
Changer les permissions sur les r�pertoires /avatar/ et /upload/ (et sous-r�pertoire) afin qu'ils soient inscriptibles (chmod 777)


3. Lancer l'installation
------------------------
Depuis votre navigateur internet, d�marrez le script d'installation : install/install.php
(ex: Tappez l'url http://www.votresiteweb.com/quickticket/install/install.php)
Ce script va vous demander les informations sur votre base de donn�e et y cr�er les tables n�cessaires � l'application.


4. Nettoyage
------------
Lorsque les �tapes pr�c�dentes sont termin�es, vous pouvez effacer le r�pertoire /install/ et changer les permissions de /bin/config.php en lecture seule.


HISTORIQUE DES VERSIONS
=======================
2.5    : Security and profile improvement. Requires php 5.x
2.4    : Includes inspections as a new type of ticket
2.3    : Improve ticket management and calendar
2.1    : Improve tags and statistics
2.0    : Allow using free/proposed categories to classify tickets (also to search and to produce specific statistics)
1.9.0.3: Improve memory usage and allow exporting statistics to csv
1.9    : Allows multiple translation for section, status and index
1.8    : Improvement in the search tools and in the statistics
1.7.0.1: Bug fix pour la s�curit� des profils et les statistiques avec une db oracle
1.7    : Version supportant le module Map
1.6    : Version supportant Oracle
