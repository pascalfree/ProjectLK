<?php 
//////////////////////////////////////
/* NAME: get_tag
/* PARAMS: arg_
- registerid
- OR tagid : select single tag
- (count) : if 1 will count number of words for each tag
- (wordid) : (array or integer) select only tags of these words
- (limit) : number of tags to load
- (/global/) : only select tags of this location
- (searchtext) : search arrays
- (select) : MySQL select clause value
- (orderby) : MySQL orderby clause value
- (groupby) : MySQL groupby clause value
/* RETURN: 
- output : return value of the function or printed text of the function, if return value is empty.
/* DESCRIPTION: calls a php function in a file inside the GUI folder
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2012
////////////////////////////////////*/

// need registerid or a specific id of the tag
$go->necessary( array(
  'registerid',
  'tagid' 
) );

// execution
if ( $go->good() ) {
  if ( NULL == $arg_select ) {
    $arg_select = 't1.*'; //default: select all
  }
  if ( 1 == $arg_count ) {
    $arg_select .= ', COUNT(*)';
  }
  //querystring
  $query = "SELECT DISTINCT " . $arg_select . " FROM lk_taglist t1"; //from lk_taglist t1 !
  if ( NULL != $arg_wordid OR NULL != $arg_groupid OR 1 == $arg_count ) {
    $query .= ", lk_tags t2";
  } //from lk_tags    t2
  if ( $arg_groupid != NULL ) {
    $query .= ", lk_words t3";
  } //from lk_words   t3
  $query .= " WHERE t1.userid='" . $userid . "' "; //right USERID !!
  if ( NULL != $arg_registerid && '*' != $arg_registerid ) {
    $query .= "AND t1.registerid='" . $arg_registerid . "' ";
  } //with registerid
  if ( NULL != $arg_wordid ) { //with one of these wordids
    $queryplus = array();
    if ( !is_array( $arg_wordid ) ) {
      $arg_wordids[ 0 ] = $arg_wordid;
    } else {
      $arg_wordids = $arg_wordid;
    }
    foreach ( $arg_wordids as $wid ) {
      $queryplus[] = " (t2.wordid='" . $wid . "') ";
    }
    $query .= "AND ( " . implode( ' OR ', $queryplus ) . " ) ";
  }
  if ( NULL != $arg_wordid OR 1 == $arg_count ) {
    $query .= "AND t2.tagid=t1.id ";
  }
  //Fix: Added "AND t1.id=t2.tagid"
  if ( NULL != $arg_groupid ) {
    $query .= " AND t3.groupid='" . $arg_groupid . "' AND t3.id=t2.wordid AND t1.id=t2.tagid ";
  } //with groupid
  if ( NULL != $arg_searchtext ) {
    $query .= " AND UCASE(t1.name) RLIKE UCASE('" . $arg_searchtext . ".*') ";
  } //search
  if ( NULL != $arg_tagid ) {
    $query .= " AND t1.id='" . $arg_tagid . "' ";
  } //with tagid
  if ( NULL != $arg_limit ) {
    $query .= " LIMIT 0," . ( $arg_limit + 1 ) . " ";
  } //limit
  if ( NULL != $arg_groupby ) {
    $query .= " GROUP BY `" . $arg_groupby . "`";
  } //groupby
  elseif ( 1 == $arg_count ) {
    $query .= " GROUP BY t1.id";
  } //default: groupby tagid
  if ( $arg_orderby != NULL ) { //20120419 - fix : $arg_orderby
    $query .= " ORDER BY `" . $arg_orderby . "` ";
  } else {
    $query .= " ORDER BY t1.name "; //default: order by tagname
  }
  //execute
  $get_tag = $go->query( $query, 1 );
}

// return
if ( $go->good() ) {
  // check if there are more (unloaded) tags
  if ( NULL != $arg_limit && $get_tag[ 'count' ] > $arg_limit ) {
    $get_tag[ 'count' ] == $arg_limit;
    $more = 1;
  } else {
    $more = 0;
  }

  // build return object
  $return = $get_tag[ 'result' ];
  if ( 1 == $arg_count ) {
    $return[ 'tagcount' ] = $return[ 'COUNT(*)' ]; //a better name
    unset( $return[ 'COUNT(*)' ] );
  }
  $return[ 'more' ]  = $more;
  $return[ 'count' ] = $get_tag[ 'count' ];
}
?>
