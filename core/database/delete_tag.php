<?php
  $go->necessary('tagid',array('allmarked','wordid'));

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
    //stop if no word was found
    if(!is_array($wordid)) { $wordid=array($wordid); }
    if(count($wordid)==0) { $go->missing('wordid'); }
  }

  if($go->good()) {
    //delete each tag-word link
    $len=count($wordid);
    $totaldel=0;
    for($i=0;$i<$len;$i++) {
      if($go->good()) {
        $query="DELETE FROM t1 USING lk_tags t1, lk_taglist t2 WHERE t1.wordid='".$wordid[$i]."' 
                AND t1.tagid='".$tagid."' AND t1.tagid=t2.id AND t2.userid='".$userid."' ";
        $delete_tag=$go->query($query,1);
	      $totaldel += $delete_tag['count'];
      } else { break; }
    }
  }

  //delete tag from list if empty
  if($go->good()) {  
    $query="DELETE FROM lk_taglist 
            WHERE NOT EXISTS (SELECT * FROM lk_tags WHERE lk_tags.tagid=lk_taglist.id) AND lk_taglist.userid = '".$userid."'";
    $delete_taglist = $go->query($query,2);  
  }
  
  if($go->good()) {  
    $return=array('count' => $totaldel);
  }
?>
