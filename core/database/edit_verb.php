<?php
//////////////////////////////////////
/* NAME: edit_verb
/* PARAMS: 
- verbid
- (newkword) : new konjugation
/* RETURN:
- newkword : final konjugation
- count : number of edited verbs
- delete : 1 if entry was deleted
/* DESCRIPTION: edit a konjugation to the table
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'verbid' );

if ( $go->good() ) {
  //forbidden characters
  plk_util_removeForbidden( $arg_newkword );
  
  if ( $arg_newkword != '' && $arg_newkword != NULL ) {
    // change value
    $query  = "UPDATE lk_verbs t1, lk_words t2 SET t1.kword='" . $arg_newkword . "' ";
    $delete = 0;
  } else {
    // remove entry if new value is empty
    $query  = "DELETE t1 FROM lk_verbs t1, lk_words t2 ";
    $delete = 1;
  }
  $query .= " WHERE t1.id='" . $arg_verbid . "' AND t2.id=t1.wordid AND t2.userid='" . $userid . "'";
  $edit_verb = $go->query( $query, 1 );
}

// return
if ( $go->good() ) {
  $return = array(
    'newkword' => $arg_newkword,
    'count'    => $edit_verb[ 'count' ],
    'delete'   => $delete
  );
}
?>
