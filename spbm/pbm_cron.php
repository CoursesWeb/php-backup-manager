#!/usr/local/bin/php
<?php
// Simple PHP Backup Manager - file from Cron Jobs
// Access this file from Cron Jobs in CPanel

include 'php/config.php';
if(isset($argv) && isset($argv[1])) $_GET['cron'] = $argv[1];
if(isset($_GET['cron']) && $_GET['cron'] == $admin_name){
  include 'php/pbm.class.php';

  //if set to backup mysql database
  if(BK_CRON_SQL ==1){
    define('CRON_BK', 1);  //used in saveBkZip() to set zip-name
    ignore_user_abort(true);
    @set_time_limit(2000);
    include 'php/pbmysql.class.php';
    $bk = new pbmysql();
    $bk->setMysql($bk_cron_mysql);  //set connection data
    $tables = (count($bk_cron_mysql['tables']) ==0) ? $bk->getTables() : $bk_cron_mysql['tables'];  //tables to backup
    $bk->saveBkZip($tables);  //make and save the backup
  }

  //if set to backup folders
  if(BK_CRON_DIR ==1){
    include 'php/pbmdirs.class.php';
    $bk = new pbmdirs();
    $bk->backup($_SERVER['DOCUMENT_ROOT'] .'/'. trim(ROOT_DIR, '/'), $bk_cron_dirs, []);
  }
}
else echo 'Invalid request';
