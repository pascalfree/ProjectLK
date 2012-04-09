<?php
  //need registerid or personid
  $go->necessary(array('registerid','personid'));
  if($go->good()) {
    //querystring
    $query="SELECT DISTINCT t1.* FROM lk_persons t1 ";                //from lk_persons t1 !
    if($wordid!=NULL) { $query.=" , lk_verbs t2 "; }                  //from lk_verbs   t2
    $query.=" WHERE t1.userid='".$userid."' ";         //right USERID !!
    if($registerid!=NULL && $registerid!='*') { $query.=" AND t1.registerid='".$registerid."' "; }
    if($wordid!=NULL) {                                               //with wordid
      $query.=" AND ( ";
      if(is_array($wordid)) {
        $len=count($wordid);
        for($i=0;$i<$len;$i++) {
          $iquery[]=" t2.wordid='".$wordid[$i]."' ";
        }
        $query.=implode(' OR ',$iquery);
      } else { $query.=" t2.wordid='".$wordid."' "; }
      $query.=" ) AND t2.personid=t1.id ";
    }
    if($personid!=NULL) { $query.="AND t1.id='".$personid."'"; }      //with personid
    if($searchtext != NULL) { $query.=" AND UCASE(t1.name) RLIKE UCASE('".$searchtext.".*') "; }                                                //with search
    $query.=" ORDER BY t1.`order`";                                   //order by
    //Execute
    $get_person=$go->query($query,1);
  }
  if($go->good()) {
    //return
    $return=$get_person['result'];
    $return['personid']=$return['id'];
    $return['personname']=$return['name'];      
    $return['count']=$get_person['count'];
  }
?>
