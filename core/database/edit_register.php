<?php
//////////////////////////////////////
/* NAME: edit_register
/* PARAMS: 
- registerid
- (newregister) : new register name, empty string will generate new name
- (newgroupcount) : number of groups in register
- (newgrouplock) : array of grouplock numbers 
- (newlanguageid) : language of register (e.g. en, de, ..)
/* RETURN: 
- registername
- groupcount
- count : number of edited registers
/* DESCRIPTION: changes properties of a register
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2012
////////////////////////////////////*/

$go->necessary( 'registerid' );

if ( $go->good() ) {
  //prepare everything to change the name
  //newregister == NULL won't change that
  // but newregister == '' will
  $name = NULL;
  if ( $arg_newregister != NULL ) {
    $name = $arg_newregister;
    if ( $name == '' ) {
      $name = 'Kartei ' . $arg_registerid;
    }
    //forbidden characters / Verbotene Zeichen
    plk_util_removeForbidden( $name , array( '/', '"', '\'', '#', '+', '\\' ) );

    //Checkname
    $num          = '';
    $originalname = $name;
    do {
      $check_name = $go->query( 
        "SELECT name 
           FROM lk_registers
         WHERE name = '" . $name . "'
               AND userid = '" . $userid . "'
               AND id != '" . $arg_registerid . "' "
      , 1 );
      $resultname = $check_name[ 'result' ][ 'name' ][ 0 ];
      if ( $resultname == $name ) {
        $used = true;
        if ( $num == '' ) {
          $num = 2;
        } else {
          $num++;
        }
      } else {
        $used = false;
      }
      if ( $num != '' ) {
        $name = $originalname . '(' . $num . ')';
      }
    } while ( $used );
  }
  
  //load groupcount and grouplock
  if ( $arg_newgrouplock != NULL || $arg_newgroupcount != NULL ) {
    $groups = ( $go->query(
      "SELECT groupcount, grouplock 
         FROM lk_registers
       WHERE id = '" . $arg_registerid . "'
             AND userid = '" . $userid . "'"
    , 2 ) );
    $groupcount = $groups[ 'result' ][ 'groupcount' ][ 0 ];
    $grouplock  = explode( '?', $groups[ 'result' ][ 'grouplock' ][ 0 ] );
  }
  
  //fill update
  $setting = NULL;
  if ( $arg_newregister != NULL ) {
    $setting[] = " name='" . $name . "'";
  }
  if ( $arg_newlanguageid != NULL ) {
    $setting[] = " language='" . $arg_newlanguageid . "'";
  }
  //change groupcount
  if ( $arg_newgroupcount != NULL ) {
    //increment and decrement
    if ( $arg_newgroupcount == '++' ) {
      $groupcount++;
    } elseif ( $arg_newgroupcount == '--' ) {
      $groupcount--;
    } else {
      $groupcount = $arg_newgroupcount;
    }
    //not less than 1
    if ( $groupcount < 1 ) {
      $groupcount = 1;
    }
    $setting[] = " groupcount = '" . $groupcount . "'";
  }
  //change one or more grouplocks //also when adding new groups
  if ( $arg_newgrouplock != NULL || $arg_newgroupcount != NULL ) {
    for ( $i = 0; $i < $groupcount; $i++ ) {
      if ( $arg_newgrouplock[ $i ] ) {
        $grouplock[ $i ] = $arg_newgrouplock[ $i ];
      }
      if ( !$grouplock[ $i ] ) {
        $grouplock[ $i ] = $grouplock[ $i - 1 ] * 2;
      }
    }
    $setting[] = " grouplock='" . implode( '?', $grouplock ) . "'";
  }
  
}

if ( $go->good() && $settings !== '' ) {
  $edit_reg = $go->query( 
    "UPDATE lk_registers 
       SET  " . implode( $setting, ' , ' ) . "
		 WHERE id='" . $arg_registerid . "' 
           AND userid='" . $userid . "' "
  , 2 );
}

if ( $go->good() ) {
  $return = array(
    'registername' => $name,
    'groupcount' => $groupcount,
    'count' => $edit_reg[ 'count' ] 
  );
}
?>
