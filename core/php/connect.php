<?php
//Connect to Database
$connect=mysql_connect(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD);
if(!$connect) { echo 'MySQL-connection failed: '.mysql_error(); }
$db=mysql_select_db(MYSQL_DBNAME, $connect);
if(!$connect) { echo 'Database-connection failed: '.mysql_error(); }

//secure
$userid = mysql_real_escape_string($_SESSION['lk_userid']);
$pwdmd5 = mysql_real_escape_string($_SESSION['lk_userpwmd5']);

//User
$fquery="SELECT id,language FROM lk_user WHERE id='".$userid."' AND passw='".$pwdmd5."'";
$validuser=mysql_query($fquery);
if(!$validuser) { $errors.= '0000:'.mysql_error(); }
//secont part of if is for checking quest account
if(mysql_num_rows($validuser)==0 || ($userid==0 && GUEST==0)) { $errors='403 Forbidden'; $errorid=1; }

//Check language from database 
if(mysql_num_rows($validuser)>0) { 
  $db_lang = mysql_result($validuser,0,1);
}
?>
