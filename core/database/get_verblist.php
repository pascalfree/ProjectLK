<?php
  if($select==NULL) { $select='DISTINCT t1.wordfore, t1.id'; }
  //get verbs with entry
  $query="SELECT ".$select." FROM lk_words t1, lk_verbs t2 
          WHERE t1.userid='".$userid."' AND t1.id=t2.wordid ";
  if($registerid!=NULL) { $query .= " AND t1.registerid='".$registerid."' "; }
  if($limit!=NULL) { $query .= " LIMIT 0,".($limit+1); }
  $qresult=$go->query($query,1);

  //get verbs without entry
  if($getempty == 1) {
    $query="SELECT ".$select." FROM lk_words t1, lk_verbs t2 
            WHERE t1.userid='".$userid."' AND t1.wordclassid=2 ";
    if($registerid!=NULL) { $query .= " AND t1.registerid='".$registerid."' "; }
    if($limit_empty!=NULL) { $query .= " LIMIT 0,".($limit_empty+1); }
    $emptyresult=$go->query($query,2);  
    $emptyresult=$emptyresult['result'];

    //difference to verbs with entries
    if( $qresult['count'] != 0) { //don't do if no results //would delete everything
      if( is_array($emptyresult['id']) ) {
        $emptyresult['id']=array_merge(array_diff($emptyresult['id'], $qresult['result']['id']));
      }
      if( is_array($emptyresult['wordfore']) ) {
        $emptyresult['wordfore']=array_merge(array_diff($emptyresult['wordfore'], $qresult['result']['wordfore']));
      }
    }
  }

  if($go->good()) {
    $return = $qresult['result'];
    $return['count'] = $qresult['count'];
    if($getempty == 1) {
      $return['empty'] = $emptyresult;
      $return['empty']['count'] = count($emptyresult['id']); //count again
    }
  }
?>
