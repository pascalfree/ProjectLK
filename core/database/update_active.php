<?php
//////////////////////////////////////
/* NAME: update_active.php
/* PARAMS: 
 - queryid
 - wordid
 - result : is the answer correct?
 - answer : users input
 - nextid : id of word that is already preloaded (don't load that)
 - uncorr : word has been skipped and will not be corrected
/* RETURN: 
 - word : next word
 - mode : mode of query (0-4)
 - shuffle : # of word that was chosen by random (not id)
 - oldgroup : group in which the word was before (for correction)
/* DESCRIPTION: 
/* VERSION: 04.04.2012
/* UPDATE: 04.04.2012 - Added this header, changed coding style
////////////////////////////////////*/
  $go -> necessary( 'queryid','wordid' );
  if( $go -> good() ) {
    // get user & mode
    $query = "SELECT userid,mode FROM lk_activelist WHERE userid = '".$userid."' AND id = '".$queryid."'";
    $checkuser = $go -> query( $query,1 );
    if( $checkuser['count'] == 0 ) { $go -> error( 100 ); }
    $registerid = $checkuser['result']['registerid'][0];
    $mode = $checkuser['result']['mode'][0];
  }
  if( $go -> good() ) {
    // save / Speichere     
    if( $uncorr == 0 ) { //only change if word was not skipped
      $query = "UPDATE lk_active SET done = '1', correct = '".$result."', answer = '".$answer."' WHERE id = '".$queryid."' AND wordid = '".$wordid."'"; 
      $qresult = $go -> query( $query,2 );
      if( $qresult['count'] == 0 ) { $go -> error( '103','Entry not created' ); }

      if( $mode == 2 OR $mode == 3 ) { // group will change
        $query = "SELECT `groupid` FROM lk_words WHERE id = '".$wordid."' AND userid = '".$userid."'"; 
        $res = $go -> query( $query,3 );
        $save_new_group = $res['result']['group'][0];
        if( $result ) { $nfach = '`groupid`+1'; } else { $nfach = "1"; } // choose how to change the group
        $query = "UPDATE lk_words SET `groupid` = ".$nfach." WHERE `groupid` != 'af' AND `groupid` != 'ar' AND id = '".$wordid."' AND userid = '".$userid."'"; 
        $qresult = $go -> query( $query,3 );
      }
    }
  }
  if( $nextid != NULL ) { //want the next word
    if( $go -> good() ) {
      //load / Lade
      $queryc = "SELECT COUNT(*) FROM lk_active WHERE done = '0' AND wordid<>'".$nextid."' AND id = '".$queryid."'"; 
      $count_wordid = $go -> query( $queryc,4 );
      $countdone = $count_wordid['result']['COUNT(*)'][0];
      $getshuffle = rand( 0,$countdone-1 );
      $query1 = "SELECT wordid FROM lk_active WHERE done = '0' AND wordid<>'".$nextid."' AND id = '".$queryid."' ORDER BY wordid LIMIT ".$getshuffle.",1"; 
      $get_wordid = $go -> query( $query1,5 );
      unset( $wordid );
      $wordid = $get_wordid['result']['wordid'];
    }
    if( $go -> good() ) {
      if( $mode == 4 ) { //verbquery
        $idstring = " AND (  ";
        if( !is_array( $wordid ) ) { $wordid = array( $wordid ); } 
        $count = count( $wordid );
        for( $i = 0;$i<$count;$i++ ) { 
          $idstring .= " t2.id = '".$wordid[$i]."' "; 
          $idstring .= $i+1<$count? " OR " : "";
        }
        $idstring .= "  )";
        if( $count>0 ) {
          $query = "SELECT t2.*, t1.wordfore, t3.name as formname, t4.name as personname 
                  FROM lk_words t1, lk_verbs t2, lk_forms t3, lk_persons t4 
                  WHERE t1.userid = '".$userid."' AND t1.id = t2.wordid 
                  AND t2.formid = t3.id AND t2.personid = t4.id ".$idstring;
          $get_verbinfo = $go -> query( $query,6 );
          $verb = $get_verbinfo['result'];
          $word['id'] = $verb['id'];
          for( $i = 0;$i<$get_verbinfo['count'];$i++ ) {
            $word['question'][$i] = $verb['personname'][$i].' '.$verb['wordfore'][$i].' ( '.$verb['formname'][$i].' )';
          }
          $word['answer'] = $verb['kword'];
        }
      } else { //otherquery
        $word = request( 'get_word',array( 'tolim' => 1,'gettags' => 0,'wordid' => $wordid ) ); 
        if( $mode == 0 OR $mode == 2 ) {
          $word['question'] =  $word['wordfirst'];
          $word['answer'] = $word['wordfore'];
        } else {
          $word['question'] = $word['wordfore'];
          $word['answer'] = $word['wordfirst'];
        }
      }
    }
    if( $go -> good() ) {
      $return = array(  'word' => $word,
                        'mode' => $mode,
                        'shuffle' => $getshuffle,
                        'oldgroup' => $save_new_group );
    }
  }
?>
