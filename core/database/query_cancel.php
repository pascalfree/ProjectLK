<?php
  $go->necessary('queryid');

  if($go->good()) {
    //Check User
    $query="SELECT userid FROM lk_activelist WHERE userid='".$userid."' AND id='".$queryid."'";
    $checkuser=$go->query($query,1);
    if($checkuser['count']==0) { $go->error(100); }
  }
  if($go->good())  {
    //Cancel / Abbrechen    
    $query="UPDATE lk_activelist SET status='0' WHERE userid='".$userid."' AND id='".$queryid."'"; 
    $go->query($query,2);
    $query="UPDATE lk_active SET done='1' WHERE id='".$queryid."'"; 
    $go->query($query,3);
  }
?>
