<?php
//////////////////////////////////////
/* NAME: get_word
/* PARAMS: (all optional) arg_
- registerid
- /global/
- searchtext : searchstring, will only return matching words. if is array: [0] search only in wordfirst, [1] search only in wordfore, [2] search only in sentence
- timerange : array with two timestamps (in seconds). select word which were created between these dates. additional 3 value : ASC or DESC for sorting order.
- gettags : if 0 wont load tags (default : 1)
- getsave : if 1 will load save which word is in (default : 0)
- count : if 1 will return the number of words in location. can be "type" will count word of that type
- nolimit : if 1 fromlim and tolim are ignored, all is loaded
- fromlim : first value in MySQL LIMIT clause
- tolim : second value in MySQL LIMIT clause
- select : MySQL SELECT clause value
- orderby : MySQL ORDER BY clause value
- groupby : MySQL GROUP BY clause value
-- additional:
using groupid='*' returns same as groupid == NULL but also groupcount.
      registerid='*' returns results from all registers  
/* RETURN: 
- all columns from select clause
- count : number of words
- wordcount (if count) : number of words per group by
- wordid (if wordid) : array of the wordids of the selected words
- groupcount (if groupid) : number of groups in register
- tagslist : array of lists of tags for each word
- saveslist : array of lists of saves for each word
/* DESCRIPTION: load words from database
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2012
/* UPDATES: 19.04.2012 - Code Style
////////////////////////////////////*/

