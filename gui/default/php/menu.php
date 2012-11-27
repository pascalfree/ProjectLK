<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: menu.php
//theme: default
//description: functions to write the menu on top
//Author: David Glenck
//Licence: GNU General Public Licence v3 (see licence.txt in Mainfolder)
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
  global $plk_la;
  
  $p = u_param($p);
  //defaults
  if( !$p['name'] ) { $p['name'] = plk_util_getName($p['type'], $p['id']); }

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
    $out .= 'title="'.$plk_la[$p['type']].'">'.$p['name'].'</span>';
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
function ajax_navigator($plk_here) {
  menu_navigator($plk_here);
}
function menu_navigator($plk_here) {
  global $plk_la, $plk_you;

  $path=NULL;
  //Pfad
  if($plk_you -> name != NULL) {
    //type, id, name, dropdown [array], showall [1/0]
    $path[0]=create_menu('type=user&name='. $plk_you -> name .'&id'. $plk_you -> id .'&dropdown=["settings","import","export","hr","logout"]');
  }
  
  if($plk_here['registerid']!=NULL) { 
    $path[1]=create_menu('type=register&id='.$plk_here['registerid'].'&dropdown=["rename","options","delete","hr","list"]');
  }

  if($plk_here['groupid']!=NULL) { 
    if(is_numeric($plk_here['groupid'])) {
      $groupname = $plk_la['group'].' '.$plk_here['groupid'];
    } else { $groupname = $plk_la[ $plk_here['groupid'] ]; }
    $path[2]=create_menu('type=group&id='.$plk_here['groupid'].'&name='.$groupname.'&dropdown=["list","all"]&showall=1');
  }

  if($plk_here['saveid']!=NULL) { 
    $path[]=create_menu('type=save&id='.$plk_here['saveid'].'&dropdown=["delete","hr","list","without","all"]&showall=1');
  }

  if($plk_here['tagid']!=NULL) { 
    $path[]=create_menu('type=tag&id='.$plk_here['tagid'].'&dropdown=["list","without","all","taglist"]&showall=1');
  } 

  if($plk_here['wordclassid']!=NULL) { 
    $path[]=create_menu('type=wordclass&id='.$plk_here['wordclassid'].'&name='.$plk_la['classname'][$plk_here['wordclassid']].'&dropdown=["list","all"]&showall=1'); 
  }

  if($plk_here['withoutid']!=NULL) { 
    $path[] = create_menu('type=without&id='.$plk_here['withoutid'].'&name='. $plk_la['without'.$plk_here['withoutid']].'&showall=1');
  }

  if($plk_here['searchid']!=NULL) { 
    $path[] = create_menu('type=search&id='.$plk_here['searchid'].'&name='.$plk_here['searchid'].'&showall=1');
  }
  if($plk_here['queryid']!=NULL) { 
    $path[] = create_menu('name='.$plk_la['query'].'&nolink=1'); 
  }	

  if($plk_here['keyoption']=='show') { $addpath=($plk_you->hints?'&help=show':''); } //pass force for ajax

  if($plk_here['keyoption']=='verb') { 
    $path[] = create_menu('type=keyoption&id=verb&name='. $plk_la['verb'] .($plk_you->hints?'&help=verb':''));
  } elseif($plk_here['keyoption']!=NULL) { 
    $path[] = create_menu('type=keyoption&id='.$plk_here['keyoption'].'&name='.urlencode($plk_la[$plk_here['keyoption']]).'&nolink=1'. $addpath); 
  }
  if($plk_here['formid']!=NULL) { 
    $path[] = create_menu('type=form&id='.$plk_here['formid'].'&showall=1&dropdown=["list","all"]');
  }
  if($plk_here['personid']!=NULL) { 
    $path[] = create_menu('type=person&id='.$plk_here['personid'].'&showall=1&dropdown=["list","all"]');
  }

  if($path!=NULL) {
    foreach($path as &$val) { $val=urldecode($val); }
    $outpath = implode('/', $path).'/';
    echo link_help('menu'), $outpath;
  }
}
?>
