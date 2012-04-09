<?php
  $go->necessary('queryid','corrid');
  if($go->good()) {
    //Check User
    $query="SELECT mode FROM lk_activelist WHERE userid='".$userid."' AND id='".$queryid."'";
    $checkuser=$go->query($query,1);
    if($checkuser==0) { $go->error(100); }
    else { $mode=$checkuser['result']['mode'][0]; } 
  }

  if($go->good()) {
    //Correct / Korrigieren    
    $query="UPDATE lk_active SET correct='1' WHERE id='".$queryid."' AND wordid='".$corrid."' AND correct='0'"; 
    $qresult=$go->query($query,2);
    if($qresult['count']==0) { $go->error(103); }
  }
  if($go->good()) {
    if(($mode==2 OR $mode==3) AND $ngroup!=NULL) {
      $ngroup++;
      $query="UPDATE lk_words SET `group`='".$ngroup."' WHERE `group`!='af' AND `group`!='ar' AND id='".$corrid."' AND userid='".$userid."'"; 
      $go->query($query,3);
    }
  }
?>
