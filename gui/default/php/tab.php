<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: tab.php
//theme: default
//description: functions for tab view
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES : 
//////////////////////////////

//////////
//TAB
//////////

require_once( GUI.'php/list.php' );

//makes tabs of an array with tabnames
function tabs($tabnames,$activetab) {
  global $la;
  //quit if no tabnames
  if( $tabnames == NULL ) { return false; }
  //make array
  if( !is_array($tabnames) ) { $tabnames = array($tabnames); }
  //walk through
  $count = count($tabnames);
  for($i = 0; $i < $count; $i++) {
    //active or inactive tab
    if($tabnames[$i] == $activetab) { $class = "tab_active"; }
    else { $class="tab_inactive"; }
    //print html
    echo '<div class="', $class ,' link" id="tab_', $tabnames[$i] ,'">', $la[$tabnames[$i]] ,'</div>';
  }
}

//tab overview
//$here is an array
function ajax_overview($registerid, $count=NULL, $hints=0) {
  //count words if not counted
  if($count == NULL) { 
    $count=request('get_word',array('count'=>'1' ,'registerid' => $registerid));
    $count=$count['wordcount'][0];
  }

  // write content
  list_group($registerid, $hints);

  if( $count>0 ) { //especially for writewordclass
    list_tag($registerid);
    list_save($registerid);
    list_wordclass($registerid);
    list_verb('nocheckbox=1&registerid='.$registerid);
  }
}
?>
