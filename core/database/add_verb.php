<?php
//////////////////////////////////////
/* NAME: add_verb
/* PARAMS: arg_
- wordid
- formid
- personid
- (newkword) : verb to add
/* RETURN: 
- verbid
- newkword : final value
- exists: 1 if entry was overwritten
- delete : 1 if entry was deleted
/* DESCRIPTION: add a konjugation to the table
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'wordid', 'formid', 'personid' );

if ( $go->good() ) {
  //forbidden characters / Verbotene Zeichen
  plk_util_removeForbidden( $arg_newkword );
  
  //Check User
  $checkuser = $go->query( 
     "SELECT userid 
        FROM lk_words 
      WHERE userid='" . $userid . "' 
            AND id='" . $arg_wordid . "'"
  , 1 );
  if ( $checkuser[ 'count' ] == 0 ) {
    $go->error( 100 );
  }
}

//if verb with this ids exists: edit.
if ( $go->good() ) {
  $exists = false;
  
  //Find existing entry and get id.
  $get_verb = $go->query( 
    "SELECT id 
       FROM lk_verbs 
     WHERE wordid='" . $arg_wordid . "' 
           AND personid='" . $arg_personid . "' 
           AND formid='" . $arg_formid . "'"
  , 3 );
  if ( $get_verb[ 'count' ] != 0 ) {
    $tid    = $get_verb[ 'result' ][ 'id' ][ 0 ]; //ID of this verb.
    $exists = true;
    //Give it to the edit_verb function
    $edit = plk_request( 'edit_verb', array(
                          'verbid' => $tid,
                          'newkword' => $arg_newkword
    ) );

    $return             = $edit;
    $return[ 'verbid' ] = $tid;
    $return[ 'exists' ] = $exists;
  }
}

//Adding
if ( $go->good() ) {
  $empty = false; 
  //don't add empty string
  if ( $arg_newkword == NULL || $arg_newkword == '' ) {
    $empty  = true; //don't go to insertion
    $return = array(
       'delete' => 1 
    );
  }
}

if ( !$exists && !$empty ) {
  if ( $go->good() ) {
    $add_verb = $go->query( 
     "INSERT 
        INTO lk_verbs (wordid, personid, formid, kword) 
        VALUES ('" . $arg_wordid . "', '" . $arg_personid . "', '" . $arg_formid . "', '" . $arg_newkword . "')"
    , 2 );
  }

  if ( $go->good() ) {
    $return = array(
       'newkword' => $arg_newkword,
       'verbid'   => $add_verb[ 'id' ] 
    );
  }
}
?>
