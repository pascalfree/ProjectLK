<?php
//////////////////////////////////////
/* NAME: create_active
/* PARAMS: 
 - registerid
 - [activename] : name of active
 - (wordid[array] OR /global/ AND allmarked=1) 
/* RETURN: 
 - activename : number of tags added
 - wordid : Array of wordids
 - savedid : Id of active
 - countwords : number of words in active
 - mode : mode of active for queries (0-4)
/* DESCRIPTION: Creates an Active (for queries) of one or more words.
/* VERSION: 19.04.2011
////////////////////////////////////*/
  $go->necessary('registerid',array('wordid','allmarked'));
  if($go->good()) {
    if($allmarked==1) {
      $wordid=NULL; //Reset $wordid, so getglobal wont take this.
      $params=getglobal();
      $params['nolimit']=1;
      $params['select']='tword.id'; //Fix: otherwise id is ambigous
      $getwordid=request('get_word',$params); 
      if( $getwordid['errnum']!=0 ) { $go->error(400,$getwordid['errnum'].': '.$getwordid['errname'].' - '.$getwordid['lastquery']); }      
      $wordid=$getwordid['id'];
    }
  }
  if($go->good()) {
    if(count($wordid)==0) { $go->missing('wordid'); }
  }
  if($go->good()) {
    //Choose mode / Wähle Modus
    $tmode=0;
    if($allmarked && is_numeric($groupid) && $tagid==NULL && $saveid==NULL && $searchid==NULL) {
      if($groupid>1) {
        $query="SELECT grouplock FROM lk_registers 
                WHERE userid='".$userid."' AND id='".$registerid."'";
        $load_grouplock=$go->query($query,3);
        if($load_grouplock['count']>0) {
          $glock=explode("?",$load_grouplock['result']['grouplock'][0]);
          if($glock[$groupid-2]<=count($wordid)) { $tmode=2; }
        }
      } else { $tmode=2; }
    }
    //Add / Hinzufügen
    $query="INSERT INTO lk_activelist (userid, registerid, name, mode) 
    VALUES ('".$userid."','".$registerid."' ,'".$activename."','".$tmode."')";
    $create_active=$go->query($query,1);
    $savedid = $create_active['id'];
  }
  if($go->good()) {
    //Querystring
    $insert='';
    $countid=count($wordid);
    for($i=0; $i<$countid; $i++) {
      $insert.="('".$savedid."', '".$wordid[$i]."') ";
      if($i<$countid-1) { $insert.=','; }
    }	
    $query="INSERT INTO lk_active (id, wordid) 
    VALUES ".$insert;
    $add_active = $go->query($query,2);
  }
  if($go->good()) {  
    $return=array('activename' => $activename,
	                'wordid' => $wordid,
	                'savedid'  => $savedid,
	                'countwords' => $add_active['count'],
	                'mode' => $tmode);
  }
?>
