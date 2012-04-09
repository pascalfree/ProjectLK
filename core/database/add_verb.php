<?php
  $go->necessary('wordid','formid','personid');
  if($go->good()) {
    //forbidden characters / Verbotene Zeichen
    $forbidden= array('"', '#', '+');
    $newkword=str_replace($forbidden, '', $newkword);

    //Check User
    $query="SELECT userid FROM lk_words WHERE userid='".$userid."' AND id='".$wordid."'";
    $checkuser=$go->query($query,1);
    if($checkuser['count']==0) { $go->error(100); }
  }

  //if verb with this ids exists: edit.
  if($go->good()) {
     $exists=false;

     //Find existing entry and get id.
     $query="SELECT id FROM lk_verbs WHERE wordid='".$wordid."' AND personid='".$personid."' AND formid='".$formid."'";
     $get_verb=$go->query($query,3);
     if($get_verb['count'] != 0) {
       $tid=$get_verb['result']['id'][0];  //ID of this verb.
       $exists=true;
       //Give it to the edit_verb function
       $edit=request('edit_verb',array('verbid'=>$tid,'newkword'=>$newkword));
       $return=$edit;
       $return['verbid']=$tid;
       $return['exists']=$exists;
     }
  }

  //Adding
  if($go->good()) {
    $empty = false;
    //don't add nothing
    if($newkword==NULL || $newkword=='') {
      $empty = true; //don't go to insertion
      $return = array('delete' => 1);
    }
  }
  
  if(!$exists && !$empty) {
    if($go->good()) {
      $query="INSERT INTO lk_verbs (wordid, personid, formid, kword) VALUES
              ('".$wordid."', '".$personid."', '".$formid."', '".$newkword."')";
      $add_verb=$go->query($query,2);
    }
    if($go->good()) {
      $return=array('verbid' => $add_verb['id']);
    }
  }
?>
