<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: form.php
//theme: default
//description: functions to create common form elements
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES :
//////////////////////////////

//////////
//FORMS
//////////
// functions should start with form_

function form_search() {
  $ret.='<form action="" id="searchform" name="searchform" method="POST">';
		$ret.='<input type="text" name="searchtext" id="searchtext" onkeydown="return block_keydown(event)" onkeyup="return action_search(event)" autocomplete="off">';
	$ret.='</form>';
  return $ret;
}
?>
