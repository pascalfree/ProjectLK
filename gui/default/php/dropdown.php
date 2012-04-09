<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: dropdown.php
//theme: default
//description: functions to create common dropdown elements
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES :
//////////////////////////////

//////////
//DROPDOWN
//////////
// functions should start with drop_

function drop_button($id,$type,$content=NULL,$where='""',$alt=NULL, $addclass='') {
  if($alt == NULL) { $alt = $id; }
  echo "<span id='drop_",$type,"_",$id,"' onclick='g_dropdown(\"",$type,"\",",json_encode($alt),",",json_encode($content),",",$where,");' onmouseout='hide_dropdown()' class='link icondrop icon drop_",$type," ",$addclass,"'></span>";
}

?>
