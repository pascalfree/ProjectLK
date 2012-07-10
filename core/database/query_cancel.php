<?php
//////////////////////////////////////
/* NAME: query_cancel
/* PARAMS: arg_
- queryid
/* RETURN: none
/* DESCRIPTION: cancels a running query
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'queryid' );

if ( $go->good() ) {
  //Check User
  $checkuser = $go->query( 
    "SELECT userid 
       FROM lk_activelist 
     WHERE userid='" . $userid . "' 
           AND id='" . $arg_queryid . "'"
  , 1 );
  if ( $checkuser[ 'count' ] == 0 ) {
    $go->error( 100 );
  }
}

if ( $go->good() ) {
  //Cancel / Abbrechen
  $go->query(
     "UPDATE lk_activelist 
        SET status = '0' 
      WHERE userid = '" . $userid . "' 
            AND id = '" . $arg_queryid . "'"
  , 2 );
  $go->query(
    "UPDATE lk_active 
       SET done='1' 
     WHERE id='" . $arg_queryid . "'"
  , 3 );
}
?>
