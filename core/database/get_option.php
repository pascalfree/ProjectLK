<?php
  $get_option=$go->query("SELECT * FROM lk_user WHERE id='".$userid."' ",1);
  $return=flat($get_option['result']);
?>
