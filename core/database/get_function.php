<?php
//////////////////////////////////////
/* NAME: get_function
/* PARAMS: 
- location : path of the php file relative to the GUI folder
- function : function to call
- (parameters) : array of parameters for the function in correspondent order
- (json) 1/0 : 1 if parameters is passed as a json string
- (forceecho) 1/0 : if 1 will force to return the printed text of the function
/* RETURN: 
- output : return value of the function or printed text of the function, if return value is empty.
/* DESCRIPTION: calls a php function in a file inside the GUI folder
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2012
////////////////////////////////////*/

$go->necessary( array( 'location', 'function' ) );

if ( $go->good() ) {
  $plk_you = new user(); //create user object
}

if ( $go->good() ) {
  //include file with function
  if ( file_exists( GUI . $arg_location ) ) {
    include_once( GUI . $arg_location );
  } else {
    $go->error( 105 );
  } //file not found
}

if ( $go->good() ) {
  //if parameters is a json string
  if ( $arg_parameters != NULL ) {
    //stripslashes removes the previously added slashes
    if ( $arg_json == 1 ) {
      $arg_parameters = json_decode( stripslashes( $arg_parameters ), true );
    }
    if ( $arg_parameters == NULL ) {
      $go->error( 109 );
    } //invalid json
  }
}

if ( $go->good() ) {
  $arg_function = 'ajax_' . $arg_function;
  if ( is_callable( $arg_function ) ) {
    ob_start(); //start caching echos
    //call function
    
    if ( is_array( $arg_parameters ) ) {
      $answer = call_user_func_array( $arg_function, $arg_parameters );
    } else {
      $answer = call_user_func( $arg_function, $arg_parameters );
    }
    //if no return get echos
    if ( empty( $answer ) || $arg_forceecho == 1 ) {
      $answer = ob_get_contents();
    }
    //delete echos
    ob_end_clean();
  } else {
    $go->error( 101 );
  }
}

if ( $go->good() ) {
  $return = array(
     'output' => $answer 
  );
}
?>
