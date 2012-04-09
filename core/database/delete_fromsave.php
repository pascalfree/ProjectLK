<?php	
  $go->necessary('saveid','registerid',array('wordid','allmarked'));

  if($go->good()) {
    //get words
    if(!is_array($wordid)) { $wordid=array($wordid); }
    
    if($allmarked) { 
      $params=getglobal();
      $params['nolimit']=1;
      $params['select']='tword.id';
      $getwordid=request('get_word',$params); 
      if( $words['errnum']!=0 ) { $go->error(400,$words['errnum'].': '.$words['errname']); }      
      $wordid=$getwordid['id'];
    }
    if(count($wordid)==0) { $go->missing('wordid'); }
  }
  if($go->good()) {
    //remove words
    $totaldel=0;
    $countid=count($wordid);
    for($i=0;$i<$countid;$i++) {
      if($go->good()) {
        $query="DELETE FROM t1 USING lk_save t1, lk_savelist t2 WHERE t1.wordid='".$wordid[$i]."' 
                AND t1.saveid='".$saveid."' AND t1.saveid=t2.id 
                AND registerid='".$registerid."' AND t2.userid='".$userid."' ";
        $delete_save = $go->query($query,1);
        $totaldel += $delete_save['count'];
      } else { break; }
    }
  }
  if($go->good()) {   
    //clean empty save 
    if($totaldel>0) {
      $query="DELETE FROM lk_savelist WHERE userid='".$userid."'
              AND NOT EXISTS (SELECT lk_save.saveid FROM lk_save WHERE lk_save.saveid=lk_savelist.id)";
      $go->query($query,2);
    }
  }
  if($go->good()) {	
    $return=array('count' => $totaldel, 'test' => $delete_save);
  }
?>
