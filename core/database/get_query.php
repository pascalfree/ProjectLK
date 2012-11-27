<?php
//////////////////////////////////////
/* NAME: get_query
/* PARAMS: 
 - queryid
/* RETURN: 
 - registerid
 - total : total number of words in query
 - done : number of done words in query
 - correct : number of yet correctly answered words in query
 - word['question'] : one, yet not answered word of the query
 - word['answer'] : The answer to this one word
 - mode : mode of the query
 - wrong['id'/'answer'] : id and given answer to a wrong answered question
/* DESCRIPTION: loads all information, needed to initialize a new or running query
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 04.04.2012
/* UPDATE: 04.04.2012 - Fix: First two words weren't shuffled, changed code style
////////////////////////////////////*/

  $go->necessary( 'queryid' );

  //User & mode
  if( $go->good() ) {
    $query = "SELECT userid,registerid,mode FROM lk_activelist 
              WHERE userid = '".$userid."' AND id = '".$arg_queryid."'";
    $checkuser = $go->query( $query,1 );
    if( $checkuser['count'] == 0 ) { 
      $go->error( 100 ); 
    }
    $registerid = $checkuser['result']['registerid'][0];
    $mode = $checkuser['result']['mode'][0];
  }
  if( $go->good() ) {
    //Delete lost ids / Lösche verschollene IDs
    $query = "DELETE FROM lk_active 
              WHERE ( 
                NOT EXISTS ( SELECT * FROM lk_words 
                            WHERE lk_active.wordid = lk_words.id 
                            AND lk_words.userid = '".$userid."' ) 
                AND NOT EXISTS ( SELECT * FROM lk_verbs, lk_words 
                                WHERE lk_active.wordid = lk_verbs.id 
                                AND lk_words.userid = '".$userid."'
                                AND lk_verbs.wordid = lk_words.id ) 
             ) AND lk_active.id = '".$arg_queryid."'"; 
    $go->query( $query,2 );
  }

  //Load / Lade  
  if( $go->good() ) {   
    //Total 
    $total = $go->query( "SELECT COUNT(*) FROM lk_active WHERE id = '".$arg_queryid."'",3 );
    $total = $total['result']['COUNT(*)'][0];
  }

  if( $go->good() ) {
    //Done 
    $done = $go->query( "SELECT COUNT(*) FROM lk_active WHERE done = '1' AND id = '".$arg_queryid."'",4 ); 
    $done = $done['result']['COUNT(*)'][0];
  }

  if( $go->good() ) {
    //Correct 
    $correct = $go->query( "SELECT COUNT(*) FROM lk_active WHERE correct = '1' AND id = '".$arg_queryid."'",5 ); 
    $correct = $correct['result']['COUNT(*)'][0];
  }

  if( $go->good() ) {
    //Wordids
    if( $arg_wordid == NULL ) {
      $getshuffle = rand( 0,$total-$done-2 );
      $arg_wordid = $go->query( 
        "SELECT wordid 
           FROM lk_active
         WHERE done = '0'
               AND id = '".$arg_queryid."'
         ORDER BY wordid
         LIMIT ".$getshuffle.",2",6 ); 
      $arg_wordid = $arg_wordid['result']['wordid'];
    }
  }

  if( $go->good() ) {
    //Wrong Answers
    $wrongword = $go->query(
      "SELECT wordid, answer
         FROM lk_active 
       WHERE done = '1'
             AND correct = '0'
             AND id = '".$arg_queryid."'
       ORDER BY wordid"
    , 7 );
    if( $wrongword['count'] > 0 ) {
      $wrongid = $wrongword['result']['wordid'];
      foreach( $wrongid as $key => $tid ) {
        $wronganswer[$tid] = $wrongword['result']['answer'][$key];
      }
    }
    $wrong = array( 'id' => $wrongid, 'answer' => $wronganswer );
  }

  if( $go->good() && !empty($arg_wordid) ) { //20120421 - fix : not empty wordid
    //Load word
    if( 4 == $mode ) { //Verbquery
      //querystring
      $idstring = " AND (  "; 
          
      plk_util_makeArray( $arg_wordid );

      $count = count( $arg_wordid );
      for( $i = 0; $i < $count; $i++ ) { 
        $idstring .= " t2.id = '".$arg_wordid[$i]."' "; 
        $idstring .= $i + 1 < $count ? " OR " : "";
      }
      $idstring .= "  )";
      if( $count > 0 ) {
        $verbinfo = $go->query( 
          "SELECT t2.*, t1.wordfore, t3.name as formname, t4.name as personname 
             FROM lk_words t1, lk_verbs t2, lk_forms t3, lk_persons t4 
           WHERE t1.userid = '".$userid."' 
                 AND t1.id = t2.wordid 
                 AND t2.formid = t3.id 
                 AND t2.personid = t4.id ".$idstring
        ,8 );
        $verb = $verbinfo['result'];
        $word['id'] = $verb['id'];
        $word['question'][0] = NULL;
        for( $i = 0; $i < $verbinfo['count']; $i++ ) {
          $word['question'][$i] = $verb['personname'][$i].' '.$verb['wordfore'][$i].' ( '.$verb['formname'][$i].' )';
        }
        $word['answer'] = $verb['kword'];
      }
    } else {  //other query
      $count = count( $arg_wordid );
      $tolim = 2;
      if( $count < $tolim ) $tolim = $count;
      if( $count > 0 && $mode <= 3 ) {
        $getword = plk_request( 'get_word',array( 'wordid' => $arg_wordid,'tolim' => $tolim,'gettags' => 0 ) );
        $word['id'] = $getword['id'];
        if( $mode == 0 OR $mode == 2 ) {
          $word['question'] =  $getword['wordfirst'];
          $word['answer'] = $getword['wordfore'];
        } else {
          $word['question'] = $getword['wordfore'];
          $word['answer'] = $getword['wordfirst'];
        }
      }
    }
  }

  if( $go->good() ) {
    $return = array(  'registerid' => $registerid,
                      'total' => $total,
                      'done'  => $done,
                      'correct' => $correct,
                      'word' => $word,
                      'mode' => $mode,
                      'wrong' => $wrong );
  }
?>
