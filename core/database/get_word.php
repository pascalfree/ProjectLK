<?php
  //informations
  //using groupid='*' returns same as groupid==NULL but also groupcount.
  //registerid='*' returns results from all registers  

  if($go->good()) {
    //default values
    if($fromlim==NULL) { $fromlim=0; }
    if($tolim==NULL) { $tolim=20; }
    if($select==NULL) { $select='tword.*'; }  
    if($orderby==NULL) { $orderby='id'; }
    if($gettags===NULL) { $gettags=1; }
    //counting words
    if($count == 1) {
      $select = " COUNT(tword.id) ";
    } elseif($count!=NULL) { 
      $select = $count."id, COUNT(tword.id) ";
      $groupby = $count.'id';    
      ${$count.'id'} = '*';  
    }

    //"groupby" separation
    if($groupby!=NULL) {
      if( strstr($groupby, 'tag') ) {
        $groupby = 'ttag.'.$groupby;
      } elseif ( strstr($groupby, 'save') ) {
        $groupby = 'tsave.'.$groupby;
      } else {
        $groupby = 'tword.'.$groupby;
      }
    }

    //time format used for time range
    $timesyntax="Y-m-d H:i:s";

    //Check if highest group - Prüfe ob letztes fach
    //if it is, words with higher groups also will load
    //usefull after removing groups with words inside
    if($registerid!=NULL && $groupid!=NULL) {
      $highestgroup=0;
      $zquery="SELECT groupcount FROM lk_registers WHERE id='".$registerid."' AND userid='".$userid."'";
      $get_groupcount=$go->query($zquery,1);
      $groupcount=$get_groupcount['result']['groupcount'][0];
      if($groupcount==$groupid) { $highestgroup=1; }
    }

  }
  if($go->good()) {
    //create query
    //select tables
    $query="SELECT ".$select;
    $query.=" FROM lk_words tword ";
    if($tagid!=NULL || $count=='tag') { 
      $query.=", lk_tags ttag "; 
    }
    if($saveid!=NULL || $count=='save') { 
      $query.=", lk_save tsave "; 
    }
    //WHERE
    //userid must match
    $query.=" WHERE tword.userid='".$userid."' ";

    //register
    if($registerid!=NULL && $registerid!='*' && $count!='register') { $query.="AND tword.registerid='".$registerid."' "; }

    //tag
    if($tagid != NULL) { 
      $query.="AND tword.id=ttag.wordid ";
      if($count != 'tag') { $query.="AND ttag.tagid='".$tagid."' "; }
    }
    //without tag
    elseif($withoutid=='tag') { 
      $query.=" AND NOT EXISTS (SELECT * FROM lk_tags lkt WHERE tword.id=lkt.wordid) "; 
    }

    //save
    if($saveid!=NULL) { 
      $query.=" AND tword.id=tsave.wordid ";
      if($count != 'save') { $query.=" AND tsave.saveid='".$saveid."' "; }
    }
    //without save
    elseif($withoutid=='save') { 
      $query.="AND NOT EXISTS (SELECT * FROM lk_save lks WHERE tword.id = lks.wordid) "; 
    }

    //wordclass   
    if($wordclassid!=NULL && $count!='wordclass') { $query.="AND tword.wordclassid='".$wordclassid."' "; }

    //group
    if($groupid!=NULL && $count!='group') { 
      $query.="AND (tword.groupid='".$groupid."' ";
      if($highestgroup==1) { $query.="OR ( tword.groupid>'".$groupid."' AND tword.groupid!='af' AND tword.groupid!='ar' )"; }
      $query.=')';
    }

    //word(s)
    if( !is_array($wordid) && $wordid!=NULL ) { //20120408 - fix: allow comma separation
      $wordid = explode(',', $wordid); 
    }
    if( is_array($wordid) && count($wordid) > 0 ) {
      $query.="AND (";
      $len = count($wordid);
      for($i = 0; $i < $len; $i++) {
        $inquery[] = " tword.id='".$wordid[$i]."' "; 
      }
      $query .= implode(' OR ',$inquery).')';
    }
//    } elseif ($wordid!=NULL) { $query.="AND tword.id='".$wordid."' "; }


    //time range
    if($timerange!=NULL) { $query.="AND tword.time_created>'".date($timesyntax,$timerange[0])."' AND tword.time_created<='".date($timesyntax,$timerange[1])."'"; }

    //Search query
    if($searchtext!=NULL) {
      $query.="AND ( ";
      if(!is_array($searchtext)) { $searchtext=array($searchtext); $noarray=1; }
      $k=0;
      foreach($searchtext as $tsearch) {
        if(substr($tsearch,0,6)=='like: ' and strlen($tsearch)>6) {
          $tsearch=substr($tsearch,6);
          $searchtextexp=str_split($tsearch);
          $len=count($searchtextexp);
          for($i=0;$i<$len;$i++) {
            $searchtextexp[$i]=regexpencode($searchtextexp[$i]);
          }
          $tsearch=implode('.?', $searchtextexp);
          $sarray[$k][]='.*'.$tsearch.'.*';
        } else {
          $tsearch=regexpencode($tsearch);
          $sarray[$k][]='^'.$tsearch.'$';
          $sarray[$k][]=', ?'.$tsearch.'$';
          $sarray[$k][]='^'.$tsearch.' ?,';
        }
        $k++;
      }
      if($noarray) { $sarray[1]=$sarray[0]; $sarray[2]=$sarray[0]; }
      $where=array('tword.wordfirst','tword.wordfore','tword.sentence');
      $len=count($sarray);
      for($i=0;$i<$len;$i++) {
        $len2=count($sarray[$i]);
        for($j=0;$j<$len2;$j++) {
          $query.=" ".$where[$i]."  RLIKE '".$sarray[$i][$j]."' "; 
          if($i+1<$len or $j+1<$len2) { $query.=" OR "; }
        }
      }
      $query.=" ) ";
    }

    //group by
    if($groupby!=NULL) { $query.=" GROUP BY ".$groupby." "; }

    //orderby
    if($timerange[2]) { $query.=" ORDER BY tword.time_created ".$timerange[2]." "; }  
    else { $query.=" ORDER BY tword.".$orderby." "; }

    //limit
    if(!$nolimit) { $query.="LIMIT ".$fromlim.",".$tolim." ";}

    //execute
    $get_words=$go->query($query,2);

    //fix for group
    // Count words from higher groups to highest existing group
    if($groupid == '*') {
      $r = & $get_words['result'];
      $countplus=0; $index=false;
      for($i=0; $i<$get_words['count']; ++$i) {
        if( is_numeric($r['groupid'][$i]) && $groupcount<$r['groupid'][$i] ) { //check if there is higher group
          $countplus += $r['COUNT(tword.id)'][$i];
          $r['COUNT(tword.id)'][$i] = 0;
        }
        elseif( $groupcount==$r['groupid'][$i] ) { $index=$i; } //find index of highest group
      }
      if($index!==false) {
        $r['COUNT(tword.id)'][$index] += $countplus; //append count to highest group
      } else { //create new entry (if there is no entry for highest group yet)
        $r['COUNT(tword.id)'][] = $countplus; 
        $r['groupid'][] = $groupcount;
      }
    }
  }
  if($go->good()) {    
    //return
    $return = $get_words['result'];
    $return['count'] = $get_words['count']; 
    if($wordid != NULL) { $return['wordid'] = $wordid; }
    if($groupid != NULL) { $return['groupcount'] = $groupcount; }
    if(count($get_words['result']['id'])>0) {
      foreach($get_words['result']['id'] as $key=>$wid) {
        if($gettags==1) { $return['tagslist'][$key]=request('get_tag',array('registerid'=>$return['registerid'][$key], 'wordid'=>$wid)); }
        if($getsave==1) { $return['savelist'][$key]=request('get_save',array('registerid'=>$return['registerid'][$key], 'wordid'=>$wid)); }
      }
    }
    if($count != NULL) {
      $return['wordcount'] = $return['COUNT(tword.id)'];
      unset( $return['COUNT(tword.id)'] );
    }
    $return['query'] = $query;
  }
?>
