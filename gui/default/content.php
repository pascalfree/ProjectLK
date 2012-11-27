<?php
$plk_here -> page="content";
//if just logged in: go to dashboard
if($plk_here -> login==1) { header('Location: '.URL. $plk_you -> name); }

//parse xml
$xmlload = simplexml_load_file('content/'.$plk_you -> language.'/'.$plk_here -> keyoption.'.xml');

//write toolbar
$toolbar = '';
foreach($xmlload -> links -> link as $link) {
  $dest = $link->attributes() -> dest;
  if($dest == 'back') { $toolbar .= link_back(); } //make backlinks possible
  else { $toolbar .= '<a href="'.URL.$dest.'/">'.$link.'</a>'; }
}

//write toolbar links for languages
$languages = plk_util_getLanguages();
$toolbar .= "&nbsp;&nbsp;&nbsp;&nbsp;";
foreach( $languages as $ll ) {
  $toolbar .= "<a href='".$plk_here -> path()."lang/". $ll ."'>". $ll ."</a>";
}

//write error messages
$error = '';
if($plk_here -> login === 0) {
  $error = $plk_la['err_login'];
}

if($plk_here -> nregerr != 0) {
  $error = $plk_la['err_'.$plk_here -> nregerr];
}
if($plk_here -> forgot != 0) { 
  if($plk_here -> forgot==1) { $error = $plk_la['forgot_sentmsg']; }
  else { $error=$plk_la['err_'.$plk_here -> forgot]; }
}

//write head
load_head($toolbar, $tabs, $error);

//write content
foreach($xmlload->body->section as $section) {
  echo '<div class="middlebox">';
    foreach($section as $type=>$input) {
      $input = plk_util_addGlobals($input); //replace keywords //<<keyword>>
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
