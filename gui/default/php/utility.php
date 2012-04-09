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
  global $here, $you, $la;
  $title[0]=P_NAME.' '.P_VERSION;
  if($you -> id != NULL) { $title[1] = $you -> name; } 
  if($here -> registerid!=NULL) { $title[2]=get_name('register',$here->registerid); } 
  if($here -> groupid!=NULL) { 
    if(is_numeric($here->groupid)) { $tfach=$la['group'].' '.$here->groupid; } else { $tfach=$la[$here->groupid]; }
    $title[]=$tfach; 
  }  
  if($here->saveid!=NULL) { $title[]=get_name('save', $here->saveid); }  
  if($here->tagid!=NULL) { $title[]=get_name('tag', $here->tagid); }  
  if($here->withoutid!=NULL) { $title[]=$la['without'.$here->withoutid]; }  
  if($here->wordclassid!=NULL) { $title[]=$la['classname'][$here->wordclassid]; }
  if($here->searchid!=NULL) { $title[]=$here->searchid; }
  if($here->queryid!=NULL) { $title[]=$la['query']; }	
  if($here->keyoption!=NULL) { $title[]=$la[$here->keyoption]; }

  if($title!=NULL) {
    foreach($title as &$val) { $val=urldecode($val); }
    return implode(' / ', $title);
  } else { return false; }
}
?>
