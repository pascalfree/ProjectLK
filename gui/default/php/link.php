<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: link.php
//theme: default
//description: functions to write common links in html
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//LINKS
//////////
// functions should start with link_

require_once( GUI.'php/utility.php' );
require_once( GUI.'php/list.php' );

//Creates a generic link with some parameters
//p: class, title, id, type, where, action, text
function link_generic($p) {
  global $la;
  //parse the parameters
  $p = u_param($p);

  if( empty($p['where']) ) { $p['where'] = "''"; } //syntax error otherwise
  if( empty($p['id']) ) { $p['id'] = "''"; }
  if( empty($p['icon']) ) { $icon = ""; }
  else { 
    $p['class'] .= ' icon_text';
    $icon = '<span class="icon icon'.$p['icon'].'"></span>'; 
  } 
  //print the link
  return '<span class="'.$p['class'].' link" title="'.$la[$p['title']].'" onclick="do_action('.$p['id'].',\''.$p['type'].'\',\''.$p['action'].'\','.$p['where'].')">'.$icon.$p['text'].'</span>';
}

//Creates a link to the help page
function link_help($anchor, $force = 0, $text = '') {
  global $you;
  if($you -> hints==1 || $force==1) {
    return '<span class="link helplink h_'.$anchor.'" onmouseover="help_load(\''.$anchor.'\')" onclick="help_toggle();">'.$text.'</span>';
  }
}

//Create a link to remove filter
function link_showall($type, $text='') {
    global $la;
    return '<span class="link iconx icon" title="'. $la['removefrompath'] .'" onclick="do_action(null,\''. $type .'\',\'goto\')">'.$text.'</span>';  
}

//Creates a link to export
function link_export() {
  global $la;
  return ' <span class="link" onclick="do_action(0,0,\'export\')">'.$la['export'].'</span>';
}

//Creates a link to load a query to a savepoint
function link_querysave($text,$wrong) {
  global $la;
  $add=$wrong==1?'-w ':'';
  $ret=' <span class="link -m4 '.$add.'query_options block" onclick="querysave(\''.$wrong.'\')" >'.$la[$text].' '.link_help('querysave').'</span> ';
  return $ret;
}

//Button to show options to edit multiple words
function link_more_options($show) {
  global $la;
  //second this is to make the dropdown appear directly under this element
  //show only in tab_show
  if($show != 1) { $show='style= "display: none"'; } else { $show=''; }
  return '<span class="link intab_show dropdown_giver icon_text" '.$show.' onclick="g_dropdown(\'more_options\',0,[\'list\'],this,this)">'.$la['moreoptions'].'<span class="icon icondrop"></span></span>';  
}

//Creates Link to hide/show Rows
function link_column($show, $column, $text=false) {
  global $la;
  $ret='<span title="'. $la['hide'] .'" class="link icon hidden iconhide" onclick="javascript: displaycolumn('.$show.',\''.$column.'\')">'.$text.'</span>';
  return $ret;
}

//Creates Link to edit registers
function link_edit() {
  global $here,$la;
  $ret=' <a href="'.$here->path(2).'edit">'.$la['edit'].'</a> ';
  return $ret;  
}

//Creates Link to keyword
function link_key($key, $text=NULL) {
  if($text==NULL) { $text=$key; }
  global $here,$la;
  $ret=' <a href="'.$here->path().$key.'">'.$la[$text].'</a>';
  return $ret;  
}

//Creates a backlink
function link_back() {
  global $la;
  $ret=' <a href="javascript: history.back()">'.$la['back'].'</a>';
  return $ret;  
}
?>
