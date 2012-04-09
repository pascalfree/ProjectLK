<?php
  $go->necessary('newwordfirst','newwordfore','newgroup','registerid');

  if($go->good()) {
    //forbidden characters
    $forbidden= array('"', '#', '+','\\');
    $todecode=array('newwordfirst','newwordfore','newsentence','newtags');
    foreach($todecode as $val) { 
		  $$val=str_replace($forbidden, '', $$val);
	  }

    //similar words
    $similar=0;
    //if($force!=1) {
    $swordid=request('get_word',array('registerid' => $registerid, 'select'=>'tword.id','searchtext'=>array($newwordfirst,$newwordfore),'nolimit'=>1, 'gettags'=>0)); //fix: don't get tags
    if($swordid['count']>0) { 
      $similar=1; 
    }
    $similarid=$swordid['id'];
    //}
  }
  if($similar == 0 || $force == 1) {
    if($go->good()) {
      //Add word
      $add=''; $addtime='';
      if($time_created!=NULL) { $add=', time_created'; $addtime=", '".$time_created."'"; }
      $query="INSERT INTO lk_words (userid, registerid, wordfirst, wordfore, `groupid`, sentence, wordclassid".$add.") 
            VALUES ('".$userid."', '".$registerid."', '".mysql_real_escape_string($newwordfirst)."', '".mysql_real_escape_string($newwordfore)."', '".$newgroup."', '".mysql_real_escape_string($newsentence)."', '".$newwordclass."'".$addtime.")";
	    $create_word=$go->query($query,1);
	    $wordid=$create_word['id'];
    }
    if($go->good()) {
	    //Tags
	    $newtags_arr=explode(',', $newtags);
	    if($newtags!='' && $newtags!=NULL) {
	      //Create querystring
		    $query="INSERT INTO lk_tags (wordid, tagid) 
		            VALUES ";
		    $ctags=count($newtags_arr);		
	      for($i=0;$i<$ctags;$i++) {
		      $thistag=trim($newtags_arr[$i]);
		      if($thistag!='' && $thistag!=NULL) {
		        //Check if already exists
		        $interquery="SELECT id FROM lk_taglist WHERE name='".$thistag."' AND userid='".$userid."' AND registerid='".$registerid."'";
				    $check_tag=$go->query($interquery,2);
		        if($check_tag['count']>0) { //Get ID
		          $tagid=$check_tag['result']['id'][0];
		        } else { //Or create new
		          $interquery="INSERT INTO lk_taglist (userid, registerid, name) 
		                       VALUES ('".$userid."', '".$registerid."', '".$thistag."') ";
		          $create_tag=$go->query($interquery,3);
	            $tagid=$create_tag['id'];	
		        }
			
		        $query .= "('".$wordid."', '".$tagid."')";
		        if( $i!=$ctags-1 ) { $query .=" , "; }
		      }
          $newtags_arr[$i]=$thistag;
          $tagid_arr[$i]=$tagid;
		    }
        if($go->good()) {
          //execute
		      $add_tags=$go->query($query,4);
		    }  
	    }
    }
  } 	
  if($go->good()) {  
    $return=array('wordfirst' => $newwordfirst,
                  'wordfore' => $newwordfore,
                  'sentence' => $newsentence,
                  'group' => $newgroup,
                  'register' => $registerid,
                  'wordclass' => $newwordclass,
                  'taglist' => array( 
                    'id' => $tagid_arr,
                    'name' => $newtags_arr,
                    'count' => $add_tags['count'],
                    'errors' => '',             
                  ),
                  'id' => $wordid,
                  'similarid' => $similarid,
                  'count' => $create_word['count'],
                  //'words' => $words,
                  'similar' => $similar);
  }
?>
