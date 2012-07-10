<?php
//////////////////////////////////////
/* NAME: create_person
/* PARAMS: 
- registerid
- newperson (comma separated)
- [neworder]
/* RETURN:
- newname : array of formnames
- newid : array of created ids
- neworder : order of the last Element added
- count : number of created forms
/* DESCRIPTION: Create a new person for verbs
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'registerid', 'newperson' );

if ( $go->good() ) {
  //forbidden characters / Verbotene Zeichen
  remove_forbidden( $arg_newperson );
  
  //increment order
  if ( $arg_neworder == NULL ) {
    $count = $go->query( 
      "SELECT COUNT(*) 
         FROM lk_persons 
       WHERE registerid='" . $arg_registerid . "' 
             AND userid='" . $userid . "'"
    , 1 );
    $arg_neworder = $count[ 'result' ][ 'COUNT(*)' ][ 0 ];
  }
}
if ( $go->good() ) {
  //comma separated input
  $newpersonarr = comma_array( $arg_newperson );
  $countarr     = count( $newpersonarr );
  $countperson  = 0;
  for ( $i = 0; $i < $countarr; $i++ ) { //add multiple entries
    //write / Schreiben
    if ( $arg_neworder != NULL ) {
      $add = ', `order`';
      $addval = ", '" . ++$arg_neworder . "'";
    } else {
      $add    = '';
      $addval = '';
    }
    $query = "INSERT 
                INTO lk_persons (userid, registerid, name " . $add . " ) 
              VALUES";
    $query .= "('" . $userid . "', '" . $arg_registerid . "', '" . $newpersonarr[ $i ] . "' " . $addval . " )";

    $create_person = $go->query( $query, $i + 2 );
    $countperson   = $countperson + $create_person[ 'count' ];
    $personid[]    = $create_person[ 'id' ];
  }
}
if ( $go->good() ) {
  $return = array(
    'newname'  => $newpersonarr,
    'newid'    => $personid,
    'neworder' => $arg_neworder,
    'count'    => $countperson 
  );
}
?>
