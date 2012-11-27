<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: load.php
//theme: default
//description: functions to load elements
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES :
//////////////////////////////

//////////
//ONLOAD
//////////
// functions should start with load_

function load_head($toolbar='', $tabs=NULL, $error='') {
  global $plk_here, $plk_la, $plk_you;
  include_once(GUI. 'htmlheader.php');
}

function load_foot($js=NULL) {
  global $plk_here, $plk_la, $plk_you;
  include_once(GUI. 'htmlfooter.php');
}

function load_stylesheet($style) {
  global $plk_you;
  $brows = $plk_you -> browser;
  if(file_exists(GUI .'theme/'.$style.'_'.$brows.'.css')) {
    return $style.'_'.$brows;
  } elseif( file_exists(GUI .'theme/'.$style.'.css') ) {
    return $style; 
  } else { return DEFAULT_STYLE; }
}

//scripts for the gui
function load_gui_scripts() {
  global $plk_here, $plk_you;

  //load gui_<..>.js
  if( RELEASE == 1 ) { //release will have a compiled js
    echo '<script src="',URL,GUI,'js/gui.js"></script>'; 
  } else {
    echo '<script src="',URL,GUI,'js/gui_functions.js"></script>';
    echo '<script src="',URL,GUI,'js/gui_search.js"></script>';
    if($plk_you -> hints == 1) { //help enabled and not helppage
      echo '<script src="',URL,GUI,'js/gui_help.js"></script>';
    } 
  }

  //give register names
  if($plk_here -> registerid == NULL) {
    $registers = plk_request('get_register');
    for($i=0;$i<$registers['count'];$i++) {
      $regname[$registers['id'][$i]] = $registers['name'][$i];
    } //If registerid isn't set load the names, they may be needed when searching words
    echo "<script> local.registers=".json_encode($regname)."; </script>";
  }
  //return $ret;
}

//load all local script specific for this page
//$scr: Array of scripts to load (without .js)
function load_local_scripts( $scr ) {
  if( RELEASE == 1 ) { return false; }
  if( !$scr ) { return false; }

  //global $plk_here;
  if( !is_array($scr) ) { $scr = array($scr); } //make array
  foreach($scr as $script) {
    echo '<script src="', URL, GUI, 'localjs/',$script,'.js"></script>';
  }
}
?>
