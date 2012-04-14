<?php
//get language

//Check language from URL
$url_l = explode('/', $url);
$url_lang = ''; //reset
if($url_l[count($url_l)-2]=='lang') { //Fix: otherwise makes errors with tag named en or de
  $url_lang=$url_l[count($url_l)-1];
}

//Try url, database (connect.php), session or default from config.php
$trythis=array($url_lang, $db_lang, $_SESSION['lk_language'], DEFAULT_LANG); 
$tla = NULL;
$cc = 0;
foreach($trythis as $try) {
  if( file_exists('language/'.$try.'.js') )      { $tla = array($try,'js') ; break; }
  elseif( file_exists('language/'.$try.'.ini') ) { $tla = array($try,'ini'); break; }
  elseif( file_exists('language/'.$try.'.php') ) { $tla = array($try,'php') ; break; }
  $cc++;
}
if($tla == NULL) { 
  echo "Error: Language file could not be loaded.";
} else {
  if( $cc == 0 ) { //if language came from url -> save to session
     $_SESSION['lk_language'] = $tla[0];
     unset($url_l[count($url_l)-1]);
     unset($url_l[count($url_l)-1]); //yes twice.
     $url = implode('/', $url_l); //cut from $url
  }
  //define language globaly
  define("LANG", $tla[0]); 
  define("LANGTYPE", $tla[1]); 
}
//load language file
if($tla[1]=='php') 
{ 
  include_once('language/'.$tla[0].'.php'); 
}
elseif($tla[1]=='js') 
{ 
  $repl=array('', '\1 "\2" :');
  $mtch=array('/\/\/.*\n/', '/([{,][ \n]*)\'?(\w+)\'?[ ]*:/');
  $la = json_decode(preg_replace($mtch, $repl, file_get_contents('language/'.$tla[0].'.js',NULL,NULL,4)), true);
}
elseif($tla[1]=='ini') 
{ 
  $la = parse_ini_file('language/'.$tla[0].'.ini'); 
}
?>
