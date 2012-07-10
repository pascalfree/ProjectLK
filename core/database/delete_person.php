<?php
//////////////////////////////////////
/* NAME: delete_person
/* PARAMS: arg_
- personid
/* RETURN: 
- count : array
    - verb : number of deleted verbs
    - person : number of deleted persons
/* DESCRIPTION: deletes person an corresponding verbs from verbtable
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 20.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'personid' );

if ( $go->good() ) {
  //delete verbs
  $delete_verb = $go->query( 
    "DELETE lk_verbs 
       FROM lk_persons, lk_verbs 
     WHERE lk_persons.id='" . $arg_personid . "' 
           AND lk_persons.userid='" . $userid . "' 
           AND lk_verbs.personid=lk_persons.id "
  , 1 );
  
  //delete person
  $delete_person = $go->query( 
    "DELETE 
       FROM lk_persons 
     WHERE id='" . $arg_personid . "' 
           AND userid='" . $userid . "' "
  , 2 );
}

if ( $go->good() ) {
  $return = array(
     'count' => array( 
        'verb' => $delete_verb[ 'count' ],
        'person' => $delete_person[ 'count' ]
      ) 
  );
}
?>
