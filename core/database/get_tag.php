<?php 
  //need registerid or a specific id of the tag
  $go->necessary(array('registerid','tagid'));

  if($go->good()) {
    if($select==NULL) { $select = 't1.*'; } //default: select all
    if( $count==1 ) { $select .= ', COUNT(*)'; }
    //querystring
    $query="SELECT DISTINCT ".$select." FROM lk_taglist t1";          //from lk_taglist t1 !
    if($wordid!=NULL OR $groupid!=NULL OR $count==1) { $query.=", lk_tags t2"; }   //from lk_tags    t2
    if($groupid!=NULL) { $query.=", lk_words t3"; }    //from lk_words   t3
    $query.=" WHERE t1.userid='".$userid."' ";         //right USERID !!
    if($registerid!=NULL && $registerid!='*') { $query.="AND t1.registerid='".$registerid."' "; }     //with registerid
    if($wordid!=NULL) {                                               //with one of these wordids
      $queryplus=Array();
      if(!is_array($wordid)) { $wordids[0]=$wordid; } else { $wordids=$wordid; }
      foreach($wordids as $wid) {
        $queryplus[]=" (t2.wordid='".$wid."') ";
      }
      $query.="AND ( ".implode(' OR ',$queryplus)." ) ";
    }
    if($wordid!=NULL OR $count==1) { $query.="AND t2.tagid=t1.id "; }
    //Fix: Added "AND t1.id=t2.tagid"
    if($groupid!=NULL) { $query.=" AND t3.groupid='".$groupid."' AND t3.id=t2.wordid AND t1.id=t2.tagid "; }  //with groupid
    if($searchtext!=NULL) { $query.=" AND UCASE(t1.name) RLIKE UCASE('".$searchtext.".*') "; }  //search
    if($tagid!=NULL) { $query.=" AND t1.id='".$tagid."' "; }          //with tagid
    if($limit!=NULL) { $query.=" LIMIT 0,".($limit+1)." "; }          //limit
    if($groupby!=NULL) { $query.=" GROUP BY `".$groupby."`"; }        //groupby
    elseif($count == 1) { $query.=" GROUP BY t1.id"; }    //default: groupby tagid
    if( $groupby != NULL ) {
      $query .= " ORDER BY `".$orderby."` ";
    } else {
      $query .= " ORDER BY t1.name "; //default: order by tagname
    }
    //execute
    $get_tag=$go->query($query,1);
  }
  if($go->good()) { //return
    if($limit!=NULL && $get_tag['count']>$limit) { $get_tag['count']==$limit; $more=1; } else { $more=0; }
    $return=$get_tag['result']; 
    if($count==1) {
      $return['tagcount'] = $return['COUNT(*)']; //a better name
      unset( $return['COUNT(*)'] );
    }
    $return['more'] = $more;
    $return['count'] = $get_tag['count'];
  }
?>
