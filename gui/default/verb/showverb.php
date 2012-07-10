<?php
$here->page="showverb";
$toolbar = link_generic('text='.urlencode($la['newperson']).'&title=newperson&action=add&where=\'popup\'&type=person&id=\'0\'&icon=plus');
$toolbar.= link_generic('text='.urlencode($la['newform']).'&title=newform&action=add&where=\'popup\'&type=form&id=\'0\'&icon=plus');
load_head($toolbar);
?>

  <!--showverb-->

  <?php
  if($error) {
    echo $error;
  } else {

    $formlist = request('get_form',array('registerid' => $here->registerid));
    $personlist = request('get_person',array('registerid' => $here->registerid));

    if($here->wordid!=NULL) {
      if( is_array( $verblist['id'] ) ) {
        $key = array_search( $here->wordid, $verblist['id'] );
      } else { $key = false; }

      if( $key === false || $verblist['count'] == 0 ) { 
        $verblist = $verblist['empty']; //use verb with no entries yet
        $key = array_search( $here->wordid, $verblist['id'] ); 
      }

      //if( $key === false && DEBUG ) { echo 'This should never happen. ERROR in showverb.php'; }
      $t_head=array('what'=>'verb', 'id'=>$verblist['id'][$key], 'name'=>$verblist['wordfore'][$key]);
      $t_top=array('what'=>'form', 'id'=>$formlist['id'], 'name'=>$formlist['name'],'count'=>$formlist['count']);
      $t_left=array('what'=>'person', 'id'=>$personlist['id'], 'name'=>$personlist['name'],'count'=>$personlist['count']);
      $loadverbs = request('get_verb',array('wordid' => $verblist['id'][$key], 'formid' => $formlist['id'], 'personid' => $personlist['id']));

    } elseif($here->formid!=NULL) {

      $key=array_search($here->formid,$formlist['id']);
      $t_head=array('what'=>'form', 'id'=>$formlist['id'][$key], 'name'=>$formlist['name'][$key]);
      $t_top=array('what'=>'person', 'id'=>$personlist['id'], 'name'=>$personlist['name'],'count'=>$personlist['count']);
      $t_left=array('what'=>'verb', 'id'=>$verblist['id'], 'name'=>$verblist['wordfore'],'count'=>$verblist['count']);
      $loadverbs=request('get_verb',array('wordid' => $verblist['id'], 'formid' => $here->formid, 'personid' => $personlist['id']));

    } elseif($here->personid!=NULL) {

      $key=array_search($here->personid,$personlist['id']);
      $t_head=array('what'=>'person', 'id'=>$personlist['id'][$key], 'name'=>$personlist['name'][$key]);
      $t_top=array('what'=>'form', 'id'=>$formlist['id'], 'name'=>$formlist['name'],'count'=>$formlist['count']);
      $t_left=array('what'=>'verb', 'id'=>$verblist['id'], 'name'=>$verblist['wordfore'],'count'=>$verblist['count']);
      $loadverbs = request('get_verb',array('wordid' => $verblist['id'], 'formid' => $formlist['id'], 'personid' => $here->personid));

    }
  ?>

  <table id="wordlist">
    <?php

    echo '<tr class="tabhead"><td>',$t_head['name'],'</td>';

    for($i=0;$i<$t_top['count'];$i++) {
      wt_entry($t_top['id'][$i], $t_top['name'][$i], $t_top['what'], NULL, $t_top['what'].'_'.$t_top['id'][$i].'_remove', array('show','hr','edit','delete'));
    }
    echo '</tr>';

    for( $i = 0; $i < $t_left['count']; $i++ ) {
      vt_line($i, $t_head, $t_top, $t_left, $loadverbs);
    }
    ?>
  </table>


<?php
  }

load_foot('verbshow');
?>
