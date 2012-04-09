<?php
  //necessary params:
  //  location: path of the php file relative to the GUI folder
  //  function: function to call
  //optional params:
  //  parameters: array of parameters for the function in right order
  //  json 1/0: 1 if parameters is passed as a json string
  $go->necessary( array( 'location', 'function' ) );

  if($go -> good()) {
    $you = new user(); //create user object
  }

  if($go->good()) {
    //include file with function
    if( file_exists(GUI.$location) ) { include_once(GUI.$location); }
    else { $go->error(105); } //file not found
  }

  if($go->good()) {
    //if parameters is a json string
    if($parameters != NULL) {
      //stripslashes removes the previously added slashes
      if($json==1) { $parameters = json_decode(stripslashes($parameters), true); }
      if($parameters == NULL) { $go -> error(109); } //invalid json
    }
  }

  if($go->good()) {
    $function='ajax_'.$function; 
    if( is_callable( $function ) ) {

      ob_start(); //start caching echos
      //call function
      
      if(is_array($parameters)) {
        $answer=call_user_func_array($function,$parameters);
      } else {
        $answer=call_user_func($function,$parameters);
      }
      //if no return get echos
      if(empty($answer) || $forceecho==1) { 
        $answer = ob_get_contents();
      }
      //delete echos
      ob_end_clean();
    } else { $go->error(101); } 
  }

  if($go->good()) { 
    $return = Array('output' => $answer);
  }
?>
