<?php
$verb_check=1;

$verblist=plk_request('get_verblist',array('registerid' => $plk_here->registerid, 'getempty' => 1));
if($plk_here->wordid==NULL && $plk_here->formid==NULL && $plk_here->personid==NULL) {
  include('verb/showverblist.php');
} elseif($plk_here->wordid!=NULL) {
  $word = plk_request('get_word',array('registerid' => $plk_here->registerid, 'wordid' => $plk_here->wordid));
  if($word['wordclassid'][0]!=2) { $error = $plk_la['err_noverb']; }
  include('verb/showverb.php');
} else {
  include('verb/showverb.php');
}


?>
