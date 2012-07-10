<?php
//////////////////////////////////////
/* NAME: delete_save
/* PARAMS: arg_
- saveid
/* RETURN: 
- count : number of deleted saves
/* DESCRIPTION: deletes a storage
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 21.04.2012
/* UPDATE: 21.04.2012 - Added this header, changed coding style
////////////////////////////////////*/

$go->necessary('saveid');

if ($go->good()) {
  $delete_save = $go->query(
   "DELETE t1,t2 
      FROM lk_savelist t1, lk_save t2 
    WHERE t1.id='" . $arg_saveid . "' 
          AND t1.userid='" . $userid . "' 
          AND t2.saveid=t1.id "
  , 1);
}

if ($go->good()) {
  $return = array('count' => $delete_save['count']);
}
?>
