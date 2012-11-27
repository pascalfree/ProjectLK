<?php
//////////////////////////////////////
/* NAME: edit_person
/* PARAMS: arg_
- personid
- newperson : new name of person
- neworder : new number to indicate order (higher = later)
/* RETURN: 
- count : number of edited persons
/* DESCRIPTION: edit a person in the verbtable
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 20.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'personid' );

if ( $go->good() ) {
  //forbidden characters / Verbotene Zeichen
  plk_util_removeForbidden( $arg_newperson );
  
  //update / überarbeiten
  $edits = NULL;
  if ( $arg_newperson != NULL && $arg_newperson != '' ) {
    $edits[] = " name='" . $arg_newperson . "' ";
  }
  if ( $arg_neworder != NULL && $arg_neworder != '' ) {
    $edits[] = " `order`='" . $arg_neworder . "' ";
  }
  if ( $edits != NULL ) {
    $edit_person = $go->query( 
      "UPDATE lk_persons 
         SET " . implode( ',', $edits ) ."
       WHERE id='" . $arg_personid . "' 
             AND userid='" . $userid . "' "
    , 1 );
  }
}

// return
if ( $go->good() ) {
  $return = array(
     'count' => $edit_person[ 'count' ] 
  );
}
?>
