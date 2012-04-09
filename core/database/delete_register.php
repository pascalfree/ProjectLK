<?php
  $go->necessary('registerid');

  if($go->good()) {
    //save
    $query2="DELETE t1, t2 FROM lk_savelist t1, lk_save t2 WHERE t1.registerid='".$registerid."' AND t1.userid='".$userid."' AND t2.saveid=t1.id ";
    $delete_save=$go->query($query2,1);
    $countrows['save'] = $delete_save['count'];
  }
  if($go->good()) {
    //tags
    $query3="DELETE t3, t4 FROM lk_taglist t3, lk_tags t4 WHERE t3.registerid='".$registerid."' AND t3.userid='".$userid."' AND t4.tagid=t3.id ";
    $delete_tag=$go->query($query3,2);
    $countrows['tag']=$delete_tag['count'];
  }
  if($go->good()) {
    //verbs
    $query5="DELETE lk_verbs FROM lk_verbs, lk_words WHERE lk_words.id=lk_verbs.wordid AND lk_words.registerid='".$registerid."' AND lk_words.userid='".$userid."' ";
    $delete_verbs=$go->query($query5,3);
    $countrows['verbs']=$delete_verbs['count'];
  }
  if($go->good()) {
    //words
    $query4="DELETE FROM lk_words WHERE registerid='".$registerid."' AND userid='".$userid."' ";
    $delete_words=$go->query($query4,4);
    $countrows['word']=$delete_words['count'];
  }
  if($go->good()) {
    //forms
    $query6="DELETE FROM lk_forms WHERE registerid='".$registerid."' AND userid='".$userid."' ";
    $delete_forms=$go->query($query6,5);
    $countrows['form']=$delete_forms['count'];
  }
  if($go->good()) {
    //persons
    $query7="DELETE FROM lk_persons WHERE registerid='".$registerid."' AND userid='".$userid."' ";
    $delete_persons=$go->query($query7,6);
    $countrows['person']=$delete_persons['count'];
  }
  if($go->good()) {
    //register
    $query1="DELETE FROM lk_registers WHERE id='".$registerid."' AND userid='".$userid."' ";
    $delete_reg=$go->query($query1,7);
    $countrows['register']=$delete_reg['count'];
  }
  if($go->good()) {
    $return=Array('count' => $countrows);
  }
?>
