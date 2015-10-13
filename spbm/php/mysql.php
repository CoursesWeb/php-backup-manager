<?php
//code used in index.php for backup mysql tables
include 'php/pbmysql.class.php';
$bk = new pbmysql();

//if not post, set data for connecting form
if((!isset($_POST) || count($_POST) ==0) && $tpl_index['pbm_re'] ==''){
  $bk->setTplv(['msg_connect', 'msg_database', 'msg_server', 'msg_user']);  //add texts from #lang in $tplv

  //if not form send, set form with fields for connection data
  if(isset($_SESSION['pbm_mysql'])) unset($_SESSION['pbm_mysql']);
  if(isset($_SESSION['pbm_re'])) unset($_SESSION['pbm_re']);
  $tpl_index['re_cnt'] = $bk->template(file_get_contents(TPL .'conn_mysql.htm'), $tplv);
}
else {
  //if request to connect to mysql
  if(isset($_POST['host']) && isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['dbname'])){
    $_SESSION['pbm_mysql'] = ['host'=>$_POST['host'], 'user'=>$_POST['user'], 'pass'=>$_POST['pass'], 'dbname'=>$_POST['dbname']];
  }
}

//if session with data for connecting to MySQL database (MySQL server, user, password, database name)
if(isset($_SESSION['pbm_mysql'])){
  @set_time_limit(2000);

  $bk->setMysql($_SESSION['pbm_mysql']);  //set connection data

  //if form with tables to backup, create ZIP archive with backup. Else: restore backup
  if(isset($_POST['tables'])){
    $tables = $_POST['tables'];  //store tables in object
    $bk->redirSes($bk->saveBkZip($tables), $tplv['php_self']);  //response, redirect-location
  }
  else if(isset($_POST['file']) && isset($_POST['pbm_zip']) && $_POST['pbm_zip'] =='res_file') $bk->redirSes($bk->restore($_POST['file']), $tplv['uri']);  //on click button to restore sql.zip. Response, redirect-location

  $tables = $bk->getListTables();  //array with [f, er] (form, error)

  //if not error when get to show tables, add form with checkboxes, else $frm
  if($tables['er'] ==''){
    $bk->setTplv(['msg_conn_other', 'msg_conn_db', 'msg_show_files']);  //add texts from #lang in $tplv
    $tpl_index['re_cnt'] = $tables['f'];
  }
  else $tpl_index['pbm_re'] = $tables['er'];
}
