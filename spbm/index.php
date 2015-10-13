<?php
// sPBM - Simple PHP Backup Manager script
session_start();
include 'php/config.php';
include 'php/pbm.class.php';
$bk = new pbm();  //set object of pbm classes

// cleans data from GET, and POST
if(isset($_GET) && count($_GET) >0) $_GET = $bk->cleanArr($_GET);
if(isset($_POST) && count($_POST) >0) $_POST = $bk->cleanArr($_POST);

//set variables
$tpl_index = [ //associative array with data for index template
  'pbm_re'=> '',
  'lang'=> LANG,
  'js_txt'=> '{
   "er_sel_df":"'. str_replace('"', '\"', $bk->langTxt('er_sel_df')) .'",
   "er_sel_table":"'. str_replace('"', '\"', $bk->langTxt('er_sel_table')) .'",
   "msg_loading":"'. str_replace('"', '\"', $bk->langTxt('msg_loading')) .'",
   "msg_nozip":"'. str_replace('"', '\"', $bk->langTxt('msg_nozip')) .'",
   "msg_restore_bkdir":"'. str_replace('"', '\"', $bk->langTxt('msg_restore_bkdir')) .'",
   "msg_root":"'. str_replace('"', '\"', $bk->langTxt('msg_root')) .'",
   "msg_when_del":"'. str_replace('"', '\"', $bk->langTxt('msg_when_del')) .'",
   "php_self":"'. $_SERVER['PHP_SELF'] .'",
   "root":"'. ROOT_DIR .'"
  }',
  'nav_links'=>'',
  're_cnt'=>''
];

//associative array with data for other template
$tplv = [
  'php_self'=> $_SERVER['PHP_SELF'],
  'uri'=> $_SERVER['REQUEST_URI']
];
$bk->setTplv(['msg_name', 'msg_pass', 'msg_send']);  //add texts from #lang in $tplv

if(isset($_SESSION['pbm_admin']) && isset($_GET['ac']) && $_GET['ac'] =='logout') unset($_SESSION['pbm_admin']);  //log out

// check if admin logged
if(isset($_SESSION['pbm_admin'])){
  if($_SESSION['pbm_admin'] == $admin_name . $admin_pass){
    //get response after refresh, delete that session
    if(isset($_SESSION['pbm_re'])){
      $tpl_index['pbm_re'] = $_SESSION['pbm_re'];
      unset($_SESSION['pbm_re']);
    }

    if(isset($_GET['ac'])){
      if($_GET['ac'] =='mysql') include 'php/mysql.php';  //if access to backup/restore mysql
      else if($_GET['ac'] =='dirs') include 'php/dirs.php';  //if access to backup dirs/files
    }
    else if(isset($_POST['pbm_zip'])){ //access from buttons to get/del zip file
      if(isset($_POST['file'])){
        if($_POST['pbm_zip'] =='get_file') $tpl_index['pbm_re'] = $bk->getZipFile($_POST['file']);  //when to get ZIP file
        else if($_POST['pbm_zip'] =='del_file') $bk->redirSes($bk->delFile($_POST['file']), $tplv['php_self']); // delete ZIP and redirect
      }
      else $tpl_index['pbm_re'] = $bk->langTxt('er_sel_file');
    }
    else {
      $zp_f = $bk->getListZip();  //array with [zip_sql, zip_dirs]
      //Files with Backup ZIP files
      $bk->setTplv(['msg_del_file', 'msg_get_file', 'msg_restore_bk', 'msg_zip_dirs', 'msg_zip_sql']);  //add texts from #lang in $tplv
      $tplv['bk_dir'] = BK_DIR;
      $tplv['zip_sql'] = $zp_f['zip_sql'];  //radio-buttons for zip files with sql backup
      $tplv['zip_dirs'] = $zp_f['zip_dirs'];  //radio-buttons for zip files with dirs/files backup
      $tpl_index['re_cnt'] = $bk->template(file_get_contents(TPL .'zip_files.htm'), $tplv);  //zip with sql/dirs backup
    }

    //set data for $nav_links
    $bk->setTplv(['msg_bk_df', 'msg_conn_db', 'msg_logout', 'msg_show_files']);  //add texts from #lang in $tplv
    $tplv['msg_conn_to'] = isset($_SESSION['pbm_mysql']) ? sprintf($bk->langTxt('msg_conn_to'), $_SESSION['pbm_mysql']['dbname']) :'';
    $tpl_index['nav_links'] = $bk->template(file_get_contents(TPL .'nav_links.htm'), $tplv);
  }
  else unset($_SESSION['pbm_admin']);
}
else if(isset($_POST['name']) && isset($_POST['pass'])) {
 // check if data form to logg in
  if($_POST['name'] == $admin_name && $_POST['pass'] == $admin_pass) {
    $_SESSION['pbm_admin'] = $admin_name . $admin_pass;
    header('Location: '. $tplv['php_self']); exit;
  }
  else $tpl_index['pbm_re'] = $bk->langTxt('er_inc_pass');
}

// if not logged, include and outputs loggin form
if(!isset($_SESSION['pbm_admin'])) $tpl_index['re_cnt'] = $bk->template(file_get_contents(TPL .'admin_logg.htm'), $tplv);

header('Content-type: text/html; charset=utf-8');
echo $bk->template(file_get_contents(TPL .'index.htm'), $tpl_index);
