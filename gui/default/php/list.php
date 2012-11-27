<?
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: list.php
//theme: default
//description: functions to write lists
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES :
//////////////////////////////

//////////
//LISTS
//////////
// functions should start with list_

require_once( GUI.'php/utility.php' );
require_once( GUI.'php/link.php' );
require_once( GUI.'php/write_one.php' );

//////////
//GENERIC
//options: registerid, nocheckbox, addnew, class
//just make a frame. Let write_one_<type>(id, name, $options) do the work
function list_generic($type, $options='', $list=0) {
  global $plk_la;

  //load options
  $options = u_param($options);

  //Load list;
  //tries to load list from get_<type> with the registerid
  if($list==0 && $options['loading']!=1) {
    $params['count'] = 1;
    if( $options['registerid'] ) {
      $params['registerid'] = $options['registerid'];
    }
    $list=plk_request('get_'.$type, $params);
  }

  echo '<div class="contentbox ', $options['class'] ,'" id="', $type ,'_content">';
    if($options['nocheckbox'] != 1) { 
      echo '<input type="checkbox" value="1" name="all', $type ,'">'; 
    }
    echo '<span class="title ', $type ,'title">',$plk_la[$options['title']],"</span>"; 
    if( $options['help'] ) { echo link_help($options['help']); } //show help link
    echo '<ul class="', $type ,'_list" id="', $type ,'list">';
      if( $options['loading']==1 ) { echo $plk_la['loading']; } 
      //call write_one_<type>
      else {
        call_user_func('write_one_'.$type, $list, $options);
      }
    echo '</ul>';
    //add new link
    if( $options['addnew'] == 1 ) {
      echo "<span class='link icon_text' onclick='do_action(0,\"", $type ,"\",\"add\",this)'><span class='icon iconplus'></span>",$plk_la['new'.$type],"</span>";
    }  
  echo '</div>';
}

//////////
//GROUP

function ajax_list_group($r) { return list_group($r); }
function list_group($registerid) {
  global $plk_la;
?>
  <div class="contentbox" id="group_content">
    <span class="title grouptitle event_hidelast"><?=$plk_la['groups'] ?>
      <span class="hidden">
      <?php
        //more/less groups
        echo link_generic('action=more&type=group&id='.$registerid.'&class=icon iconplus&title=moregroups');
        echo link_generic('action=less&type=group&id='.$registerid.'&class=icon iconminus&title=lessgroups');   
      //don't break line below. (lastchild will be \n)?>
      </span></span>
    <ul class="grouplist">
      <?php   
        $getgroup=plk_request('get_reg_info',array('registerid' => $registerid));
        $groupcount=$getgroup['groupcount'];
        $grouplock=explode('?',$getgroup['grouplock']);

        //count words in groups
        $params=array(
          'registerid' => $registerid,
          'count' => 'group'
        );
        $wordnum=plk_request('get_word', $params);

        //iterate groups
	      for($j=1; $j<=$groupcount+2; $j++) {
          if( $j-$groupcount==1 ) { $i='af'; $text=$plk_la['af']; } //af
          elseif( $j-$groupcount==2 ) { $i='ar'; $text=$plk_la['ar']; } //ar
          else { $i=$j; $text=$plk_la['group'].' '.$i; } //number

          if( NULL !== $wordnum['groupid'] ) {
            $index = array_search($i, $wordnum['groupid']);  
          } else {
            $index = false;
          }
        
          if( $index===false ) { $fullcount = 0; }
          else { $fullcount = $wordnum['wordcount'][ $index ]; }
          $c = $fullcount > 0;
          if($i>1) { //show grouplock
            $plus='/<span id="grouplock_'.$i.'" class="grouplock_'.$i.'">'.$grouplock[$i-2].'</span>'; 
          } else { $plus=''; }
          echo '<li class="group_', $i ,' count_', $fullcount ,' event_hidelast">';
	        echo '<span ';
          if($c) { echo ' class="link" title="', $plk_la['show'] ,'" onclick="do_action(\'',$i,'\',\'group\',\'goto\')" ';  //make link if not empty
          }
          echo '>';
            echo $text;
            u_counter('group', $i, $fullcount, $plus);
          echo '</span>';

          //help link for grouplock
          if($i == 2) {
            echo link_help('grouplock');
          }

          //options
          echo '<span id="group_', $i ,'_options" class="hidden options">';
            if($c) {                     //option to query
              echo link_generic('action=query&type=group&id=\''.$i.'\'&class=icon iconplay&title=query');
            }
            if( $i>1 ) {                 //option to edit grouplock
              echo link_generic('action=edit&type=grouplock&id=\''.$i.'\'&class=icon icone&title=edit&where=$(\'grouplock_'.$i.'\')');
            }
            if($i == 1 || $i == 'af') { //option to add word 
              echo link_generic('action=addword&type=group&id=\''.$i.'\'&class=icon iconplus&title=addword');
            }
          echo '</span>';

          echo '</li>';
	      }
      ?>
    </ul>
  </div>
<?php
}

//////////
//SAVE

