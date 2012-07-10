<?php
//////////////////////////////////////
/* NAME: create_active_verb
/* PARAMS:
- registerid
- formid, allform(1/0) : array of formids
- personid, allperson(1/0) : array of personids
- verbid, allverb(1/0) : array of verbids
/* RETURN:
- id : array of verbids
- savedid : id of query
- count : number of verbs
/* DESCRIPTION: Creates an active (for reviews) of one or more verbs.
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'registerid' );

// load verbs / Lade
if ( $go->good() ) {
  // selected forms
  if ( 1 != $arg_allform ) {
    $qform = " AND ( ";
    $cf    = count( $arg_formid );
    for ( $i = 0; $i < $cf; $i++ ) {
      $qform .= " tverb.formid='" . $arg_formid[ $i ] . "' ";
      $qform .= $i + 1 < $cf ? " OR " : "";
    }
    $qform .= " )";
  } else {
    $cf = 1;
  }

  // selected persons
  if ( $arg_allperson != 1 ) {
    $qperson = " AND ( ";
    $cp      = count( $arg_personid );
    for ( $i = 0; $i < $cp; $i++ ) {
      $qperson .= " tverb.personid='" . $arg_personid[ $i ] . "' ";
      $qperson .= $i + 1 < $cp ? " OR " : "";
    }
    $qperson .= " )";
  } else {
    $cp = 1;
  }
  
  // selected verbs
  if ( $arg_allverb != 1 ) {
    $qverb = " AND ( ";
    $cv    = count( $arg_verbid );
    for ( $i = 0; $i < $cv; $i++ ) {
      $qverb .= " tverb.wordid='" . $arg_verbid[ $i ] . "' ";
      $qverb .= $i + 1 < $cv ? " OR " : "";
    }
    $qverb .= " )";
  } else {
    $cv = 1;
  }
  
  // ERRORS if anything is missing
  if ( $cf == 0 ) {
    $go->missing( 'formid' );
  }
  if ( $cp == 0 ) {
    $go->missing( 'personid' );
  }
  if ( $cv == 0 ) {
    $go->missing( 'verbid' );
  }
}

// get verbs
if ( $go->good() ) {
  $query1     = "SELECT tverb.id FROM lk_verbs tverb, lk_forms tform, lk_persons tperson, lk_words tword
            WHERE ( tverb.formid=tform.id AND tform.userid='" . $userid . "' AND tform.registerid='" . $arg_registerid . "' )
            AND ( tverb.personid=tperson.id AND tperson.userid='" . $userid . "' AND tperson.registerid='" . $arg_registerid . "' )
            AND ( tverb.wordid=tword.id AND tword.userid='" . $userid . "' AND tword.registerid='" . $arg_registerid . "' )" . $qverb . $qform . $qperson;
  $load_ids   = $go->query( $query1, 1 );
  $id         = $load_ids[ 'result' ][ 'id' ];
  $countids   = $load_ids[ 'count' ];
  $countwords = $countids;
  //if($countids==0) { $go->missing('wordid'); }
}

// add query / Hinzufügen
if ( $go->good() && $countids != 0 ) {
  $query         = "INSERT INTO lk_activelist (userid, registerid, name, mode) 
            VALUES ('" . $userid . "', '" . $arg_registerid . "', '" . $activename . "','4')";
  $create_active = $go->query( $query, 2 );
  $savedid       = $create_active[ 'id' ];
  
  // add every verb
  $insert = '';
  for ( $i = 0; $i < $countids; $i++ ) {
    $insert .= "('" . $savedid . "', '" . $id[ $i ] . "') ";
    if ( $i < $countids - 1 ) {
      $insert .= ',';
    }
  }
  $query      = "INSERT INTO lk_active (id, wordid) 
	          VALUES " . $insert;
  $add_active = $go->query( $query, 3 );
  $countwords = $add_active[ 'count' ];
}

// return
if ( $go->good() ) {
  $return = array(
    'id' => $id,
    'savedid' => $savedid,
    'count' => $countwords 
  );
}
?>
