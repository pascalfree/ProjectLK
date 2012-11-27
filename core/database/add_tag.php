<?php
//////////////////////////////////////
/* NAME: add_tag
/* PARAMS:
- registerid
- newtag: name of tag (comma separated)
- (wordid[array] OR /global/ AND allmarked=1) 
/* RETURN: 
- counttag : number of tags added
- countword
- count : number of tags added to the table  
- wordid
- tags : Array of tagnames
- tagid : Array of tagids
/* DESCRIPTION: Adds a tag to a word or to an array of words.
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2011
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'registerid', 'newtag', array(
  'wordid',
  'allmarked' 
) ); //Error 2xx

if ( $go->good() ) {
  //forbidden characters / Verbotene Zeichen
  plk_util_removeForbidden($arg_newtag);
  
  //every word is marked: get them by global parameters
  if ( $arg_allmarked ) {
    $arg_wordid = plk_util_loadWordid( $go );
  }
}

if ( $go->good() ) { //Check for every word if it belongs to the user
  plk_util_makeArray( $arg_wordid );

  $len = count( $arg_wordid );
  for ( $i = 0; $i < $len; $i++ ) {
    //checkuser
    $chquery    = "SELECT id 
                     FROM lk_words 
                   WHERE id='" . $arg_wordid[ $i ] . "' 
                         AND userid='" . $userid . "'";
    $check_user = $go->query( $chquery, 1 );
    if ( 0 == $check_user[ 'count' ] ) {
      $go->error( 100 ); //Error 100
      break;
    }
  }
}

if ( $go->good() ) {
  ////add tags
  //build query
  $tags_arr = plk_util_commaArray( $arg_newtag );

  $query = "INSERT IGNORE 
                 INTO lk_tags (wordid, tagid) 
                 VALUES ";
  $ctags = count( $tags_arr );
  for ( $i = 0; $i < $ctags; $i++ ) { //for every tag
    $thistag = $tags_arr[ $i ];
    if ( $thistag != '' && $thistag != NULL ) { //eliminate doubled commas
      //Check if tag already exists
      $check_tag  = $go->query( 
        "SELECT id 
           FROM lk_taglist 
         WHERE name='" . $thistag . "' 
               AND userid='" . $userid . "' 
               AND registerid='" . $arg_registerid . "'"
      , 2 );

      if ( 0 < $check_tag[ 'count' ] ) {
        $tagid = intval( $check_tag[ 'result' ][ 'id' ][ 0 ] );
      } else { //If not: create new one
        $create_tag = $go->query( 
          "INSERT 
             INTO lk_taglist (userid, registerid, name) 
             VALUES ('" . $userid . "', '" . $arg_registerid . "', '" . $thistag . "') "
        , 3 );
        $tagid = $create_tag[ 'id' ];
      }

      //adds tag and tagid to the list of added tags
      $tagid_arr[ $i ] = $tagid;
      $tags_arr[ $i ]  = $thistag;

      //make list to add in query
      for ( $j = 0; $j < $len; $j++ ) {
        $query .= "('" . $arg_wordid[ $j ] . "', '" . $tagid . "')";
        if ( $j != $len - 1 ) {
          $query .= " , ";
        }
      }
      if ( $i != $ctags - 1 ) {
        $query .= " , ";
      }
    }
  }
  if ( $go->good() ) {
    //Execute
    $add_tags = $go->query( $query, 4 ); //Error 304
  }
}

$return = array(
  'counttag'  => count( $tagid_arr ), //number of added tags
  'countword' => count( $arg_wordid ), //number of words
  'count'     => $add_tags[ 'count' ], //total number of assignments tag->word
  'wordid'    => $arg_wordid, //ids of words
  'id'        => $tagid_arr, //ids of tags as array
  'name'      => $tags_arr //names of tags as array
);
?>
