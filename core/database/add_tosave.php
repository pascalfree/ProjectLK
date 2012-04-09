<?php  
//////////////////////////////////////
/* NAME: add_tosave
/* PARAMS: 
 - newsaveid : id of save to put the word in
 - registerid
 - ( wordid[array] OR /global/ AND allmarked = 1 ) 
/* RETURN: 
 - count : number of words added to save 
/* DESCRIPTION: Adds words to an existing savepoint
/* VERSION: 05.04.2012
/* UPDATES: 05.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

  $go -> necessary( 'newsaveid', 'registerid', array( 'wordid', 'allmarked' ) );
  $countwords = 0;
  if( $go -> good() ) {
    //check id / id überprüfen
    $query = "SELECT id FROM lk_savelist WHERE id = '".$newsaveid."' AND userid = '".$userid."' 
              AND registerid = '".$registerid."'";
    $check_saveids = $go -> query( $query,1 );
    if( $check_saveids['count'] == 0 ) { $go -> errors( 102 ); }
  }
	if( $go -> good() ) {
    if( $allmarked ) { 
      $params['nolimit'] = 1;
      $wordid = request( 'get_word', $params ); 
      if( $words['errnum'] != 0 ) { $go -> error( 400,$words['errnum'].': '.$words['errname'] ); }  
      $wordid = $wordid['id'];
    }

    $countid = count( $wordid );
    if( $countid > 0 ) {
      //querystring
      $insert = '';
      for( $i = 0; $i < $countid; $i++ ) {
        $insert .= "( '".$newsaveid."', '".$wordid[$i]."' ) ";
        if( $i < $countid-1 ) { $insert .= ','; }
		  }	
		  $query = "INSERT IGNORE INTO lk_save ( saveid, wordid ) 
				      VALUES ".$insert;
      //execute
		  $add_save = $go -> query( $query, 2 );
	  }
  }
	if( $go -> good() ) {	
    $return = array( 'count' => $add_save['count'] );
  }
?>
