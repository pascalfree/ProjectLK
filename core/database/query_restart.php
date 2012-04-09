<?php
  $go->necessary('queryid');

  if($go->good()) {
    //Check User
    $query="SELECT userid FROM lk_activelist WHERE userid='".$userid."' AND id='".$queryid."'";
    $checkuser=$go->query($query,1);
    if($checkuser['count']==0) { $go->error(100); }
  }

  if($go->good())  {
    //restart / neu starten
    $query="UPDATE lk_activelist SET status='1' WHERE userid='".$userid."' AND id='".$queryid."'";
    $go->query($query,2);

    //wrong answered only / nur falsche
    if($wrong) {
      $query="DELETE FROM lk_active WHERE correct='1' AND id='".$queryid."'"; 
      $go->query($query,3);
    }

    $query="UPDATE lk_active SET done='0', correct='0' WHERE id='".$queryid."'"; 
    $go->query($query,4);
  }
?>
