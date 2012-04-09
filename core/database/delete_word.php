<?php
  $go->necessary(array('wordid','allmarked'));

  if($go->good()) {
    if(!is_array($wordid)) { $wordid=array($wordid); }

    if($allmarked) { 
      $params=getglobal();
      $params['nolimit']=1;
      $params['select']='id';
      $getwordid=request('get_word',$params); 
      if( $words['errnum']!=0 ) { $go->error(400,$words['errnum'].': '.$words['errname']); }      
      $wordid=$getwordid['id'];
    }
    if(count($wordid)==0) { $go->missing('wordid'); }
  }

  if($go->good()) { //count words  
    $countid = count($wordid);
  }

  if($go->good()) { 
    //////////
    //Clean up
    //////////
    //tags
    //delete this reference to tag
    $query="DELETE lk_tags FROM lk_tags, lk_words WHERE ";
    for($i=0; $i<$countid; $i++) {
      $query.=" ( lk_tags.wordid='".$wordid[$i]."' AND lk_tags.wordid=lk_words.id AND lk_words.userid='".$userid."') ";
      if($i<$countid-1) { $query.=' OR '; }
    }	
    $deleted_from_tag = $go->query($query,2);
    //delete all dead reference to words
    /* THIS IS EVIL. WILL DELETE EVERY TAG OF OTHER USERS
    $query="DELETE FROM lk_tags
            WHERE NOT EXISTS (SELECT * FROM lk_words WHERE lk_tags.wordid=lk_words.id AND lk_words.userid='".$userid."')";
    $cleanup_from_tag = $go->query($query,3);*/
    //delete unused tags
    $query="DELETE FROM lk_taglist 
            WHERE NOT EXISTS (SELECT * FROM lk_tags WHERE lk_tags.tagid=lk_taglist.id) AND lk_taglist.userid='".$userid."'";
    $deleted_tag = $go->query($query,4);
  }
  if($go->good()) { 
    //save
    //delete this reference to save
    $query="DELETE lk_save FROM lk_save, lk_words WHERE ";
    for($i=0; $i<$countid; $i++) {
      $query.=" ( lk_save.wordid='".$wordid[$i]."' AND lk_save.wordid=lk_words.id AND lk_words.userid='".$userid."') ";
      if($i<$countid-1) { $query.=' OR '; }
    }	
    $deleted_from_save = $go->query($query,5);

    //delete all dead reference to words
    /* THIS IS EVIL. WILL DELETE EVERY SAVE OF OTHER USERS
    $query="DELETE FROM lk_save
            WHERE NOT EXISTS (SELECT * FROM lk_words WHERE lk_save.wordid=lk_words.id AND lk_words.userid='".$userid."')";
    $cleanup_from_save = $go->query($query,6);*/

    //delete empty save
    $query="DELETE FROM lk_savelist WHERE userid='".$userid."'
            AND NOT EXISTS (SELECT lk_save.saveid FROM lk_save WHERE lk_save.saveid=lk_savelist.id)";
    $deleted_save = $go->query($query,7);
  }

  ////delete word
  if($go->good()) { 
    $query="DELETE FROM lk_words WHERE ";
    for($i=0;$i<$countid;$i++) {
      $query.=" ( id='".$wordid[$i]."' AND userid='".$userid."') ";
      if($i<$countid-1) { $query.=' OR '; }
    }
    $delete_word=$go->query($query,1);
  }
  if($go->good()) { 
    $return=Array('delfromtag' => $deleted_from_tag['count'],
                  'delfromsave' => $deleted_from_save['count'],
                  'cleanfromtag' => $cleanup_from_tag['count'],
                  'cleanfromsave' => $cleanup_from_save['count'],
                  'deltag' => $deleted_tag['count'],
                  'delsave' => $deleted_save['count'],
			            'count' => $delete_word['count']);
  }
?>
