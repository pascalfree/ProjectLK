<?php
//////////////////////////////////////
/* NAME: get_verblist
/* PARAMS: (all optional) arg_
- registerid
- limit
- getempty : if 1 will load verbs without a verbtable
- limit_empty
- select : MySQL SELECT clause value
/* RETURN: 
/* -> REMARKS : id and wordfore may not be returned when changing "select"
- id : wordid of verb
- wordfore : verb in foreign language
- count : number of loaded verbs
- empty : object, verbs without verbtable
    - count : number of loaded verbs without verbtable
    - id : see above
    - wordfore : see above
/* DESCRIPTION: loads verbs in verblist ( and without verblist )
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES:
- 22.04.2012 : Changed coding style
- 22.04.2012 : added this header
- 22.04.2012 : optimized query for verbs without verbtable
////////////////////////////////////*/

if( NULL == $arg_select ) { 
  $arg_select = 'DISTINCT t1.wordfore, t1.id'; 
}

//get verbs with entry
$query = "SELECT ".$arg_select." 
            FROM lk_words t1, lk_verbs t2 
          WHERE t1.userid='".$userid."' 
                AND t1.id=t2.wordid ";
if( NULL != $arg_registerid ) { 
  $query .= " AND t1.registerid='".$arg_registerid."' "; 
}
if( NULL != $arg_limit) { 
  $query .= " LIMIT 0," . ( $arg_limit + 1 ); 
}
$qresult = $go->query( $query, 1 );

//get verbs without entry
if( 1 == $arg_getempty) {
  $query = "SELECT ".$arg_select." 
              FROM lk_words t1
            WHERE t1.userid='".$userid."' 
                  AND t1.wordclassid=2 
                  AND NOT EXISTS (
                    SELECT t1.id FROM lk_verbs lkv
                    WHERE t1.id = lkv.wordid)";
  if(  NULL != $arg_registerid ) { 
    $query .= " AND t1.registerid='".$arg_registerid."' "; 
  }
  if( NULL != $arg_limit_empty ) { 
    $query .= " LIMIT 0," . ( $arg_limit_empty + 1 ); 
  }
  $emptyresult = $go->query( $query, 2 );
}

// return
if($go->good()) {
  $return = $qresult[ 'result' ];
  $return[ 'count' ] = $qresult[ 'count' ];
  if($arg_getempty == 1) {
    $return[ 'empty' ] = $emptyresult[ 'result' ];
    $return[ 'empty' ][ 'count' ] = $emptyresult[ 'count' ];
  }
}
?>
