<?php
//code used in index.php for backup directories /files
include 'php/pbmdirs.class.php';
$bk = new pbmdirs();

$tplv['dir'] = isset($_POST['dir']) ? '/'. trim(trim(strip_tags($_POST['dir'])), '/') .'/' : ROOT_DIR;  //folder to iterate
$df = $bk->getDF($_SERVER['DOCUMENT_ROOT'] . $tplv['dir']);  //get and output $dir structure

//if request from ajax output json with $dir content, else if to backup, else set <li> with dirs
if(isset($_POST['dir'])){
  echo json_encode($df);
  exit;
}
if(isset($_POST['root']) && isset($_POST['dirs']) && isset($_POST['files']) && isset($_POST['bk_exc'])){
  $_POST['root'] = $_SERVER['DOCUMENT_ROOT'] .'/'. trim($_POST['root'], '/');
  $bk_exc = array_map('trim', explode(',', $_POST['bk_exc']));  //file-extensions to exclude from backup
  echo $bk->backup($_POST['root'], json_decode($_POST['dirs'], true), json_decode($_POST['files'], true));
  exit;
}
else {
  //set html for #dirs_root and #sel_bk
  $tplv['dirs_root'] = $tplv['sel_bk'] ='';
  $tplv['nrd'] = count($df['d']);
  $tplv['nrf'] = count($df['f']);
  for($i=0; $i<$tplv['nrd']; $i++) {
    $tplv['dirs_root'] .='<li><strong>+</strong><span data-path="'. rtrim($tplv['dir'], '/') .'/'. trim($df['d'][$i], '/') .'">'. $df['d'][$i] .'</span></li>';
    $tplv['sel_bk'] .='<li class="dirs"><label><input type="checkbox" value="'. $df['d'][$i] .'" name="d[]" />'. $df['d'][$i] .'</label></li>';
  }
  foreach($df['f'] as $f=>$s) $tplv['sel_bk'] .='<li class="files"><label><input type="checkbox" value="'. $f .'" name="f[]" />'. $f .'</label><span>'. $s .'</span></li>';

  $bk->setTplv(['msg_backup', 'msg_exclude_bk', 'msg_files', 'msg_folders', 'msg_root', 'msg_sel_all', 'msg_sel_dir_bk']);
  $tpl_index['re_cnt'] = $bk->template(file_get_contents(TPL .'dir_files.htm'), $tplv);
}
