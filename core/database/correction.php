<?php
//////////////////////////////////////
/* NAME: correction
/* PARAMS:
- queryid
- corrid : id of word to correct
- ngroup : group in which the word was before
/* RETURN: -
/* DESCRIPTION: If a word was answered wrong, this will make it right in hindsight
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'queryid', 'corrid' );

//Check User
if ( $go->good() ) {
  $query     = "SELECT mode FROM lk_activelist WHERE userid='" . $userid . "' AND id='" . $arg_queryid . "'";
  $checkuser = $go->query( $query, 1 );
  if ( 0 == $checkuser ) {
    $go->error( 100 );   // no permission
  } else {
    $mode = $checkuser[ 'result' ][ 'mode' ][ 0 ];
  }
}

// Correction / Korrigieren  
if ( $go->good() ) {  
  $query   = "UPDATE lk_active SET correct='1' WHERE id='" . $arg_queryid . "' AND wordid='" . $arg_corrid . "' AND correct='0'";
  $qresult = $go->query( $query, 2 );
  if ( 0 == $qresult[ 'count' ] ) { //correction failed
    $go->error( 103 );
  }
}

// restore group if mode is 2 or 3 (Leitner modes)
if ( $go->good() ) {
  if ( ( 2 == $mode OR 3 == $mode ) AND NULL != $arg_ngroup ) {
    $arg_ngroup++;
    $query = "UPDATE lk_words SET `groupid`='" . $arg_ngroup . "' WHERE `groupid`!='af' AND `groupid`!='ar' AND id='" . $arg_corrid . "' AND userid='" . $userid . "'";
    $go->query( $query, 3 );
  }
}
?>
