<?php
  $go->necessary('registerid');  

  if($go->good()) {  //Load register information
    $query="SELECT groupcount, grouplock, language FROM lk_registers WHERE id='".$registerid."' AND userid='".$userid."' ";
	  $get_reg_info=$go->query($query,1);
    if($get_reg_info['count']==0) { $go->error('102','Entry not found'); }
  }
  if($go->good()) {  //result
    $return=flat($get_reg_info['result']);
  }
?>
