<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: wordtable.php
//theme: default
//description: functions for the wordtable view
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//UPDATES : 
//////////////////////////////

//////////
//WORDTABLE
//////////
// functions should start with wt_

require_once( GUI.'php/dropdown.php' );
require_once( GUI.'php/link.php' );
require_once( GUI.'php/write_one.php' );

//helperfunction: create entry with dropdown
function ajax_wt_entry($i,$v,$t,$a=NULL,$ad='',$d=array('edit')) {
  wt_entry($i,$v,$t,$a,$ad,$d);
}
function wt_entry($id, $value, $type, $alt=NULL, $addtdclass='',$dropdown=array('edit'), $title='') {
  global $plk_la;
  //default
  if($alt==NULL) { $alt=$id; }
  
  //write td
  echo '<td id="',$type,'_',$id,'" class="',$addtdclass,' event_hidelast">';
    //write value
    echo "<span id='",$type,"_span_",$id,"' title='", $title ,"' class='tab_",$type,"_",$id," ",$type,"_",$id,"' ondblclick='do_action(",json_encode($alt),",\"",$type,"\",\"edit\",this)'>";
      echo $value;
    echo '</span>';

    //write a dropdown or an edit link
    $exceptions = array('wordfore','wordfirst','sentence','kword');
    if( in_array( $type, $exceptions ) ) {
      echo link_generic('action=edit&id='. str_replace('"',"'",json_encode($alt)) .'&type='. $type .'&where=Element.previous(this)&class=link icon icone hidden&title=edit');
    } else { 
      //a dropdown with more options
      drop_button($alt, $type, $dropdown,'Element.previous(this)');
    }
  echo '</td>';
}

//calling directly via ajax
function ajax_wordform($params, $checked=0, $register=0) {
  $wordlist = plk_request('get_word',(array) $params);
  wt_line($wordlist, $checked, $register);
}
//writes some lines of the wordform
//checked: on/off
//register: show/hide
function wt_line($wordlist, $checked=0, $register=0) {
  global $plk_la;
  
  //Load registernames if necessary
  if($register==1) {
    $get_reg=plk_request('get_register');
    for($i=0;$i<$get_reg['count'];$i++) {
      $regname[$get_reg['id'][$i]]=$get_reg['name'][$i];
    }
  }

  //go through all words in list
  for($i=0;$i<$wordlist['count'];$i++) {
    $tid=$wordlist['id'][$i]; //shortcut id
          
    //begin line
    echo '<tr id="wordlist_tr_',$tid,'" class="word_',$tid,'_remove ';
    if($checked==1) { echo 'list_marked'; }
    echo '">';
    //with or without checkbox
    //if($checkbox==1) { 
      echo '<td onclick="checkthisbox('.$tid.')" onmouseout="multicheck('.$tid.')">';
      echo '<input type="checkbox" id="check_'.$tid.'" name="wordid[]" value="'.$tid.'" onclick="javascript: this.checked=!this.checked" ';
      if($checked==1) { echo 'checked="checked"'; }
      echo '>';
      echo '</td>'; 
    //}
    //with or without register
    if($register==1) { echo '<td id="register_'.$tid.'">'.$regname[$wordlist['registerid'][$i]].'</td>'; }
    //some entries
    //group: special for ar and af
    if( !is_numeric($wordlist['groupid'][$i]) ) { 
      $name = $plk_la['a_'.$wordlist['groupid'][$i]]; 
      $title = $plk_la[$wordlist['groupid'][$i]]; 
    } else {
      $name = $wordlist['groupid'][$i]; 
      $title = $plk_la['group'].' '.$wordlist['groupid'][$i];
    }
    wt_entry($tid, $name, 'group',array($tid,$wordlist['groupid'][$i]),'',array('show','edit'), $title);

    //word and sentense
    wt_entry($tid, $wordlist['wordfirst'][$i], 'wordfirst');
    wt_entry($tid, $wordlist['wordfore'][$i], 'wordfore');
    wt_entry($tid, $wordlist['sentence'][$i], 'sentence');

    //tags 
    echo '<td id="tag_'.$tid.'" class="event_hidelast">';
      //print each tag
      write_one_tag($tid,$wordlist['tagslist'][$i]['id'],$wordlist['tagslist'][$i]['name']);
      //plusbutton
      echo'<span onclick="do_action(\'',$tid,'\',\'tag\',\'add\',this)" class="link iconplus icon hidden" title="'.$plk_la['add'].'"></span>';
    echo '</td>';

    //worclass
    wt_entry($tid, $plk_la['classname'][$wordlist['wordclassid'][$i]], 'wordclass',array($tid, $wordlist['wordclassid'][$i]),'',array('list_edit','hr','show','edit'));

    //option dropdown
    echo '<td id="options_'.$tid.'" class="opts">';
    if($wordlist['wordclassid'][$i]==2) { 
      drop_button($tid, 'word', array('delete','hr','verbtable'), 'this', NULL, 'hidden');
    } else {
      echo link_generic('action=delete&type=word&id='. $tid .'&class=icon iconx hidden&title=delete');
    }
    echo '</td>';

    //finaly
    echo '</tr>';
  }
}

