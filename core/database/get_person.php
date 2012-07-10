<?php
//////////////////////////////////////
/* NAME: get_person
/* PARAMS: arg_
- registerid
- OR personid
- (wordid)
- (searchtext)
/* RETURN: 
- id : array
- userid : array
- registerid : array
- name : array
- order : array, a number that indicates the order of the persons
- deleted : array, indicates if person is deleted
- count : number of loaded persons
/* DESCRIPTION: load persons from a verbtable
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

//need registerid or personid
$go->necessary( array( 'registerid', 'personid' ) );

if ( $go->good() ) {
  //querystring
  $query = "SELECT DISTINCT t1.* FROM lk_persons t1 ";
  if ( $arg_wordid != NULL ) {
    $query .= " , lk_verbs t2 ";
  }
  $query .= " WHERE t1.userid='" . $userid . "' ";
  if ( $arg_registerid != NULL && $arg_registerid != '*' ) {
    $query .= " AND t1.registerid='" . $arg_registerid . "' ";
  }
  if ( $arg_wordid != NULL ) { //with wordid
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
    $query .= " ) AND t2.personid=t1.id ";
  }

  //with personid
  if ( $arg_personid != NULL ) {
    $query .= "AND t1.id='" . $arg_personid . "'";
  }

  //with search
  if ( $arg_searchtext != NULL ) {
    $query .= " AND UCASE(t1.name) RLIKE UCASE('" . $arg_searchtext . ".*') ";
  }

  $query .= " ORDER BY t1.`order`"; //order by

  //Execute
  $get_person = $go->query( $query, 1 );
}

// return
if ( $go->good() ) {
  $return                 = $get_person[ 'result' ];
  $return[ 'count' ]      = $get_person[ 'count' ];
}
?>
