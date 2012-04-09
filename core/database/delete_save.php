<?php
  $go->necessary('saveid');

  if($go->good()) {
    $query="DELETE t1,t2 FROM lk_savelist t1, lk_save t2 WHERE t1.id='".$saveid."' AND t1.userid='".$userid."' AND t2.saveid=t1.id ";
    $delete_save=$go->query($query,1);
  }
  if($go->good()) {		
    $return=array('count' => $delete_save['count']);
  }
?>
