<?php
//////////////////////////////////////
/* NAME: delete_register
/* PARAMS: arg_
- registerid
/* RETURN: 
- count : (array) number of deleted elements
/* DESCRIPTION: deletes entire register
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 20.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'registerid' );

if ( $go->good() ) {
  //save
  $delete_save = $go->query( 
    "DELETE t1, t2 
       FROM lk_savelist t1, lk_save t2 
     WHERE t1.registerid='" . $arg_registerid . "' 
           AND t1.userid='" . $userid . "' 
           AND t2.saveid=t1.id "
  , 1 );
}
if ( $go->good() ) {
  //tags
  $delete_tag = $go->query( 
    "DELETE t3, t4 
       FROM lk_taglist t3, lk_tags t4 
     WHERE t3.registerid='" . $arg_registerid . "' 
           AND t3.userid='" . $userid . "' 
           AND t4.tagid=t3.id "
  , 2 );
}
if ( $go->good() ) {
  //verbs
  $delete_verbs         = $go->query( 
    "DELETE lk_verbs 
       FROM lk_verbs, lk_words 
     WHERE lk_words.id=lk_verbs.wordid 
           AND lk_words.registerid='" . $arg_registerid . "' 
           AND lk_words.userid='" . $userid . "' "
  , 3 );
}
if ( $go->good() ) {
  //words
  $delete_words = $go->query( 
    "DELETE 
       FROM lk_words 
     WHERE registerid='" . $arg_registerid . "' 
           AND userid='" . $userid . "' "  
  , 4 );
}
if ( $go->good() ) {
  //forms
  $delete_forms = $go->query( 
    "DELETE 
       FROM lk_forms 
     WHERE registerid='" . $arg_registerid . "' 
           AND userid='" . $userid . "' "
  , 5 );
}
if ( $go->good() ) {
  //persons
  $delete_persons = $go->query( 
    "DELETE 
       FROM lk_persons 
     WHERE registerid='" . $arg_registerid . "' 
           AND userid='" . $userid . "' "
  , 6 );
}
if ( $go->good() ) {
  //register
  $delete_reg = $go->query( 
    "DELETE 
       FROM lk_registers 
     WHERE id='" . $arg_registerid . "' 
           AND userid='" . $userid . "' "
  , 7 );
}

// return
if ( $go->good() ) {
  $return = array(
    'count' => array(
      'register' => $delete_reg[ 'count' ],
      'save'     => $delete_save[ 'count' ],
      'tag'      => $delete_tag[ 'count' ],
      'verb'     => $delete_verbs[ 'count' ],
      'word'     => $delete_words[ 'count' ],
      'form'     => $delete_forms[ 'count' ],
      'person'   => $delete_persons[ 'count' ],
    )
  );
}
?>