function list_save($registerid) {
  global $plk_la;
  $savelist=plk_request('get_save',array('count'=>1, 'registerid' => $registerid));
  if($savelist['count']>0) {
  ?>
  <div class="contentbox">
    <span class="title savetitle"><?=$plk_la['savepoints'] ?></span>
    <ul class="grouplist">
      <?php  
      //without save
      $wordnum=plk_request('get_word',array('count'=>'1','registerid'=>$registerid, 'withoutid'=>'save'));
      if($wordnum['wordnum'][0]>0) {
        echo '<li class="link without_save" onclick="do_action(\'save\',\'without\',\'goto\')">';
          echo $plk_la['withoutsave'];
          u_param('without', 'save', $wordnum['wordnum'][0]);
        echo '</li>';
      } 

      //savepoints:
	    for($i=0;$i<$savelist['count'];$i++) {
        //show anyway. should be deleted if empty
        echo '<li class="save_',$savelist['id'][$i],'_remove event_hidelast">';
        echo '<span class="save_',$savelist['id'][$i],' link" onclick="do_action(\'',$savelist['id'][$i],'\',\'save\',\'goto\')">';
          echo $savelist['name'][$i];
          echo u_counter('save', $savelist['id'][$i], $savelist['savecount'][$i]);
        echo '</span>';
        echo '<span id="save_', $savelist['id'][$i] ,'_options" class="hidden options">';
        echo link_generic('action=query&type=save&id='.$savelist['id'][$i].'&class=icon iconplay&title=query');
        echo link_generic('action=delete&type=save&id='.$savelist['id'][$i].'&class=icon iconx&title=delete');
        echo '</span>';
        echo '</li>';
	    }
      ?>
    </ul>
  </div>
  <?php
  }
}

//////////
//WORDCLASS

function list_wordclass($registerid) {
  global $plk_la;
  ?>
  <div class="contentbox">
    <span class="title wordclasstitle"><?=$plk_la['wordclass'] ?></span>
    <ul class="wordclasslist">
      <?php 
      $len = count( $plk_la['classname'] );

      $wordnum = plk_request('get_word',array('count'=>'wordclass', 'registerid' => $registerid));

	    for($i=0; $i<$len; $i++) {
        $index = array_search($i, $wordnum['wordclassid']);          
        if( $index===false ) { $fullcount = 0; }
        else { $fullcount=$wordnum['wordcount'][$index]; }
        if($fullcount>0) {
          echo '<li class="wordclass_', $i ,' link" onclick="do_action(\'', $i ,'\',\'wordclass\',\'goto\')">';
          echo $plk_la['classname'][$i];
          u_counter('wordclass', $i, $fullcount);
          echo '</li>';
        }
	    }
      ?>
    </ul>
  </div>
<?php
}

//////////
//TAG
function list_tag($registerid, $taglimit=NULL) {
  global $plk_la;
  $taglist=plk_request('get_tag',array('count'=>1,'registerid' => $registerid, 'limit' => $taglimit));
  if ($taglist['count']>0):
    ?>
    <div class="contentbox">
      <span class="title tagtitle"><?=$plk_la['tags'] ?></span>
      <ul class="taglist">
        <?php
          //without tag
          $wordnum=plk_request('get_word',array('count'=>'1','registerid'=>$registerid, 'withoutid'=>'tag'));
          $wordnum=$wordnum['wordcount'][0];

          //show list if there are words
          if($wordnum>0) {
            echo '<li class="without_tag link nowrap" onclick="do_action(\'tag\',\'without\',\'goto\')">';
              echo $plk_la['withouttag'];
              u_counter('without', 'tag', $wordnum);
            echo '</li>, ';
          }

          //count number of words with that tag as $wordnum 
	        for($i=0;$i<$taglist['count'];$i++) {

            $tagname=$taglist['name'][$i];
            echo '<li class="tag_', $taglist['id'][$i] ,' link nowrap" onclick="do_action(\'', $taglist['id'][$i] ,'\',\'tag\',\'goto\')">';
              echo $tagname;
              u_counter('tag', $taglist['id'][$i], $taglist['tagcount'][$i]);
            echo '</li>';

            if( $i!==$taglist['count']-1 ) { echo ', '; }
	        }
        ?>
      </ul>
    </div>
    <?php
  endif;
}

//////////
//VERB

//Writes a list of verbs. With given verblist, or load new ($verblist=0)
//options: registerid, title, nocheckbox
function list_verb($options=NULL, $verblist=0) {
  global $plk_la;
  if( is_string($options) ) { parse_str($options); }
  elseif( DEBUG ) { 
    echo 'DEBUG ERROR: First parameter must be a string in write_verb_list.'; 
    return false;
  }
  //defaults:
  if( $title == NULL ) { $title = 'verbs'; }

  //Load Verbs;
  if($verblist==0) {
    if( $registerid ) {
      $verblist=plk_request('get_verblist',array('registerid' => $registerid));
    } elseif( DEBUG ) { 
      echo 'DEBUG ERROR: verblist or registerid must be given in write_verb_list.';
      return false;
    }
  }

  //print list
  if( $verblist['count']!=0 ) {

    echo '<div id="verblist" class="contentbox">'; 
    if($nocheckbox != 1) { echo '<input type="checkbox" value="1" name="allverb">'; }
    echo '<span class="title verbtitle">'.$plk_la[$title]."</span>"; 
    echo '<ul class="verblist">';
    for($i=0;$i<$verblist['count'];$i++) { 
      $id = & $verblist['id'][$i]; //shortcut
      echo '<li>';
      if($nocheckbox != 1) { 
        echo '<input type="checkbox" value="'.$id.'" name="verbid[]" id="check_verb_'.$id.'">';
      }
      echo '<span class="link" onclick="do_action(\'', $id ,'\', \'verb\', \'goto\')">';
        echo $verblist['wordfore'][$i];
      echo '</span></li>';
    }
    echo '</ul></div>';
  }
}
?>
