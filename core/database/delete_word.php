<?php
//////////////////////////////////////
/* NAME: delete_word
/* PARAMS: 
- wordid
- OR allmarked and /global/
/* RETURN: 
- count : number of deleted words
- delfromtag : number of deleted references to tags
- delfromsave : number of deleted references to save
- deltag : number of deleted tags
- delsave : number of deleted saves
/* DESCRIPTION: delete one or more words
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 04.04.2012
/* UPDATE: 20.04.2012 - Added this header, changed coding style
////////////////////////////////////*/

$go->necessary( array(
  'wordid',
  'allmarked' 
) );

if ( $go->good() ) {
  // make word an array
  plk_util_makeArray( $arg_wordid );
  
  // load words from location
  if ( $arg_allmarked ) {
    $arg_wordid = plk_util_loadWordId( $go );
  }
}

if ( $go->good() ) {
  //////////
  //Clean up
  //////////
  //tags
  //delete this reference to tag
  if($countid > 0) { //FIX
    $query = "DELETE lk_tags 
                FROM lk_tags, lk_words 
              WHERE ";
    for ( $i = 0; $i < $countid; $i++ ) {
      $query .= " ( lk_tags.wordid='" . $arg_wordid[ $i ] . "' 
                    AND lk_tags.wordid=lk_words.id
                    AND lk_words.userid='" . $userid . "' ) ";
      if ( $i < $countid - 1 ) {
        $query .= ' OR ';
      }
    }
    $deleted_from_tag = $go->query( $query, 2 );
  }
}

if ( $go->good() ) {
  //delete unused tags
  $deleted_tag = $go->query( 
     "DELETE 
        FROM lk_taglist 
      WHERE lk_taglist.userid='" . $userid . "'
        AND NOT EXISTS (
          SELECT * 
            FROM lk_tags 
          WHERE lk_tags.tagid=lk_taglist.id)"
  , 4 );
}
if ( $go->good() ) {
  //save
  //delete this reference to save
  $query = "DELETE lk_save 
              FROM lk_save, lk_words 
            WHERE ";
  $countid = count( $arg_wordid );
  for ( $i = 0; $i < $countid; $i++ ) {
    $query .= " ( lk_save.wordid='" . $arg_wordid[ $i ] . "' 
                  AND lk_save.wordid=lk_words.id 
                  AND lk_words.userid='" . $userid . "') ";
    if ( $i < $countid - 1 ) {
      $query .= ' OR ';
    }
  }
  $deleted_from_save = $go->query( $query, 5 );
  
  //delete empty save
  $deleted_save = $go->query( 
     "DELETE 
        FROM lk_savelist 
      WHERE userid='" . $userid . "'
        AND NOT EXISTS (
          SELECT lk_save.saveid 
            FROM lk_save 
          WHERE lk_save.saveid=lk_savelist.id)"
  , 7 );
}

////delete word
if ( $go->good() ) {
  $query = "DELETE 
              FROM lk_words 
            WHERE ";
  for ( $i = 0; $i < $countid; $i++ ) {
    $query .= " ( id='" . $arg_wordid[ $i ] . "' 
                  AND userid='" . $userid . "') ";
    if ( $i < $countid - 1 ) {
      $query .= ' OR ';
    }
  }
  $delete_word = $go->query( $query, 1 );
}

// return
if ( $go->good() ) {
  $return = array(
    'delfromtag'  => $deleted_from_tag[ 'count' ],
    'delfromsave' => $deleted_from_save[ 'count' ],
    'deltag'      => $deleted_tag[ 'count' ],
    'delsave'     => $deleted_save[ 'count' ],
    'count'       => $delete_word[ 'count' ] 
  );
}
?>
