<?php
//////////////////////////////////////
/* NAME: change_password
/* PARAMS: newpassword, oldpassword (userid by session)
/* RETURN: -
/* DESCRIPTION: Changes userpassword
/* VERSION: 19.04.2011
////////////////////////////////////*/

  $go->necessary('newpassword','oldpassword');

  if($userid==0) { $go->error(100); }
  if($go->good()) {
    $query="UPDATE lk_user SET passw='".md5($newpassword)."' WHERE id='".$userid."' AND passw='".md5($oldpassword)."'"; 
    $qresult=$go->query($query,1);
    //if($qresult['count']==0) { $go->error(103); }
  }
  
  if($go->good()) {
    $return['success'] = $qresult['count'];
  }
?>
