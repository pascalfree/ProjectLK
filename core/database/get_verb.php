<?php
//////////////////////////////////////
/* NAME: get_verb
/* PARAMS: (all optional) arg_
- id
- worid
- personid
- formid
- searchtext
- struc : if 1 won't return result as multidimensional array. 
/* RETURN: 
if struc == 1
id	wordid	personid	formid	kword	regular	time_created
- id : array
- wordid : array
- personid : array
- formid : array
- kword : array, konjugation
- regular : array, indicates if konjugation is regular
- time_created array
- count : number of loaded konjugations
else
- [wordid][personid][formid] : array
    - id : 
    - name : 
/* DESCRIPTION: load konjugation from a verbtable
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header
////////////////////////////////////*/


//Querystring
$query = "SELECT t1.registerid, t2.* 
            FROM lk_words t1, lk_verbs t2 
          WHERE t1.userid='" . $userid . "' 
                AND t1.id=t2.wordid";

$getarray = array(
  'id',
  'wordid',
  'personid',
  'formid' 
);

foreach ( $getarray as $tname ) {
  if ( ${'arg_' . $tname} != NULL ) {
    $getid = ${'arg_' . $tname};
    $query .= " AND ";
    if ( is_array( $getid ) ) {
      $len = count( $getid );
      unset( $inquery );
      for ( $i = 0; $i < $len; $i++ ) {
        $inquery[] = "t2." . $tname . " = '" . $getid[ $i ] . "'";
      }
      $query .= "( " . implode( ' OR ', $inquery ) . " )";
    } else {
      $query .= "t2." . $tname . " = '" . $getid . "'";
    }
  }
}
if ( $arg_searchtext != NULL ) {
  $query .= " AND UCASE(t2.kword) RLIKE UCASE('" . $arg_searchtext . ".*') ";
}

// execute
$getverb = $go->query( $query, 1 );

// return
if ( $go->good() ) {
  if ( $arg_struc == 1 ) {
    $return            = $getverb[ 'result' ];
    $return[ 'count' ] = $getverb[ 'count' ];
  } else {
    $v = &$getverb[ 'result' ];
    for ( $i = 0; $i < $getverb[ 'count' ]; $i++ ) {
      $return[ $v[ 'wordid' ][ $i ] ][ $v[ 'personid' ][ $i ] ][ $v[ 'formid' ][ $i ] ] = array(
        'name' => $v[ 'kword' ][ $i ],
        'id' => $v[ 'id' ][ $i ] 
      );
    }
  }
}
?>
