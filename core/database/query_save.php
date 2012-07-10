<?php
//////////////////////////////////////
/* NAME: query_save
/* PARAMS: 
- queryid : id of query to save
- newsavename : name for the storage
- (registerid)
- (wrong) : of 1 only wrong answered words will be saved
/* RETURN: 
- count : number of saved words
- registerid : finaly used registerid
- savedid : id of created storage
/* DESCRIPTION: save a query to a storage
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 
- 20.04.2012 - Changed coding style, added this header
- 22.04.2012 - made registerid optional
////////////////////////////////////*/


$go->necessary( 'queryid', 'newsavename');

//get registerid from queryid
if ( $go->good() && NULL == $arg_registerid) {
  $get_register = $go->query(
    "SELECT registerid
       FROM lk_activelist
     WHERE userid = '" . $userid . "'
           AND id = '" . $arg_queryid . "'"
  , 5 );
  if( $get_register[ 'count' ] == 0  ) {
    $go->missing( 'registerid' ); // no registerid found
  } else {
    $arg_registerid = $get_register[ 'result' ][ 'registerid' ][0];
  }
}

if ( $go->good() ) {
  //Check name / Name überprüfen
  $check_savename = $go->query( 
     "SELECT name 
        FROM lk_savelist 
      WHERE userid='" . $userid . "' 
            AND registerid='" . $arg_registerid . "' 
            AND name='" . $arg_newsavename . "'"
  , 1 );
  if ( $check_savename[ 'count' ] > 0 ) {
    $go->error( 104 ); //name allready exists
  }
}

if ( $go->good() ) {
  //Load / Laden
  $andwrong   = $arg_wrong == 1 ? "AND correct='0'" : "";
  $get_wordid = $go->query( 
    "SELECT wordid 
       FROM lk_active 
     WHERE id='" . $arg_queryid . "'" . $andwrong
  , 2 );
  $wordid     = $get_wordid[ 'result' ][ 'wordid' ];
  if ( count( $wordid ) == 0 ) {
    $go->missing( 'wordid' );
  }
}

if ( $go->good() ) {
  //Add / Hinzufügen
  $create_save = $go->query( 
   "INSERT 
      INTO lk_savelist (userid, registerid, name) 
      VALUES ('" . $userid . "', '" . $arg_registerid . "', '" . $arg_newsavename . "')"
  , 3 );
  $savedid = $create_save[ 'id' ];
}

if ( $go->good() ) {
  //insert wordids
  $countid = count( $wordid );
  if ( $countid > 0 ) {
    //Querystring
    $insert = '';
    for ( $i = 0; $i < $countid; $i++ ) {
      $insert .= "('" . $savedid . "', '" . $wordid[ $i ] . "') ";
      if ( $i < $countid - 1 ) {
        $insert .= ',';
      }
    }
    //Execute
    $add_save = $go->query( 
      "INSERT 
         INTO lk_save (saveid, wordid) 
         VALUES " . $insert
    , 4 );
  }
}

// return
if ( $go->good() ) {
  $return = array(
    'registerid' => $arg_registerid,
    'savedid' => $savedid,
    'count' => $add_save[ 'count' ] 
  );
}
?>
