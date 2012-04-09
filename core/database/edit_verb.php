<?php
  $go->necessary('verbid');

  if($go->good()) {  
    $forbidden= array('/', '"', '#', '+');
	  $newkword=str_replace($forbidden, '', $newkword);

    if($newkword!='' && $newkword!=NULL) {
      $query="UPDATE lk_verbs t1, lk_words t2 SET t1.kword='".$newkword."' ";
      $delete=0;
    } else {
      $query="DELETE t1 FROM lk_verbs t1, lk_words t2 ";
      $delete=1;
    }
    $query.=" WHERE t1.id='".$verbid."' AND t2.id=t1.wordid AND t2.userid='".$userid."'";
    $edit_verb=$go->query($query,1);
  }
  if($go->good()) {  
    $return = array('count' => $edit_verb['count'], 'delete' => $delete, 'query' => $query);
  }
?>
