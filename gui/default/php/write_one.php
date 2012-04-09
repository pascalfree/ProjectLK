<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: write_one.php
//theme: default
//description: functions to write single Elements
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES :
//////////////////////////////

//////////
//WRITE ONE
//////////
// functions should start with write_one_

require_once( GUI.'php/dropdown.php' );
require_once( GUI.'php/link.php' );

//////////
//OPTION
//Helper
function write_one_option($list) {
  foreach( $list as $element ) {
    echo "<li>";
      echo $element;
    echo "</li>";
  }
}

//////////
//REGISTER

//write one (or more) register to a list //also ajax
//helper
function ajax_write_one_register($l) { write_one_register($l); }
function write_one_register($list) {
  global $la;
  //force arrays
  if( !is_array($list['id']) ) {  $list['id']=array(  $list['id'] ); }
  if( !is_array($list['name']) ) { $list['name']=array( $list['name'] ); }

  for( $i=0; $i<$list['count']; $i++ ):
    $id = & $list['id'][$i];
    $cr = & $list['registercount'][$i];

    if( !$cr ) { $cr=0; }

    echo '<li class="register_',$id,'_remove event_hidelast">';
    echo '<span class="link" onclick="do_action(\''.$id.'\',\'register\',\'goto\')">';
      echo '<span id="register_'.$id.'" class="register_',$id,'">'.$list['name'][$i].'</span> ';
      echo '<span class="counters">('.$cr.')</span>';
    echo '</span>';
      //options
      echo '<span id="register_', $id ,'_options" class="hidden options">';
        //Create a Dropdown
        drop_button($id,'register',array('rename','options','delete'),'Element.up(this).previous()');
        //direct buttons
        if($cr != 0) {
          echo link_generic('action=query&type=register&id='.$id.'&class=icon iconplay&title=query');
        }
        //option to add word 
        echo link_generic('action=addword&type=register&id='.$id.'&class=icon iconplus&title=add');
        //link to show words directly - if there are any
        if($cr != 0) {
          echo ' <span class="link showlink" onclick="do_action(\''.$id.'\',\'register\',\'show\')">',$la['show'],'</span>';
        }
        //link to show verblist directly  
        $verbnum = request('get_verblist',array('select'=>'COUNT(DISTINCT t1.id)', 'registerid' => $list['id'][$i]));

        if($verbnum['COUNT(DISTINCT t1.id)'][0]!=0) {
          echo ' <span class="link showlink" onclick="do_action(\''.$id.'\',\'register\',\'verb\')">',$la['verb'],'</span>';
        }
      echo '</span>';
    echo "</li>";
  endfor;
}

//////////
//TAG

//helper
//write one (or more) tag span //also ajax
//tagid and tagname may be arrays with same length
function ajax_write_one_tag($wordid, $tagid, $tagname) {
  write_one_tag($wordid, $tagid, $tagname);
}
function write_one_tag($wordid, $tagid, $tagname) {
  global $la;
  //check if empty
  if( empty($tagid) || empty($tagname) ) { return false; }
  //if not array make array
  if(!is_array($tagid)) { $tagid=array($tagid); }
  if(!is_array($tagname)) { $tagname=array($tagname); }
  //error if tagid and tagname have different length
  if(count($tagid) != count($tagname)) { return 'Error [write_one_tag]:must have as many tagid as tagname'; }
  //else loop through and print
  $count=count($tagid);
  for($i=0; $i<$count; $i++) {
    echo '<span id="tag_span_',$wordid,'_',$tagid[$i],'" class="link tag_',$wordid,'_',$tagid[$i],'_remove event_hidelast" title="', $la['show'] ,'">';
    //not adding link directly, so don't have to pass a parameter (ajax)
    echo '<span onclick="do_action(\'',$tagid[$i],'\',\'tag\',\'goto\')">',$tagname[$i],'</span>';
    echo '<span class="link iconx icon hidden" onclick="do_action([\'',$wordid,'\',\'',$tagid[$i],'\'],\'tag\',\'delete\')" title="', $la['remove'] ,'"></span>';
    echo '</span> '; 
  }
}

//////////
//FORM
//helper
function ajax_write_one_form($l) { write_one_form($l); }
function write_one_form($list) {
  for( $i=0; $i<$list['count']; $i++ ):
    $id = & $list['id'][$i];
    echo '<li id="form_'.$id.'" class="form_'.$id.'_remove event_hidelast">';

    echo '<input type="checkbox" value="'.$id.'" name="formid[]" id="check_form_'.$id.'" title="'.$list['info'][$i].'">'; 

    echo '<span class="icongrab"></span><span id="form_'.$id.'_edit" class="link form_'.$id.'" onclick="do_action(\''.$id.'\', \'form\', \'goto\')">';
      echo $list['name'][$i]; 
    echo '</span>'; 

    drop_button($id,'form',array('rename','options','delete'),'Element.previous(this)');
    echo '</li>';
  endfor;
}

//////////
//PERSON

//helper
//write one line of person //also ajax
function ajax_write_one_person($l) { write_one_person($l); }
function write_one_person($list) {

  for( $i=0; $i<$list['count']; $i++ ):
    $id = & $list['id'][$i];

    echo '<li id="person_'.$id.'" class="person_'.$id.'_remove event_hidelast" onmousedown="rep_start('.$id.')" onmouseup="rep_end()" onmouseover="rep_change('.$id.')">';
    echo '<input type="checkbox" value="'.$id.'" name="personid[]" id="check_person_'.$id.'">';
    echo '<span class="icongrab icon"></span><span id="person_'.$id.'_edit" class="link person_'.$id.'" onclick="do_action(\''.$id.'\', \'person\', \'goto\')">';
      echo $list['name'][$i];
    echo '</span>'; 
    drop_button($id,'person',array('rename','delete'),'Element.previous(this)');
    echo '</li>';
  endfor;
}

?>
