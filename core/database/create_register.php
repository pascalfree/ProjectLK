<?php
//////////////////////////////////////
/* NAME: create_register
/* PARAMS: 
- [newregister] : name of new register
- [groupcount]
- [grouplock] : grouplocks in the format: "50?100?200"
- [language] : languageid of register
- [time_created] : MySQL timestamp (default: CURRENT_TIMESTAMP)
/* RETURN:
- newname : final name of register
- newid : id of created register
- count : number of created registers
/* DESCRIPTION: Create a new register
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
/* UPDATES: 14.04.2012 - Code Style
////////////////////////////////////*/

if ( $go->good() ) {
  // forbidden characters
  remove_forbidden( $arg_newregister, array( '/', '"', '\'', '#', '+', '\\' ) );
  
  //add first to create name with id	
  $query      = "INSERT INTO lk_registers (userid) VALUES (" . $userid . ") ";
  $insert_reg = $go->query( $query, 1 );
  $regid      = $insert_reg[ 'id' ];
  if ( $arg_newregister == '' ) { //if no name, create one
    $arg_newregister = 'Register ' . $regid;
  }
}

// Check if name is already used
if ( $go->good() ) {
  $num          = '';
  $originalname = $arg_newregister;
  do {
    $query      = "SELECT name FROM lk_registers WHERE name='" . $arg_newregister . "' AND userid='" . $userid . "' ";
    $check_name = $go->query( $query, 2 );
    $resultname = $check_name[ 'result' ][ 'name' ][ 0 ];
    if ( $resultname == $arg_newregister ) {
      $used = true;
      //increment number begin with 2
      if ( $num == '' ) {
        $num = 2;
      } else {
        $num++;
      }
      // next name to try
      if ( $num != '' ) {
        $arg_newregister = $originalname . '(' . $num . ')';
      }
    } else {
      $used = false;
    }
  } while ( $used );
}
  
// Insert
if ( $go->good() ) {
  $query = "UPDATE lk_registers SET name='" . $arg_newregister . "'";
  // overwrite defaults from MYSQL table
  if ( isset( $arg_groupcount ) ) {
    $query .= ", groupcount='" . $arg_groupcount . "'";
  }
  if ( isset( $arg_grouplock ) ) {
    $query .= ", grouplock='" . $arg_grouplock . "'";
  }
  if ( isset( $arg_language ) ) {
    $query .= ", language='" . $arg_language . "'";
  }
  if ( isset( $arg_time_created ) ) {
    $query .= ", time_created='" . $arg_time_created . "'";
  }
  $query .= "WHERE id='" . $regid . "' ";
  $result = $go->query( $query, 1 );
}

// return
if ( $go->good() ) {
  $return = array(
    'newid'   => $regid,
    'newname' => $arg_newregister,
    'count'   => $result[ 'count' ] 
  );
}
?>
