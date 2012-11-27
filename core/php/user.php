<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: user.php
//core
//description: Class for the $plk_you object (current user enviroment)
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//USER CLASS
//////////

class user{
  public $name = NULL;
  public $id = NULL;
    //Information about interface
  public $gui = DEFAULT_GUI;
  public $style = DEFAULT_STYLE;
  public $language = LANG;
  public $hints = 0;
    //Status
  public $statuscount = 0;
    //agent information
  public $browser = NULL;
  public $mobile = 0;

  //Constructor
  function __construct() {
    $this -> check_browser();
    $this -> check_mobile();
    $this -> verify_user();

    //load user settings (lang, gui, style)
    if( $this -> id != NULL ) { //fix: only if logged in
      $this -> getusersettings();
    }
    define("GUI", 'gui/'. $this -> gui .'/');
  }

  //Checks which browser is used
  private function check_browser() {  
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if    (strpos($agent, 'Firefox'))    { $ret = 'ff'; }
    elseif(strpos($agent, 'Chrome' ))    { $ret = 'gc'; }
    elseif(strpos($agent, 'MSIE'   ))    { $ret = 'ie'; }
    elseif(strpos($agent, 'Safari' ))    { $ret = 'sa'; }
    elseif(strpos($agent, 'Opera'  ))    { $ret = 'op'; }
    elseif(strpos($agent, 'SeaMonkey'))  { $ret = 'sm'; }
    else                                 { $ret = NULL; }
    $this -> browser = $ret;
  }

  //Checks if mobile device is used
  private function check_mobile() {  
    $mobiledev = array(
		  '2.0 MMP',
		  '240x320',
		  '400X240',
		  'AvantGo',
		  'BlackBerry',
		  'Blazer',
		  'Cellphone',
		  'Danger',
		  'DoCoMo',
		  'Elaine/3.0',
		  'EudoraWeb',
		  'Googlebot-Mobile',
		  'hiptop',
		  'IEMobile',
		  'KYOCERA/WX310K',
		  'LG/U990',
		  'MIDP-2.',
		  'MMEF20',
		  'MOT-V',
		  'NetFront',
		  'Newt',
		  'Nintendo Wii',
		  'Nitro', // Nintendo DS
		  'Nokia',
		  'Opera Mini',
		  'Palm',
		  'PlayStation Portable',
		  'portalmmm',
		  'Proxinet',
		  'ProxiNet',
		  'SHARP-TQ-GX10',
		  'SHG-i900',
		  'Small',
		  'SonyEricsson',
		  'Symbian OS',
		  'SymbianOS',
		  'TS21i-10',
		  'UP.Browser',
		  'UP.Link',
		  'Windows CE',
		  'WinWAP',
		  'YahooSeeker/M1A1-R2D2',
		  'iPhone',
		  'iPod',
		  'Android',
		  'BlackBerry9530',
		  'LG-TU915 Obigo', // LG touch browser
		  'LGE VX'
    );
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $ret = 0;
    foreach( $mobiledev as $mdev ) {
      if( strpos($agent, $mdev) ) { $ret = 1; break; }
    }
    $this -> mobile = $ret;
  }

  //verification of user via Session variable
  private function verify_user() {
    $query = "SELECT id FROM lk_user WHERE name='".mres($_SESSION['lk_username'])."' AND passw='".mres($_SESSION['lk_userpwmd5'])."' AND id='".mres($_SESSION['lk_userid'])."' ";
    $ver_user = mysql_query($query);
    if(!$ver_user) { echo mysql_error(); }
    $existing = mysql_num_rows($ver_user);

    //second part of 'if' is for checking guestaccount
    if($existing && ($_SESSION['lk_userid']!=0 || GUEST!=0)) {
      while($ids = mysql_fetch_array($ver_user)) {
			  $this -> id = $ids['id'];
		  }
      $this -> name = $_SESSION['lk_username'];
    }
  }

  private function verify_gui($gui) {
    $tdir=opendir('./gui/');
    while (($tfile = readdir($tdir)) !== false) {
      if(!is_dir($tfile)) {
        if($gui==$tfile) {
          $this->gui = $gui; 
          break; 
        }
      }
    }
  }
  private function verify_style($style) {
    $tdir=opendir('./gui/'.$this->gui.'/theme/');
    while (($tfile = readdir($tdir)) !== false) {
      if(!is_file($tfile)) {
        if(($style.'.css')==$tfile) {
          $this->style = $style; 
          break; 
        }
      }
    }
  }

  //Gets User Settings from Database
  private function getusersettings() {
    $query = "SELECT id, gui, theme, hints FROM lk_user WHERE id='". mres($_SESSION['lk_userid']) ."' ";
    $get_gui = mysql_query( $query );
    if( !$get_gui ) { echo mysql_error(); }
    while( $tgui = mysql_fetch_array( $get_gui ) ) {
      $this -> verify_gui( $tgui['gui'] );
      $this -> verify_style( $tgui['theme'] );   
      $this -> hints = $tgui['hints'];
    }
  }

  //print out all variables of this class. (for debugging)
  public function __toString() {
    return print_r( $this, true );
  }

};

?>
