<?php
  //no permission to call it from js.
  if( $req == 1 ) { $go->error(100); }

  if($go->good()) {
    $go->necessary(array('username','email'));
  }

  if($go->good()) {
    if(!empty($email)) {
      $get_result=$go->query('SELECT id,name FROM lk_user WHERE email="'.$email.'"',1);
      $username = $get_result['result']['name'][0];
    } else {
      $get_result=$go->query('SELECT id,email FROM lk_user WHERE name="'.$username.'"',2);
      $email = $get_result['result']['email'][0];
      if(empty($email)) { $nomail = 1; }
    }
    $id = $get_result['result']['id'][0];
  }
  if($go->good()) {
    $found = $get_result['count'];
  }
  if($go->good() && $nomail != 1 && $found != 0) { //Don't change guest password
    if($id==0) { $go->error(100); }
  }
  if($go->good() && $nomail != 1 && $found != 0) {
    $newpasswordhash = passgen(32);
    $query = "UPDATE lk_user SET forgot='".$newpasswordhash."' WHERE id='".$id."'"; 
    $sethash = $go->query($query,3);
  }
  if($go->good()) {
    $return=array('found' => $found,
                  'nomail' => $nomail,
                  'username' => $username,
                  'email' => $email,
                  'passwordhash' => $newpasswordhash);
  }
?>
