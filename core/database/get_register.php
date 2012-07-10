<?php
//////////////////////////////////////
/* NAME: get_verblist
/* PARAMS: (all optional) arg_
- registerid
- registername : regular expression to match a register name
- searchtext : searchstring to match registername
- limit
- gettime : if 1 loads timestamp
- count : if 1 counts words in register
- select : MySQL SELECT clause value
/* RETURN: 
- count : number of registers found
- id : array with id of registers
- name : array with names of registers
/* DESCRIPTION: loads 
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES:
- 22.04.2012 : Changed coding style
- 22.04.2012 : added this header
- 22.04.2012 : optimized counting
////////////////////////////////////*/

//select
$query = "SELECT tr.id, tr.name";
if ( $arg_gettime == 1 ) {
  $query .= ", tr.time_created ";
} //time
if ( $arg_count == 1 ) {
  $query .= ", COUNT(tw.id) as registercount "; //optimize: use 'as ..'
} //count
$query .= " FROM lk_registers tr ";
if ( $arg_count == 1 ) {
  $query .= " LEFT JOIN lk_words tw ON tr.id=tw.registerid ";
} //count

$query .= "WHERE tr.userid='" . $userid . "' "; //USERID

if ( $arg_registerid != NULL && $arg_registerid !== '*' ) { //20120422 - fix: !=(=) NULL
  $query .= " AND tr.id='" . $arg_registerid . "' ";
}

if ( $arg_registername != NULL ) {
  $query .= " AND tr.name='" . regexpencode( $arg_registername ) . "' ";
}
if ( $arg_searchtext != NULL ) {
  $query .= " AND UCASE(tr.name) RLIKE UCASE('" . regexpencode( $arg_searchtext ) . ".*') ";
}
if ( $arg_count == 1 ) { //count
  $query .= " GROUP BY tr.id ";
}
$query .= " ORDER BY tr.time_created";

//execute
$get_reg = $go->query( $query, 1 );

// return
if( $go->good() ) {
  $return            = $get_reg[ 'result' ];
  $return[ 'count' ] = $get_reg[ 'count' ];
}
?>
