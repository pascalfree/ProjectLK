<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: utility.php
//core
//description: Various functions
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//UTILITIES
//////////

//Loads available languages
function getlanguages($prefix='') {
  $tdir=opendir('./language/');
  unset($langs);
  while (($tfile = readdir($tdir)) !== false) {
    if( preg_match('/^.+\.ini$/',$tfile) OR 
        preg_match('/^.+\.php$/',$tfile) OR
        preg_match('/^.+\.js$/',$tfile)) 
    {
      $lang=explode('.',$tfile);
      $langs[]=$prefix.$lang[0];
    }
  }
  
  return array_unique( $langs ); //return every language only once
}

//Loads available themes
function getgui() {
  $tdir=opendir('./gui/');
  unset($gui);
  while (($tfile = readdir($tdir)) !== false) {
    if(!is_dir($tfile)) {
      $gui[]=$tfile;
    }
  }
  return $gui;
}

//Loads available css themes
function getthemes() {
  $tdir=opendir('./'.GUI.'/theme/');
  unset($subthemes);
  while (($tfile = readdir($tdir)) !== false) {
    if(preg_match('/^[^_]+\.css$/',$tfile)) {
      $subtheme=explode('.',$tfile);
      $subthemes[]=$subtheme[0];
    }
  }
  return $subthemes;
}

//Transforms an import file into an Array
function getimport($file,$full=0) {
  $xmlload = simplexml_load_file($file);
  if($full) { $toload=array('registerlist','wordlist','taglist','tags','savelist','saves','verblist','personlist','formlist'); }
  else { $toload=array('registerlist','taglist','savelist','personlist','formlist'); }
  $unique=array('registerlist','taglist','savelist','personlist','formlist');
  unset($ret);
  foreach($toload as $tload) {
    foreach($xmlload->$tload as $list) {
      $i=0;
      foreach($list as $wname=>$vals) {
        $id=in_array($tload,$unique)?(string) $vals->attributes()->id:$i;
        foreach($vals->attributes() as $name=>$val) {
          $ret[$tload][$id][$name]=rawurldecode((string) $val);
        }
        $i++;
      }
    }
  }

  return $ret;
}

//Links for public pages
function link_out($option,$name) {
  return '<a href="'.URL.$option.'/">'.$name.'</a>';
}

//Searches an expression in the language file and returns the key.
function getlangkey($expr) {
  if(is_string($expr)) {
    global $la;
    $ret=array_search($expr,$la);
    if($ret===false) {
      $expr[0]=strtoupper($expr[0]);
      $ret=array_search($expr,$la);
    }
    if($ret===false) {
      $expr[0]=strtolower($expr[0]);
      $ret=array_search($expr,$la);
    }
    return $ret;
  } else { return false; }
}

//replaces keywords with global values to the content.xml files
function addglobals($string) {
  $replace=array('<<img>>','<<pname>>','<<guest>>','<<username>>','<<email>>');
  $with=array(URL.'/content/images',P_NAME,'?login=1&username=gast&password=1234',$_REQUEST['username'],$_REQUEST['email']);
  return str_replace($replace,$with,$string);
}

//gives back the key of an array matching a string
function array_isearch($str, $array) {
  if(isset($array[0]) && is_array($array)) {
    foreach($array as $k => $v) {
      if(strcasecmp($str, $v) == 0) return $k;
    }
  }	
  return NULL;
}

//gives back true if any value of the first array is also in the second one
function array_match($arraya, $arrayb) {
  if($arraya[0]!==NULL && $arrayb[0]!==NULL && is_array($arraya) && is_array($arrayb)) {
    foreach($arraya as $v) {
      if(in_array($v, $arrayb)) { return true; break; }
    }
  }	
  return false;
}

//recursive search of a string in a multidimesional array.
function array_search_r($str, $array) {
  if(is_array($array)) {
    $ret=0;
    foreach($array as $next) {
      if(array_search_r($str,$next)) { 
        $ret=1; 
        break; 
      }
    }
    return $ret;
  } else {
    return $str == $array;
  }
}

//Creates a scrollbar
function scrollbar($width, $action='') {
  echo '<div style="width: ',$width,'px; overflow-x: scroll;" onscroll="'.$action.'"><div style="width: ',$width+100,'px; height: 0px; color: transparent; visibility:hidden; font-size:1px">x</div></div>';
}

//escapes input for MySQL
function mres($input) {
  if(is_array($input)) {
    foreach($input as $tin) {
      $res[] = mres($tin);
    }
  } else {
    $res = mysql_real_escape_string($input);
  }
  return $res;
}

//gets the name of a type with specific id.
function get_name($type, $id) {
  $getname= request('get_'.$type, array($type.'id' => $id));
  return $getname['name'][0];
}

//load the global location parameters. creates an array like the global $here
function getglobal() {
  global $registerid,$groupid,$saveid,$tagid,$wordclassid,$searchid,$wordid,$withoutid;
  $ret=array('registerid'=>$registerid,
             'groupid'=>$groupid,
             'saveid'=>$saveid,
             'tagid'=>$tagid,
             'wordclassid'=>$wordclassid,
             'searchid'=>$searchid,
             'wordid'=>$wordid,
             'withoutid'=>$withoutid); //fix: get without tag or save
  return $ret;
}

//regular expression encoder for mysql
function regexpencode($string) {
  $searchstr=array('\\','(',')','*');
  $replace=array('\\\\','\\(','\\)','\\*');
  return mysql_real_escape_string(str_replace($searchstr,$replace,$string));
}

//flaten Array
function flat($array) {
  if(is_array($array)) {
    foreach($array as $key=>$val) {
      if(is_array($val)) { $ret[$key]=$val[0]; }
      else { $ret[$key]=$val; }
    }
  } else {
    $ret=false;
  }
  return $ret;
}

//Generate Password
function passgen($len) {
  $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
  $passw='';
  for($i=0; $i<$len; $i++) {
    $r=rand(0,61); //62-1 !!
    $passw .= substr($chars, $r, 1);
  }
  return $passw;
}

?>
