  /* MYSQL */

//if clicked on #ch_all, check all tables in .ch_tables in mysql
var ch_all = document.getElementById('ch_all');
if(ch_all){
  var tables = document.querySelectorAll('#frm_cht .ch_tables input');
  ch_all.querySelector('input').addEventListener('click', function(){ pbmChech('#frm_cht .ch_tables input', this);});
  document.getElementById('frm_cht').addEventListener('submit', function(ev){
    //check if table selected
    for(var i=0; i<tables.length; i++){
      if(tables[i].checked == true){ pbmLoading(); return true;}
    }
    ev.preventDefault();
    alert(pbm_txt.er_sel_table);
  });
}
/* End */

  /* DIRS-FILES */
function DirsFiles(){
  var dirs_root = document.getElementById('dirs_root');
  var dirs_root_ls = dirs_root.querySelectorAll('ul span');  //list with main directories
  var sel_bk_ul = document.querySelector('#sel_bk ul');  //UL with lists with folders and files in selected $dir
  var sel_bk_inp = sel_bk_ul.querySelectorAll('li input');  //all input lists with folders and files to select for backup
  var dir_sel = pbm_txt.root;  //current selected directory
  var dir_sel_e = document.getElementById('dir_sel');  //elm. to show selected folder path
  var ch_dirs = document.querySelector('#ch_dirs input');  //btn to check all folders
  var ch_dirs_n = document.querySelector('#ch_dirs em');  //number of folders
  var ch_files = document.querySelector('#ch_files input');  //btn to check all files
  var ch_files_n = document.querySelector('#ch_files em');  //number of files
  var bk_exc = document.getElementById('bk_exc');  //input with file-extensions to exclude from backup
  var btn_bk = document.getElementById('btn_bk');  //button to backup selected dir/files

  //Send "data" to "php", using the method added to "via", and pass response to "callback" function
  function ajaxSend(data, php, via, callback) {
    var ob_ajax =  (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");    // XMLHttpRequest object

    //put data from "data" into a string to be send to "php"
    var str_data ='';
    for(var k in data) {
      str_data += k +'='+ data[k].replace(/\?/g, '%3F').replace(/=/g, '%3D').replace(/&/g, '%26').replace(/[ ]+/g, '%20') +'&'
    }
    str_data = str_data.replace(/&$/, '');  //delete ending &

    //send data to php
    ob_ajax.open(via, php, true);
    if(via =='post') ob_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ob_ajax.send(str_data);

    //check the state request, if completed, pass the response to callback function
    ob_ajax.onreadystatechange = function() {
      if (ob_ajax.readyState == 4) callback(ob_ajax.responseText);
    }
  }

  //get directories and files in $elm (LI from #dirs_root)
  function getDF(elm){
    dir_sel = (elm.getAttribute('data-path')).replace('//', '/');
    var form_data = {'dir': dir_sel};
    ajaxSend(form_data, pbm_txt.php_self +'?ac=dirs', 'post', function(resp){
      resp = JSON.parse(resp);
      dir_sel_e.innerHTML = dir_sel +'/';

      //delete .dir_sel class
      var cls_ds = dirs_root.querySelector('.dir_sel');
      if(cls_ds) cls_ds.removeAttribute('class');

      //set and add html in $elm (from dirs_root), and $sel_bk_ul
      var elm_d ='';  var sel_df ='';  var nrf =0;
      for(var i=0; i<resp.d.length; i++){
        elm_d +='<li><strong>+</strong><span data-path="'+ (dir_sel +'/'+ resp.d[i]).replace('//', '/') +'">'+ resp.d[i] +'</span></li>';
        sel_df +='<li class="dirs"><label><input type="checkbox" value="'+ resp.d[i] +'" name="d[]" />'+ resp.d[i] +'</label></li>';
      }
      for(var f in resp.f){
        nrf++;
        sel_df +='<li class="files"><label><input type="checkbox" value="'+ f +'" name="f[]" />'+ f +'</label><span>'+ resp.f[f] +'</span></li>';
      }
      var elm_parent = elm.parentNode;
      elm_parent.innerHTML = '<strong>-</strong><span class="dir_sel" data-path="'+ dir_sel +'">'+ elm.innerHTML +'</span><ul>'+ elm_d +'</ul>';
      sel_bk_ul.innerHTML = sel_df;

      elm_parent.className ='show_ul';  //class to not hide childs UL

      //register click to new added dirs
      sel_bk_inp = sel_bk_ul.querySelectorAll('li input');
      clickDir(elm_parent.querySelectorAll('ul span'));
      clickPreDir(elm_parent.querySelectorAll('ul strong'));

      ch_dirs_n.innerHTML = resp.d.length;  ch_files_n.innerHTML = nrf;  //actualize number of dir/files
      ch_dirs.checked =false;  ch_files.checked = false;  //uncheck buttons for select all
    });
  }

  //register click event to <strong> in list with directories in $elm to set class to hide/show childs UL
  function clickPreDir(elm){
    for(var i=0; i<elm.length; i++) elm[i].addEventListener('click', function(){
      var elm_parent = this.parentNode;
      var cls_sign = {hide_ul:{cls:'show_ul', sign:'+'}, show_ul:{cls:'hide_ul', sign:'-'}}; //obj. to switch show_ul/hide_ul
      if(cls_sign[elm_parent.className]){
        elm_parent.className = cls_sign[elm_parent.className].cls;
        this.innerHTML = cls_sign[elm_parent.className].sign;
      }
    });
  }
  clickPreDir(document.querySelectorAll('#dirs_root ul strong'));

  //register click event to list with directories in $elm to get $dir structure
  function clickDir(elm){
    for(var i=0; i<elm.length; i++) elm[i].addEventListener('click', function(){ getDF(this);});
  }
  clickDir(dirs_root_ls);

  //called when click #btn_bk button
  function pbmBackup(){
    var bk_dirs = [];  var bk_files = [];  //arrays with names of selected dirs/files

    //traverse the inputs, adds checked in $bk_dirs /$bk_files according to "name"
    for(var i=0; i<sel_bk_inp.length; i++){
      if(sel_bk_inp[i].checked ===true){
        var i_n = sel_bk_inp[i].getAttribute('name');
        if(i_n =='d[]') bk_dirs.push(sel_bk_inp[i].value);
        else if(i_n =='f[]') bk_files.push(sel_bk_inp[i].value);
      }
    }

    if(bk_dirs.length <1 && bk_files.length <1) { window.scrollTo(0,0); alert(pbm_txt.er_sel_df);}
    else {
      pbmLoading();
      ajaxSend({root:dir_sel, dirs:JSON.stringify(bk_dirs), files:JSON.stringify(bk_files), bk_exc:bk_exc.value}, pbm_txt.php_self +'?ac=dirs', 'post', function(resp){
      pbm_load.style.display ='none';
      alert(resp);
      if(resp.match(/^\-(.+?)-$/i)) window.location = pbm_txt.php_self;  //redirect to zip archives
    });
    }
  }

  //change '/' with 'Root' in #dir_sel
  if(dir_sel_e.innerHTML =='/') dir_sel_e.innerHTML = pbm_txt.msg_root;

  document.querySelector('nav div').style.display ='none';  //hide elm. with DB name
  ch_dirs.addEventListener('click', function(){ pbmChech('#sel_bk ul .dirs input', this);});  //check all folders
  ch_files.addEventListener('click', function(){ pbmChech('#sel_bk ul .files input', this);});  //check all files

  //click on #btn_bk that calls pbmBackup()
  btn_bk.addEventListener('click', pbmBackup);
  

  document.getElementById('root_dir').addEventListener('click', function(){ window.location.reload(true);});  //refresh when click #root_dir
}
if(document.getElementById('dirs_root')) DirsFiles();
/* End */

  /* COMMONS */

//display loading message
function pbmLoading(){
  var pbm_load = document.getElementById('pbm_load');
  if(!pbm_load) document.querySelector('body').insertAdjacentHTML('beforeend', '<div id="pbm_load"><span>'+ pbm_txt.msg_loading +'</span></div>');
  else pbm_load.style.display ='block';
}

//check /uncheck elms. from $slt (selector), according to $bt (checkbox button) when click it
function pbmChech(slt, bt){
  var elms = document.querySelectorAll(slt);
  if(elms){
    for(var i=0; i<elms.length; i++) elms[i].checked = bt.checked;
  }
}

//if #frm_zip
var frm_zip = document.getElementById('frm_zip');
if(frm_zip){
  var dir_bk = document.getElementById('dir_bk').value +'/';
  var zip_files = document.querySelectorAll('#frm_zip .zip_files');

  //get buttons in #frm_zip, register click to submit form according to button
  if(zip_files){
    var btn_zip = document.querySelectorAll('#frm_zip #res_file, #frm_zip #get_file, #frm_zip #del_file');
    for(var i=0; i<btn_zip.length; i++){
      btn_zip[i].addEventListener('click', function(e){
        for(var i2=0; i2<zip_files.length; i2++){
          if(zip_files[i2].checked === true){
            //if to restore dir/files backup, stop and alert msg.
            if(zip_files[i2].parentNode.parentNode.id =='bk_dirs' && e.target.id =='res_file'){ alert(pbm_txt.msg_restore_bkdir); break;}
            else {
              var conf_del = (e.target.id =='del_file') ? window.confirm(pbm_txt.msg_when_del) : true;
              if(conf_del){
                frm_zip['pbm_zip'].value = e.target.id;
                if(e.target.id =='res_file') frm_zip.setAttribute('action', frm_zip.action +'?ac=mysql');  //change 'action' address
                if(e.target.id !='get_file') pbmLoading();  //show Loading if not get_file request
                frm_zip.submit();
                break;
              }
            }
          }
        }
      });
    }
  }

  frm_zip.addEventListener('submit', pbmLoading);  //on submit form with zip_files, show Loading
}
