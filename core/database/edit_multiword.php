<?php
//////////////////////////////////////
/* NAME: edit_multiword
/* PARAMS: 
- arg_wordid
- OR allmarked AND /global/
- movetogroup : af or 1
/* RETURN: 
- count : number of edited words
/* DESCRIPTION: move multiple words to a group (1 or af)
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 20.04.2012 - Code Style
////////////////////////////////////*/

//before enabling moveto_register:
// what happens with tags and saves?
// what happens with verbtables (if any)?

//array('movetoreg','movetogroup')
$go->necessary( array( 'wordid', 'allmarked' ), 'movetogroup' );

if ( $go->good() ) {
  make_array( $arg_wordid );

  //every word is marked: get them by global parameters
  if ( $arg_allmarked ) {
    $arg_wordid = load_arg_wordid( $go );
  }
}

/*
if($go->good()) {
//registercheck
if($movetoreg!=NULL) { 
$query="Select id FROM lk_registers WHERE id='".$movetoreg."' AND userid='".$userid."'";
$checkuser=$go->query($query,1);
if($checkuser['count']==0) { $go->error(100); }
}
}*/

if ( $go->good() ) {
  $countedit = 0;
  $len       = count( $arg_wordid );
  $edits     = NULL;
  //if($movetoreg!=NULL && $movetoreg!='') { $edits[]=" registerid='".$movetoreg."'"; }
  if ( "1" == $arg_movetogroup || 'af' === $arg_movetogroup ) {
    $edits[] = " `groupid`='" . $arg_movetogroup . "'";
  }
  if ( $edits != NULL ) {
    for ( $i = 0; $i < $len; $i++ ) {
      $edit_word = $go->query( 
        "UPDATE lk_words 
           SET " . implode( ',', $edits ) ."
         WHERE id='" . $arg_wordid[ $i ] . "' 
               AND userid='" . $userid . "' "
      , 2 );
      $countedit += $edit_word[ 'count' ];
    }
  }
}

// return
if ( $go->good() ) {
  $return = array(
     'count' => $countedit 
  );
}
?>
