<?php
$errors='';

//Import File
if($_REQUEST['startimport']!=NULL) {
  $import = plk_request('import',$_POST);
}
//Save File
if($_FILES['newimport']!=NULL) {
  $filename = basename($_FILES['newimport']['name']);
  $ext = explode('.',$filename); $ext=$ext[count($ext)-1];
  if($ext != 'xml') { $errors=$plk_la['err_invalidfile']; }
  else {
    if(!move_uploaded_file($_FILES['newimport']['tmp_name'], UPDIR . $plk_you -> id.'import.xml')) {
      $errors=$plk_la['err_upload'];
    }
  }
  //Cleaning Job
  $tdir = opendir(UPDIR);
  while (($tfile = readdir($tdir)) !== false) {
    if(preg_match('/[1-9]+import\.xml$/',$tfile)) {
      $last = filemtime(UPDIR.$tfile);
      $now = time();
      if($now-$last>60*60) { unlink(UPDIR.$tfile); }
    }
  }
}

//header
$importfile = UPDIR . $plk_you -> id.'import.xml';
$toolbar = link_back();
if(file_exists($importfile)) { 
  $toolbar .= ' <a href="javascript: void(0)" onclick="plk.req(\'delete_import\',\'\',[function(){ do_shutter(1); },function() {location.href = plk.here.path();}])">'.$plk_la['otherfile'].'</a>';  
}
load_head($toolbar);