if ( $go->good() ) {
  //default values
  if ( NULL == $arg_fromlim ) {
    $arg_fromlim = 0;
  }
  if ( NULL == $arg_tolim ) {
    $arg_tolim = 20;
  }
  if ( NULL == $arg_select ) {
    $arg_select = 'tword.*';
  }
  if ( NULL == $arg_orderby ) {
    $arg_orderby = 'id';
  }
  if ( NULL === $arg_gettags ) {
    $arg_gettags = 1;
  }
  if ( 1 == $arg_count ) {
    $arg_select = " COUNT(tword.id) as wordcount "; //counting words
  } elseif ( NULL != $arg_count ) {
    $arg_select  = $arg_count . "id, COUNT(tword.id) as wordcount ";
    $arg_groupby = $arg_count . 'id';
    ${'arg_' . $arg_count . 'id'} = '*';
  }
  
  //"groupby" separation
  if ( NULL != $arg_groupby ) {
    if ( strstr( $arg_groupby, 'tag' ) ) {
      $arg_groupby = 'ttag.' . $arg_groupby;
    } elseif ( strstr( $arg_groupby, 'save' ) ) {
      $arg_groupby = 'tsave.' . $arg_groupby;
    } else {
      $arg_groupby = 'tword.' . $arg_groupby;
    }
  }
  
  //time format used for time range
  $timesyntax = "Y-m-d H:i:s";
  
  //Check if highest group - Prüfe ob letztes fach
  //if it is, words with higher groups also will load
  //usefull after removing groups with words inside
  if ( NULL != $arg_registerid && NULL != $arg_groupid ) {
    $highestgroup   = 0;
    $get_groupcount = $go->query(
      "SELECT groupcount 
         FROM lk_registers 
       WHERE id='" . $arg_registerid . "' 
             AND userid='" . $userid . "'"
    , 1 );
    $groupcount = $get_groupcount[ 'result' ][ 'groupcount' ][ 0 ];
    if ( $groupcount == $arg_groupid ) {
      $highestgroup = 1;
    }
  }
  
}
if ( $go->good() ) {
  //create query
  //select tables
  $query = "SELECT " . $arg_select;
  $query .= " FROM lk_words tword ";
  if ( NULL != $arg_tagid || 'tag' == $arg_count ) {
    $query .= ", lk_tags ttag ";
  }
  if ( NULL != $arg_saveid || 'save' == $arg_count ) {
    $query .= ", lk_save tsave ";
  }
  //WHERE
  //userid must match
  $query .= " WHERE tword.userid='" . $userid . "' ";
  
  //register
  if ( NULL != $arg_registerid && '*' != $arg_registerid && 'register' != $arg_count ) {
    $query .= "AND tword.registerid='" . $arg_registerid . "' ";
  }
  
  //tag
  if ( NULL != $arg_tagid ) {
    $query .= "AND tword.id=ttag.wordid ";
    if ( 'tag' != $arg_count ) {
      $query .= "AND ttag.tagid='" . $arg_tagid . "' ";
    }
  }
  //without tag
  elseif ( 'tag' == $arg_withoutid ) {
    $query .= " AND NOT EXISTS (SELECT * FROM lk_tags lkt WHERE tword.id=lkt.wordid) ";
  }
  
  //save
  if ( NULL != $arg_saveid ) {
    $query .= " AND tword.id=tsave.wordid ";
    if ( 'save' != $arg_count ) {
      $query .= " AND tsave.saveid='" . $arg_saveid . "' ";
    }
  }
  //without save
  elseif ( 'save' == $arg_withoutid ) {
    $query .= "AND NOT EXISTS (SELECT * FROM lk_save lks WHERE tword.id = lks.wordid) ";
  }
  
  //wordclass   
  if (  NULL != $arg_wordclassid && 'wordclass' != $arg_count ) {
    $query .= "AND tword.wordclassid='" . $arg_wordclassid . "' ";
  }
  
  //group
  if (  NULL != $arg_groupid && 'group' != $arg_count ) {
    $query .= "AND (tword.groupid='" . $arg_groupid . "' ";
    if ( $highestgroup == 1 ) {
      $query .= "OR ( tword.groupid>'" . $arg_groupid . "' 
                      AND tword.groupid!='af' 
                      AND tword.groupid!='ar' )";
    }
    $query .= ')';
  }
  
  //word(s)
  if ( !is_array( $arg_wordid ) && NULL != $arg_wordid ) { //20120408 - fix: allow comma separation
    $arg_wordid = plk_util_commaArray( $arg_wordid );
  }
  if ( is_array( $arg_wordid ) && 0 < count( $arg_wordid ) ) {
    $query .= "AND (";
    $len = count( $arg_wordid );
    for ( $i = 0; $i < $len; $i++ ) {
      $inquery[] = " tword.id='" . $arg_wordid[ $i ] . "' ";
    }
    $query .= implode( ' OR ', $inquery ) . ')';
  }
  
  
  //time range
  if ( NULL != $arg_timerange ) {
    $query .= "AND tword.time_created>'" . date( $timesyntax, $arg_timerange[ 0 ] ) . "' 
               AND tword.time_created<='" . date( $timesyntax, $arg_timerange[ 1 ] ) . "'";
  }
  
  //Search query
  if ( NULL != $arg_searchtext) {
    $query .= "AND ( ";
    if ( !is_array( $arg_searchtext ) ) {
      $arg_searchtext = array(
         $arg_searchtext 
      );
      $noarray    = 1;
    }
    $k = 0;
    foreach ( $arg_searchtext as $tsearch ) {
      if ( substr( $tsearch, 0, 6 ) == 'like: ' and 6 < strlen( $tsearch ) ) {
        $tsearch       = substr( $tsearch, 6 );
        $searchtextexp = str_split( $tsearch );
        $len           = count( $searchtextexp );
        for ( $i = 0; $i < $len; $i++ ) {
          $searchtextexp[ $i ] = plk_util_regExpEncode( $searchtextexp[ $i ] );
        }
        $tsearch        = implode( '.?', $searchtextexp );
        $sarray[ $k ][] = '.*' . $tsearch . '.*';
      } else {
        $tsearch        = plk_util_regExpEncode( $tsearch );
        $sarray[ $k ][] = '^' . $tsearch . '$';
        $sarray[ $k ][] = ', ?' . $tsearch . '$';
        $sarray[ $k ][] = '^' . $tsearch . ' ?,';
      }
      $k++;
    }
    if ( 1 == $noarray ) {
      $sarray[ 1 ] = $sarray[ 0 ];
      $sarray[ 2 ] = $sarray[ 0 ];
    }
    $where = array(
      'tword.wordfirst',
      'tword.wordfore',
      'tword.sentence' 
    );
    $len   = count( $sarray );
    for ( $i = 0; $i < $len; $i++ ) {
      $len2 = count( $sarray[ $i ] );
      for ( $j = 0; $j < $len2; $j++ ) {
        $query .= " " . $where[ $i ] . "  RLIKE '" . $sarray[ $i ][ $j ] . "' ";
        if ( $i + 1 < $len or $j + 1 < $len2 ) {
          $query .= " OR ";
        }
      }
    }
    $query .= " ) ";
  }
  
  //group by
  if ( NULL != $arg_groupby ) {
    $query .= " GROUP BY " . $arg_groupby . " ";
  }
  
  //orderby
  if ( $arg_timerange[ 2 ] ) {
    $query .= " ORDER BY tword.time_created " . $arg_timerange[ 2 ] . " ";
  } else {
    $query .= " ORDER BY tword." . $arg_orderby . " ";
  }
  
  //limit
  if ( !$arg_nolimit ) {
    $query .= "LIMIT " . $arg_fromlim . "," . $arg_tolim . " ";
  }
  
  //execute
  $get_words = $go->query( $query, 2 );
  
  //fix for group
  // Count words from higher groups to highest existing group
  if ( '*' == $arg_groupid ) {
    $r =& $get_words[ 'result' ];
    $countplus = 0;
    $index     = false;
    for ( $i = 0; $i < $get_words[ 'count' ]; ++$i ) {
      if ( is_numeric( $r[ 'groupid' ][ $i ] ) && $groupcount < $r[ 'groupid' ][ $i ] ) { //check if there is higher group
        $countplus += $r[ 'wordcount' ][ $i ];
        $r[ 'wordcount' ][ $i ] = 0;
      } elseif ( $groupcount == $r[ 'groupid' ][ $i ] ) {
        $index = $i;
      } //find index of highest group
    }
    if ( false !== $index ) {
      $r[ 'wordcount' ][ $index ] += $countplus; //append count to highest group
    } else { //create new entry (if there is no entry for highest group yet)
      $r[ 'wordcount' ][] = $countplus;
      $r[ 'groupid' ][]         = $groupcount;
    }
  }
}

