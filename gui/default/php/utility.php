<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: utility.php
//theme: default
//description: Utility functions for the theme
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES :
//////////////////////////////

//////////
//UTILITY
//////////
// functions should start with u_

//parse a string (GET) to an array. return array.
function u_param($string, $param=false) {
  //load options
  if( is_string($string) ) { parse_str($string,$options); }
  elseif( DEBUG ) { 
    echo 'DEBUG ERROR: First parameter must be a string in u_param.'; 
    return false;
  } else { return false; }
  if( $param ) { 
    return $options[$param];
  } else {
    return $options;
  }
}

//write html for a counter
function u_counter($type, $id, $fullcount, $plus='') {
  echo ' <span class="counters">(';
    //insert sub- and fullcount
    echo '<span class="', $type ,'_subcount_', $id ,'"></span>';
    echo '<span class="', $type ,'_fullcount_', $id ,'">';
      echo $fullcount;
    echo '</span>';
    //additional output
    echo $plus;
  echo ')</span>'; 
}

//generates a Pagetitle
function u_title() {
  global $plk_here, $plk_you, $plk_la;
  $title[0]=P_NAME.' '.P_VERSION;
  if($plk_you -> id != NULL) { $title[1] = $plk_you -> name; } 
  if($plk_here -> registerid!=NULL) { $title[2]=plk_util_getName('register',$plk_here->registerid); } 
  if($plk_here -> groupid!=NULL) { 
    if(is_numeric($plk_here->groupid)) { $tfach=$plk_la['group'].' '.$plk_here->groupid; } else { $tfach=$plk_la[$plk_here->groupid]; }
    $title[]=$tfach; 
  }  
  if($plk_here->saveid!=NULL) { $title[]=plk_util_getName('save', $plk_here->saveid); }  
  if($plk_here->tagid!=NULL) { $title[]=plk_util_getName('tag', $plk_here->tagid); }  
  if($plk_here->withoutid!=NULL) { $title[]=$plk_la['without'.$plk_here->withoutid]; }  
  if($plk_here->wordclassid!=NULL) { $title[]=$plk_la['classname'][$plk_here->wordclassid]; }
  if($plk_here->searchid!=NULL) { $title[]=$plk_here->searchid; }
  if($plk_here->queryid!=NULL) { $title[]=$plk_la['query']; }	
  if($plk_here->keyoption!=NULL) { $title[]=$plk_la[$plk_here->keyoption]; }

  if($title!=NULL) {
    foreach($title as &$val) { $val=urldecode($val); }
    return implode(' / ', $title);
  } else { return false; }
}
?>
