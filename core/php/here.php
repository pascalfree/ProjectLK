<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: here.php
//core
//description: Class for the $here object (current url location)
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//HERE CLASS
//////////

//store current page information and does all redirecting
class place{
  public $url;
  public $login = NULL;
  public $nregerr = NULL;
  public $forgot = NULL;
    //Information about location
  public $registerid = NULL;
  public $groupid = NULL;
  public $tagid = NULL;
  public $saveid = NULL;
  public $wordclassid = NULL;
  public $wordid = NULL;
  public $timerange = NULL; //timerange for words
  public $formid = NULL;  
  public $personid = NULL;
  public $withoutid = NULL;
  public $queryid = NULL;
  public $searchid = NULL;
  public $searchtext = NULL;
  public $keyoption = NULL;
  public $page = NULL;

  //user object
  private $user;
    
  //Private
  const maxlevel = 10; //maximal depth of path
  //keywords with verification
  private $keyver=array('tag','save','group','form','person','word');
  //defining keywords
  private $keyone=array('settings','show', 'overview', 'add', 'edit', 'verb', 'taglist','export','import','help');
  //second keywords
  private $keytwo=array('tag', 'search', 'save', 'word', 'edited', 'group', 'query', 'person', 'form','wordclass','verbquery','without');

  //Constructor
  function __construct($url) {
    $this -> url = $url;
  }

  //append user information
  function load_user($user) {
    $this -> user = $user;    
  }

  //Checks if content is available
  private function is_content($name) {
    if(file_exists('./content/'.$this -> user -> language.'/'.$name.'.xml')) { return true; } 
    else { return false; }
  }

  //Verfications //[TODO]: Improvements in readability are possible here.
  private function verify_register($reg) {
    $query="SELECT id,name FROM lk_registers WHERE (name='".$reg."' OR id='".$reg."') AND userid='".mres($_SESSION['lk_userid'])."' ";
    $ver_reg=mysql_query($query);
    if(!$ver_reg) { echo mysql_error(); }
    $existing= mysql_num_rows($ver_reg);
    if($existing) {
      while($ids =mysql_fetch_array($ver_reg)) {
			  $this->registerid=$ids['id'];
		  }
      $this->page='register';
    }
  }
  private function verify_person($person) {
    $query="SELECT id,name FROM lk_persons WHERE (name='".$person."' OR id='".$person."') AND userid='".mres($_SESSION['lk_userid'])."' ";
    $ver_person=mysql_query($query);
    if(!$ver_person) { echo mysql_error(); }
    $existing= mysql_num_rows($ver_person);
    if($existing) {
      while($ids =mysql_fetch_array($ver_person)) {
			  $this->personid=$ids['id'];
		  }
      $this->page='person';
    }
  }
  private function verify_form($form) {
    $query="SELECT id,name FROM lk_forms WHERE (name='".$form."' OR id='".$form."') AND userid='".mres($_SESSION['lk_userid'])."' ";
    $ver_form=mysql_query($query);
    if(!$ver_form) { echo mysql_error(); }
    $existing= mysql_num_rows($ver_form);
    if($existing) {
      while($ids =mysql_fetch_array($ver_form)) {
			  $this->formid=$ids['id'];
		  }
      $this->page='form';
    }
  }
  private function verify_group($group) {
    if($group=='af' or $group=='ar') { 
      $this->groupid=$group; 
      $this->page='group';
    } elseif(is_numeric($group)) {
      $query="SELECT groupcount FROM lk_registers WHERE id='".$this->registerid."' AND userid='".mres($_SESSION['lk_userid'])."' ";
      $ver_group=mysql_query($query);
      if(!$ver_group) { echo mysql_error(); }
      $existing= mysql_num_rows($ver_group);
      if($existing) {
        if($group<=mysql_result($ver_group,0)) {
          $this->groupid=$group;
          $this->page='group';
        }
      }
    }
  }
  private function verify_word($word) {
    $query="SELECT id,registerid FROM lk_words WHERE id='".$word."' AND userid='".mres($_SESSION['lk_userid'])."' ";
    if($this->registerid != NULL) { $query.= "AND registerid='".$this->registerid."'"; }
    $ver_word=mysql_query($query);
    if(!$ver_word) { echo mysql_error(); }
    if(mysql_num_rows($ver_word)) {
      $this->verify_register(mysql_result($ver_word,0,1));
      $this->wordid=$word;
      $this->page='word'; 
    }
  }
  private function verify_save($save) {
    $query="SELECT id,name FROM lk_savelist WHERE (name='".$save."' OR id='".$save."') AND registerid='".$this->registerid."' AND userid='".mres($_SESSION['lk_userid'])."' ";
    $ver_save=mysql_query($query);
    if(!$ver_save) { echo mysql_error(); }
    $existing= mysql_num_rows($ver_save);
    if($existing) {
      $this->saveid=mysql_result($ver_save,0,0);
      $this->page='save';
    }
  }
  function verify_tag($tag) {
    $query="SELECT id,name FROM lk_taglist WHERE (name='".$tag."' OR id='".$tag."') AND registerid='".$this->registerid."' AND userid='".mres($_SESSION['lk_userid'])."' ";
    $ver_tag=mysql_query($query);
    if(!$ver_tag) { echo mysql_error(); }
    $existing= mysql_num_rows($ver_tag);
    if($existing) {
      $this->tagid=mysql_result($ver_tag,0,0);
      $this->page='tag';
    }
  }

