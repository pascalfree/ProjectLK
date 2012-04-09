<?php
  $go->necessary('wordid');

	$edits=NULL;
	if($newwordfirst!=NULL && $newwordfirst!='') { $edits[]=" wordfirst='".$newwordfirst."'"; }
	if($newwordfore!=NULL && $newwordfore!='') { $edits[]=" wordfore='".$newwordfore."'"; }
  //fixed: 'af'==0 is true!
	if($newgroup!=NULL && $newgroup!='') { $edits[]=" `groupid`='".$newgroup."'"; } // ` needed!
	if($newsentence!=NULL) { $edits[]=" sentence='".$newsentence."'"; }
	if($newwordclass!=NULL && $newwordclass!='') { $edits[]=" wordclassid='".$newwordclass."'"; }
	if($newregister!=NULL && $newregister!='') { $edits[]=" registerid='".$newregister."'"; }
	
	if($edits==NULL) { $go->error(103); }
	else {
	  $query="UPDATE lk_words SET ".implode(',',$edits);
	  $query.=" WHERE id='".$wordid."' AND userid='".$userid."' ";
    $edit_word=$go->query($query,1);
	}
  if($go->good()) {  
    $return=array('count' => $edit_word['count']);
  }
?>
