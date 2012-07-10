<?php
//////////////////////////////////////
/* NAME: query_restart
/* PARAMS: arg_
- queryid
- (wrong) : if 1 will only restart with wrong answered words
/* RETURN: none
/* DESCRIPTION: restarts a finished query (make all undone)
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
  //restart / neu starten
  $go->query( 
    "UPDATE lk_activelist 
       SET status='1'
     WHERE userid='" . $userid . "'
           AND id='" . $arg_queryid . "'"
  , 2 );
  
  //wrong answered only / nur falsche
  if ( $arg_wrong ) {
    $go->query( 
      "DELETE
         FROM lk_active
       WHERE correct='1'
             AND id='" . $arg_queryid . "'"
    , 3 );
  }
  
  // make all undone
  $go->query( 
    "UPDATE lk_active 
       SET done='0', correct='0'
     WHERE id='" . $arg_queryid . "'"
  , 4 );
}
?>
