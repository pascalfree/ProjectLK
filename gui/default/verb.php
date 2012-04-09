<?php
$verb_check=1;

$verblist=request('get_verblist',array('registerid' => $here->registerid, 'getempty' => 1));
if($here->wordid==NULL && $here->formid==NULL && $here->personid==NULL) {
  include('verb/showverblist.php');
} elseif($here->wordid!=NULL) {
  $word=request('get_word',array('registerid' => $here->registerid, 'wordid' => $here->wordid));
  if($word['wordclassid'][0]!=2) { $error=$la['err_noverb']; }
  include('verb/showverb.php');
} else {
  include('verb/showverb.php');
}


?>
