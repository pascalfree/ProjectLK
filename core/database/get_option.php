<?php
//////////////////////////////////////
/* NAME: get_option
/* PARAMS: none
/* RETURN: 
- ...
/* DESCRIPTION: loads information of current user
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 21.04.2011
/* UPDATES: 21.04.2012 - Code Style
////////////////////////////////////*/
  $get_option = $go->query(
    "SELECT * 
       FROM lk_user 
     WHERE id='" . $userid . "' "
  , 1 );
  $return = plk_util_flat( $get_option['result'] );
?>
