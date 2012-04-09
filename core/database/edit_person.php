<?php
  $go->necessary('personid');

  if($go->good()) {
    //forbidden characters / Verbotene Zeichen
    $forbidden= array('"', '#', '+');
	  $newperson=str_replace($forbidden, '', $newperson);
       
    //update / überarbeiten
    $edits=NULL;
	  if($newperson!=NULL && $newperson!='') { $edits[]=" name='".$newperson."'"; }
	  if($neworder!=NULL && $neworder!='') { $edits[]=" `order`='".$neworder."'"; }
    if($edits!=NULL) {
      $query = "UPDATE lk_persons SET ".implode(',',$edits);
      $query .= " WHERE id='".$personid."' AND userid='".$userid."' ";
      $edit_person = $go->query($query,1); 
    }
  }
  if($go->good()) {
    $return = array('count' => $edit_person['count']);
  }
?>
