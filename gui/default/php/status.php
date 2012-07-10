<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: status.php
//theme: default
//description: functions for the status button etc.
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES : 
//////////////////////////////

//////////
//STATUS
//////////
// functions should start with status_

function status_info() {
  global $here, $you, $la;

  if( $here->page == 'query' ) {
    $param = array( 'nodelete' => 1 ); //fix: don't delete any active query if this is active
  }
  $active = request('get_active', $param);
  $len = $active['count'];
  $qrs = '<ul class="statuslist">';
  for ( $i = 0; $i < $active['count']; $i++) {
    if($here -> queryid != $active['id'][$i]) {
      $date = new DateTime($active['time_created'][$i]);
      $qrs .= '<li><a href="'. $here -> path(1) . $active['registerid'][$i] .'/query/'. $active['id'][$i] .'">'. $active['name'][$i] .' '. $date -> format('j.n.Y ') . $la['time_at'] . $date->format(' G:i') .'</a></li>';
    } else { $len--; }
  }
  $qrs .= '</ul>';  
  if( $len > 0 ) {
    if( $len == 1 ) 
    { 
      echo '<span class="title statustitle">'. $len ." ". $la['unfinquery'] ."</span>"; 
    }
    else 
    { 
      echo '<span class="title statustitle">'. $len ." ". $la['unfinqueries'] ."</span>"; 
    }
    echo $qrs;
    $you -> statuscount += $len;
  }
}


function status_button() {
  global $you;
  if($you -> statuscount > 0) {
    $ret = '<a href="javascript: void(0)" id="statusbutton" onclick="javascript: showstatus(this)" onmouseover="javascript: holdstatus()" onmouseout="javascript: hidestatus()">'. $you -> statuscount .'</a>';
  }
  return $ret;
}
?>
