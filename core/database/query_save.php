<?php
  $go->necessary('newsavename','registerid');
  if($go->good()) {
    //Check name / Name überprüfen
    $query="SELECT name FROM lk_savelist 
            WHERE userid='".$userid."' AND registerid='".$registerid."' 
            AND name='".$newsavename."'";
    $check_savename=$go->query($query,1);
    if($check_savename['count']>0) { $go->error(104); }
  }
  if($go->good()) {
    //Load / Laden
    $andwrong=$wrong==1? "AND correct='0'" : "";
    $query="SELECT wordid FROM lk_active WHERE id='".$queryid."'".$andwrong; 
    $get_wordid=$go->query($query,2);
    $wordid=$get_wordid['result']['wordid'];
    if(count($wordid)==0) { $go->missing('wordid'); }
  }
  if($go->good()) {
    //Add / Hinzufügen
    $query="INSERT INTO lk_savelist (userid, registerid, name) 
            VALUES ('".$userid."', '".$registerid."', '".$newsavename."')";
    $create_save=$go->query($query,3);
    $savedid=$create_save['id'];
  }
  if($go->good()) {  
    //insert wordids
    $countid=count($wordid);
    if($countid>0) {
      //Querystring
      $insert='';
      for($i=0; $i<$countid; $i++) {
        $insert.="('".$savedid."', '".$wordid[$i]."') ";
        if($i<$countid-1) { $insert.=','; }
      }	
      $query="INSERT INTO lk_save (saveid, wordid) 
      VALUES ".$insert;
      //Execute
      $add_save=$go->query($query,4);
    }
  }
  if($go->good()) {  
    $return=array('savedid' => $savedid,
                  'count' => $add_save['count']);
  }
?>
