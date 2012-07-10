<?php
//////////////////////////////////////
/* NAME: change_password
/* PARAMS: newpassword, oldpassword
/* RETURN: 
- success : 1 if password was changed
/* DESCRIPTION: changes user password
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2011
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'newpassword', 'oldpassword' );

if ( $userid == 0 ) { //guest can't change password
  $go->error( 100 );
}

if ( $go->good() ) {
  $query   = "UPDATE lk_user SET passw='" . md5( $arg_newpassword ) . "' WHERE id='" . $userid . "' AND passw='" . md5( $arg_oldpassword ) . "'";
  $qresult = $go->query( $query, 1 );
}

if ( $go->good() ) {
  $return[ 'success' ] = $qresult[ 'count' ];
}
?>
