<?php
//////////////////////////////////////
/* NAME: update_active
/* PARAMS: 
 - queryid
 - wordid
 - (result) : is the answer correct?
 - (answer) : users input
 - (nextid) : id of word that is already preloaded (don't load that)
 - (uncorr) : word has been skipped and will not be corrected
/* RETURN: 
 - word : next word as object ( question, answer )
 - mode : mode of query (0-4)
 - shuffle : # of word that was chosen by random (not id)
 - oldgroup : group in which the word was before (for correction)
/* DESCRIPTION: update answered word and return new one
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 04.04.2012
/* UPDATE: 20.04.2012 - Added this header, changed coding style
////////////////////////////////////*/

$go->necessary( 'queryid', 'wordid' );

if ( $go->good() ) {
  // get user & mode
  $checkuser = $go->query( 
   "SELECT userid,mode 
      FROM lk_activelist 
    WHERE userid = '" . $userid . "' 
          AND id = '" . $arg_queryid . "'"
  , 1 );

  if ( 0 == $checkuser[ 'count' ] ) {
    $go->error( 100 );
  }

  $registerid = $checkuser[ 'result' ][ 'registerid' ][ 0 ];
  $mode       = $checkuser[ 'result' ][ 'mode' ][ 0 ];
}

  // save / Speichere   
if ( $go->good() ) {  
  if ( 0 == $arg_uncorr ) { //only change if word was not skipped
    $qresult = $go->query( 
      "UPDATE lk_active 
         SET done = '1', 
             correct = '" . $arg_result . "', 
             answer = '" . $arg_answer . "' 
       WHERE id = '" . $arg_queryid . "'
             AND wordid = '" . $arg_wordid . "'"
    , 2 );

    if ( 0 == $qresult[ 'count' ] ) {
      $go->error( '103', 'Entry not created' );
    }
    
    if ( 2 == $mode OR 3 == $mode ) { // group will change
      $res = $go->query( 
        "SELECT `groupid` 
           FROM lk_words 
         WHERE id = '" . $arg_wordid . "' 
               AND userid = '" . $userid . "'"
      , 3 );

      $save_new_group = $res[ 'result' ][ 'groupid' ][ 0 ];
      if ( $arg_result ) {
        $nfach = '`groupid`+1';
      } else {
        $nfach = "1";
      } // choose how to change the group
      $qresult = $go->query( 
         "UPDATE lk_words 
            SET `groupid` = " . $nfach . " 
          WHERE `groupid` != 'af' 
                AND `groupid` != 'ar' 
                AND id = '" . $arg_wordid . "' 
                AND userid = '" . $userid . "'"
      , 3 );
    }
  }
}
if ( NULL != $arg_nextid ) { //want the next word
  if ( $go->good() ) {
    //load / Lade
    $count_wordid = $go->query( 
      "SELECT COUNT(*) 
         FROM lk_active 
       WHERE done = '0' 
             AND wordid<>'" . $arg_nextid . "' 
             AND id = '" . $arg_queryid . "'"
    , 4 );
    $countdone    = $count_wordid[ 'result' ][ 'COUNT(*)' ][ 0 ];
    $getshuffle   = rand( 0, $countdone - 1 );
    $get_wordid   = $go->query( 
      "SELECT wordid 
         FROM lk_active 
       WHERE done = '0' 
             AND wordid<>'" . $arg_nextid . "' 
             AND id = '" . $arg_queryid . "' 
       ORDER BY wordid 
       LIMIT " . $getshuffle . ",1"
    , 5 );
    $wordid = $get_wordid[ 'result' ][ 'wordid' ];
  }
  if ( $go->good() ) {
    if ( 4 == $mode ) { //verbquery
      $idstring = " AND (  ";
      plk_util_makeArray( $wordid );
      $count = count( $wordid );
      for ( $i = 0; $i < $count; $i++ ) {
        $idstring .= " t2.id = '" . $wordid[ $i ] . "' ";
        $idstring .= $i + 1 < $count ? " OR " : "";
      }
      $idstring .= "  )";
      if ( 0 < $count ) {
        $query = "SELECT t2.*, t1.wordfore, t3.name as formname, t4.name as personname 
                  FROM lk_words t1, lk_verbs t2, lk_forms t3, lk_persons t4 
                  WHERE t1.userid = '" . $userid . "' 
                        AND t1.id = t2.wordid 
                        AND t2.formid = t3.id 
                        AND t2.personid = t4.id " . $idstring;
        $get_verbinfo = $go->query( $query, 6 );
        $verb         = $get_verbinfo[ 'result' ];
        $word[ 'id' ] = $verb[ 'id' ];
        for ( $i = 0; $i < $get_verbinfo[ 'count' ]; $i++ ) {
          $word[ 'question' ][ $i ] = $verb[ 'personname' ][ $i ] . ' ' . $verb[ 'wordfore' ][ $i ] . ' ( ' . $verb[ 'formname' ][ $i ] . ' )';
        }
        $word[ 'answer' ] = $verb[ 'kword' ];
      }
    } else { //otherquery
      $word = plk_request( 'get_word', array(
                                   'tolim' => 1,
                                   'gettags' => 0,
                                   'wordid' => $wordid 
      ) );
      if ( 0 == $mode OR 2 == $mode ) {
        $word[ 'question' ] = $word[ 'wordfirst' ];
        $word[ 'answer' ]   = $word[ 'wordfore' ];
      } else {
        $word[ 'question' ] = $word[ 'wordfore' ];
        $word[ 'answer' ]   = $word[ 'wordfirst' ];
      }
    }
  }
  if ( $go->good() ) {
    $return = array(
      'word'     => $word,
      'mode'     => $mode,
      'shuffle'  => $getshuffle,
      'oldgroup' => $save_new_group 
    );
  }
}
?>
