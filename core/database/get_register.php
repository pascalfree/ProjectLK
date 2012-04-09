<?php
  //select
  $query="SELECT tr.id, tr.name";
  if($gettime==1) { $query.=", tr.time_created "; }  //time
  if($count==1) { $query.=", COUNT(tw.id) "; }        //count
  $query .= " FROM lk_registers tr ";   
  if($count==1) { $query.=" LEFT JOIN lk_words tw ON tr.id=tw.registerid "; }     //count
  $query .= "WHERE tr.userid='".$userid."' "; //USERID
  if($registerid!==NULL && $registerid!=='*') { $query.=" AND tr.id='".$registerid."' "; }
  if($registername!=NULL) { $query.=" AND tr.name='".regexpencode($registername)."' "; }
  if($searchtext!=NULL) { $query.=" AND UCASE(tr.name) RLIKE UCASE('".regexpencode($searchtext).".*') "; }
  if($count==1) { //count
    $query .= " GROUP BY tr.id ";
  }
  $query .= " ORDER BY tr.time_created";

  //execute
  $get_reg=$go->query($query,1);
  
  $return=$get_reg['result'];
  $return['count']=$get_reg['count'];
  //counting
  if($count==1) {
    $return['registercount'] = $return['COUNT(tw.id)']; //a better name
    unset( $return['COUNT(tw.id)'] );
  }
?>
