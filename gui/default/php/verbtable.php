<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: verbtable.php
//theme: default
//description: functions for the verbtables view
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES : 
//////////////////////////////

//////////
//VERBTABLE
//////////
// functions should start with vt_

require_once( GUI.'php/wordtable.php' );

//write a line in the verbtable //also ajax
//i : index of line
//head: ['what'],['id']
//top: ['what'],['id'][],['count'] 
//['what']=verb/person/form
//verbs is an array like it comes back of get_verblist.
function ajax_writeverbline($i, $head, $top, $left, $verbs=NULL) {
  vt_line($i, $head, $top, $left, $verbs);
}
function vt_line($i, $head, $top, $left, $verbs=NULL) {
  echo '<tr id="tr_',$left['what'],'_',$left['id'][$i],'" class="',$left['what'],'_',$left['id'][$i],'_remove">';
  //makes editing person possible but not verbs.
  if($left['what']!='verb') {
    wt_entry($left['id'][$i], $left['name'][$i], $left['what'],NULL,'tabhead',array('show','hr','edit','delete'));
  } else {
  // uneditable output
    echo '<td class="tabhead" id="',$left['id'][$i],'">';
      echo $left['name'][$i];
    echo '</td>';
  }
  //all the verbforms
  for($j=0;$j<$top['count'];$j++) {
    if($head['what']=='verb') {
      $uidverb=$head['id']; $uidperson=$left['id'][$i]; $uidform=$top['id'][$j];
    } elseif($head['what']=='form') {
      $uidverb=$left['id'][$i]; $uidperson=$top['id'][$j]; $uidform=$head['id'];
    } elseif($head['what']=='person') {
      $uidverb=$left['id'][$i]; $uidperson=$head['id']; $uidform=$top['id'][$j];
    }
    if($verbs==NULL) { $tverb['name'] = ''; }
    else { $tverb=$verbs[$uidverb][$uidperson][$uidform]; }       
    wt_entry('v'.$uidverb.'_p'.$uidperson.'_f'.$uidform, $tverb['name'], 'kword', array($uidverb,$uidperson,$uidform),$top['what'].'_'.$top['id'][$j].'_remove');
  }
  echo '</tr>';
}
?>
