<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: dbfunctions.php
//core
//description: global functions that are also availible in the databasefunction files.
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

///////////
// Request
// Error handler
///////////

////////////
//REQUEST
//Loads information from DB
function request($function,$params=NULL) {

  //Parse Params
  if(is_array($params)) {
    foreach($params as $name => $val) { 
      $$name=mres($val);
    }
  }

  //userid & pwd
  $userid = mres($_SESSION['lk_userid']);
  $pwdmd5 = mres($_SESSION['lk_userpwmd5']);

  $go=new managerr($function,$params); //Start the error handler
  if(file_exists(DBFUNCTIONS.$function.'.php')) {  //Load requested function
    try {
      require(DBFUNCTIONS.$function.'.php');
    } catch (Exception $e) {
      echo "Error in ",$function,": ",$e -> getMessage();
    }
  } else {
    $go->error('101','Function not found: '.DBFUNCTIONS.$function.'.php');  //Oh no
  } 
  if(DEBUG==1 && $_REQUEST['req']!=1) { $go -> debug(); }  //printing Error as text
  $return=$go->geterr($return);  //Adding the Error Information to the return variable.
  return $return;
}

////////////
//Class: Error handler
class managerr{
  private $errname='';
  private $errnum=0;
  private $sqlnum=0;

  //Constructor: with functionname and params
  function __construct($function,$params=NULL) {
    $this->errloc=$function;
    if(is_array($params)) {
      foreach($params as $name => $val) { 
        if($val!=NULL AND $val!='') {
          $this->params[$name]=1;
        }
      }
    }
  }

  //define the Errorcode of a missing variable
  private function getmisscode($varname) {
    switch($varname) {
      case 'registerid': return 201; break;
      case 'groupid': return 202; break;
      case 'saveid': return 203; break;
      case 'tagid': return 204; break;
      case 'wordclassid': return 205; break;
      case 'searchid': return 206; break;
      case 'formid': return 207; break;
      case 'personid': return 208; break;
      case 'wordid': return 209; break;
      case 'verbid': return 210; break;
      case 'newregister': return 221; break;
      case 'newgroup': return 222; break;
      case 'newsave': return 223; break;
      case 'newtag': return 224; break;
      case 'newwordclass': return 225; break;
      case 'newsearch': return 226; break;
      case 'newform': return 227; break;
      case 'newperson': return 228; break;
      case 'newword': return 229; break;
      case 'newverb': return 230; break;
      case 'newwordfirst': return 231; break;
      case 'newwordfore': return 232; break;
      case 'newsentence': return 233; break;
      case 'newpassword': return 234; break;
      case 'password': return 234; break;
      case 'oldpassword': return 235; break;
      case 'passwordrepeat': return 236; break;
      case 'username': return 237; break;
      case 'email': return 238; break;
      default: return 200; break;
    }    
  }

  //Checks for necessary parameters. Give Error if missing.
  public function necessary() {
    $params=func_get_args();
    foreach($params as $val) { 
      if(is_array($val)) { //OR-function. one of some parameters is necessary
        foreach($val as $orval) { 
          global $$orval;   
          $isgiven=0;
          //First is for javascript and second for php requests!!
          if(($$orval!=NULL AND $$orval!='') OR $this->params[$orval]==1 ) { 
            $isgiven=1; break;
          }    
        }
        if($isgiven==0) {
          $this->errname='Missing: '.implode(' or ',$val);
          $this->errnum=200;
          break;
        }
      } else {  //normal case
        global $$val;
        //First is for javascript and second for php requests!!
        if(($$val==NULL OR $$val=='') AND $this->params[$val]!=1 ) { 
          $this->errname='Missing: '.$val;
          $this->errnum=$this->getmisscode($val);
          break;
        }
      }
    }    
  }

  //returns true if no error occured yet.
  public function good() {
    if($this->errnum==0) { return 1; } else { return 0; }
  }

  //does a mysql query. errnumber is to specify a query, making debuging easier.
  public function query($querystr,$errnumber=0) {
    $answer=NULL;
    $this->lastquery=$querystr; //Save last query, for debuging
    $result=mysql_query($querystr);  // Do MySQL query
    if(!$result) {
      if(mysql_errno()==1062) { $this->error(104); } //Duplicate Entry?
      else {  //Other Errors
        $this->errname='MySQL: '. mysql_error(); 
        $this->errnum=300+($errnumber%100);
        $this->sqlnum=mysql_errno();
      }
      $answer=false;
    } else {  //No Error: Save Result in an Array
      $answer['id']=mysql_insert_id();  //gets Id
      $answer['count']=mysql_affected_rows();  //Count entries like this
      if(!$answer['count']) { @$answer['count']=mysql_num_rows($result); } //Or this way
      if(is_resource($result)) {
        if($answer['count']>0) { //Only do this, if there is something
          while($values= mysql_fetch_array($result,MYSQL_ASSOC)) { //Fetch and write
            foreach($values as $key=>$val) {
              $answer['result'][$key][]=$val;
            }        
          }
        }
      }
    }
    return $answer;
  }

  //adds a custom error with errorcode ($num) and custom or default errormessage ($name)
  public function error($num, $name=NULL) {
    $this->errnum=$num;
    switch($num) {
      case 100: $this->errname='No Permission.'; break;
      case 101: $this->errname='Function Not Found.'; break;
      case 102: $this->errname='Entry Not Found.'; break;
      case 103: $this->errname='Entry Not Created.'; break;
      case 104: $this->errname='Duplicate Entry.'; break;
      case 105: $this->errname='File Not Found.'; break;
      case 106: $this->errname='Not Matching Passwords.'; break;
      case 107: $this->errname='User has no email.'; break;
      case 108: $this->errname='E-mail not sent.'; break;
      case 109: $this->errname='Invalid JSON.'; break;
      default: $this->errname=$name; break;
    }
  }

  //Define a missing parameter error.
  public function missing($varname) {
    $this->errname='Missing: '.$varname;
    $this->errnum=$this->getmisscode($varname);    
  }

  //Put out the error info as an array
  public function geterr($ret=NULL) {
    $ret['errnum']=$this->errnum;
    if($this->errnum!=0) {
      $ret['errname']=$this->errname;
      $ret['lastquery']=$this->lastquery;
      if($this->sqlnum!=0) { $ret['sqlnum']=$this->sqlnum; }
      $ret['errloc']=$this->errloc;
    }
    return $ret;
  }

  //Put out the error info as text (for debuging in php)  
  public function debug() {
    if($this->errnum!=0) {
      echo "DEBUG ERROR @ ",$this->errloc,'-',$this->errnum,':',$this->errname,'|',$this->lastquery;
    }
  }

}
?>
