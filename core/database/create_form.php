<?php
//////////////////////////////////////
/* NAME: create_form
/* PARAMS: 
- registerid
- newform (comma separated)
- newinfo
/* RETURN:
- newname : array of formnames
- newid : array of created ids
- count : number of created forms
/* DESCRIPTION: Create a new form for verbs
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'registerid', 'newform' );

if ( $go->good() ) {
  //forbidden characters / Verbotene Zeichen
  plk_util_removeForbidden( $arg_newform );
  
  //comma separated input
  $newformarr = plk_util_commaArray( $arg_newform );
  $countarr   = count( $newformarr );
  $countform  = 0;
  for ( $i = 0; $i < $countarr; ++$i ) { //add multiple entries
    //write / Schreiben
    if ( NULL != $arg_newinfo ) {
      $add    = ', info';
      $addval = ", '" . $arg_newinfo . "'";
    } else {
      $add    = '';
      $addval = '';
    }
    $query  = "INSERT 
                 INTO lk_forms (userid, registerid, name " . $add . " ) 
               VALUES";
    $query .= "('" . $userid . "', '" . $arg_registerid . "', '" . $newformarr[ $i ] . "' " . $addval . ")";

    $create_form = $go->query( $query, $i + 1 );
    $countform  += $create_form[ 'count' ];
    $formid[]    = $create_form[ 'id' ];
  }
}

// return
if ( $go->good() ) {
  $return = Array(
    'newname' => $newformarr,
    'newid'   => $formid,
    'count'   => $countform 
  );
}
?>
