<?php
  $go->necessary('formid');

  if($go->good()) {
    //Delete
    $query2="DELETE lk_verbs FROM lk_forms, lk_verbs WHERE lk_forms.id='".$formid."' AND lk_forms.userid='".$userid."' AND lk_verbs.formid=lk_forms.id ";
    $delete_verb=$go->query($query2,1);
    $countrows['verb'] = $delete_verb['count'];
  }
  if($go->good()) { 
    $query1="DELETE FROM lk_forms WHERE id='".$formid."' AND userid='".$userid."' ";
    $delete_form=$go->query($query1,2);
    $countrows['form'] = $delete_form['count'];
  }
  if($go->good()) { 
    $return=array('count' => $countrows);
  }
?>
