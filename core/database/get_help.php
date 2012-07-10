<?php
//////////////////////////////////////
/* NAME: add_tosave
/* PARAMS: arg_
 - language : language of help to get
 - id
 - OR title
 - OR gettitle : if 1 only returns title without content
 - OR all : load all help topics with contents
/* RETURN: 
 - count : number of topics loaded
 - id : array of loaded ids
 - title : array of titles
 - titletext : array of correspondind titles to language
 - valuetext : array of topic values
/* DESCRIPTION: loads help topic(s)
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2012
/* UPDATES: 19.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'language', array( 'id', 'title', 'gettitle', 'all' ) );

if ( $go->good() ) {
  // only get title?
  if ( $arg_gettitle != 1 ) {
    $sel = ',valuetext';
  } else {
    $sel = '';
  }
  $query = "SELECT id, title, titletext " . $sel . " 
              FROM lk_help 
            WHERE language='" . $arg_language . "' ";
  if ( $arg_id != NULL || $arg_title != NULL ) {  // select specific topic
    $query .= " AND (title='" . $arg_title . "' 
                     OR id='" . $arg_id . "')";
  }
  $get_help = $go->query( $query, 1 );
}

// return 
if ( $go->good() ) { 
  $return = $get_help[ 'result' ];
  //utf8_encode: just wont work without it.
  for ( $i = 0; $i < $get_help[ 'count' ]; $i++ ) {
    $return[ 'valuetext' ][ $i ] = utf8_encode( $return[ 'valuetext' ][ $i ] );
    $return[ 'titletext' ][ $i ] = utf8_encode( $return[ 'titletext' ][ $i ] );
  }
  $return[ 'count' ] = $get_help[ 'count' ];
}
?>
