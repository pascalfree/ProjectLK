<?php
  $go->necessary(array('registerid','formid'));
  if($go->good()) {
    //querystring
    $query="SELECT DISTINCT t1.* FROM lk_forms t1 ";
    if($wordid!=NULL) { $query.=" , lk_verbs t2 "; }
    $query.=" WHERE t1.userid='".$userid."' ";
    if($registerid!=NULL && $registerid!='*') { $query.=" AND t1.registerid='".$registerid."' "; }
    if($wordid!=NULL) { 
      $query.=" AND ( ";
      if(is_array($wordid)) {
        $len=count($wordid);
        for($i=0;$i<$len;$i++) {
          $iquery[]=" t2.wordid='".$wordid[$i]."' ";
        }
        $query.=implode(' OR ',$iquery);
      } else { $query.=" t2.wordid='".$wordid."' "; }
      $query.=" ) AND t2.formid=t1.id ";
    }
    if($formid!=NULL) { $query.=" AND t1.id='".$formid."'"; }
    if($searchtext != NULL) { $query.=" AND UCASE(t1.name) RLIKE UCASE('".$searchtext.".*') "; }
    
    //Execute
    $get_form=$go->query($query,1);
  }
  if($go->good()) {
    //return
    $return=$get_form['result'];
    $return['formid']=$return['id'];
    $return['formname']=$return['name'];      
    $return['count']=$get_form['count'];
  }
?>
