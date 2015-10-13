<?php
/*
 Base Class for PHP Backup Manager script
 From: http://coursesweb.net/
*/

class pbm {
  protected $bk_dir ='';  //folder to store ZIP archive with backup
  public $lang = [];  //array with texts from "lang_...json"

  // receives languuage indice $lang, and folder-path that stores backup
  function  __construct(){
    $this->setLang(LANG);
    if(defined('BK_DIR')) $this->bk_dir = trim(BK_DIR, '/') .'/';
  }

  //set $lang with texts from passed json file
  protected function setLang($lang){
    if(file_exists(TPL .'lang_'. $lang .'.json')) $this->lang = json_decode(file_get_contents(TPL .'lang_'. $lang .'.json'), true);
    else if(file_exists(TPL .'lang_en.json')) $this->lang = json_decode(file_get_contents(TPL .'lang_en.json'), true);
    if(!is_array($this->lang)){
      $this->lang = [];
      echo $this->langTxt('er_json');
    }
  }

  //return the text from $key in $lang
  public function langTxt($key){
    if(isset($this->lang[$key])) return $this->lang[$key];
    else return $key;
  }
  // removes additional slashes, tags, and external whitespace from $arr (simple or multi dimensional array); $tags=0 strip_tags
  public function cleanArr($arr, $tags = 0) {
    foreach($arr as $k => $v) {
      if(is_array($v)) $arr[$k] = $this->cleanArr($v);    // recall this function
      else $arr[$k] = ($tags == 0) ? trim(strip_tags($v)) : trim($v);
    }
    return $arr;
  }

  //add in $tplv (for template) the text with index of $lang from $ar (array with indexes)
  public function setTplv($ar){
    GLOBAL $tplv;
    $nr = count($ar);
    if($nr >0){
      for($i=0; $i<$nr; $i++) $tplv[$ar[$i]] = $this->langTxt($ar[$i]);
    }
  }

  // replace in $tpl the strings equal with keys from $tplv, with associateed values
  public function template($tpl, $tplv) {
    // changes "key" to "{$key}"
    foreach($tplv as $k => $v) {
      $tplv['{$'. $k .'}'] = $v;
      unset($tplv[$k]);
    }

    return strtr($tpl, $tplv);
  }

  //return array with html radio-buttons for #zip_sql /#zip_dirs with the backup ZIP archive in $bk_dir
  public function getListZip(){
    $re = ['zip_sql'=>'', 'zip_dirs'=>''];
    $files = glob($this->bk_dir .'*.zip');
    $nr = count($files);

    //traverse the files, set name & size of each file
    for($i=0; $i<$nr; $i++) {
      $file = str_ireplace($this->bk_dir, '', $files[$i]);
      $size = filesize($files[$i]);
      $size = ($size/1024 <1) ? number_format($size, 2) .' Bytes' : (($size/1024 >=1 && $size/(1024*1024) <1) ? number_format($size/1024, 2) .' KB' : number_format($size/(1024*1024), 2) .'MB');
      $inp = '<label><input type="radio" name="file" value="'. $file .'" class="zip_files">'. $file .' (<em>'. $size .'</em>)</label>';
      if(preg_match('/^mysql-/i', $file)) $re['zip_sql'] .= $inp;
      else $re['zip_dirs'] .= $inp;
    }

    return $re;
  }

  //return the $zip file for download, in $bk_dir
  public function getZipFile($zip){
    if(file_exists($this->bk_dir . $zip)){
      header('Pragma: public'); // required
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Cache-Control: private',false);    // required for certain browsers
      header('Content-Type: application/zip');
      header('Content-Disposition: attachment; filename='. $zip .';' );
      header('Content-Transfer-Encoding: binary');
      header('Content-Length: '. filesize($this->bk_dir . $zip));
      readfile($this->bk_dir . $zip);
      exit;
    }
    else return sprintf($this->langTxt('er_file'), $zip);
  }

  //delete $file from $bk_dir
  public function delFile($file){
   if(@unlink($this->bk_dir . $file)) return $this->langTxt('ok_delete');
    else return sprintf($this->langTxt('er_delete'), $file);
  }

  //store $re in session and redirect to $loc
  public function redirSes($re, $loc){
    $_SESSION['pbm_re'] = $re;
    header('Location: '. $loc);
    exit;
  }
}
