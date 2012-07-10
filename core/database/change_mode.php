<?php
//////////////////////////////////////
/* NAME: change_mode
/* PARAMS: 
- queryid
- chwhat : 0 - toggle Leitner mode, 1 - toggle language direction
/* RETURN: 
- chmode : new mode
/* DESCRIPTION: change mode of query
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'queryid' );

if ( $go->good() ) {
  //check User and get mode
  $query     = "SELECT userid,mode FROM lk_activelist WHERE userid='" . $userid . "' AND id='" . $arg_queryid . "'";
  $checkuser = $go->query( $query, 1 );
  if ( $checkuser[ 'count' ] == 0 ) {
    $go->error( 100 );
  } else {
    $mode = $checkuser[ 'result' ][ 'mode' ][ 0 ];
  }
}

if ( $go->good() ) {
  // mode can only be changed in new queries, check if it's new
  $query     = "SELECT done FROM lk_active WHERE done='1' AND id='" . $arg_queryid . "'";
  $checkdone = $go->query( $query, 2 );
  if ( $checkdone[ 'count' ] > 0 ) {
    $go->error( 103 );
  }
}

if ( $go->good() ) {
  // change mode / Ändern
  $chmode  = $arg_chwhat == 0 ? array( 0, 1, 0, 1, 4 ) : array( 1, 0, 3, 2, 4 );
  $query   = "UPDATE lk_activelist SET mode='" . $chmode[ $mode ] . "' WHERE id='" . $arg_queryid . "' AND userid='" . $userid . "'";
  $qresult = $go->query( $query );
  if ( $qresult[ 'count' ] == 0 ) {
    $go->error( 103 );
  }
}

if ( $go->good() ) {
  $return = array(
     'chmode' => $chmode[ $mode ] 
  );
}
?>
