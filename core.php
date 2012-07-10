<?php

session_start();
$time=microtime(true);

//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: core.php
//core
//description: Switch/ Reading URL and loading specific content.
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//content:
//  includes
//  Get URL
//  AJAX
//  loading user & page
//////////

//////////
//GET URL
//////////
$url = urldecode(trim($_SERVER['REQUEST_URI'],'/'));
$last = end( explode('/',$url) ); //get last keyword

//////////
//INCLUDES
//////////
require_once('config.php'); //Load configurations file

//show errors in php
if( DEBUG ) {
  ini_set("display_errors","1");
  ERROR_REPORTING(E_ALL & ~E_NOTICE);
}

require_once('core/php/connect.php');       //connect to DB
require_once('core/php/get_language.php');  //load language
require_once('core/php/utility.php');       //utility functions
require_once('core/php/dbfunctions.php');   //functions for database functions
require_once('core/php/user.php');          //user class

//////////
//AJAX
//////////
//Loading a request via Ajax?
if( $_REQUEST['req'] == 1 ) {
  try{
    header('Content-Type: application/json  charset=UTF-8');
    if( $response === NULL ) { $response = $_REQUEST['response']; } 

    $return['locat'] = $location;

    foreach($_REQUEST as $name => $val) {  //Load all variables
      //secure
      // don't overwrite existing variable
      if( !isset( ${'arg_' . $name} ) ) { ${'arg_' . $name} = mres( $val );}
      //else { $$nname = mres( $$nname ); }
      //null or empty == NULL 
      if( ${'arg_' . $name} == '' || ${'arg_' . $name} == 'null' ) { ${'arg_' . $name} = NULL; }
    }

    //userid & pwd (secure)
    $userid = mres($_SESSION['lk_userid']);
    $pwdmd5 = mres($_SESSION['lk_userpwmd5']);

    $func = explode('.', $last);
    $func = $func[0];
    $go = new managerr( $func );
    if( file_exists(DBFUNCTIONS.$last) ) {  //Load requested function
      require_once(DBFUNCTIONS.$last);
      $return = $go -> geterr($return); //add error msg to return
      if( $response ) {                 //Response 
        //on success
        //compression
        if($func == 'get_function') { //may produce large output
          ob_start("ob_gzhandler");
        }
        //output
        echo json_encode( $return );
      } else { 
        //on error
        echo json_encode( $go -> geterr() );
      }
    } else {  
      //error function not found
      echo "{errnum:101 ,errname:'Function not found.'}";
    }
  } catch ( Exception $e ) {
    //bad error
    header('Content-Type: application/json  charset=UTF-8');
    echo "{errnum:1000 ,errname:'Error in ",$last,": ",$e->getMessage(),"'}";
    
  }
  exit; die; //really
}

//////////
//LOADING USER & PAGE
//////////

//Begin loading Page
header('Content-Type: text/html; charset=UTF-8');
//some global variables
define("P_NAME", 'ProjectLK');
define("P_VERSION", '1.2RC1');
define("P_AUTHOR", 'David Glenck'); //comma separated

//global content functions
include_once('core/php/here.php');
include_once('core/php/load_scripts.php');
include_once('core/php/query.php');

//create HERE
$here = new place($url);

//Login user
if( $_REQUEST['login'] == 1 ) {
  $login = request( 'user_login',
    array('username' => $_REQUEST['username'], 
          'password'=> $_REQUEST['password'])
  );
  $here -> login = $login['login'];
//logout user
} else if( $last == 'logout' ) {
  request('user_logout');
//register new user
} else if( $_REQUEST['nregister'] == 1 ) {
  $params=array('username' => $_REQUEST['username'], 
                'password' => $_REQUEST['password'],
                'passwordrepeat' => $_REQUEST['passwordrepeat'],
                'email' => $_REQUEST['email'],
                'acceptprivacy' => $_REQUEST['accept']);
  $nregister = request( 'user_nregister',$params );
  $here -> login = $nregister['success'];
  $here -> nregerr = $nregister['errnum'];
  if($nregister['nametaken'] !== 0) {
    $here -> nregerr = 'nametaken';
  }
//forgot password
} else if($_REQUEST['forgot'] == 1) {
  $forgot = request( 'user_forgot',
      array( 'username' => $_REQUEST['username'], 
             'email' => $_REQUEST['email'])
  );

  if( $forgot['found'] == 0 ) { $here -> forgot = 102; } //if nothing found
  elseif( $forgot['nomail'] == 1 ) { $here -> forgot = 107; } //no email
  elseif( $forgot['errnum'] != 0 ) {$here -> forgot = $forgot['errnum'];} //other errors
  elseif( $forgot['found'] == 1 ) { $here -> forgot = 1; } //if worked

  //send mail
  if( $here -> forgot == 1 ) {
    $sent = mail($forgot['email'],
      P_NAME.' '.$la['forgot_subject'], 
      $la['username'].": ".$forgot['username']."\n".
      $la['password'].": ".$forgot['passwordhash']."\n\n".
      $la['forgot_text'], 
      "From: ".strtolower(P_NAME."@".$_SERVER['SERVER_NAME']));

    if( !$sent ) { $here -> forgot = 108; } //could not send
  }
}

//create user information //after login/out
$you = new user();
$here -> load_user($you);

//compress
ob_start("ob_gzhandler");

//Load Page from url
$here -> getkeys();
//Header
if( $here -> getheader() ) {
  require_once( $here -> getheader() );
}
//Mainpage
require_once( $here -> loadpage() );
//Footer
if( $here -> getfooter() ) {
  require_once( $here -> getfooter() );
}

$ntime = microtime(true);
?>
<!-- php-loadtime: <?=($ntime-$time) ?> -->
