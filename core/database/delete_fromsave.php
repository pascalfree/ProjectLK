<?php
//////////////////////////////////////
/* NAME: delete_fromsave
/* PARAMS: 
- saveid
- registerid
- wordid : can be an array
- OR allmarked and /global/
/* RETURN: 
- count : number of deleted references
/* DESCRIPTION: delete one or more words from a save
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 20.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'saveid', 'registerid', array( 'wordid', 'allmarked' ) );

if ( $go->good() ) {
  plk_util_makeArray( $arg_wordid );

  // load if allmarked
  if ( $arg_allmarked ) {
    $arg_wordid = load_arg_wordid( $go );
  }
}
if ( $go->good() ) {
  $query = "DELETE lk_save 
              FROM lk_save, lk_words 
            WHERE lk_words.userid='" . $userid . "'
                  AND lk_save.wordid=lk_words.id 
                  AND (";
  $countid = count( $arg_wordid );
  for ( $i = 0; $i < $countid; $i++ ) {
    $query .= " ( lk_save.wordid='" . $arg_wordid[ $i ] . "' 
                  AND lk_save.saveid='" . $arg_saveid . "') OR";
  }
  $query .= " 0 ) ";
  $delete_save = $go->query( $query , 1 );
 
/*
  //remove words
  $totaldel = 0;
  $countid  = count( $wordid );
  for ( $i = 0; $i < $countid; $i++ ) {
    if ( $go->good() ) {
      $delete_save = $go->query( 
        "DELETE 
           FROM t1 
           USING lk_save t1, lk_savelist t2 
         WHERE t1.wordid='" . $wordid[ $i ] . "' 
               AND t1.saveid='" . $saveid . "' 
               AND t1.saveid=t2.id 
               AND registerid='" . $registerid . "' 
               AND t2.userid='" . $userid . "' "
      , 1 );
      $totaldel += $delete_save[ 'count' ];
    } else {
      break;
    }
  }
*/
}


if ( $go->good() ) {
  //clean empty save 
  if ( 0 < $delete_save[ 'count' ] ) {
    $go->query( 
       "DELETE 
          FROM lk_savelist 
        WHERE userid='" . $arg_userid . "'
              AND NOT EXISTS (
                  SELECT lk_save.saveid 
                    FROM lk_save 
                  WHERE lk_save.saveid=lk_savelist.id)"
    , 2 );
  }
}

// return
if ( $go->good() ) {
  $return = array(
    'count' => $delete_save[ 'count' ]
  );
}
?>
