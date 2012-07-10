<?php
load_head(link_back());
  
  if($here->formid!=NULL) {
    //Form
    $forminfo = request('get_form',(array) $here);
    ?>
	  <div class="middlebox">
      <span class="title"><?=$la['edit']; ?></span>
      <form id="edit_reg_form" name="edit_reg_form" method="POST" action="#" onsubmit="javascript: return req('edit_form',this.serialize(true),'')">
	      <table style="float:right;">
		      <tr>
		        <td><label for="newform"><?=$la['name'] ?>:</label></td>     
            <td><input type="text" name="newform" value="<?=$forminfo['name'][0] ?>"></td> 
			    </tr>	
			    <tr>
		        <td class="satzlabel"><label for="newinfo"><?=$la['info'] ?>:</label></td> 
            <td><textarea cols="30" rows="7" id="newinfo" name="newinfo"><?=$forminfo['info'][0]; ?></textarea></td> 
			    </tr>
		    </table>
        <input type="hidden" name="formid" value="<?=$here->formid ?>">
        <input type="submit" value="<?=$la['save'] ?>">
        <input type="reset" value="<?=$la['reset'] ?>">
      </form>
    </div>
    <?php
	} elseif($here->registerid!=NULL) {
	  //Edit register
    $reginfo = request('get_reg_info',array('registerid' => $here->registerid));
    $grouplock = explode('?', $reginfo['grouplock']);
    ?>
      
    <div class="middlebox">
      <span class="title"><?=$la['edit']; ?></span>
	    <form id="edit_reg_form" name="edit_reg_form" method="POST" action="<?=$here->path(2); ?>" onsubmit="return req('edit_register',$(this).serialize(true), [function(){ do_shutter(1); } ,function() {location.reload();}])">
	      <table>
		      <tr>
			      <td><?=$la['name'] ?>: </td>
				    <td><input type="text" name="newregister" value="<?=get_name('register',$here->registerid) ?>"></td>
			    </tr>	
			    <tr>
			      <td><?=$la['groups'] ?>: </td>
				    <td><input type="text" name="newgroupcount" value="<?=$reginfo['groupcount'] ?>"></td>
			    </tr>
			    <tr>
			      <td colspan="2"><?=$la['grouplocks'] ?>: </td>
			    </tr>

				    <?php
				      for($i=0;$i<$reginfo['groupcount']-1;$i++) {
				    ?>	
				    <tr>
					    <td><?=$la['group'] ?> <?=$i+2 ?>: </td>
					    <td><input type="text" name="newgrouplock[]" value="<?=$grouplock[$i] ?>"></td>
				    </tr>
				    <?php
				      }
				    ?>
				
			    <tr>
			      <td><?=$la['language'] ?>: </td>
				    <td>
				      <select size="1" name="newlanguageid">
                <?php 
                  $len=count($la['languagename']);
                  for($i=0;$i<$len;$i++) {
                    echo '<option ';
                    if($i==$reginfo['language']) { echo'selected'; }
                    echo ' value="'.$i.'">'.$la['languagename'][$i].'</option>';
                  }
                ?>
				      </select>	
				    </td>
			    </tr>
		    </table>
		    <input type="hidden" name="registerid" value="<?=$here->registerid ?>">
		    <input type="submit" value="<?=$la['save'] ?>">
		    <input type="reset" value="<?=$la['reset'] ?>">
	    </form>
    </div>
	
<?php
	} else { echo 'error[0003]'; }

load_foot();
?>
