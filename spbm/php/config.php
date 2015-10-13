<?php
// sPBM - Simple PHP Backup Manager

// Set name and password for Admin 
$admin_name = 'admin';
$admin_pass = 'pass';

define('ROOT_DIR', '/');  //Root folder for backup dirs/files

// 'new' : zip archive with SQL backup will have the name with this format: "mysql-DB_NAME-DATE@TIME.sql.zip"
// 'fix' : will have a fixed name: "mysql-DB_NAME.sql.zip" (will Overwrite previous backup of the Same database)
define('ZIP_SQL', 'new');


define('LANG', 'en');  //indice of the "lang_...json" file with texts
define('BK_DIR', 'backup/');  //folder to store the ZIP archive with backups
define('TPL', 'templ/');  //folder with template files (htm, css, js)


/* Settings for Cron */

//Data for connecting to database
$bk_cron_mysql = [
 'host'=> 'localhost',
 'user'=> 'root',
 'pass'=> 'password',
 'dbname'=> 'database_name',
 'tables'=> []  //array with tables to backup. If empty, will backup the tables
];

//array with folders from ROOT_DIR which to backup ['dir1', 'dir2', '...']. Let [''] to backup all the folders from ROOT_DIR
$bk_cron_dirs = [''];

$bk_exc = [];  //array with extensions of files to exclude from backup ['ext1', 'ext2']

define('BK_CRON_SQL', 1);  //value 1 to backup mysql database. Set 0 to not backup database in Cron
define('BK_CRON_DIR', 1);  //value 1 to backup directories. Set 0 to not backup folders in Cron
