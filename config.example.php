<?php

//Setup
define("MYSQL_DBNAME", 'projectlk_db');                 //Name of MySQL Database
define("MYSQL_USERNAME", 'projectlk');                  //MySQL username
define("MYSQL_PASSWORD", 'password');                   //MySQL password
define("MYSQL_HOST", 'localhost');                      //should work for most DB

define("DIRNAME", 'projectlk1.2/');  //with trailing /  //name of directory/ies

//options
define("ADMIN_MAIL", 'admin@email.com');                //Define Admins emailadress
define("GUEST", 1);                                     //Allow live demo

//Default values
define("DEFAULT_LANG", 'en');                           //default language (must be installed)
define("DEFAULT_GUI", 'default');                       //default gui
define("DEFAULT_STYLE", 'light');                       //default theme

//Don't change, if you don't know what you do.
define("HOST", 'http://'.$_SERVER['SERVER_NAME'].'/');
define("DBFUNCTIONS", 'core/database/');
define("URL", HOST.DIRNAME);                            
define("ABSPATH", dirname(__FILE__));
define("UPDIR", 'upload/');
define("RELEASE", 0);
define("DEBUG", 1);

?>
