<?php
//////////////////////////////////////
/* NAME: get_form
/* PARAMS: arg_
- registerid
- OR formid
- (wordid)
- (searchtext)
/* RETURN: 
- id : array
- userid : array
- registerid : array
- name : array
- info : information about this form
- deleted : array, indicates if form is deleted
- count : number of loaded persons
/* DESCRIPTION: load form from a verbtable
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( array( 'registerid', 'formid' ) );

if ( $go->good() ) {
  //querystring
  $query = "SELECT DISTINCT t1.* 
              FROM lk_forms t1 ";
  if ( $arg_wordid != NULL ) {
    $query .= " , lk_verbs t2 ";
  }
  $query .= " WHERE t1.userid='" . $userid . "' ";
  if ( $arg_registerid != NULL && $arg_registerid != '*' ) {
    $query .= " AND t1.registerid='" . $arg_registerid . "' ";
  }
  if ( $arg_wordid != NULL ) {
    $query .= " AND ( ";
    if ( is_array( $arg_wordid ) ) {
      $len = count( $arg_wordid );
      for ( $i = 0; $i < $len; $i++ ) {
        $iquery[] = " t2.wordid='" . $arg_wordid[ $i ] . "' ";
      }
      $query .= implode( ' OR ', $iquery );
    } else {
      $query .= " t2.wordid='" . $arg_wordid . "' ";
    }
    $query .= " ) AND t2.formid=t1.id ";
  }
  if ( $arg_formid != NULL ) {
    $query .= " AND t1.id='" . $arg_formid . "'";
  }
  if ( $arg_searchtext != NULL ) {
    $query .= " AND UCASE(t1.name) RLIKE UCASE('" . $arg_searchtext . ".*') ";
  }
  
  //Execute
  $get_form = $go->query( $query, 1 );
}

// return
if ( $go->good() ) {
  $return            = $get_form[ 'result' ];
  $return[ 'count' ] = $get_form[ 'count' ];
}
?>
