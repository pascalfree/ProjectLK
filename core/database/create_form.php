<?php
  $go->necessary('registerid','newform');
  if($go->good()) {
    //forbidden characters / Verbotene Zeichen
    $forbidden= array('"', '#', '+');
	  $newform=str_replace($forbidden, '', $newform);
	
    //comma separated input
    $newformarr=explode(",",$newform);
    $countarr=count($newformarr);
    $countform=0;
    for($i=0; $i<$countarr; $i++) { //add multiple entries
      //trim
      $newformarr[$i] = trim($newformarr[$i]);
      //write / Schreiben
      $add=''; $addval='';
      if($newinfo!=NULL) { $add.=', info'; $addval.=", '".$newinfo."'"; }
      $query="INSERT INTO lk_forms (userid, registerid, name ".$add." ) VALUES";
      $query.="('".$userid."', '".$registerid."', '".$newformarr[$i]."' ".$addval.")";
      $create_form=$go->query($query,$i+1);
      $countform= $countform + $create_form['count'];
      $formid[]=$create_form['id'];
    }
  }
  if($go->good()) {	
    $return=Array('newname' => $newformarr,
                  'newid' => $formid,
                  'count' => $countform);
  }
?>
