<?php
//////////////////////////////////////
/* NAME: create_active
/* PARAMS: 
 - registerid
 - [activename] : name of active
 - (wordid[array] OR /global/ AND allmarked=1) 
/* RETURN: 
 - activename : number of tags added
 - wordid : Array of wordids
 - savedid : Id of active
 - countwords : number of words in active
 - mode : mode of active for queries (0-4)
/* DESCRIPTION: Creates an active (for reviews) of one or more words.
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2011
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'registerid', array(
  'wordid',
  'allmarked' 
) );

//get words
if ( $go->good() ) {
  if ( $arg_allmarked == 1 ) {
    $arg_wordid              = NULL; //Reset $arg_wordid, so getglobal wont take this.
    $params              = getglobal('arg_');
    $params[ 'nolimit' ] = 1;
    $params[ 'select' ]  = 'tword.id'; //Fix: otherwise id is ambigous
    $getwordid           = request( 'get_word', $params );
    if ( $getwordid[ 'errnum' ] != 0 ) {
      $go->error( 400, $getwordid[ 'errnum' ] . ': ' . $getwordid[ 'errname' ] . ' - ' . $getwordid[ 'lastquery' ] );
    }
    $arg_wordid = $getwordid[ 'id' ];
  }
}

// if no words -> error
if ( $go->good() ) {
  if ( count( $arg_wordid ) == 0 ) {
    $go->missing( 'wordid' );
  }
}


//Choose mode / Wähle Modus
if ( $go->good() ) {
  $tmode = 0;
  // if whole group is queried -> Leitner mode
  if ( $arg_allmarked && is_numeric( $groupid ) && $tagid == NULL && $saveid == NULL && $wordclassid == NULL && $searchid == NULL ) {
    // check for group locks
    if ( $groupid > 1 ) {
      $query          = "SELECT grouplock FROM lk_registers 
                         WHERE userid='" . $userid . "' AND id='" . $arg_registerid . "'";
      $load_grouplock = $go->query( $query, 3 );
      if ( $load_grouplock[ 'count' ] > 0 ) {
        $glock = explode( "?", $load_grouplock[ 'result' ][ 'grouplock' ][ 0 ] );
        if ( $glock[ $groupid - 2 ] <= count( $arg_wordid ) ) {
          $tmode = 2;
        }
      }
    } else {
      $tmode = 2;
    }
  }
}

// add query / Hinzufügen
if ( $go->good() ) {
  $query         = "INSERT INTO lk_activelist (userid, registerid, name, mode) 
    VALUES ('" . $userid . "','" . $arg_registerid . "' ,'" . $arg_activename . "','" . $tmode . "')";
  $create_active = $go->query( $query, 1 );
  $savedid       = $create_active[ 'id' ];
}

// add all words
if ( $go->good() ) {
  $insert  = '';
  $countid = count( $arg_wordid );
  for ( $i = 0; $i < $countid; $i++ ) {
    $insert .= "('" . $savedid . "', '" . $arg_wordid[ $i ] . "') ";
    if ( $i < $countid - 1 ) {
      $insert .= ',';
    }
  }
  $query      = "INSERT INTO lk_active (id, wordid) 
    VALUES " . $insert;
  $add_active = $go->query( $query, 2 );
}

// return
if ( $go->good() ) {
  $return = array(
    'activename' => $arg_activename,
    'wordid' => $arg_wordid,
    'savedid' => $savedid,
    'countwords' => $add_active[ 'count' ],
    'mode' => $tmode 
  );
}
?>
