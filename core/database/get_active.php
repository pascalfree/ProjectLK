<?php
  //Delete / Löschen
  $query="UPDATE lk_activelist t1 SET t1.status='0' 
          WHERE 1=ALL(SELECT t2.done FROM lk_active t2 WHERE t2.id=t1.id) 
          AND t1.userid='".$userid."'"; 
  $go->query($query,1);
  $query="DELETE t1, lk_activelist
          FROM lk_active t1, lk_activelist   
          WHERE lk_activelist.userid='".$userid."' 
          AND t1.id=lk_activelist.id
          AND lk_activelist.status='0'";
  $go->query($query,2);
  $query="DELETE FROM lk_activelist   
          WHERE lk_activelist.userid='".$userid."' 
          AND lk_activelist.status='0'";
  $go->query($query,3);
     
  //Load / Laden
  if($go->good()) {
    $query="SELECT * FROM lk_activelist WHERE userid='".$userid."' AND status='1'";
    $get_active=$go->query($query,3);
  }
  if($go->good()) {
    $return=$get_active['result'];
    $return['count']=$get_active['count'];
  }
?>
