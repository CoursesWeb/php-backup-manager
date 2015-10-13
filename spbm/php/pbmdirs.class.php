<?php
/*
 PHP Class to Backup Directories and saves into a ZIP archive
 From: http://coursesweb.net/
*/

class pbmdirs extends pbm {
  //get and returns array with directories and files in $dir ([d:[dirs], f:[filename: size]])
  public function getDF($dir){
    $df = ['d'=>[], 'f'=>[]];  //directories and files [filename: size]
    $ob = new DirectoryIterator($dir);
    foreach($ob as $finf){
      $fname = $finf->getFilename();
      if($finf->isDir() && !$finf->isDot()){  // if it's folder
        //exclude backup folder from results
        if($dir .$fname == rtrim($_SERVER['DOCUMENT_ROOT']. dirname($_SERVER['PHP_SELF']) .'/'. BK_DIR, '/')) continue;
        $df['d'][] = $fname;
      }
      else if($finf->isFile()){  // if it's file
        $size = $finf->getSize();
        $size = ($size/1024 <1) ? number_format($size, 2) .' Bytes' : (($size/1024 >=1 && $size/(1024*1024) <1) ? number_format($size/1024, 2) .' KB' : number_format($size/(1024*1024), 2) .'MB');
        $df['f'][$fname] = $size;
      }
    }
    return $df;
  }

  // create ZIP archive with dir/files from $root, having $dirs and $files (arrays with their names)
  public function backup($root, $dirs, $files){
    ignore_user_abort(true);
    @set_time_limit(2400);
    if(!class_exists('ZipArchive')) $re = $this->langTxt('er_zip');
    else{
      function ZipAddDir($dir, $zip, $zipPath, $files=[]){
        if($dir != rtrim($_SERVER['DOCUMENT_ROOT']. dirname($_SERVER['PHP_SELF']) .'/'. BK_DIR, '/')){ //if not  backup folder
          GLOBAL $bk_exc;
          $nrf = count($files);
          $d = opendir($dir);
          $zipPath = str_replace('//', '/', $zipPath);
          if($zipPath && $zipPath != '/') $zip->addEmptyDir($zipPath);

          while(($f = readdir($d)) !== false){
            if($f == '.' || $f == '..') continue;
            else if($nrf ==0 || in_array($f, $files)){
              $f_ex = explode('.', $f);
              $f_ex = end($f_ex);  //file-extension
              $filePath = str_replace('//', '/', $dir.'/'.$f);
              if(is_file($filePath) && !in_array($f_ex, $bk_exc)) $zip->addFile($filePath, ($zipPath ? $zipPath .'/' : '').$f);
              else if(is_dir($filePath)) ZipAddDir($filePath, $zip, ($zipPath ? $zipPath .'/' : '').$f);
            }
          }
          closedir($d);
        }
      }
      //create object with ZIP, call ZipAddDir() to add passed $dirs/$files from $root
      function ZipDir($root, $dirs, $files, $zipFile, $zipPath = ''){
        $nrd = count($dirs);
        $zip = new ZipArchive();
        $zip->open($zipFile, ZIPARCHIVE::OVERWRITE);

        if(count($files) >0) ZipAddDir($root, $zip, $zipPath, $files);  //add the files from root in ZIP
        if($nrd >0){ //add the $dirs
          for($i=0; $i<$nrd; $i++) ZipAddDir(str_replace('//', '/', $root .'/'. $dirs[$i]), $zip, $dirs[$i]);
        }
        $zip->close();
      }

      //set zip name (last part from $root)
      $name = explode('/', $root);
      $name = end($name);
      $zip_nm = 'dir-'. ($name =='' ? 'root' : $name) .'.zip';
      try{
        $zipPath = rtrim($this->bk_dir, '/') .'/'. $zip_nm;
        ZipDir($root, $dirs, $files, $zipPath);

        $re = '-'. sprintf($this->langTxt('ok_saved'), $zip_nm) .'-';
      }
      catch(Exception $ex){
        $re = sprintf($this->langTxt('er_saved'), $zip_nm);
      }
    }
    return $re;
  }
}
