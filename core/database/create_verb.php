<?php
//////////////////////////////////////
/* NAME: create_verb
/* PARAMS: 
- wordid : wordid to add a verb to
- personid : (array of) person ids
- formid : (array of) form ids
- newverb : (array of) konjugations
/* RETURN:
- newverb : final array of verbnames
- newid : array of created ids
- count : number of created konjugations
/* DESCRIPTION: adds multiple verbs at once (unlike add_verb)
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'wordid', 'personid', 'formid', 'newverb' );

//Fix: make them all arrays
if ( $go->good() ) {
  if ( !is_array( $arg_newverb ) ) {
    $arg_newverb = array( $arg_newverb );
  }
  if ( !is_array( $arg_personid ) ) {
    $arg_personid = array( $arg_personid );
  }
  if ( !is_array( $arg_formid ) ) {
    $arg_formid = array( $arg_formid );
  }
}

if ( $go->good() ) {
  //forbidden
  $arg_newverb = array_map( "plk_util_removeForbidden", $arg_newverb );
}

if ( $go->good() ) {
  //check User
  $query     = "SELECT userid FROM lk_words WHERE userid='" . $userid . "' AND id='" . $arg_wordid . "'";
  $checkuser = $go->query( $query, 1 );
  if ( $checkuser[ 'count' ] == 0 ) {
    $go->error( 100 );
  }
}
if ( $go->good() ) {
  //write / Schreiben
  //Querystring
  $add    = '';
  $addval = '';
  if ( $time_created != NULL ) {
    $add .= ', time_created';
    $addval .= ", '" . $time_created . "'";
  }
  if ( $newregular != NULL ) {
    $add .= ', regular';
    $addval .= ", '" . $newregular . "'";
  }
  $query = "INSERT INTO lk_verbs (wordid, personid, formid, kword " . $add . " ) VALUES ";
  $len   = count( $arg_newverb );
  for ( $i = 0; $i < $len; $i++ ) {
    if ( $arg_newverb[ $i ] != '' ) {
      $inquery[] = "('" . $arg_wordid . "', '" . $arg_personid[ $i ] . "', '" . $arg_formid[ $i ] . "', '" . $arg_newverb[ $i ] . "' " . $addval . " ) ";
    }
  }

  if ( is_array( $inquery ) ) {
    $query .= implode( ',', $inquery );
    //Execute
    $create_verb = $go->query( $query, 2 );
    $countverb   = $create_verb[ 'count' ];
  } else {
    $countverb = 0;
  }
}

// return
if ( $go->good() ) {
  $return = array(
    'newverb' => $arg_newverb,
    'count'   => $countverb 
  );
}
?>
