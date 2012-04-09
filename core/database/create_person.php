<?php
  $go->necessary('registerid','newperson');
  if($go->good()) {
    //forbidden characters / Verbotene Zeichen
    $forbidden= array('"', '#', '+');
	  $newperson=str_replace($forbidden, '', $newperson);
	  
    //increment order
    if($neworder == NULL) {
      $count=$go->query("SELECT COUNT(*) FROM lk_persons WHERE registerid='".$registerid."' AND userid='".$userid."'",1);
      $neworder = $count['result']['COUNT(*)'][0];
    }
  }
  if($go->good()) {
    //comma separated input
    $newpersonarr=explode(",",$newperson);
    $countarr=count($newpersonarr);
    $countperson=0;
    for($i=0; $i<$countarr; $i++) { //add multiple entries
      //trim
      $newpersonarr[$i] = trim($newpersonarr[$i]);
      //write / Schreiben
      $add=''; $addval='';
      if($neworder!=NULL) { $add.=', `order`'; $addval.=", '".$neworder++."'"; }
      $query="INSERT INTO lk_persons (userid, registerid, name ".$add." ) VALUES";
      $query.="('".$userid."', '".$registerid."', '".$newpersonarr[$i]."' ".$addval." )";
      $create_person=$go->query($query,$i+2);
      $countperson= $countperson + $create_person['count'];
      $personid[]=$create_person['id'];
    }
  }
  if($go->good()) {
    $return=Array('newname' => $newpersonarr,
                  'newid' => $personid,
                  'neworder' => $neworder,
                  'count' => $countperson);
  }
?>
