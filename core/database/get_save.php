<?php
  $go->necessary(array('registerid','saveid'));

  if($go->good()) {
    //querystring
    //select tables
    $query = "SELECT t1.*";
    if($count==1) { $query.=", COUNT(*)"; }
    $query .= " FROM lk_savelist t1 ";
    if($wordid!=NULL OR $count==1) { $query.=", lk_save t2 "; }  
    //userid
    $query.=" WHERE t1.userid='".$userid."' ";
    //registerid
    if($registerid!=NULL && $registerid!='*') { $query.=" AND t1.registerid='".$registerid."' "; }
    //wordid(s)
    if($wordid!=NULL) {
      $queryplus=Array();
      if(!is_array($wordid)) { $wordids[0]=$wordid; } else { $wordids=$wordid; }
      foreach($wordids as $wid) {
        $queryplus[]=" (t2.wordid='".$wid."') ";
      }
      $query.="AND ( ".implode(' OR ',$queryplus)." ) ";
    }
    if($wordid!=NULL OR $count==1) { $query.="AND t2.saveid=t1.id "; }
    //search
    if($searchtext!=NULL) { $query.=" AND UCASE(t1.name) RLIKE UCASE('".$searchtext.".*') "; }
    //id
    if($saveid!=NULL) { $query.=" AND t1.id='".$saveid."'"; }
    //count
    if($count == 1) { $query.=" GROUP BY t1.id"; }
    //execute
    $get_save=$go->query($query,1);
  }
  if($go->good()) { //return  
    $return=$get_save['result'];
    $return['count']=$get_save['count'];
    //counting
    if($count==1) {
      $return['savecount'] = $return['COUNT(*)']; //a better name
      unset( $return['COUNT(*)'] );
    }
  }
?>
