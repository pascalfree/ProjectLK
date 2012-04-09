<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: menu.php
//theme: default
//description: functions to write the menu on top
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES :
//////////////////////////////

//////////
//MENU
//////////
// functions should start with menu_

require_once( GUI.'php/utility.php' );
require_once( GUI.'php/link.php' );

//helper
//create menu with:
//type, id, name, dropdown [array], showall [1/0]
//$name, $id, $type, $dropdown=NULL
function create_menu($p) {
  global $la;
  
  $p = u_param($p);
  //defaults
  if( !$p['name'] ) { $p['name'] = get_name($p['type'], $p['id']); }

  //Print   
  $out = '<span class="path_container event_hidelast';
  if( $p['showall']==1 ) { $out .= ' event_hidefirst'; } //hide the x
  $out .= '">';
    //show link to show all of this type
    if($p['showall']==1) {
      $out .= link_generic('title=removefrompath&action=goto&id=null&type='. $p['type'] .'&class=icon iconx');
    } else { $out .= '<span class="icon"></span>'; } //spacemaker
    //print name
    $out .= '<span id="mn_'.$p['type'].'"'; 
    if($nolink != 1) { $out .= 'class="link '.$p['type'].'_'.$p['id'].'" onclick="do_action(\''.$p['id'].'\',\''.$p['type'].'\',\'force_goto\')"'; }
    $out .= 'title="'.$la[$p['type']].'">'.$p['name'].'</span>';
    if($p['dropdown']) { 
      //make json string
      if( is_array($p['dropdown']) ) { $p['dropdown'] = json_encode($p['dropdown']); }
      //print dropdown
      $out.="<span class='link icon icondrop' onclick='g_dropdown(\"".$p['type']."\",\"".$p['id']."\",".$p['dropdown'].",Element.previous(this))' onmouseout='hide_dropdown()'></span>"; 
    } else { $out .= '<span class="icon"></span>'; } //spacemaker
  $out .= '</span>';
  if( $p['help'] ) { $out .= link_help($p['help'],1); }
  return $out;
} 

//also ajax
function ajax_navigator($here) {
  menu_navigator($here);
}
function menu_navigator($here) {
  global $la, $you;

  $path=NULL;
  //Pfad
  if($you -> name != NULL) {
    //type, id, name, dropdown [array], showall [1/0]
    $path[0]=create_menu('type=user&name='. $you -> name .'&id'. $you -> id .'&dropdown=["settings","import","export","hr","logout"]');
  }
  
  if($here['registerid']!=NULL) { 
    $path[1]=create_menu('type=register&id='.$here['registerid'].'&dropdown=["rename","options","delete","hr","list"]');
  }

  if($here['groupid']!=NULL) { 
    if(is_numeric($here['groupid'])) {
      $groupname = $la['group'].' '.$here['groupid'];
    } else { $groupname = $la[ $here['groupid'] ]; }
    $path[2]=create_menu('type=group&id='.$here['groupid'].'&name='.$groupname.'&dropdown=["list","all"]&showall=1');
  }

  if($here['saveid']!=NULL) { 
    $path[]=create_menu('type=save&id='.$here['saveid'].'&dropdown=["delete","hr","list","without","all"]&showall=1');
  }

  if($here['tagid']!=NULL) { 
    $path[]=create_menu('type=tag&id='.$here['tagid'].'&dropdown=["list","without","all","taglist"]&showall=1');
  } 

  if($here['wordclassid']!=NULL) { 
    $path[]=create_menu('type=wordclass&id='.$here['wordclassid'].'&name='.$la['classname'][$here['wordclassid']].'&dropdown=["list","all"]&showall=1'); 
  }

  if($here['withoutid']!=NULL) { 
    $path[] = create_menu('type=without&id='.$here['withoutid'].'&name='. $la['without'.$here['withoutid']].'&showall=1');
  }

  if($here['searchid']!=NULL) { 
    $path[] = create_menu('type=search&id='.$here['searchid'].'&name='.$here['searchid'].'&showall=1');
  }
  if($here['queryid']!=NULL) { 
    $path[] = create_menu('name='.$la['query'].'&nolink=1'); 
  }	

  if($here['keyoption']=='show') { $addpath=($here['hints']?'&help=show':''); } //pass force for ajax

  if($here['keyoption']=='verb') { 
    $path[] = create_menu('type=keyoption&id=verb&name='. $la['verb'] .($here['hints']?'&help=verb':''));
  } elseif($here['keyoption']!=NULL) { 
    $path[] = create_menu('type=keyoption&id='.$here['keyoption'].'&name='.urlencode($la[$here['keyoption']]).'&nolink=1'. $addpath); 
  }
  if($here['formid']!=NULL) { 
    $path[] = create_menu('type=form&id='.$here['formid'].'&showall=1&dropdown=["list","all"]');
  }
  if($here['personid']!=NULL) { 
    $path[] = create_menu('type=person&id='.$here['personid'].'&showall=1&dropdown=["list","all"]');
  }

  if($path!=NULL) {
    foreach($path as &$val) { $val=urldecode($val); }
    $outpath = implode('/', $path).'/';
    echo link_help('menu', $you -> hints), $outpath;
  }
}
?>
