<?php
$here->page="showverblist";
$toolbar = link_generic('text='.urlencode($la['newperson']).'&title=newperson&action=add&where=\'popup\'&type=person&id=\'0\'&icon=plus');
$toolbar .= link_generic('text='.urlencode($la['newform']).'&title=newform&action=add&where=\'popup\'&type=form&id=\'0\'&icon=plus');
$toolbar .= link_generic('action=query&type=verb&id=0&class=icon_text iconplay&icon=play&text='.urlencode($la['query']));

load_head($toolbar);

if($error) {
  echo $error;
} else {
    //usefull to serialize inputs
    echo '<form id="verbqueryform" onsubmit="return false" name="verbqueryform" class="align_left" action="#">';

    list_verb('title=sverbs',$verblist);
    list_generic('form', 'addnew=1&title=forms&pluspass=info&registerid='.$here->registerid);
    list_generic('person', 'addnew=1&title=persons&registerid='.$here->registerid);
    list_verb('title=verbswithouttable&nocheckbox=1', $verblist['empty']);

  echo '</form>'; 
}

load_foot(array('verblist'));
?>
