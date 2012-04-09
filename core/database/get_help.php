<?php
  $go->necessary('language',array('id','title','gettitle','all'));

  if($go->good()) {
    if($gettitle!=1) { $sel=',valuetext';  } else { $sel=''; }
    $query="SELECT id,title,titletext".$sel." FROM lk_help WHERE language='".$language."' ";
    if($id!=NULL || $title!=NULL) { 
      $query.=" AND (title='".$title."' OR id='".$id."')"; 
    }
    $get_help=$go->query($query,1);
  }
  if($go->good()) { //return  
    $return=$get_help['result'];
    //utf8_encode: just wont word without it.
    for($i=0; $i<$get_help['count']; $i++) {
      $return['valuetext'][$i]=utf8_encode($return['valuetext'][$i]);
      $return['titletext'][$i]=utf8_encode($return['titletext'][$i]);
    }
    $return['count']=$get_help['count'];
  }
?>