if($_REQUEST['startimport'] != NULL) {  //Import done
?>
  <div class="contentbox">
    <span class="title"><?=$plk_la['import'] ?></span><br>
    <?php
      if($import['errnum']) { echo $plk_la['err_import'],'<br><br>'; } 
    ?>
    <?=$plk_la['registers']?>: <?=$import['count']['register']?><br>
    <?=$plk_la['savepoints']?>: <?=$import['count']['save']?><br>
    <?=$plk_la['words']?>: <?=$import['count']['word']?><br>
    <?=$plk_la['forms']?>: <?=$import['count']['form']?><br>
    <?=$plk_la['persons']?>: <?=$import['count']['person']?><br>
    <?=$plk_la['sverbs']?>: <?=$import['count']['verb']?><br>
    <a href=""><?=$plk_la['moreimport'] ?></a>
  </div>
<?php
} elseif(file_exists($importfile)) {  //Select what to import
  $importing = plk_util_getImport($importfile);

  echo '<form action="" method="POST" style="text-align:left">';
  if($importing['registerlist']) {
    //register
    foreach($importing['registerlist'] as $reglist) {
      $list_reg[]='<input type="checkbox" checked="checked" class="im_newreg" id="check_reg_'.$reglist['id'].'" name="registerlist[]" value="'.$reglist['id'].'" onclick="javascript: ableall(\'reg_'.$reglist['id'].'\',this.checked)"><label for="check_reg_'.$reglist['id'].'">'.$reglist['name'].'</label><br>';
    }
    $list_reg = implode('',$list_reg);

    $disable = $plk_here->registerid!=NULL?'':' disabled="disabled" ';
    $existingreg = plk_request('get_register');  
    if($existingreg['count']>0) {
      for($i = 0; $i < $existingreg['count']; $i++) {
        $ch = $plk_here -> registerid == $existingreg['id'][$i] ? ' checked="checked" ' : '';
        $list_realreg[] = '<input type="radio" '. $ch . $disable .' class="im_realreg" id="real_reg_'. $existingreg['id'][$i] .'" name="registerid" value="'. $existingreg['id'][$i]. '"/><label for="real_reg_'. $existingreg['id'][$i] .'">'. $existingreg['name'][$i] .'</label><br>';
      }
      $list_realreg = implode('',$list_realreg);
    }

    if($plk_here -> registerid != NULL) { $check = array(' checked="checked" ', ''); }
    else { $check = array('', ' checked="checked" '); }
    ?>
    <div class="contentbox">
    <?php
      echo '<span class="title">'.$plk_la['registers'].'</span><br>';
      if($existingreg['count'] > 0) {
      echo '<fieldset>';
      echo '<legend><input type="checkbox" name="intoregister" value="1" id="intoregister" '. $check[0] .' onclick="javascript: ableall(\'realreg\',this.checked);">';
      echo '<label for="intoregister">'. $plk_la['importtoreg'] .'</label></legend>';
      echo $list_realreg;
      echo '</fieldset>';
      }
      echo '<fieldset>';
      echo '<legend>'. $plk_la['importregs'] .'</legend>';  
      echo $list_reg;
      echo '</fieldset>';
    ?>
    </div>
    <?php
  }

  //tags
  if($importing['taglist']) {
    foreach($importing['taglist'] as $taglist) {
      $list_tag[$taglist['registerid']][]='<input type="checkbox" checked="checked" id="check_tag_'.$taglist['id'].'" name="taglist[]" value="'.$taglist['id'].'" class="im_reg_'.$taglist['registerid'].'"/><label for="check_tag_'.$taglist['id'].'">'.$taglist['name'].'</label><br>';
    }
  }
  //20120409 - fix: always show
  foreach($importing['registerlist'] as $reglist) { //without tag
    $list_tag[$reglist['id']][] = '<input type="checkbox" checked="checked" id="check_tag_without" name="withouttag[]" value="'.$reglist['id'].'" class="im_reg_'.$reglist['id'].'"/><label for="check_tag_without">'.$plk_la['withouttag'].'</label><br>';
  }
  echo '<div class="contentbox">';
    echo '<span class="title">'.$plk_la['tags'].'</span><br>';
    foreach($importing['registerlist'] as $reglist) {
      if($list_tag[$reglist['id']]) { //if has tags
        echo '<fieldset class="im_reg_'.$reglist['id'].'">';
        echo '<legend>'.$reglist['name'].'</legend>';
        echo implode('',$list_tag[$reglist['id']]);
        echo '</fieldset>';
      }
    }
  echo '</div>';
  //}

  //saves
  if($importing['savelist']) {
    foreach($importing['savelist'] as $savelist) {
      $list_save[$savelist['registerid']][]='<input type="checkbox" checked="checked" id="check_save_'.$savelist['id'].'" name="savelist[]" value="'.$savelist['id'].'" class="im_reg_'.$savelist['registerid'].'"/><label for="check_save_'.$savelist['id'].'">'.$savelist['name'].'</label><br>';
    }
  }
  foreach($importing['registerlist'] as $reglist) { //20120409 - fix: always show
    $list_save[$reglist['id']][]='<input type="checkbox" checked="checked" id="check_save_without" name="withoutsave[]" value="'.$reglist['id'].'" class="im_reg_'.$reglist['id'].'"/><label for="check_save_without">'.$plk_la['withoutsave'].'</label><br>';
  }
  echo '<div class="contentbox">';
    echo '<span class="title">'.$plk_la['savepoints'].'</span><br>';
    foreach($importing['registerlist'] as $reglist) {
      if($list_save[$reglist['id']]) { //if has saves
        echo '<fieldset class="im_reg_'.$reglist['id'].'">';
        echo '<legend>'.$reglist['name'].'</legend>';
        echo implode('',$list_save[$reglist['id']]);
        echo '</fieldset>';
      }
    }
  echo '</div>';
  //}

  //Person
  if($importing['personlist']) {
    foreach($importing['personlist'] as $personlist) {
      $list_person[$personlist['registerid']][]='<input type="checkbox" checked="checked" id="check_person_'.$personlist['id'].'" name="personlist[]" value="'.$personlist['id'].'" class="im_reg_'.$personlist['registerid'].'"/><label for="check_person_'.$personlist['id'].'">'.$personlist['name'].'</label><br>';
    }
    echo '<div class="contentbox">';
      echo '<span class="title">'.$plk_la['persons'].'</span><br>';
      foreach($importing['registerlist'] as $reglist) {
        if($list_person[$reglist['id']]) { //if has persons
          echo '<fieldset class="im_reg_'.$reglist['id'].'">';
          echo '<legend>'.$reglist['name'].'</legend>';
          echo implode('',$list_person[$reglist['id']]);
          echo '</fieldset>';
        }
      }
    echo '</div>';
  }
  //Form
  if($importing['formlist']) {
    foreach($importing['formlist'] as $formlist) {
      $list_form[$formlist['registerid']][]='<input type="checkbox" checked="checked" id="check_form_'.$formlist['id'].'" name="formlist[]" value="'.$formlist['id'].'" class="im_reg_'.$formlist['registerid'].'"/><label for="check_form_'.$formlist['id'].'">'.$formlist['name'].'</label><br>';
    }
    echo '<div class="contentbox">';
      echo '<span class="title">'.$plk_la['forms'].'</span><br>';
      foreach($importing['registerlist'] as $reglist) {
        if($list_form[$reglist['id']]) { //if has forms
          echo '<fieldset class="im_reg_'.$reglist['id'].'">';
          echo '<legend>'.$reglist['name'].'</legend>';
          echo implode('',$list_form[$reglist['id']]);
          echo '</fieldset>';
        }
      }
    echo '</div>';
  }
  ?>
  <div class="contentbox">
    <input type="hidden" name="startimport" value="1"/>
    <input type="submit" value="<?=$plk_la['import'] ?>"/>
  </div>
  <?php
  echo '</form>';
} else {  //Upload a file
  if($errors!='') { echo $errors; }
  ?>

  <div class="middlebox">
    <span class="title"><?=$plk_la['import'] ?></span>
    <form id="importfile_form" enctype="multipart/form-data" action="" method="POST">
      <input type="file" name="newimport"/><br>
      <input type="submit" value="<?=$plk_la['upload']?>"/>
    </form>
  </div>

  <?php
}

load_foot('import');
?>
