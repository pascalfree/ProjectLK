<?php
//////////////////////////////////////
/* NAME: edit_word
/* PARAMS: 
- wordid
- (newwordfirst)
- (newwordfore)
- (newgroup) : group id (af,1)
- (newsentence)
- (newwordclass) : numeric wordclassid
/* RETURN: 
- count : number of words edited 
/* DESCRIPTION: edit words
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 20.04.2012
/* UPDATES: 20.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'wordid' );

$edits = NULL;

if ( NULL != $arg_newwordfirst && '' != $arg_newwordfirst ) {
  $edits[] = " wordfirst='" . $arg_newwordfirst . "'";
}
if ( NULL != $arg_newwordfore && '' != $arg_newwordfore ) {
  $edits[] = " wordfore='" . $arg_newwordfore . "'";
}
//fixed: 'af'==0 would be true!
if ( "1" == $arg_newgroup || 'af' === $arg_newgroup ) { //only these are allowed
  $edits[] = " `groupid`='" . $arg_newgroup . "'"; //fix: ` needed
}
if ( NULL !== $arg_newsentence ) {
  $edits[] = " sentence='" . $arg_newsentence . "'";
}
if ( NULL != $arg_newwordclass && '' != $arg_newwordclass ) {
  $edits[] = " wordclassid='" . $arg_newwordclass . "'";
}
//20.04.2012 fix : may cause problems with verbtables:
//if($arg_newregister!=NULL && $arg_newregister!='') { $edits[]=" registerid='".$arg_newregister."'"; }

if ( NULL == $edits ) {
  $go->error( 103 ); //nothing changed
} else {
  $edit_word = $go->query( 
    "UPDATE lk_words 
       SET " . implode( ',', $edits ) . "
     WHERE id='" . $arg_wordid . "' 
           AND userid='" . $userid . "' "
  , 1 );
}

//return
if ( $go->good() ) {
  $return = array(
     'count' => $edit_word[ 'count' ] 
  );
}
?>
