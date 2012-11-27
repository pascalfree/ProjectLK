<?php
//////////////////////////////////////
/* NAME: get_save
/* PARAMS: arg_
- registerid
- OR saveid
- (count) : if 1 will count words inside a storage
- (searchtext) : searchstring to match name
/* RETURN: 
- id
- userid
- registerid
- name
- deleted : indicates if the save is deleted
- time_created
- savecount : number of words in each save (returned if count == 1)
- count : number of loaded saves
/* DESCRIPTION: load storages
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( array( 'registerid', 'saveid' ) );

if ( $go->good() ) {
  //querystring
  //select tables
  $query = "SELECT t1.*";
  if ( $arg_count == 1 ) {
    $query .= ", COUNT(t2.wordid) as savecount";
  }
  $query .= " FROM lk_savelist t1 ";
  if ( $arg_wordid != NULL OR $arg_count == 1 ) {
    $query .= ", lk_save t2 ";
  }
  //userid
  $query .= " WHERE t1.userid='" . $userid . "' ";
  //registerid
  if ( $arg_registerid != NULL && $arg_registerid != '*' ) {
    $query .= " AND t1.registerid='" . $arg_registerid . "' ";
  }
  //wordid(s)
  if ( $arg_wordid != NULL ) {

    plk_util_makeArray( $arg_wordid );

    foreach ( $arg_wordid as $wid ) {
      $queryplus[] = " (t2.wordid='" . $wid . "') ";
    }
    $query .= "AND ( " . implode( ' OR ', $queryplus ) . " ) ";
  }

  if ( $arg_wordid != NULL || $arg_count == 1 ) {
    $query .= "AND t2.saveid=t1.id ";
  }

  //search
  if ( $arg_searchtext != NULL ) {
    $query .= " AND UCASE(t1.name) RLIKE UCASE('" . $arg_searchtext . ".*') ";
  }
  //id
  if ( $arg_saveid != NULL ) {
    $query .= " AND t1.id='" . $arg_saveid . "'";
  }
  //count
  if ( $arg_count == 1 ) {
    $query .= " GROUP BY t1.id";
  }

  //execute
  $get_save = $go->query( $query, 1 );

}

//return  
if ( $go->good() ) {
  $return            = $get_save[ 'result' ];
  $return[ 'count' ] = $get_save[ 'count' ];
}
?>
