<?php 
  //adds multiple verbs at once (unlike add_verb)
  $go->necessary('wordid','personid','formid','newverb');

  //Fix: make them all arrays
  if($go->good()) {
    if( !is_array($newverb) ) { $newverb = array($newverb); }
    if( !is_array($personid) ) { $personid = array($personid); }
    if( !is_array($formid) ) { $formid = array($formid); }    
  }  

  if($go->good()) {
    //forbidden
    $forbidden= array('"', '#', '+');
    $len=count($newverb);
    for($i=0;$i<$len;$i++) { 
      $newverb[$i]=str_replace($forbidden, '', $newverb[$i]);
    }
  }
  if($go->good()) {
    //check User
    $query="SELECT userid FROM lk_words WHERE userid='".$userid."' AND id='".$wordid."'";
    $checkuser=$go->query($query,1);
    if($checkuser['count']==0) { $go->error(100); }
  }
  if($go->good()) {
    //write / Schreiben
    //Querystring
    $add=''; $addval='';
    if($time_created!=NULL) { $add.=', time_created'; $addval.=", '".$time_created."'"; }
    if($newregular!=NULL) { $add.=', regular'; $addval.=", '".$newregular."'"; }
    $query="INSERT INTO lk_verbs (wordid, personid, formid, kword ".$add." ) VALUES ";
    $len=count($newverb);
    for($i=0;$i<$len;$i++) {
      if($newverb[$i]!='') {
        $inquery[]="('".$wordid."', '".$personid[$i]."', '".$formid[$i]."', '".$newverb[$i]."' ".$addval." ) ";
      }
    }
    if(is_array($inquery)) {
      $query.=implode(',', $inquery);
      //Execute
      $create_verb=$go->query($query,2);
      $countverb=$create_verb['count'];
    } else { $countverb=0; }
  }
  if($go->good()) {		
    $return=array('count'  => $countverb);
  }
?>
