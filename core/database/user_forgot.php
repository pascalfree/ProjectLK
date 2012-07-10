<?php
//////////////////////////////////////
/* NAME: user_forgot
/* PARAMS: arg_
- email
- OR username
/* RETURN: 
- found : 1 if user was found
- nomail : user has no email
- username : matching username
- email : matching email
- passwordhash : generated password to login
/* DESCRIPTION: if a user forgot the password, this searches for email or username and creates a password hash for alternative login, which can then be emailed to the user.
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 21.04.2012
/* UPDATE: 21.04.2012 - Added this header, changed coding style
////////////////////////////////////*/

//no permission to call it from js.
if( 1 == $req ) { 
  $go->error(100); 
}

if($go->good()) {
  $go->necessary( array( 'username', 'email' ) );
}

if( $go->good() ) {
  if( !empty($arg_email) ) {
    $get_result = $go->query(
      'SELECT id,name 
         FROM lk_user 
       WHERE email="'.$arg_email.'"'
    , 1 );
    $arg_username = $get_result['result']['name'][0];
  } else {
    $get_result = $go->query(
      'SELECT id,email 
         FROM lk_user 
       WHERE name="'.$arg_username.'"'
    , 2 );
    $arg_email = $get_result['result']['email'][0];
    if( empty($arg_email) ) { $nomail = 1; }
  }
  $id = $get_result['result']['id'][0];
}

if($go->good()) {
  $found = $get_result['count'];
}

if( 1 != $nomail && 0 != $found ) { //Don't change guest password
  if($go->good()) {
    if(0 == $id) { 
      $go->error(100); 
    }
  }

  if($go->good()) {
    $newpasswordhash = passgen(32); 
    $sethash = $go->query(
     "UPDATE lk_user 
        SET forgot='".$newpasswordhash."' 
      WHERE id='".$id."'"
    , 3 );
  }
}

// return
if($go->good()) {
  $return = array('found'        => $found,
                  'nomail'       => $nomail,
                  'username'     => $arg_username,
                  'email'        => $arg_email,
                  'passwordhash' => $newpasswordhash);
}
?>
