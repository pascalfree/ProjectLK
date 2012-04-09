<?php
  if($go->good()) {
    $forbidden= array('/', '"', '\'', '#', '+', '\\');
    $newregister=str_replace($forbidden, '', $newregister);

    //add first to create name with id	
	  $query="INSERT INTO lk_registers (userid) VALUES (".$userid.") ";
    $insert_reg=$go->query($query,1);
	  $regid=$insert_reg['id'];
	  if($newregister=='') { $newregister='Register '.$regid; }
  }
  if($go->good()) {
	  //Checkname
	  $num='';
	  $originalname=$newregister;
	  do{
	    $query="SELECT name FROM lk_registers WHERE name='".$newregister."' AND userid='".$userid."' ";
	    $check_name=$go->query($query,2);
		  $resultname=$check_name['result']['name'][0];
		  if($resultname==$newregister) {
			  $used=true;
			  if($num=='') { $num=2; } else { $num++; }
   		} else { $used=false; }
		  if($num!='') { $newregister=$originalname.'('.$num.')'; }
	  } while($used);	

    //Insert
	  $query="UPDATE lk_registers SET name='".$newregister."'";
	  if(isset($fachzahl)) { $query.=", groupcount='".$fachzahl."'"; }
	  if(isset($fachsperren)) { $query.=", grouplock='".$fachsperren."'"; }
	  if(isset($language)) { $query.=", language='".$language."'"; }
	  if(isset($time_created)) { $query.=", time_created='".$time_created."'"; }
	  $query.="WHERE id='".$regid."' ";
	  $result = $go->query($query,1);
  }
  if($go->good()) {  
    $return=array('newid' => $regid,
		              'newname' => $newregister,
                  'count' => $result['count']);
  }
?>
