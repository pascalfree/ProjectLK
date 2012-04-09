<?php
$here -> page="content";
//if just logged in: go to dashboard
if($here -> login==1) { header('Location: '.URL. $you -> name); }

//parse xml
$xmlload = simplexml_load_file('content/'.$you -> language.'/'.$here -> keyoption.'.xml');

//write toolbar
$toolbar = '';
foreach($xmlload -> links -> link as $link) {
  $dest = $link->attributes() -> dest;
  if($dest == 'back') { $toolbar .= link_back(); } //make backlinks possible
  else { $toolbar .= link_out($dest, $link); }
}

//write toolbar links for languages
$languages = getlanguages();
$toolbar .= "&nbsp;&nbsp;&nbsp;&nbsp;";
foreach( $languages as $ll ) {
  $toolbar .= "<a href='".$here -> path()."lang/". $ll ."'>". $ll ."</a>";
}

//write error messages
$error = '';
if($here -> login === 0) {
  $error = $la['err_login'];
}

if($here -> nregerr != 0) {
  $error = $la['err_'.$here -> nregerr];
}
if($here -> forgot != 0) { 
  if($here -> forgot==1) { $error = $la['forgot_sentmsg']; }
  else { $error=$la['err_'.$here -> forgot]; }
}

//write head
load_head($toolbar, $tabs, $error);

//write content
foreach($xmlload->body->section as $section) {
  echo '<div class="middlebox">';
    foreach($section as $type=>$input) {
      $input=addglobals($input); //replace keywords //<<keyword>>
      if($type=='title') {
        echo '<span class="title">'.$input.'</span>';
      } elseif($type=='text') {
        echo '<p>'.$input.'</p>';
      }
    }
  echo '</div>';  
}

load_foot('content');
?>
