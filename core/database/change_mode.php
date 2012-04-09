<?php
  $go->necessary('queryid');  

  if($go->good()) {
    //check User and get mode
    $query="SELECT userid,mode FROM lk_activelist WHERE userid='".$userid."' AND id='".$queryid."'";
    $checkuser=$go->query($query,1);
    if($checkuser['count']==0) { $go->error(100); }
    else { $mode=$checkuser['result']['mode'][0]; } 
  }
  if($go->good()) {
    //unused?
    $query="SELECT done FROM lk_active WHERE done='1' AND id='".$queryid."'";
    $checkdone=$go->query($query,2);
    if($checkdone['count']>0) { $go->error(103); }    
  }
  if($go->good()) {
    //Change / Ändern
    $chmode=$chwhat==0? array(0,1,0,1,4) : array(1,0,3,2,4);    
    $query="UPDATE lk_activelist SET mode='".$chmode[$mode]."' WHERE id='".$queryid."' AND userid='".$userid."'"; 
    $qresult=$go->query($query);
    if($qresult['count']==0) { $go->error(103); }
  }
  if($go->good()) {
    $return=array('chmode' => $chmode[$mode]);
  }
?>
