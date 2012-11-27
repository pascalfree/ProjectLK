<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: load_scripts.php
//core
//description: Load javascript files
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//LOAD DATA
//////////

//Loads scripts
//This Function is called in the header (or footer) of the html file in the gui.
function plk_scripts() {
  global $plk_here, $plk_you, $plk_la, $time;

  if( RELEASE==1 ) {
    $ret="<script src='".URL."core/javascript/core.js'></script>\n";
  } else {
    //Prototype library
    $ret="<script src='".URL."core/javascript/library/prototype.js'></script>\n";
    //JS-functions
    $ret.="<script src='".URL."core/javascript/functions.js'></script>\n";
    //functions for querying
    $ret.="<script src='".URL."core/javascript/query.js'></script>\n";
  }
  //language
  if( LANGTYPE=='js' ) {
    $ret .= "<script src='".URL."language/".LANG.".js'></script>\n";
  } else {
    $ret .= "<script>
      plk.la = ".json_encode($plk_la).";
    </script>\n";
  }
  //global variables
  $ret.="<script>
    plk.here.set( ".json_encode($plk_here)." );
    plk.you = ".json_encode($plk_you).";
    time = ".$time."; 
    var URL = '".URL."';
    var DEBUG = '".DEBUG."';
  </script>\n";
  return $ret;
}

?>