// RETURN
if ( $go->good() ) {
  $return            = $get_words[ 'result' ];
  $return[ 'count' ] = $get_words[ 'count' ];
  if ( NULL != $arg_wordid ) {
    $return[ 'wordid' ] = $arg_wordid;
  }
  if ( NULL != $arg_groupid ) {
    $return[ 'groupcount' ] = $groupcount;
  }
  if ( count( $get_words[ 'result' ][ 'id' ] ) > 0 ) {
    foreach ( $get_words[ 'result' ][ 'id' ] as $key => $wid ) {
      if ( 1 == $arg_gettags ) {
        $return[ 'tagslist' ][ $key ] = plk_request( 'get_tag', array(
          'registerid' => $return[ 'registerid' ][ $key ],
          'wordid' => $wid 
        ) );
      }
      if ( 1 == $arg_getsave ) {
        $return[ 'savelist' ][ $key ] = plk_request( 'get_save', array(
          'registerid' => $return[ 'registerid' ][ $key ],
          'wordid' => $wid 
        ) );
      }
    }
  }

/*
  if ( NULL != $arg_count ) {
    $return[ 'wordcount' ] = $return[ 'COUNT(tword.id)' ];
    unset( $return[ 'COUNT(tword.id)' ] );
  }
*/
//  $return[ 'debug' ] = $query;
}
?>
