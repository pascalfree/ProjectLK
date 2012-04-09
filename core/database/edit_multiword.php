<?php
// move multiple words to a group (1 or af)

  //before enabling moveto_register:
  // what happens with tags and saves?
  // what happens with verbtables (if any)?
  
  //array('movetoreg','movetogroup')
  $go->necessary(array('wordid','allmarked'),'movetogroup');

  if($go->good()) {
    if(!is_array($wordid)) { $wordid=array($wordid); }
    //every word is marked: get them by global parameters
    if($allmarked) { 
      $params=getglobal();
      $params['nolimit']=1;
      $words=request('get_word', $params);
      if( $words['errnum']!=0 ) { $go->error(400,$words['errnum'].': '.$words['errname']); }
      $wordid=$words['id'];
    }
  }
  /*
  if($go->good()) {
    //registercheck
    if($movetoreg!=NULL) { 
      $query="Select id FROM lk_registers WHERE id='".$movetoreg."' AND userid='".$userid."'";
      $checkuser=$go->query($query,1);
      if($checkuser['count']==0) { $go->error(100); }
    }
  }*/
  if($go->good()) {
    $countedit=0;
    $len=count($wordid);
    $edits=NULL;
    //if($movetoreg!=NULL && $movetoreg!='') { $edits[]=" registerid='".$movetoreg."'"; }
    if($movetogroup!=NULL && $movetogroup!='0') { $edits[]=" `groupid`='".$movetogroup."'"; }
    if($edits!=NULL) {
      for($i=0;$i<$len;$i++) {
        $query="UPDATE lk_words SET ".implode(',',$edits);
        $query.=" WHERE id='".$wordid[$i]."' AND userid='".$userid."' ";
        $edit_word=$go->query($query,2);
        $countedit+=$edit_word['count'];
      }
    }
  }
  if($go->good()) {
    $return=array('count' => $countedit);
  }
?>
