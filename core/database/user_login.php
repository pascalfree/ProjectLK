<?php
//////////////////////////////////////
/* NAME: user_login
/* PARAMS: 
- username
- password
- forgot : alternative password if "forgot password" is used
/* RETURN: 
- userid 
- login : 1 if login was successful, 0 else
- viaforgot : 1 if logged in via alternative password
/* DESCRIPTION: logs in a user, starts session
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

$go->necessary( 'username', array( 'password', 'forgot' ) );

//also possible to login via forgot-hash as password
if ( $go->good() ) {
  if ( !$arg_forgot ) {
    $arg_forgot = $arg_password;
  }
}

//check user
if ( $go->good() ) {
  $password   = md5( $arg_password );
  $get_result = $go->query( 'SELECT id,forgot FROM lk_user WHERE name="' . $arg_username . '" AND passw="' . $password . '"', 1 );
  $auth       = $get_result[ 'count' ];
  $id         = $get_result[ 'result' ][ 'id' ][ 0 ];
}

//login via forgot-hash //must have exact string length
if ( $auth == 0 && strlen( $arg_forgot ) == 32 && $go->good() ) {
  $get_result = $go->query( 'SELECT id,passw,forgot FROM lk_user WHERE name="' . $arg_username . '" AND forgot="' . $arg_forgot . '"', 2 );
  $viaforgot  = $get_result[ 'count' ];
  $auth       = $viaforgot;
  $id         = $get_result[ 'result' ][ 'id' ][ 0 ];
}

//only one chance. hash is now password //(forgot = password)
if ( $id && $get_result[ 'result' ][ 'forgot' ][ 0 ] != "0" && $go->good() ) { //fix: need "0" not 0
  $reset_forgot = $go->query( 'UPDATE lk_user SET forgot="0", passw="' . md5( $arg_forgot ) . '" WHERE id="' . $id . '"', 3 );
}

//block logging in as guest if function is disabled
if ( $go->good() ) {
  if ( $id == 0 && GUEST == 0 ) {
    $auth = 0;
  }
}

//login user
if ( $go->good() && $auth == 1 ) {
  $_SESSION[ 'lk_username' ]  = $arg_username;
  $_SESSION[ 'lk_userid' ]    = $id;
  $_SESSION[ 'lk_userpwmd5' ] = $password;
}

//return
if ( $go->good() ) {
  $return = array(
    'userid' => $id,
    'login' => $auth,
    'viaforgot' => $viaforgot 
  );
}
?>
