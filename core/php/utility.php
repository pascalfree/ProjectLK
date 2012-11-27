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
function plk_util_getLanguages($prefix='') {
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
function plk_util_getGui() {
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
function plk_util_getThemes() {
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
function plk_util_getImport($file,$full=0) {
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

//Searches an expression in the language file and returns the key.
function plk_util_getLangKey($expr) {
  if(is_string($expr)) {
    global $plk_la;
    $ret=array_search($expr,$plk_la);
    if($ret===false) {
      $expr[0]=strtoupper($expr[0]);
      $ret=array_search($expr,$plk_la);
    }
    if($ret===false) {
      $expr[0]=strtolower($expr[0]);
      $ret=array_search($expr,$plk_la);
    }
    return $ret;
  } else { return false; }
}

//replaces keywords with global values to the content.xml files
function plk_util_addGlobals($string) {
  $replace=array('<<img>>','<<pname>>','<<guest>>','<<username>>','<<email>>');
  $with=array(URL.'/content/images',P_NAME,'?login=1&username=gast&password=1234',$_REQUEST['username'],$_REQUEST['email']);
  return str_replace($replace,$with,$string);
}

//returns the key of an array matching a string (case-insensitive)
function plk_util_iSearchArray($str, $array) {
  if(isset($array[0]) && is_array($array)) {
    foreach($array as $k => $v) {
      if(strcasecmp($str, $v) == 0) return $k;
    }
  }	
  return NULL;
}

//returns true if any value of the first array is also in the second one
function plk_util_matchArray($arraya, $arrayb) {
  if($arraya[0]!==NULL && $arrayb[0]!==NULL && is_array($arraya) && is_array($arrayb)) {
    foreach($arraya as $v) {
      if(in_array($v, $arrayb)) { return true; break; }
    }
  }	
  return false;
}

//recursive search of a string in a multidimesional array.
function plk_util_rSearchArray($str, $array) {
  if(is_array($array)) {
    $ret=0;
    foreach($array as $next) {
      if(plk_util_rSearchArray($str,$next)) { 
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
function plk_util_scrollbar($width, $action='') {
  echo '<div style="width: ',$width,'px; overflow-x: scroll;" onscroll="'.$action.'"><div style="width: ',$width+100,'px; height: 0px; color: transparent; visibility:hidden; font-size:1px">x</div></div>';
}

//escapes input for MySQL
// makes mysql_real_escape_string  recursive and shorter
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

// returns the name of a type with specific id.
function plk_util_getName($type, $id) {
  $getname= plk_request('get_'.$type, array($type.'id' => $id));
  return $getname['name'][0];
}

//load the global location parameters. creates an array like the global $here
function plk_util_getGlobal( $pre = '' ) {
  global  ${$pre.'registerid'},
          ${$pre.'groupid'},
          ${$pre.'saveid'},
          ${$pre.'tagid'},
          ${$pre.'wordclassid'},
          ${$pre.'searchid'},
          ${$pre.'wordid'},
          ${$pre.'withoutid'};
  $ret=array('registerid' => ${$pre.'registerid'},
             'groupid' => ${$pre.'groupid'},
             'saveid' => ${$pre.'saveid'},
             'tagid' => ${$pre.'tagid'},
             'wordclassid' => ${$pre.'wordclassid'},
             'searchid' => ${$pre.'searchid'},
             'wordid' => ${$pre.'wordid'},
             'withoutid' => ${$pre.'withoutid'}); //fix: get without tag or save
  return $ret;
}

//regular expression encoder for mysql
function plk_util_regExpEncode($string) {
  $searchstr=array('\\','(',')','*');
  $replace=array('\\\\','\\(','\\)','\\*');
  return mysql_real_escape_string(str_replace($searchstr,$replace,$string));
}

//flaten Array
function plk_util_flat($array) {
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
function plk_util_passGen($len) {
  $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
  $passw='';
  for($i=0; $i<$len; $i++) {
    $r=rand(0,61); //62-1 !!
    $passw .= substr($chars, $r, 1);
  }
  return $passw;
}

//trim with parameter by reference //for array_walk!
function plk_util_trim(&$value)
{
 $value = trim($value);
}

// get comma separated, trimmed values
function plk_util_commaArray( $string ) {
  $arr = explode(",", $string);
  array_walk( $arr, 'plk_util_trim' );
  return $arr;
}

// remove forbidden characters
function plk_util_removeForbidden( &$string, $forbidden = array('\\"', '#', '+') ) {
	$string = str_replace($forbidden, '', $string);
  return $string; //needed to walk array
}

// if parameter isn't an array make a single value array of it.
function plk_util_makeArray( &$param ) {
  if ( !is_array( $param ) ) {
    $param = array(
       $param
    );
    return 1;
  }
  return 0;
}

// loads wordids assuming the location is defined by $arg_.. variables
// returns wordids as array.
function plk_util_loadWordId( &$go ) {
  $params              = plk_util_getGlobal( 'arg_' );
  $params[ 'nolimit' ] = 1;
  $params[ 'select' ]  = 'id';
  $params[ 'wordid' ]  = NULL;
  $getwordid           = plk_request( 'get_word', $params );

  if ( 0 != $getwordid[ 'errnum' ] ) {
    $go->error( 400, $getwordid[ 'errnum' ] . ': ' . $getwordid[ 'errname' ] );
  }

  if ( 0 == $getwordid['count'] ) {
    $go->missing( 'wordid' );
  }

  return $getwordid[ 'id' ];
}

?>