//Write a form and table of words
function wt_form($wordlist) {
  global $plk_here, $plk_la;

  //the form is still usefull to serialize the checkboxes ?>
  <form id="wordform" name="wordform" action="#" onsubmit="javascript: return false"> 
    <table id='wordlist' class="with_wordlist" style="display: <?=$wordlist['count']==0?'none':'block' ?>" onMouseDown="javascript: local.mouseisdown=1" onMouseUp="javascript: local.mouseisdown=0">
    <?php
      //write the headline
      echo '<thead>';
        echo '<tr class="tabhead event_hidelast_in" id="tabhead">';

        echo '<th><input type="checkbox" id="check_allmarked" name="allmarked" value="1" onclick="javascript: checkbox_checkall(this.checked)"></th>'; // }
        // with register ?
        echo '<th id="register_head" class="event_hidelast" ', $plk_here->registerid!=NULL?'style="display: none':'' ,'"><span id="register_span_head">',$plk_la['register'],'</span>',link_column(0, 'register'),'</th>';
        // titles
        echo '<th id="group_head" class="event_hidelast"><span id="group_span_head">',$plk_la['group'],'</span>',drop_button(0, 'group', array('list','all','hr','hide')),'</th>';
        echo '<th id="wordfirst_head" class="event_hidelast"><span id="wordfirst_span_head">',$plk_la['word'],'</span>',link_column(0, 'wordfirst'),'</th>';
        echo '<th id="wordfore_head" class="event_hidelast"><span id="wordfore_span_head">',$plk_la['fore'],'</span>',link_column(0, 'wordfore'),'</th>';
        echo '<th id="sentence_head" class="event_hidelast"><span id="sentence_span_head">',$plk_la['phrase'],'</span>',link_column(0, 'sentence'),'</th>';
        echo '<th id="tag_head" class="event_hidelast"><span id="tag_span_head">',$plk_la['tags'],'</span>',drop_button(0, 'tag', array('list','without','all','hr','hide')),'</th>';
        echo '<th id="wordclass_head" class="event_hidelast"><span id="wordclass_span_head">',$plk_la['wordclass'],'</span>',drop_button(0, 'wordclass', array('list','all','hr','hide')),'</th>';
        // Dropdown
        echo '<th><span id="column_drop" class="icon icondrop hidden link" onclick="g_dropdown(\'column\', 0, [\'list\'])"></span></th>';
        echo '</tr>';
      echo '</thead>';

      //print words
      echo '<tbody>';
        //load later with ajax
      echo '</tbody>';

      ?>
    </table>
  </form>

<?php
}
?>
