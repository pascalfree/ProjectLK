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
  global $plk_here, $plk_you, $plk_la;

  if( $plk_here->page == 'query' ) {
    $param = array( 'nodelete' => 1 ); //fix: don't delete any active query if this is active
  }
  $active = plk_request('get_active', $param);
  $len = $active['count'];
  $qrs = '<ul class="statuslist">';
  for ( $i = 0; $i < $active['count']; $i++) {
    if($plk_here -> queryid != $active['id'][$i]) {
      $date = new DateTime($active['time_created'][$i]);
      $qrs .= '<li><a href="'. $plk_here -> path(1) . $active['registerid'][$i] .'/query/'. $active['id'][$i] .'">'. $active['name'][$i] .' '. $date -> format('j.n.Y ') . $plk_la['time_at'] . $date->format(' G:i') .'</a></li>';
    } else { $len--; }
  }
  $qrs .= '</ul>';  
  if( $len > 0 ) {
    if( $len == 1 ) 
    { 
      echo '<span class="title statustitle">'. $len ." ". $plk_la['unfinquery'] ."</span>"; 
    }
    else 
    { 
      echo '<span class="title statustitle">'. $len ." ". $plk_la['unfinqueries'] ."</span>"; 
    }
    echo $qrs;
    $plk_you -> statuscount += $len;
  }
}


function status_button() {
  global $plk_you;
  if($plk_you -> statuscount > 0) {
    $ret = '<a href="javascript: void(0)" id="statusbutton" onclick="javascript: showstatus(this)" onmouseover="javascript: holdstatus()" onmouseout="javascript: hidestatus()">'. $plk_you -> statuscount .'</a>';
  }
  return $ret;
}
?>
