<?php 
////////////
//local functions

  //addword
  function ajax_addword($here) {
    global $la; 
  ?>
    <div class="middlebox">
    <span class="iconx link" onclick="tab_switch('show')"></span><span class="title"><?=$la['addword'] ?></span>
    <form id="addword" method="POST" action="" onsubmit="do_action(0,'word','add',this); return false;">

    <label for="newregister"><?=$la['register'] ?>:</label>
    <select name="registerid" size=1 id="newregister">
    <?php
      $registers = request('get_register');
      for($i=0; $i<$registers['count']; $i++) {
        echo '<option value="', $registers['id'][$i] ,'"';
          if( $here['registerid'] == $registers['id'][$i] ) { echo ' selected '; } //chosen one  
        echo '>', $registers['name'][$i] ,'</option>';
      }
    ?>    
    </select><br>

    <label for="newgroup"><?=$la['group'] ?>:</label>
    <select name='newgroup' size=1 id="newgroup">
    <option value="1"><?=$la['group'] ?> 1</option>
    <option value="af" <?php if($here['groupid']=='af') { echo 'selected'; } ?>><?=$la['af'] ?></option>
    </select><br>

    <label for="newwordfirst"><?=$la['lang'] ?>:</label><?=link_help('syntax') ?><input type="text" name="newwordfirst" id="newwordfirst"></input><br>
    <label for="newwordfore"><?=$la['fore'] ?>:</label><input type="text" name="newwordfore" id="newwordfore"></input><br>
    <label for="newtags"><?=$la['tags'] ?>:</label><?=link_help('tag') ?><input type="text" name="newtags" id="newtags" value="<?=$tags ?>"></input><br>
    <label for="newsentence" class="satzlabel"><?=$la['phrase'] ?>:</label><textarea name="newsentence" id="newsentence"></textarea><br>
    <label for="newwordclass"><?=$la['wordclass'] ?>:</label>
    <select name='newwordclass' size=1 id="newwordclass">
    <?php
      for($i=0; $i<6; $i++) {
        echo '<option value=',$i;
          if( $here['wordclassid']==$i ) { echo ' selected '; } //chosen one  
        echo '>',$la["classname"][$i],'</option>';
      }
    ?>
    </select><br>
    <input type="hidden" name="force" value="0"></input>

    <input type="submit" value="<?=$la['create'] ?>"></submit>

    </form>
    </div>
  <?php 
  }

////////////
//load page

//get the wordlist
//limited in descending order for addword
if( $here->page == "add" ) {
  $now=time();
  $range=3*60*60; // 3 hours
  $here->timerange = array($now-$range,$now,'DESC');
}
$wordlist=request('get_word', (array) $here);   //load

//create a toolbar
$toolbar=link_generic('action=addword&class=intab_show intab_overview&icon=plus&text='.urlencode($la['addword'])); //add words
if($wordlist['count']>0) { //query
  $toolbar.=link_generic('action=query&type=0&id=0&icon=play&text='.urlencode($la['query']));
}
//verbtable link
$toolbar.=link_generic('action=force_goto&type=keyoption&id=\'verb\'&text='.$la['verbs']);
$toolbar.=link_more_options($here->page=='show'); //show only if this is show

//create tabs
$tabs=array('show','overview');

// 20120407 fix : disable toolbar in global search
if( $here->page == 'search' && $here->registerid == NULL ) {
  $tabs = array();
  $toolbar = link_back();
} 

load_head($toolbar, $tabs);

//////////
//content

//----
//Tab addword
echo '<div id="content_tab_add" class="intab_add" style="display: ', $here->page=="add" ? 'block' : 'none' ,';">';
  ajax_addword( (array) $here );

  //show text that these are the last words added
  $invis=$wordlist['count']==0?' style="display:none"':'';
  echo '<p id="lastlabel" class="with_wordlist" '.$invis.'>'.$la['lastadded'].'</p>';
echo '</div>';

//----
//Tab show
echo '<div id="content_tab_show" class="intab_show intab_add intab_search" style="display: ', ($here->page=="show" ||  $here->page=="add" || $here->page=="search" )  ? 'block' : 'none' ,';">';

//write the wordtable
wt_form($wordlist);

//----
//Tab overview
echo '</div><div id="content_tab_overview" class="intab_overview" style="display: ', ($here->page == "overview" || $here->page=="search" ) ? 'block' : 'none' ,';">';

//do it later with ajax

echo '</div>';

load_foot('show');
?>
