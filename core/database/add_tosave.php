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
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 05.04.2012
/* UPDATES: 20.04.2012 - Changed coding style, added this header
////////////////////////////////////*/

$go->necessary( 'newsaveid', 'registerid', array( 'wordid', 'allmarked' ) );

if( $go->good() ) {
  //check id / id überprüfen
  $query = "SELECT id FROM lk_savelist 
            WHERE id = '".$arg_newsaveid."' 
                  AND userid = '".$userid."' 
                  AND registerid = '".$arg_registerid."'";
  $check_saveids = $go->query( $query,1 );
  if( 0 == $check_saveids['count'] ) { 
    $go->errors( 102 ); 
  }
}

if( $go->good() ) {
  make_array( $arg_wordid );

  if( $arg_allmarked ) { 
    $arg_wordid = load_wordid( $go );
  }

  $countid = count( $arg_wordid );
  if( 0 < $countid ) {
    //querystring
    $insert = '';
    for( $i = 0; $i < $countid; $i++ ) {
      $insert .= "( '".$arg_newsaveid."', '".$arg_wordid[$i]."' ) ";
      if( $i < $countid-1 ) { 
        $insert .= ','; 
      }
	  }	
	  $query = "INSERT IGNORE 
                INTO lk_save ( saveid, wordid ) 
			            VALUES ".$insert;
    //execute
	  $add_save = $go->query( $query, 2 );
  }
}

//return
if( $go->good() ) {	
  $return = array( 'count' => $add_save['count'] );
}
?>
