<?php
  //Querystring
  $query="SELECT t1.registerid, t2.* FROM lk_words t1, lk_verbs t2 
  WHERE t1.userid='".$userid."' AND t1.id=t2.wordid";
  $getarray=array('id','wordid','personid','formid');
  foreach ($getarray as $tname) {
    if($$tname!=NULL) {
      $getid=$$tname;
      $query.=" AND ";
      if(is_array($getid)) {
        $len=count($getid);
        unset($inquery);
        for($i=0;$i<$len;$i++) {
          $inquery[]="t2.".$tname."='".$getid[$i]."'";
        }
        $query.="( ".implode(' OR ',$inquery)." )";
      } else {
        $query.="t2.".$tname."='".$getid."'";
      }
    }
  }
  if($searchtext != NULL) { $query.=" AND UCASE(t2.kword) RLIKE UCASE('".$searchtext.".*') "; }

  //execute
  $getverb=$go->query($query,1); 

  if($go->good()) {
    //return
    if($struc==1) {
      $return=$getverb['result'];
      $return['count']=$getverb['count'];
    } else {
      $v=$getverb['result'];
      for($i=0;$i<$getverb['count'];$i++) {
        $return[$v['wordid'][$i]][$v['personid'][$i]][$v['formid'][$i]]=array('name'=>$v['kword'][$i],'id'=>$v['id'][$i]);
      }
    }
  }
?>
