<?php
  $go->necessary('registerid','newsave',array('wordid','allmarked'));

  //find name that doesn't exist
  $i=1;
  do {
    if($go->good()) {
      if($i!=1) { $checkname = $newsave." (".$i.")"; }
      else { $checkname = $newsave; }
      $i++;
      //Check name / Name überprüfen
      $query="SELECT name FROM lk_savelist WHERE userid='".$userid."' AND registerid='".$registerid."' AND name='".$checkname."'";
      $check_savename=$go->query($query,1);
    }
  } while( $check_savename['count'] != 0 );

  if($go->good()) {
    $newsave = $checkname; //now definitely
  }  

  if($go->good()) {
    //Add / Hinzufügen
    $add=''; $addtime='';
    if($time_created!=NULL) { $add=', time_created'; $addtime=", '".$time_created."'"; }
    $query="INSERT INTO lk_savelist (userid, registerid, name".$add.") 
            VALUES ('".$userid."', '".$registerid."', '".$newsave."'".$addtime.")";
    $create_save=$go->query($query,2);
		$savedid=$create_save['id'];
  }
  if($go->good()) {
    //every word is marked: get them by global parameters
    if($allmarked) { 
      $params=getglobal();
      $params['nolimit']=1;
      $words=request('get_word', $params);
      if( $words['errnum']!=0 ) { $go->error(400,$words['errnum'].': '.$words['errname']); }
      $wordid=$words['id'];
    }
  }
  if($go->good()) {
    if(count($wordid)==0) { $go->missing('wordid'); }
  }
  if($go->good()) {
	  $countid=count($wordid);
	  $insert='';
	  for($i=0; $i<$countid; $i++) {
	    $insert.="('".$savedid."', '".$wordid[$i]."') ";
	    if($i<$countid-1) { $insert.=','; }
	  }	
	  $query="INSERT INTO lk_save (saveid, wordid) 
			      VALUES ".$insert;
	  $add_save=$go->query($query,3);
  }
  if($go->good()) {	
    $return=Array('wordid' => $wordid,
			            'savedid'  => $savedid,
			            'count' => $add_save['count']);
  }
?>
