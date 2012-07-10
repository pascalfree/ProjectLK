<?php
//////////////////////////////////////
/* NAME: user_logout
/* DESCRIPTION: logout current user
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 14.04.2012
////////////////////////////////////*/

session_start();
unset( $_SESSION['lk_username'] );
unset( $_SESSION['lk_userpwmd5'] );
unset( $_SESSION['lk_userid'] );
?>
