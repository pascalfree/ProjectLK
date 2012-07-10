<?php
//////////////////////////////////////
/* NAME: create_word
/* PARAMS: arg_
- registerid
- newwordfirst
- newwordfore
- newgroup
- (newtags) : comma separated tags
- (newsentence)
- (newwordclass)
/* RETURN: 
- wordfirst
- wordfore
- sentence
- group
- register
- wordclass
- taglist : array of tags (see add_tag.php)
- id
- similarid : id of word, that is similar to new word
- count : number of added wors
- similar : 1 if similar word was found
/* DESCRIPTION: create a new word
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 21.04.2012
/* UPDATE: 21.04.2012 - Added this header, changed coding style
////////////////////////////////////*/

$go->necessary( 'newwordfirst', 'newwordfore', 'newgroup', 'registerid' );

// only add words to af or group 1
if( $go->good() ) {
  if( !(1 == $arg_newgroup || 'af' === $arg_newgroup) ) {
    $arg_newgroup = 'af';
  }
}

if( $go->good() ) {
  //forbidden characters
  $todecode = array('newwordfirst', 'newwordfore', 'newsentence', 'newtags');
  foreach($todecode as $val) { 
    remove_forbidden( $$val );
    //$$val = str_replace($forbidden, '', $$val);
  }

  //similar words
  $similar = 0;
  $swordid = request('get_word', array(
      'registerid' => $arg_registerid, 
      'select' => 'tword.id',
      'searchtext' => array($arg_newwordfirst, $arg_newwordfore),
      'nolimit' => 1, 
      'gettags' => 0 //fix: don't get tags
  ) );
  //if($swordid['count']>0) { 
  $similar = ($swordid['count'] > 0); 
  $similarid = $swordid['id'];
  //}
}

if( 0 == $similar || 1 == $force ) {

  // add word
  if($go->good()) {
    if($time_created != NULL) { 
      $add = ', time_created'; 
      $addtime = ", '".$time_created."'"; 
    } else {
      $add = ''; 
      $addtime = '';
    }
    $create_word = $go->query(
      "INSERT
        INTO lk_words ( userid, 
                        registerid, 
                        wordfirst, 
                        wordfore, 
                        `groupid`, 
                        sentence, 
                        wordclassid
                        ".$add.") 
        VALUES ('" . $userid . "', 
                '" . $arg_registerid . "', 
                '" . $arg_newwordfirst . "', 
                '" . $arg_newwordfore . "', 
                '" . $arg_newgroup . "', 
                '" . $arg_newsentence . "', 
                '" . $arg_newwordclass . "'
                " . $addtime . ")"
    , 1 );
    $wordid = $create_word['id'];
  }

  // add tags
  if($go->good()) {
    $add_tag = request('add_tag', array(
      'registerid' => $arg_registerid,
      'newtag' => $arg_newtags,
      'wordid' => $wordid,
    ) );
  }
}

// return
if($go->good()) {  
  $return = array('wordfirst' => $arg_newwordfirst,
                  'wordfore'  => $arg_newwordfore,
                  'sentence'  => $arg_newsentence,
                  'group'     => $arg_newgroup,
                  'register'  => $arg_registerid,
                  'wordclass' => $arg_newwordclass,
                  'taglist'   => $add_tag,
                  'id'        => $wordid,
                  'similarid' => $similarid,
                  'count'     => $create_word['count'],
                  'similar'   => $similar);
}
?>
