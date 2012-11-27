<?php
//////////////////////////////////////
/* NAME: create_save
/* PARAMS: 
- registerid
- newsave : name of storage to create
- wordid[array] OR /global/ and allmarked == 1
/* RETURN:
- newname : final name of created storage
- savedid : created id
- wordid : array of wordids added to the storage
- count : number of created storages
/* DESCRIPTION: Create a new storage with given name and words
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'registerid', 'newsave', array(
  'wordid',
  'allmarked' 
) );

//wordids
//every word is marked: get them by global parameters
if ( $go->good() ) {
  if ( 1 == $arg_allmarked ) {
    $params              = plk_util_getGlobal('arg_');
    $params[ 'nolimit' ] = 1;
    $words               = plk_request( 'get_word', $params );
    if ( $words[ 'errnum' ] != 0 ) {
      $go->error( 400, $words[ 'errnum' ] . ': ' . $words[ 'errname' ] );
    }
    $arg_wordid = $words[ 'id' ];
  }
}

//ERROR if no words are selected
if ( $go->good() ) {
  if ( count( $arg_wordid ) == 0 ) {
    $go->missing( 'wordid' );
  }
}

//find name that doesn't exist
$i = 1;
do {
  if ( $go->good() ) {
    if ( $i != 1 ) {
      $checkname = $arg_newsave . " (" . $i . ")";
    } else {
      $checkname = $arg_newsave;
    }
    $i++;
    //Check name / Name überprüfen
    $query          = "SELECT name FROM lk_savelist WHERE userid='" . $userid . "' AND registerid='" . $arg_registerid . "' AND name='" . $checkname . "'";
    $check_savename = $go->query( $query, 1 );
  }
} while ( $check_savename[ 'count' ] != 0 );

if ( $go->good() ) {
  $arg_newsave = $checkname; //now definitely
}

if ( $go->good() ) {
  //Add / Hinzufügen
  if ( $time_created != NULL ) {
    $add     = ', time_created';
    $addtime = ", '" . $time_created . "'";
  } else {
    $add     = '';
    $addtime = '';
  }
  $query       = "INSERT INTO lk_savelist (userid, registerid, name" . $add . ") 
                  VALUES ('" . $userid . "', '" . $arg_registerid . "', '" . $arg_newsave . "'" . $addtime . ")";
  $create_save = $go->query( $query, 2 );
  $savedid     = $create_save[ 'id' ];
}

//insert words to save
if ( $go->good() ) {
  $countid = count( $arg_wordid );
  $insert  = '';
  for ( $i = 0; $i < $countid; $i++ ) {
    $insert .= "('" . $savedid . "', '" . $arg_wordid[ $i ] . "') ";
    if ( $i < $countid - 1 ) {
      $insert .= ',';
    }
  }
  $query    = "INSERT INTO lk_save (saveid, wordid) 
			         VALUES " . $insert;
  $add_save = $go->query( $query, 3 );
}

// return
if ( $go->good() ) {
  $return = Array(
    'newname' => $arg_newsave,
    'wordid'  => $arg_wordid,
    'savedid' => $savedid,
    'count'   => $add_save[ 'count' ] 
  );
}
?>