  private function verify($what,$with) {
    $func = 'verify_'.$what;
    $this -> $func( $with );
  }

  //Loads keys with parameters
  //uarr are the parts of the url as an array
  private function loadseckeys($uarr) {
    foreach($this->keytwo as $tkeys) {
      $key = array_search($tkeys, $uarr);
      if($key != false) { 
        //key with verification
        if( in_array($tkeys, $this->keyver) ) { 
          $this->verify($tkeys, mres($uarr[$key+1]));
        //other keys
        } else {
          $this->{$tkeys.'id'} = htmlspecialchars(utf8_encode(urldecode($uarr[$key+1]))); //encoding necessary for searching
          $this->page = $tkeys;
        }
      }
    }
  }

  //catches a language from the URL ( /lang )
  //This function is especially for logged out users!
  /*
  private function catchlanguage() {
    $last=explode('/', $this->url);
    if($last[count($last)-2]=='lang') { //Fix: otherwise makes errors with tag named en or de
      $lasts=$last[count($last)-1];
      if(file_exists('./language/'.$lasts.'.js') || 
         file_exists('./language/'.$lasts.'.php') || 
         file_exists('./language/'.$lasts.'.ini')) 
      {
         $_SESSION['lk_language'] = $lasts;
         unset($last[count($last)-1]);
         $this->url=implode('/',$last);
      }
    }
  }*/

