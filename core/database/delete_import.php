<?php	
//////////////////////////////////////
/* NAME: delete_import
/* PARAMS: none
/* RETURN: 
- success: 1 if successful
/* DESCRIPTION: deletes the uploaded and cached importfile of a user
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2012
////////////////////////////////////*/

  $success = 0;
  if( file_exists( 'upload/' . $userid . 'import.xml' ) ) {
    $success = unlink( 'upload/' . $userid . 'import.xml' );
  }
  $return = array('succcess' => $success);
?>
