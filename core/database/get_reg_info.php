<?php
//////////////////////////////////////
/* NAME: get_reg_info
/* PARAMS: 
- registerid
/* RETURN: 
- groupcount
- grouplock
- language
/* DESCRIPTION: load informations about a register
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 20.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'registerid' );

// Load register information
if ( $go->good() ) {
  $get_reg_info = $go->query( 
    "SELECT groupcount, grouplock, language 
       FROM lk_registers 
     WHERE id='" . $arg_registerid . "' 
           AND userid='" . $userid . "' "
  , 1 );

  if ( $get_reg_info[ 'count' ] == 0 ) {
    $go->error( '102', 'Entry not found' );
  }
}
if ( $go->good() ) { //result
  $return = flat( $get_reg_info[ 'result' ] );
}
?>
