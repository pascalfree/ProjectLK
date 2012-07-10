<?php
//////////////////////////////////////
/* NAME: user_nregister
/* PARAMS: arg_
- username
- password
- passwordrepeat
- email
- acceptprivacy
/* RETURN: 
- userid : created userid
- success : 1 if user was created
- nametaken : 1 if username is allready taken
/* DESCRIPTION: sign up new user
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 21.04.2012
/* UPDATE: 21.04.2012 - Added this header, changed coding style
////////////////////////////////////*/

$go->necessary( 'username', 'password', 'passwordrepeat', 'email', 'acceptprivacy' );

//passwords don't match
if ( $arg_password != $arg_passwordrepeat ) {
  $go->error( 106 );
}

//check if username's available
if ( $go->good() ) {
  $check_user = $go->query( 'SELECT id 
                               FROM lk_user 
                             WHERE name = "' . $arg_username . '"', 1 );
}

//create user (if name not taken)
if ( $go->good() && $check_user[ 'count' ] === 0 ) {
  $password  = md5( $arg_password );
  $make_user = $go->query( 'INSERT lk_user (name, 
                                            passw, 
                                            email) 
                            VALUES ("' . $arg_username . '", 
                                    "' . $password . '", 
                                    "' . $arg_email . '")'
  , 2 );
}

//login user
if ( $go->good() && $make_user[ 'count' ] === 1 ) {
  $_SESSION[ 'lk_username' ]  = $arg_username;
  $_SESSION[ 'lk_userid' ]    = $make_user[ 'id' ];
  $_SESSION[ 'lk_userpwmd5' ] = $password;
}

// return
if ( $go->good() ) {
  $return = array(
    'userid'    => $make_user[ 'id' ],
    'success'   => $make_user[ 'count' ],
    'nametaken' => $check_user[ 'count' ]
  );
}
?>
