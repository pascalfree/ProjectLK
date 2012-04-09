<?php
  $go->necessary('personid');

  if($go->good()) {
    //delete verbs
    $query2="DELETE lk_verbs FROM lk_persons, lk_verbs WHERE lk_persons.id='".$personid."' AND lk_persons.userid='".$userid."' AND lk_verbs.personid=lk_persons.id ";
    $delete_verb=$go->query($query2,1);
    $countrows['verb']= $delete_verb['count'];

    //delete person
    $query1="DELETE FROM lk_persons WHERE id='".$personid."' AND userid='".$userid."' ";
    $delete_person=$go->query($query1,2);
    $countrows['form']=$delete_person['count'];
  }
  if($go->good()) {
    $return=array('count' => $countrows);
  }
?>
