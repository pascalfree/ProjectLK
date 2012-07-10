<?php
//////////////////////////////////////
/* NAME: delete_form
/* PARAMS: arg_
- formid
/* RETURN: 
- count : array
    - verb : number of deleted verbs
    - form : number of deleted forms
/* DESCRIPTION: delete forms in verbtable
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 22.04.2012
/* UPDATES: 22.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'formid' );

if ( $go->good() ) {
  // delete verbs
  $delete_verb = $go->query( 
    "DELETE lk_verbs 
       FROM lk_forms, lk_verbs 
     WHERE lk_forms.id = '" . $arg_formid . "' 
           AND lk_forms.userid = '" . $userid . "' 
           AND lk_verbs.formid = lk_forms.id "
  , 1 );
}

if ( $go->good() ) {
  // delete forms
  $delete_form = $go->query( 
    "DELETE 
       FROM lk_forms 
     WHERE id = '" . $arg_formid . "' 
           AND userid = '" . $userid . "' "
  , 2 );
}

// return
if ( $go->good() ) {
  $return = array(
    'count' => array(
      'verb' => $delete_verb[ 'count' ],
      'form' => $delete_form[ 'count' ]
    )
  );
}
?>
