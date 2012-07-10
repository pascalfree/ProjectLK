<?php
//////////////////////////////////////
/* NAME: change_options
/* PARAMS: arg_
- newemail
- newlang
- newgui
- newhints
/* RETURN: -
/* DESCRIPTION: change settings of user
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'newemail', 'newlang', 'newgui', 'newhints' );

if( $userid == 0 ) { $go->error( 100 ); } //guest can't change settings

if( $go->good() ) {
  $query   = "UPDATE lk_user SET email='" . $arg_newemail . "', language='" . $arg_newlang . "', theme='" . $arg_newtheme . "', gui='" . $arg_newgui . "', hints='" . $arg_newhints . "' WHERE id='" . $userid . "'"; 
  $qresult = $go->query( $query, 1 );
}
?>
