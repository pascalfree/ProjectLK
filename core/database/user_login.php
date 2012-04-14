<?php
  $go -> necessary('username',array('password','forgot'));

  //also possible to login via forgot-hash as password
  if($go->good()) {
    if( !$forgot ) { $forgot = $password; }
  }

  //check user
  if($go->good()) {
    $password=md5($password);
    $get_result = $go -> query('SELECT id,forgot FROM lk_user WHERE name="'.$username.'" AND passw="'.$password.'"',1);
    $auth = $get_result['count'];
    $id = $get_result['result']['id'][0];
  }

  //login via forgot-hash //must have exact string length
  if($auth == 0 && strlen($forgot) == 32 && $go->good()) {
    $get_result = $go -> query('SELECT id,passw,forgot FROM lk_user WHERE name="'.$username.'" AND forgot="'.$forgot.'"',2);
    $viaforgot = $get_result['count'];
    $auth = $viaforgot;
    $id = $get_result['result']['id'][0];
  }

  //only one chance. hash is now password //(forgot = password)
  if($id && $get_result['result']['forgot'][0] != "0" && $go -> good()) { //fix: need "0" not 0
    $reset_forgot = $go -> query('UPDATE lk_user SET forgot="0", passw="'.md5($forgot).'" WHERE id="'.$id.'"',3);    
    //$forgot = $password;
  }

  //block logging in as guest if function is disabled
  if( $go -> good() ) {
    if( $id == 0 && GUEST == 0 ) { $auth = 0; }
  }

  //login user
  if($go -> good() && $auth == 1) {
    $_SESSION['lk_username']  = $username;
    $_SESSION['lk_userid']    = $id;
    $_SESSION['lk_userpwmd5'] = $password;
  }

  if($go->good()) {
    $return=array('userid' => $id,
                  'login' => $auth,
                  'viaforgot' => $viaforgot);
  }
?>
