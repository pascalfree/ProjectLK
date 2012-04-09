<?php
  $go->necessary('formid');

  if($go->good()) {
    //forbidden characters / Verbotene Zeichen
    $forbidden= array('"', '#', '+');
	  $newform=str_replace($forbidden, '', $newform);
       
    //update / überarbeiten
    $edits=NULL;
	  if($newform!=NULL && $newform!='') { $edits[]=" name='".$newform."'"; }
	  if($newinfo!=NULL) { $edits[]=" info='".$newinfo."'"; }
    if($edits!=NULL) {
      $query="UPDATE lk_forms SET ".implode(',',$edits);
      $query.=" WHERE id='".$formid."' AND userid='".$userid."' ";
      $edit_form=$go->query($query,1); 
    }
  }
  if($go->good()) { 
    $return=array('count' => $edit_form['count']);
  }
?>
