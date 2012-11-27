<?php
//$toolbar=link_regadd();
load_head( link_generic('title=registers&id=0&type=register&action=add&icon=plus&where=\'popup\'&text='.urlencode($plk_la['newregister'])) );

//writereg();
list_generic('register','title=registers&addnew=1&help=register&class=doublewidth&nocheckbox=1');

//create a list with some options
$list = array(
  link_export(), link_key('import'), link_key('settings'), link_key('logout')
);
list_generic('option','title=options&nocheckbox=1',$list);

load_foot();
?>

