<?php	
  $go -> necessary('userid');

  $success = 0;
  if( file_exists( 'upload/'.$userid.'import.xml' ) ) {
    $success = unlink('upload/'.$userid.'import.xml');
  }
  $return = array('succcess' => $success);
?>
