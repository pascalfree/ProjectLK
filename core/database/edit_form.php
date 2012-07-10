<?php
//////////////////////////////////////
/* NAME: edit_form
/* PARAMS: arg_
- formid
- (newform)
- (newinfo) : new information about form
/* RETURN: 
- count : number of edited forms
/* DESCRIPTION: edit forms in verbtable
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'formid' );

if ( $go->good() ) {
  remove_forbidden( $arg_newform );
  
  //update / überarbeiten
  $edits = NULL;
  if ( $arg_newform != NULL && $arg_newform != '' ) {
    $edits[] = " name = '" . $arg_newform . "'";
  }
  if ( $arg_newinfo != NULL ) {
    $edits[] = " info = '" . $arg_newinfo . "'";
  }
  if ( $edits != NULL ) {
    $edit_form = $go->query( 
      "UPDATE lk_forms 
         SET " . implode( ',', $edits ) . "
       WHERE id='" . $arg_formid . "' 
       AND userid='" . $userid . "' "
    , 1 );
  }
}

// return
if ( $go->good() ) {
  $return = array(
    'count' => $edit_form[ 'count' ] 
  );
}
?>
