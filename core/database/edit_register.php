<?php
  $go->necessary('registerid');

  if($go->good()) {
    //prepare everything to change the name
    //newregister == NULL won't change that
    // but newregister == '' will
    $name = NULL;
    if( $newregister != NULL ) {
      $name=$newregister;
      if($name=='') { $name='Kartei '.$registerid; }
      //forbidden characters / Verbotene Zeichen
      $forbidden= array('/', '"', '\'', '#', '+', '\\');
      $name=str_replace($forbidden, '', $name);

	    //Checkname
	    $num='';
	    $originalname = $name;
	    do{
	      $query="SELECT name FROM lk_registers WHERE name='".$name."' AND userid='".$userid."' AND id!='".$registerid."' ";
	      $check_name=$go->query($query,1);
		    $resultname=$check_name['result']['name'][0];
		    if($resultname==$name) {
			    $used=true;
			    if($num=='') { $num=2; } else { $num++; }
     		} else { $used=false; }
		    if($num!='') { 
		      $name=$originalname.'('.$num.')';
		    }
	    } while($used);	
    }
	  //if(is_array($grouplock)) { $grouplock=implode('?', $grouplock); }
		
    //load groupcount and grouplock
    if($newgrouplock!=NULL || $newgroupcount!=NULL) {
	    $query="SELECT groupcount, grouplock FROM lk_registers WHERE id='".$registerid."' AND userid='".$userid."'";
      $groups=($go->query($query,2));
      $groupcount = $groups['result']['groupcount'][0];
      $grouplock = explode('?',$groups['result']['grouplock'][0]);   
    }


    //fill update
	  $setting=NULL;
    if($newregister!=NULL) { $setting[]=" name='".$name."'"; }
	  if($newlanguageid!=NULL) { $setting[]=" language='".$newlanguageid."'"; }
    //change groupcount
    if($newgroupcount != NULL) { 
      //increment and decrement
      if( $newgroupcount == '++' ) {
        $groupcount++;
      } elseif( $newgroupcount == '--' ) {
        $groupcount--;
      } else { $groupcount = $newgroupcount; }
      //not less than 1
      if( $groupcount < 1 ) { $groupcount = 1; }
      $setting[] = " groupcount = '".$groupcount."'"; 
    }
    //change one or more grouplocks //also when adding new groups
	  if($newgrouplock!=NULL || $newgroupcount!=NULL) {     
      for($i=0; $i < $groupcount; $i++) {
        if( $newgrouplock[$i] ) { $grouplock[$i] = $newgrouplock[$i]; }
        if( !$grouplock[$i] ) { $grouplock[$i] = $grouplock[$i-1]*2; }
      }
      $setting[]=" grouplock='".implode('?',$grouplock)."'"; 
    }
		
  }
  if($go->good() && $settings !== '') {		
	  $query="UPDATE lk_registers 
	          SET 
					  ".implode( $setting, ' , ' )."
					  WHERE id='".$registerid."' AND userid='".$userid."' ";
    $edit_reg=$go->query($query,2);
  }

  if($go->good()) {		
    $return=array('registername' => $name,
                  'groupcount' => $groupcount,
				          'count' => $edit_reg['count']);
  }
?>
