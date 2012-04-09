<?php
  $go->necessary('username','password','passwordrepeat','email','acceptprivacy');
  if($password!=$passwordrepeat) { $go->error(106); }

  //check if username's available
  if($go->good()) {
    $check_user = $go->query('SELECT id FROM lk_user WHERE name="'.$username.'"',1);
    $nametaken = $check_user['count'];    
  }

  //create user
  if($go->good() && $nametaken === 0) {
    $password=md5($password);
    $make_user=$go->query('INSERT lk_user (name, passw, email) 
              VALUES ("'.$username.'", "'.$password.'", "'.$email.'")',2);
    $auth=$make_user['count'];
    $id=$make_user['id'];
  }

  //login user
  if($go->good() && $auth === 1) {
    $_SESSION['lk_username'] = $username;
    $_SESSION['lk_userid'] = $id;
    $_SESSION['lk_userpwmd5'] = $password;
  }
  if($go->good()) {
    $return=array('userid' => $id,
                  'success' => $auth,
                  'nametaken' => $nametaken);
  }
?>
