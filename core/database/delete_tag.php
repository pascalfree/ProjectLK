<?php
//////////////////////////////////////
/* NAME: delete_tag
/* PARAMS: arg_
- tagid
- wordid
- OR allmarked and /global/
/* RETURN: 
- count : number of deleted tags
/* DESCRIPTION: deletes a single tag from one or more words
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 04.04.2012
/* UPDATE: 20.04.2012 - Added this header, changed coding style
////////////////////////////////////*/

$go->necessary( 'tagid', array(
  'allmarked',
  'wordid' 
) );

if ( $go->good() ) {
  //every word is marked: get them by global parameters
  if ( $arg_allmarked ) {
    $arg_wordid = load_wordid( $go );
  }
}


if ( $go->good() ) {
  make_array( $arg_wordid );
}

// delete each tag-word link
if ( $go->good() ) {
  $len      = count( $arg_wordid );
  $query    = "DELETE FROM t1 USING lk_tags t1, lk_taglist t2 
                      WHERE t2.userid='" . $userid . "' 
                            AND ( ";
  for ( $i = 0; $i < $len; ++$i ) {
      $query .= "( t1.wordid='" . $arg_wordid[ $i ] . "' 
                   AND t1.tagid='" . $arg_tagid . "' 
                   AND t1.tagid=t2.id ) OR ";
  }
  $query .= ' 0 )';
  $delete_tag = $go->query( $query, 1 );
  $totaldel   = $delete_tag[ 'count' ];
}

//delete tag from list if empty
if ( $go->good() ) {
  $query = "DELETE 
              FROM lk_taglist 
            WHERE lk_taglist.userid='" . $userid . "' 
                  AND NOT EXISTS (
                      SELECT * 
                        FROM lk_tags 
                      WHERE lk_tags.tagid=lk_taglist.id)";
  $delete_taglist = $go->query( $query, 2 );
}

//return
if ( $go->good() ) {
  $return = array(
     'count' => $totaldel 
  );
}
?>
