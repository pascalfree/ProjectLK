<?php
  $go->necessary('newemail','newlang','newgui','newhints');

  if($userid==0) { $go->error(100); }
  if($go->good()) {
    $query="UPDATE lk_user SET email='".$newemail."', language='".$newlang."', theme='".$newtheme."', gui='".$newgui."', hints='".$newhints."' WHERE id='".$userid."'"; 
    $qresult=$go->query($query,1);
  }
?>
