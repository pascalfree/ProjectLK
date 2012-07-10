<?php
//////////////////////////////////////
/* NAME: get_active
/* PARAMS:
- nodelete : if 1 won't delete finished queries
/* RETURN: 
- count : number of active queries
- id
- userid
- registerid
- name
- mode : mode of query (0-4)
- status
- time_created
/* DESCRIPTION: load active queries
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header, added arg_nodelete
////////////////////////////////////*/

//Delete finished queries / Löschen
if ( 1 != $arg_nodelete ) {
  // updated status of finished queries (finished == all done)
  $go->query( 
    "UPDATE lk_activelist t1 SET t1.status='0' 
       WHERE 1 = ALL(SELECT t2.done FROM lk_active t2 WHERE t2.id=t1.id) 
             AND t1.userid='" . $userid . "'"
  , 1 );
  // delete 
  $go->query( 
    "DELETE t1, lk_activelist
       FROM lk_active t1, lk_activelist   
     WHERE lk_activelist.userid='" . $userid . "' 
           AND t1.id = lk_activelist.id
           AND lk_activelist.status = '0'"
  , 2 );
}

// Load / Laden
if ( $go->good() ) {
  $get_active = $go->query( 
    "SELECT * 
       FROM lk_activelist 
     WHERE userid = '" . $userid . "' 
           AND status = '1'"
  , 3 );
}

// return
if ( $go->good() ) {
  $return            = $get_active[ 'result' ];
  $return[ 'count' ] = $get_active[ 'count' ];
}
?>