  //Load all the key from URL
  public function getkeys() {
    //split url
    $url_arr = explode('/', $this->url);

    if(DIRNAME != '') 
    { //set firstkey as the key after the directory of projectlk
      // count Elements in DIRNAME
      $firstkey = count(explode('/', trim( DIRNAME ,'/') ));
      //*$firstkey = array_search( DIRNAME, $url_arr )+1; 
    } 
    else 
    { 
      $firstkey = 0; 
    }

    $lastkey = count($url_arr) - 1;  //index of last key
    $keycount = $lastkey - $firstkey + 1; //number of keys

    //check if a user is logged in
    if( $this -> user -> id !== NULL  ) 
    {
      $this -> page = 'dashboard'; //default page for logged in user
    }
    //check if second key is a register
    if($keycount >= 2) 
    { 
      $this -> verify_register( $url_arr[$firstkey + 1] ); 
    }
    //check if third key is a group
    if($keycount >= 3 and $this -> registerid != NULL) 
    { 
      $this -> verify_group($url_arr[$firstkey+2]); 
    }
    //get all other keys
    $this -> loadseckeys($url_arr);

    //define Page
    if($firstkey > $lastkey) 
    { //no keywords in url
      $this -> page = 'home'; 
    } 
    elseif( $this -> is_content($url_arr[$lastkey]) ) 
    {
      $this -> page = $url_arr[$lastkey];  //defining keyword in url
    } 
    elseif($this -> user -> id != NULL) //only logged in users
    {  
      if( in_array($url_arr[$lastkey], $this -> keyone) ) 
      {
        $this -> page = $url_arr[$lastkey];  //defining keyword in url?
      }
    } 
    else //user wants to be logged in but isn't
    { 
      $this -> page = 'login'; 
    }

    //help page is for all
    if( $url_arr[$lastkey] == 'help' ) { 
      $this -> page = 'help';
      $this -> user -> hints = 0;
    }   

    //define keyoption
    if( in_array( $this -> page, $this -> keyone) || $this -> is_content( $this -> page) ) { 
      $this -> keyoption = $this -> page; 
    } 
  }


  //Include Header/Footer or don't
  public function getheader() {
    if(file_exists('./gui/'.$this -> user -> gui.'/header.php') && $this -> page!='export') 
    { 
      return './gui/'. $this -> user -> gui .'/header.php'; 
    } 
    else 
    { 
      return false; 
    }
  }
  public function getfooter() {
    if(file_exists('./gui/'.$this -> user -> gui.'/footer.php') && $this -> page!='export')
    { 
      return './gui/'.$this -> user -> gui.'/footer.php'; 
    }
    else 
    { 
      return false; 
    }
  }

  //Now load the page
  public function loadpage() {
    //no page found (NULL -> 404)
    if($this -> page == NULL) 
    { 
      $this -> page == '404'; 
    }
    //export is special
    if($this -> page == 'export') 
    { 
      return './core/php/export.php'; 
    }
    //if content, load content.php
    elseif( $this -> is_content( $this -> page ) ) 
    { 
      return './gui/'.$this -> user -> gui.'/content.php'; 
    }
    //try to load page in gui
    elseif( file_exists('./gui/'.$this -> user -> gui.'/'.$this->page.'.php') ) 
    { 
      return './gui/'. $this -> user -> gui .'/'. $this -> page .'.php'; 
    }
    //try to load 404 page in gui
    elseif( file_exists('./gui/'. $this -> user -> gui .'/404.php') ) 
    { 
      return './gui/'. $this -> user -> gui .'/404.php'; 
    }
    //try to load any 404 page
    elseif( file_exists('./content/404.php') ) 
    {
      return './content/404.php'; 
    }
    elseif(file_exists('./content/404.html')) 
    { 
      return './content/404.html'; 
    }
    else
    { 
      echo '404 - Page not Found'; 
      return false; 
    }
  }


  //Utility
  //returns a Path of the current location
  public function path($level = NULL) {
    unset($path);
    //define the content of the path in right order.
    $pathparams = array(
      'type' => array('','registerid','groupid','saveid', 'tagid','wordclassid','wordid','personid','formid','searchid','queryid','keyoption'), 
      'prefix'=> array('','','','save/','tag/','wordclass/','word/','person/','form/','search/','query/','')
    );

    //default level //maxlevel
    if($level === NULL) {
      $level = count( $pathparams['type'] );
    }

    //get username
    if( 0 < $level ) {
      $path[] = $this -> user -> name .'/';
    }
    //go through and fill
    for ( $i = 1; $i < $level; $i++) {
      if( $this -> $pathparams['type'][$i] !== NULL) { 
        $path[] = $pathparams['prefix'][$i] . $this -> $pathparams['type'][$i].'/'; 
      }
    }
    return URL . implode('', $path);
  }

  //print out all variables of this class. (for debugging)
  public function __toString() {
    return print_r( $this, true );
  }
}

?>
