<?php
  $go->necessary('registerid');

  if($go->good()) {
    //Load / Lade
    if($allform!=1) {
      $qform=" AND ( "; 
      $cf=count($formid);
      for($i=0;$i<$cf;$i++) { 
        $qform.=" tverb.formid='".$formid[$i]."' "; 
        $qform.=$i+1<$cf? " OR " : "";
      }
      $qform.=" )";
    } else { $cf=1; }
    if($allperson!=1) {
      $qperson=" AND ( "; 
      $cp=count($personid);
      for($i=0;$i<$cp;$i++) { 
        $qperson.=" tverb.personid='".$personid[$i]."' "; 
        $qperson.=$i+1<$cp? " OR " : "";
      }
      $qperson.=" )";
    } else { $cp=1; }
    if($allverb!=1) {
      $qverb=" AND ( "; 
      $cv=count($verbid);
      for($i=0;$i<$cv;$i++) { 
        $qverb.=" tverb.wordid='".$verbid[$i]."' "; 
        $qverb.=$i+1<$cv? " OR " : "";
      }
      $qverb.=" )";
    } else { $cv=1; }

    //errors
    if($cf==0) { $go->missing('formid'); }
    if($cp==0) { $go->missing('personid'); }
    if($cv==0) { $go->missing('verbid'); }
  }
  if($go->good()) {
    $query1="SELECT tverb.id FROM lk_verbs tverb, lk_forms tform, lk_persons tperson, lk_words tword
            WHERE ( tverb.formid=tform.id AND tform.userid='".$userid."' AND tform.registerid='".$registerid."' )
            AND ( tverb.personid=tperson.id AND tperson.userid='".$userid."' AND tperson.registerid='".$registerid."' )
            AND ( tverb.wordid=tword.id AND tword.userid='".$userid."' AND tword.registerid='".$registerid."' )".$qverb.$qform.$qperson;
    $load_ids=$go->query($query1,1);
    $id=$load_ids['result']['id'];
    $countids=$load_ids['count'];
    $countwords = $countids;
    //if($countids==0) { $go->missing('wordid'); }
  }
  if($go->good() && $countids!=0) {
    //Add / Hinzufügen
    $query="INSERT INTO lk_activelist (userid, registerid, name, mode) 
            VALUES ('".$userid."', '".$registerid."', '".$activename."','4')";
    $create_active=$go->query($query,2);
    $savedid=$create_active['id'];

    $insert='';
    for($i=0; $i<$countids; $i++) {
      $insert.="('".$savedid."', '".$id[$i]."') ";
      if($i<$countids-1) { $insert.=','; }
    }	
    $query="INSERT INTO lk_active (id, wordid) 
	          VALUES ".$insert;
    $add_active=$go->query($query,3);
    $countwords =$add_active['count'];
  }
  if($go->good()) {  
    $return=array('id' => $id,
                  'savedid'  => $savedid,
                  'count' => $countwords);
  }
?>
