<?php
load_head(link_back());
  
  if($plk_here->formid!=NULL) {
    //Form
    $forminfo = plk_request('get_form',(array) $plk_here);
    ?>
	  <div class="middlebox">
      <span class="title"><?=$plk_la['edit']; ?></span>
      <form id="edit_reg_form" name="edit_reg_form" method="POST" action="#" onsubmit="javascript: return plk.req('edit_form',this.serialize(true),'')">
	      <table style="float:right;">
		      <tr>
		        <td><label for="newform"><?=$plk_la['name'] ?>:</label></td>     
            <td><input type="text" name="newform" value="<?=$forminfo['name'][0] ?>"></td> 
			    </tr>	
			    <tr>
		        <td class="satzlabel"><label for="newinfo"><?=$plk_la['info'] ?>:</label></td> 
            <td><textarea cols="30" rows="7" id="newinfo" name="newinfo"><?=$forminfo['info'][0]; ?></textarea></td> 
			    </tr>
		    </table>
        <input type="hidden" name="formid" value="<?=$plk_here->formid ?>">
        <input type="submit" value="<?=$plk_la['save'] ?>">
        <input type="reset" value="<?=$plk_la['reset'] ?>">
      </form>
    </div>
    <?php
	} elseif($plk_here->registerid!=NULL) {
	  //Edit register
    $reginfo = plk_request('get_reg_info',array('registerid' => $plk_here->registerid));
    $grouplock = explode('?', $reginfo['grouplock']);
    ?>
      
    <div class="middlebox">
      <span class="title"><?=$plk_la['edit']; ?></span>
	    <form id="edit_reg_form" name="edit_reg_form" method="POST" action="<?=$plk_here->path(2); ?>" onsubmit="return plk.req('edit_register',$(this).serialize(true), [function(){ do_shutter(1); } ,function() {location.reload();}])">
	      <table>
		      <tr>
			      <td><?=$plk_la['name'] ?>: </td>
				    <td><input type="text" name="newregister" value="<?=plk_util_getName('register',$plk_here->registerid) ?>"></td>
			    </tr>	
			    <tr>
			      <td><?=$plk_la['groups'] ?>: </td>
				    <td><input type="text" name="newgroupcount" value="<?=$reginfo['groupcount'] ?>"></td>
			    </tr>
			    <tr>
			      <td colspan="2"><?=$plk_la['grouplocks'] ?>: </td>
			    </tr>

				    <?php
				      for($i=0;$i<$reginfo['groupcount']-1;$i++) {
				    ?>	
				    <tr>
					    <td><?=$plk_la['group'] ?> <?=$i+2 ?>: </td>
					    <td><input type="text" name="newgrouplock[]" value="<?=$grouplock[$i] ?>"></td>
				    </tr>
				    <?php
				      }
				    ?>
				
			    <tr>
			      <td><?=$plk_la['language'] ?>: </td>
				    <td>
				      <select size="1" name="newlanguageid">
                <?php 
                  $len=count($plk_la['languagename']);
                  for($i=0;$i<$len;$i++) {
                    echo '<option ';
                    if($i==$reginfo['language']) { echo'selected'; }
                    echo ' value="'.$i.'">'.$plk_la['languagename'][$i].'</option>';
                  }
                ?>
				      </select>	
				    </td>
			    </tr>
		    </table>
		    <input type="hidden" name="registerid" value="<?=$plk_here->registerid ?>">
		    <input type="submit" value="<?=$plk_la['save'] ?>">
		    <input type="reset" value="<?=$plk_la['reset'] ?>">
	    </form>
    </div>
	
<?php
	} else { echo 'error[0003]'; }

load_foot();
?>
